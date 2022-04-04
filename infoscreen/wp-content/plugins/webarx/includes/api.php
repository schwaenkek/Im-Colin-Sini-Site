<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used as a base for communicating with the Patchstack API.
 */
class W_Api extends W_Core
{
	/**
	 * @var integer The current blog id.
	 */
	public $blog_id;

	/**
	 * Add the actions required for the API.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		$this->blog_id = get_current_blog_id();
		add_action('webarx_update_license_status', array($this, 'update_license_status'));
		add_action('webarx_send_ping', array($this, 'ping'));
	}

	/**
	 * Get the API token.
	 *
	 * @param string $clientid The API client ID.
	 * @param string $secretkey The API secret key.
	 * @param boolean $freshToken Whether or not to get a fresh token.
	 * @return null|string
	 */
	public function get_access_token($clientid = '', $secretkey = '', $freshToken = false)
	{
		// Get current access token, if it exists.
		$api_token_array = $this->get_blog_option($this->blog_id, 'webarx_api_token', false);

		// If we do not need a fresh token, get the current one if it's not expired.
		if (!$freshToken && !empty($api_token_array) && isset($api_token_array['token'])) {
			$token = $api_token_array['token'];
			$expiresin = $api_token_array['expiresin'];

			if (!$this->has_expired($expiresin)) {
				return $token;
			}
		}

		// Call API and get the new access token.
		$response_data = $this->fetch_access_token($clientid, $secretkey);
		if ($response_data && $response_data->result == 'success') {
			$token = $response_data->message;
			$expiresin = $response_data->expiresin;
			$api_token_array = array('token' => $token, 'expiresin' => $expiresin);
			$this->update_blog_option($this->blog_id, 'webarx_api_token', $api_token_array);
			return $token;
		}

		// If we reach this, it means we were not able to get the access token.
		$this->update_blog_option($this->blog_id, 'webarx_api_token', '');
		return null;
	}

	/**
	 * Fetch the API Token from API Server.
	 *
	 * @param string $clientid The API client ID.
	 * @param string $secretkey The API secret key.
	 * @return string|array
	 */
	public function fetch_access_token($clientid = '', $secretkey = '')
	{
		// Skeleton for the response data.
		$response_data = json_decode(json_encode(array(
			'result' => '',
			'message' => '',
			'expiresin' => '',
		)), false);

		// Determine if the license id/key is set.
		$client_id = $this->get_blog_option($this->blog_id, 'webarx_clientid', false) ? $this->get_blog_option($this->blog_id, 'webarx_clientid', false) : $clientid;
		$client_secret = $this->get_blog_option($this->blog_id, 'webarx_secretkey', false) ? $this->get_blog_option($this->blog_id, 'webarx_secretkey', false) : $secretkey;
		if (empty($client_id) || empty($client_secret)) {
			$response_data->result = 'failed';
			$response_data->message = __('API Keys Missing! Unable to obtain a token.', 'webarx');
			return $response_data;
		}

		// Send a request to our server to obtain the access token.
		$response = wp_remote_post($this->plugin->api_server_url . '/oauth/token', array(
			'method' => 'POST',
			'timeout' => 60,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(),
			'body' => array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'grant_type' => 'client_credentials'
			),
			'cookies' => array(),
		));

		// Stop if we received an error from the API.
		if (is_wp_error($response)) {
			$response_data->result = 'failed';
			$response_data->message = __('Unexpected Error! Unable to obtain a token. ', 'webarx') . $response->get_error_message();
			return $response_data;
		}

