<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to activate and deactivate the plugin.
 * Additionally, we use it to run migrations.
 */
class W_Activation extends W_Core
{
	/**
	 * Add the actions required for the activation.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
        add_action('activated_plugin', array($this, 'redirect_activation'), 10, 2);
    }

    /**
     * Redirect the user to our settings page after plugin activation.
     *
     * @param string $plugin The plugin that is activated.
     * @param boolean $network_activation If a network wide activation. (multisite)
     * @return void
     */
    public function redirect_activation($plugin, $network_activation)
    {
        if ($plugin == $this->plugin->basename) {

            // In case of multisite, we want to redirect the user to a different page.
            if ($network_activation) {
                wp_redirect(network_admin_url('admin.php?page=webarx-multisite-settings&tab=multisite&activated=1'));
            } else {
                wp_redirect(admin_url('admin.php?page=' . $this->plugin->name . '&activated=1'));
            }
            exit;
        }
    }

    /**
     * Check if the plugin meets requirements and disable it if they are not present.
     *
     * @return boolean
     */
    public function check_requirements()
    {
        if ($this->meets_requirements()) {
            return true;
        }

        // Add a dashboard notice.
        add_action('all_admin_notices', array($this, 'requirements_not_met_notice'));
        return false;
    }

    /**
     * Check that all plugin requirements are met.
     * 
     * @return boolean
     */
    public function meets_requirements()
    {
        // Check to see if we can access the API.
        $response = wp_remote_request($this->plugin->api_server_url, array('method' => 'GET', 'timeout' => 10, 'redirection' => 5));

        // Check if we can access the API.
        if (is_wp_error($response)) {
            $this->activation_errors[] = 'We were unable to contact our API server in order to activate your license. Please contact your host and ask them to make sure that outgoing connections to api.webarxsecurity.com and api.patchstack.com are not blocked.<br>Additional error message to give to your host: ' . $response->get_error_message();
            return false;
        }

        // Do checks for required classes / functions or similar.
        // Add detailed messages to $this->activation_errors array.
        if (version_compare(phpversion(), '5.3.0', '<')) {
            $this->activation_errors[] = 'Please update the PHP version on your host to at least 5.3.0. Ask your host if you do not know what this means.';
            return false;
        }

        if (!in_array('curl', get_loaded_extensions())) {
            $this->activation_errors[] = 'Please enable the cURL extension (php_curl) on your server. Ask your host if you do not know what this means.';
            return false;
        }

        global $wp_version;
        if (version_compare($wp_version, '4.3.0', '<')) {
            $this->activation_errors[] = 'Please upgrade your WordPress site to at least 4.3.0 first.';
            return false;
        }

        return true;
    }

