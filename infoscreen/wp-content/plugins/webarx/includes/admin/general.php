<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used for any general admin functionality.
 * For example to display general errors on all pages or event listeners.
 */
class W_Admin_General extends W_Core
{
	/**
	 * Add any general actions for the backend.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);

		// Add admin and network notices.
		add_action('admin_notices', array($this, 'webarx_file_error_notice'));
		add_action('network_admin_notices', array($this, 'webarx_file_error_notice'));

		add_action('wp_loaded', array($this, 'webarx_update_rules'));
        add_action('update_option_siteurl', array($this, 'update_option_url'), 10, 2);
		add_action('admin_init', array($this, 'webarx_alter_ips'));
		
		// If the firewall or whitelist rules do not exist, attempt to pull fresh.
		$token = get_option('webarx_api_token', false);
		if (!empty($token) && (get_option('webarx_firewall_rules', '') == '' || get_option('webarx_whitelist_keys_rules') == '')) {
			do_action('webarx_post_dynamic_firewall_rules');
		}
    }
	
	/**
	 * Display error message if file/folder permissions are not set properly.
     * 
     * @return void
	 */
    public function webarx_file_error_notice()
    {
		// Check root .htaccess file and data folder writability.
		$files = array();
		$whitelist = true;
		if (file_exists(ABSPATH . '.htaccess') && !wp_is_writable(ABSPATH . '.htaccess')) {
			array_push($files, ABSPATH . '.htaccess');
		}

		if (!wp_is_writable($this->plugin->path . 'data')) {
		    if (!file_exists($this->plugin->path . 'data')) {
                wp_mkdir_p($this->plugin->path . 'data');
            } else {
                array_push($files, $this->plugin->path . 'data');
            }
		}

        // Are there any errors to display?
		if (count($files) > 0) {
		?>
		<div class="error notice">
			<h2>Patchstack File Permission Error</h2>
			<p><?php _e('The following file/folder could not be written to:<br>' . implode('<br>', $files), 'webarx_file_error_notice'); ?></p>
			<?php
			    foreach ($files as $file) {
                    echo '<p><b>Debug info: </b>' . $file . ' chmod permissions: <b>' . substr(decoct(fileperms($file)), -3) . '</b>, owned by <b>' . posix_getpwuid(fileowner($file))['name'] . '</b></p>';
                }
            ?>
            <p><?php _e('<strong>How to fix?</strong><br>CHMOD the file/folder to <strong>755</strong> through a <a href="http://www.dummies.com/web-design-development/wordpress/navigation-customization/how-to-change-file-permissions-using-filezilla-on-your-ftp-site/" target="_blank">FTP client</a>, <a href="http://support.hostgator.com/articles/cpanel/how-to-change-permissions-chmod-of-a-file" target="_blank">CPanel</a>, <a href="https://www.inmotionhosting.com/support/website/managing-files/change-file-permissions" target="_blank">WHM</a> or ask your hosting provider. Make sure file or folder ownership is set to <b>' . posix_getpwuid(fileowner(ABSPATH . 'index.php'))['name'] . '</b> user .', 'webarx_file_error_notice'); ?></p>
			<p><?php _e('<strong>CHMOD properly set but still not working?</strong><br>Make sure the group/owner (chown) settings of the /wp-content/plugins/webarx/ folder is properly setup, you may have to ask your host to fix this.', 'webarx_file_error_notice'); ?></p>
		</div>
		<?php
		}
    }

	/**
	 * When the user changes Patchstack plugin settings, update the firewall rules.
     * 
     * @return void
	 */
    public function webarx_update_rules()
    {
		if (isset($_GET['settings-updated'], $_GET['page']) && strpos($_GET['page'], 'webarx') !== false && current_user_can('administrator')) {
			$this->plugin->rules->post_firewall_rules();
			$this->plugin->rules->dynamic_firewall_rules();

			// Update firewall status after settings saved
            $token = $this->plugin->api->get_access_token();

			// Update the firewall status.
            if (!empty($token)) {
				$this->plugin->api->update_firewall_status(array('status' => $this->get_option('webarx_basic_firewall') == 1));
            }

			// Update the custom whitelist.
			update_option('webarx_custom_whitelist_rules', '<?php exit; ?>' . $this->get_option('webarx_whitelist'));
		}
    }
    
	/**
	 * When the user updates the site URL, update it on the API side as well.
     * This needs to be done so we can communicate with the site properly.
	 * 
	 * @param mixed $old_value
	 * @param mixed $new_value
	 * @return void
	 */
    public function update_option_url($old_value, $new_value)
    {
		if ($old_value != $new_value) {
			$this->plugin->api->update_url(array('plugin_url' => $new_value));
		}
    }
    
	/**
	 * Executed when the user modifies the blocked or whitelisted IP addresses on the
	 * login protection settings page.
	 * 
	 * @return void
	 */
    public function webarx_alter_ips()
    {
		if (!isset($_GET['action'], $_GET['WebarxNonce']) || !wp_verify_nonce($_GET['WebarxNonce'], 'webarx-nonce-alter-ips') || !current_user_can('administrator') || !in_array($_GET['action'], array('webarx_unblock', 'webarx_unblock_whitelist', 'webarx_whitelist'))) {
			return;
		}
		
		// Figure out if the user has the new logging tables installed.
		global $wpdb;

		// Unblock the IP; delete the logs of the IP.
		if ($_GET['action'] == 'webarx_unblock' && isset($_GET['id'])) {
			// First get the IP address to unblock.
			$result = $wpdb->get_results(
				$wpdb->prepare("SELECT ip FROM " . $wpdb->prefix . "webarx_event_log WHERE id = %d", array($_GET['id']))
			);

			// Unblock the IP address.
			if (isset($result[0], $result[0]->ip)) {
				$wpdb->query(
					$wpdb->prepare("DELETE FROM " . $wpdb->prefix . "webarx_event_log WHERE ip = %s", array($result[0]->ip))
				);
			}
		}

		// Unblock and whitelist the IP.
		if ($_GET['action'] == 'webarx_unblock_whitelist' && isset($_GET['id'])) {
			// First get the IP address to whitelist.
			$result = $wpdb->get_results(
				$wpdb->prepare("SELECT ip FROM " . $wpdb->prefix . "webarx_event_log WHERE id = %d", array($_GET['id']))
			);

			// Whitelist and unblock the IP address.
			if (isset($result[0], $result[0]->ip)) {
				update_option('webarx_login_whitelist', $this->get_option('webarx_login_whitelist', '') . "\n" . $result[0]->ip);
				$wpdb->query(
					$wpdb->prepare("DELETE FROM " . $wpdb->prefix . "webarx_event_log WHERE ip = %s", array($result[0]->ip))
				);
			}
		}

		// Whitelist an IP address.
		if ($_GET['action'] == 'webarx_whitelist' && isset($_GET['ip'])) {
	        update_option('webarx_login_whitelist', $this->get_option('webarx_login_whitelist', '') . "\n" . $_GET['ip']);
		}

		// Redirect the user back to the login tab.
		wp_redirect(admin_url('admin.php?page=' . $this->plugin->name . '&tab=login'));
		exit;
	}
}