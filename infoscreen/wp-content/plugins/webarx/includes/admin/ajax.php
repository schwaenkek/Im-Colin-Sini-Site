<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used for any admin AJAX interactions.
 */

class W_Admin_Ajax extends W_Core
{
    /**
     * Add the actions required for AJAX interactions.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
        if (isset($_POST['WebarxNonce']) && current_user_can('manage_options') && wp_verify_nonce($_POST['WebarxNonce'], 'webarx-nonce')) {
			// Log tables actions.
			add_action('wp_ajax_users_log_table', array($this, 'users_log_table'));
			add_action('wp_ajax_firewall_log_table', array($this, 'firewall_log_table'));

			// License related actions.
			add_action('wp_ajax_activate_license', array($this, 'activate_license'));
			add_action('wp_ajax_deactivate_license', array($this, 'deactivate_license'));

			// Firewall related actions.
			add_action('wp_ajax_activate_deactivate_firewall', array($this, 'activate_deactivate_firewall'));

			// Hide login related actions.
			add_action('wp_ajax_send_new_url_email', array($this, 'send_new_url_email'));
			
			// API related actions.
			add_action('wp_ajax_save_api', array($this, 'save_api'));
			add_action('wp_ajax_test_api', array($this, 'test_api'));

			// Any other actions.
			add_action('wp_ajax_activate_deactivate_recaptcha', array($this, 'activate_deactivate_recaptcha'));
		}
    }

	/**
	 * Firewall logs pagination.
	 *
	 * @return array
	 */
    public function firewall_log_table()
    {
		// Pull all entries, given parameters.
        global $wpdb;
		$entries = $wpdb->get_results(
			$wpdb->prepare("SELECT a.id, a.ip, a.flag, a.method, a.log_date, case when a.referer IS NULL or a.referer = '' then a.request_uri else a.referer end as referer, a.fid, b.description 
				FROM " . $wpdb->prefix . "webarx_firewall_log AS a
				LEFT JOIN " . $wpdb->prefix . "webarx_logic AS b ON b.id = a.fid
				ORDER BY a.id DESC
				LIMIT %d, %d
			", array($_POST['start'], $_POST['length']))
		);

		// Get total amount of rows.
		$count = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "webarx_firewall_log");
		$firewall_rules = json_decode(get_option('webarx_firewall_rules', ''), true);

		// Modify data if necessary.
		$list = array();
		foreach ($entries as $entry) {
		    foreach ($entry as $key => $value) {
		        if (!in_array($key, array('referer'))) {
			        $entry->$key = sanitize_textarea_field($value);
		        }
			}

			// Attempt to find the block reason.
			$reason = $wpdb->get_var($wpdb->prepare("SELECT cname FROM " . $wpdb->prefix . "webarx_logic WHERE id = %d LIMIT 1", array($entry->fid)));
			if ($reason) {
				$entry->fid = $reason;
			} elseif ($firewall_rules != '') {
				foreach ($firewall_rules as $rule) {
					if (isset($rule['title'], $rule['cat']) && '55' . $rule['id'] == $entry->fid) {
						$entry->fid = $rule['cat'];
						$entry->description = $rule['title'];
					}
				}
			} else {
				$entry->fid = 'Unknown';
			}

			$list[] = $entry;
		}

		// Return output.
		echo json_encode(array(
			'data' => $list,
			'recordsFiltered' => $count,
			'recordsTotal' => $count,
			'draw' => $_POST['draw']
		));
		exit;
    }
    