    /**
     * Adds a notice to the dashboard if the plugin requirements are not met.
     *
     * @return void
     */
    public function requirements_not_met_notice()
    {
        // Deactivate the plugin.
        deactivate_plugins($this->plugin->basename);

        // Compile default message.
        $default_message = sprintf(__('Patchstack is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'webarx'), admin_url('plugins.php'));
        $details = null;

        // Add details if any exist.
        if ($this->activation_errors && is_array($this->activation_errors)) {
            $details = '<small>' . implode('</small><br /><small>', $this->activation_errors) . '</small>';
        }
        ?>
        <div id="message" class="error">
            <p><?php echo wp_kses_post($default_message); ?></p>
            <?php echo wp_kses_post($details); ?>
        </div>
        <?php
    }
        
    /**
     * Activate the plugin.
     * 
     * @param Webarx $core
     * @return void
     */
    public function activate($core)
    {
        // Bail early if requirements are not met.
        if (!$this->check_requirements()) {
            $this->requirements_not_met_notice();
            exit;
        }

        // Make sure any rewrite functionality has been loaded.
        $this->migrate();
        add_option('webarx_first_activated', '1');

        // Activate the license.
        if ($this->plugin->client_id != 'THE_WEBARX_CLIENT_ID' && $this->plugin->private_key != 'THE_WEBARX_PRIVATE_KEY') {
            $this->alter_license($this->plugin->client_id, $this->plugin->private_key, 'slm_activate');
        } else if (get_option('webarx_clientid', false) != false && get_option('webarx_secretkey', false) != false) {
            $this->alter_license(get_option('webarx_clientid'), get_option('webarx_secretkey'), 'slm_activate');
        }

        // Update firewall status after activating plugin
        $webarxApi = new W_Api($core);
        $token = $webarxApi->get_access_token();
        if (!empty($token)) {
            $webarxApi->update_firewall_status(array('status' => 1));
            $webarxApi->update_url(array('plugin_url' => get_option('siteurl')));
        }

        // Immediately send software data to our server to set firewall as enabled.
        // Also immediately download the whitelist file and the firewall rules.
        do_action('webarx_send_software_data');
        do_action('webarx_post_firewall_rules');
        do_action('webarx_post_dynamic_firewall_rules');
    }

    /**
     * Used to activate an individual license on multisite/network.
     * 
     * @param object $site
     * @param array $license
     * @return void
     * 
     */
    public function activate_multisite_license($site, $license)
    {
        // Build the Patchstack tables on the site.
        $this->migrate(null, $site->id);

        // Add the options to given site.
        foreach ($this->plugin->admin_options->options as $name=>$value) {
            add_blog_option($site->id, $name, $value);
        }

        // Set the client id and secret key.
        update_blog_option($site->id, 'webarx_clientid', $license['id']);
        update_blog_option($site->id, 'webarx_secretkey', $license['secret']);
        $this->plugin->api->blog_id = $site->id;

        // Activate the license and update firewall status after activating the plugin.
        $token = $this->plugin->api->get_access_token($license['id'], $license['secret'], true);
        if (!empty($token)) {
            $this->plugin->api->update_firewall_status(array('status' => $this->get_option('webarx_basic_firewall') == 1));
            $this->plugin->api->update_url(array('plugin_url' => get_blog_option($site->id, 'siteurl')));

            // If we have an access token, tell our API that the firewall is activated
            // and the current URL of the site.
            update_blog_option($site->id, 'webarx_license_activated', '1');
            $this->plugin->api->update_license_status();

            // This will trigger the software synchronization action.
            wp_remote_get(get_site_url($site->id), array('sslverify' => false));
        }

        // Make sure to switch back to the current blog id.
        $this->plugin->api->blog_id = get_current_blog_id();
    }

    /**
     * Build the required Patchstack tables.
     *
     * @param null|string $ver The version to upgrade to.
     * @param null|integer $site_id The blog id to perform the upgrades on.
     * @return void
     */
    public function migrate($ver = null, $site_id = null)
    {
        global $wpdb;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $charset_collate = $wpdb->get_charset_collate();
        $prefix = $site_id != null ? $wpdb->get_blog_prefix($site_id) : $wpdb->prefix;

        // The following conditions will only execute if Patchstack is installed because of an update
        // and if we need to perform migrations.
        if ($ver !== null && $ver != '1.0.7' && file_exists(dirname(__FILE__) . '/migrations/v' . str_replace('.', '', $ver) . '.php')) {
            require_once dirname(__FILE__) . '/migrations/v' . str_replace('.', '', $ver) . '.php';
            return;
        }

        // Require the base migration.
        require_once dirname(__FILE__) . '/migrations/base.php';
    }

    /**
     * Check if the database version of the plugin is running behind.
     * If so, run the migrations up until the latest version.
     *
     * @return void
     */
    public function migrate_check()
    {
        $db_version = get_option('webarx_db_version', false);
        foreach (array('1.0.7', '1.0.8', '1.2', '1.3.5', '2.0.0', '2.1.0') as $version) {
            if (version_compare($db_version, $version, '<')) {
                $this->migrate($version);
            }
        }
    }

    /**
     * Perform cleanup when the plugin is deactivated.
     * 
     * @return void
     */
    public function deactivate()
    {
        // Update firewall status after de-activating plugin
        $webarxApi = new W_Api($this);
        $token = $webarxApi->get_access_token();
        if (!empty($token)) {
            $webarxApi->update_firewall_status(array('status' => 0));
        }

        // Clear all Patchstack scheduled tasks.
        $tasks = array('webarx_zip_backup', 'webarx_send_software_data', 'webarx_send_hacker_logs', 'webarx_send_visitor_logs', 'webarx_send_event_logs', 'webarx_reset_blocked_attacks', 'webarx_post_firewall_rules', 'webarx_post_dynamic_firewall_rules', 'webarx_update_license_status', 'webarx_update_plugins', 'webarx_send_ping');
        foreach ($tasks as $task) {
            wp_clear_scheduled_hook($task);
        }

        // Cleanup the .htaccess file.
        $this->plugin->htaccess->cleanup_htaccess_file();        
    }

    /**
     * Activate or deactivate a license on the current site.
     *
     * @param integer $id
     * @param string $secret
     * @param string $action
     * @return array
     */
    public function alter_license($id, $secret, $action)
    {
        // Store current keys in tmp variable so in case it fails, we can set it back.
		$tmpClientId = get_option('webarx_clientid');
		$tmpSecretKey = get_option('webarx_secretkey');
		update_option('webarx_clientid', $id);
        update_option('webarx_secretkey', $secret);
        
        // Activate a license.
        if ($action == 'slm_activate') {
            $api_result = $this->plugin->api->get_access_token($id, $secret, true);

            // Valid result?
            if (!$api_result) {
                update_option('webarx_clientid', $tmpClientId);
                update_option('webarx_secretkey', $tmpSecretKey);
                return array('result' => 'error', 'message' => 'Cannot activate license!');
            }

            // If we have an access token, tell our API that the firewall is activated
            // and the current URL of the site.
            update_option('webarx_license_activated', '1');
            $this->plugin->api->update_license_status();
            $token = $this->plugin->api->get_access_token();
            if (!empty($token)) {
                do_action('webarx_send_software_data');
                do_action('webarx_post_firewall_rules');
                do_action('webarx_post_dynamic_firewall_rules');
                $this->plugin->api->update_firewall_status(array('status' => $this->get_option('webarx_basic_firewall') == 1));
                $this->plugin->api->update_url(array('plugin_url' => get_option('siteurl')));
            }
            return array('result' => 'success', 'message' => 'License activated!');
        }
        
        // Deactivate a license.
        if ($action == 'slm_deactivate') {
            update_option('webarx_api_token', '');
            update_option('webarx_license_activated', '0');
            return array('result' => 'success', 'message' => 'License deactivated!');
        }
    }
}