		// Parse the result.
		$result = json_decode(wp_remote_retrieve_body($response));
		if (isset($result->access_token)) {
			$response_data->result = 'success';
			$response_data->message = $result->access_token;
			$response_data->expiresin = $result->expires_in;

			// We need to know when the token expires.
			// Defer to 'expires' if it is provided instead.
			if (isset($result->expires_in)) {
				if (!is_numeric($result->expires_in)) {
					$response_data->message = 'expires_in value must be an integer';
					return $response_data;
				}
				$response_data->expiresin = $result->expires_in != 0 ? time() + $result->expires_in : 0;
			} elseif (!empty($result->expires_in)) {
				// Some providers supply the seconds until expiration rather than
				// the exact timestamp. Take a best guess at which we received.
				$expires = $options['expires'];
				if (!$this->isExpirationTimestamp($expires)) {
					$expires += time();
				}
				$response_data->expiresin = $expires;
			}
			return $response_data;
		} elseif (isset($result->error)) {
			$response_data->result = $result->error;
			$response_data->message = __('Unexpected Error! Unable to obtain a token. ', 'webarx') .  $result->message;
			return $response_data;
		}
	}

	/**
	 * Checks if the API token has expired.
	 *
	 * @param integer $expiresin API token expiry.
	 * @return boolean If the token has expired.
	 */
	public function has_expired($expiresin)
	{
		return ($expiresin < (time() + 30));
	}

	/**
	 * Retrieve the status of a license.
	 * 
	 * @return void
	 */
	public function update_license_status()
	{
		// Get current license status and update the representing options.
		$response = $this->send_request('/api/license/verify', 'GET');
		if (isset($response['expires_at'])) {
			$this->update_blog_option($this->blog_id, 'webarx_license_expiry', $response['expires_at']);
		}

		if (isset($response['status'], $response['active']) && ($response['status'] == 'active' || $response['status'] == 'trial') && $response['active'] == true) {
			$this->update_blog_option($this->blog_id, 'webarx_license_activated', true);
			return;
		}

		$this->update_blog_option($this->blog_id, 'webarx_license_activated', false);
		$this->update_blog_option($this->blog_id, 'webarx_api_token', '');
	}

	/**
	 * Send a request to the API with optionally POST data.
	 *
	 * @param string $url
	 * @param string $request
	 * @param array $data
	 * @return void|array If successful array, otherwise void.
	 */
	public function send_request($url, $request, $data = array())
	{
		// Attempt to get the access token.
		$token = $this->get_access_token();
		if (empty($token)) {
			return;
		}

		// Send the remote request using the WordPress built-in method.
		$response = wp_remote_request($this->plugin->api_server_url . $url, array(
			'method' => $request,
			'timeout' => 60,
			'redirection' => 5,
			'httpversion' => '1.0',
			'blocking' => true,
			'headers' => array(
				'Authorization' => 'Bearer ' . $token,
				'LicenseID' => $this->get_blog_option($this->blog_id, 'webarx_clientid', 0),
				'Source-Host' => get_site_url()
			),
			'body' => $data,
			'cookies' => array()
		));

		// Check error or status code.
		if (is_wp_error($response) || wp_remote_retrieve_response_code($response) != 200) {
			$this->update_blog_option($this->blog_id, 'webarx_api_token', '');
			return;
		}

		return json_decode(wp_remote_retrieve_body($response), true);
	}

	/**
	 * Get the firewall rules.
	 *
	 * @return array The firewall rules.
	 */
	public function post_firewall_rule_json()
	{
		// If the request is coming from the API, fetch fresh rules.
		if(isset($_POST['webarx_refresh_rules'])){
			return $this->send_request('/api/get-rules/2?bypass=cache', 'POST');
		}

		return $this->send_request('/api/get-rules/2', 'POST');
	}

	/**
	 * Get the .htaccess rules.
	 *
	 * @param array $settings The settings on which .htaccess rules to get.
	 * @return array The .htaccess rules.
	 */
	public function post_firewall_rule($settings)
	{
		return $this->send_request('/api/rules', 'POST', $settings);
	}

	/**
	 * Send the firewall logs to the API.
	 *
	 * @param array $logs
	 * @return array
	 */
	public function upload_firewall_logs($logs)
	{
		return $this->send_request('/api/logs/log', 'POST', $logs);
	}

    /**
     * Send the activity logs to the server.
	 * 
     * @param array $logs
     * @return array
     */
	public function upload_activity_logs($logs)
    {
        return $this->send_request('/api/activity/log', 'POST', $logs);
    }

	/**
	 * Send WordPress core, theme, plugins versions and information to the API.
	 *
	 * @param array $software
	 * @return array
	 */
	public function upload_software($software)
	{
		return $this->send_request('/api/sw/json', 'POST', $software);
	}

	/**
	 * Update the firewall status.
	 * 
	 * @param array $status
	 * @return array
	 */
	public function update_firewall_status($status)
	{
		return $this->send_request('/api/firewall/update/status', 'POST', $status);
	}

	/**
	 * Update the URL on the API.
	 * 
	 * @param array $url The current URL of the site.
	 * @return array
	 */
	public function update_url($url)
	{
		return $this->send_request('/api/plugin/update/url', 'POST', $url);
	}

    /**
     * Send list of sites and get the id and secret key in response.
     *
     * @param array $sites
     * @return array
     */
    public function get_site_licenses($sites)
    {
        return $this->send_request('/api/multisite-keys', 'POST', $sites);
	}

	/**
	 * Send a ping to the Patchstack API every 3 hours to make sure that the plugin is still running.
	 * 
	 * @return void
	 */
	public function ping()
	{
		$this->send_request('/api/ping', 'POST', array('firewall' => $this->get_option('webarx_basic_firewall') == 1 ? 1 : 0));
	}
}