	/**
	 * Activity logs pagination.
	 *
	 * @return void
	 */
    public function users_log_table()
    {
        // Determine if searching?
		global $wpdb;
		$isSearching = false;
		if (isset($_POST['search']['value']) && $_POST['search']['value'] != '') {
			$val = $_POST['search']['value'];
			$isSearching = true;
			$columns = array('author', 'ip', 'object', 'object_name', 'action');
			$search = "WHERE 1=2 ";
			foreach ($columns as $column) {
				$search .= "OR " . $column . " LIKE '%" . $wpdb->esc_like($val) . "%' ";
			}
		}

        $logs = $wpdb->get_results(
            $wpdb->prepare("SELECT *
				FROM " . $wpdb->prefix . "webarx_event_log " . ($isSearching ? $search : '') . "
				ORDER BY id DESC
				LIMIT %d, %d
			", array($_POST['start'], $_POST['length']))
        );

        $countLogs = $wpdb->get_var("SELECT COUNT(id) FROM " . $wpdb->prefix . "webarx_event_log " . ($isSearching ? $search : ''));

        // Modify data if necessary.
        $list = array();
        foreach ($logs as $log) {
            $list[] = $log;
        }

        // Return output.
        echo json_encode(array(
            'data' => $list,
            'recordsFiltered' => $countLogs,
            'recordsTotal' => $countLogs,
            'draw' => $_POST['draw']
        ));
        exit;
	}

	/**
	 * Test and activate a new license.
	 *
	 * @return void
	 */
	public function activate_license() {
		if (!isset($_POST['clientid'], $_POST['secretkey'])) {
			return;
		}

		// Test the new keys.
		update_option('webarx_api_token', '');
		$results = $this->plugin->activation->alter_license($_POST['clientid'], $_POST['secretkey'], 'slm_activate');
		if ($results) {
			$this->plugin->api->update_license_status();
			die(json_encode($results));
		}
	}

	/**
	 * Test and deactivate an existing license.
	 *
	 * @return void
	 */
	public function deactivate_license()
	{
		if (!isset($_POST['clientid'], $_POST['secretkey'])) {
			return;
		}

		// Test current key and turn off.
		$results = $this->plugin->activation->alter_license($_POST['clientid'], $_POST['secretkey'], 'slm_deactivate');
		if ($results) {
			update_option('webarx_license_activated', false);
			update_option('webarx_api_token', '');
			die(json_encode($results));
		}
	}

	/**
	 * Enable or disable the firewall.
	 * 
	 * @return void
	 */
	public function activate_deactivate_firewall()
	{
		$results = $this->plugin->htaccess->firewall();
		if (!$results) {
			die('fail');
		}

		$firewall_state = $this->plugin->widget->get_firewall_state();
		die($firewall_state > 1 ? '1' : '0');
	}

    /**
     * Send an email to the current logged in user that contains the new admin page URL.
     * 
     * @return void
     */
	public function send_new_url_email()
	{
		$success = $this->plugin->hide_login->send_email();
		die($success ? 'success' : 'fail');
	}
	
	/**
	 * Update the client id/key.
	 * 
	 * @return boolean
	 */
	public function save_api()
	{
		if (!isset($_POST['clientid'], $_POST['secretkey'])) {
			return;
		}

		update_option('webarx_clientid', $_POST['clientid']);
		update_option('webarx_secretkey', $_POST['secretkey']);
		die('Saved');
	}

	/**
	 * Test if the plugin is connected with the API.
	 * 
	 * @return void
	 */
	public function test_api()
	{
		$clientid = get_option('webarx_clientid');
		$secretkey = get_option('webarx_secretkey');
		$results = $this->plugin->api->fetch_access_token($clientid, $secretkey);

		// Immediately send software data to our server to set firewall as enabled.
		// Also immediately download the whitelist file and the firewall rules.
		do_action('webarx_send_software_data');
		do_action('webarx_post_firewall_rules');
		do_action('webarx_post_dynamic_firewall_rules');
		if ($results) {
			die(json_encode($results));
		}
	}

    /**
     * Activate or deactivate reCAPTCHA.
     * 
     * @return void
     */
    public function activate_deactivate_recaptcha()
    {
        // Toggle the state, depending on the current state.
        $onoff = !$this->get_option('webarx_captcha_login_form') ? 1 : 0;
        foreach (array('webarx_captcha_login_form', 'webarx_captcha_registration_form', 'webarx_captcha_reset_pwd_form', 'webarx_pluginedit', 'webarx_move_logs', 'webarx_userenum', 'webarx_basicscanblock', 'webarx_hidewpcontent', 'webarx_hidewpversion') as $option) {
            update_option($option, $onoff);
        }

        die($onoff);
    }
}