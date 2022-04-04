<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Plugin Name: Patchstack
 * Plugin URI:  https://patchstack.com
 * Description: A powerful WordPress security plugin.
 * Version: 2.0.20
 * Author: Patchstack
 * Author URI:  https://patchstack.com
 * Donate link: https://patchstack.com
 * License: EULA
 * Text Domain: webarx
 * Domain Path: /languages
 *
 * @link    https://patchstack.com
 *
 * @package Patchstack
 * @version 2.0.20
 *
 */

/**
 * Copyright (c) Patchstack - All Rights Reserved
 * Unauthorized copying and distribution of this file and other files of the Patchstack plugin via any medium is strictly prohibited.
 */

if (!function_exists('webarx_autoload_classes')) {
	/**
	 * Autoloads the Patchstack classes when called.
	 *
	 * @param string $class_name
	 * @return void
	 */
	function webarx_autoload_classes($class_name) {
		// If the requested class doesn't have our prefix, don't load it.
		if (strpos($class_name, 'W_') !== 0) {
			return;
		}

		// Set up our filename.
		$fileName = strtolower(str_replace('_', '-', substr($class_name, strlen('W_'))));
		$dir = trailingslashit(dirname(__FILE__)) . 'includes/';
		$target = array($dir . $fileName . '.php', $dir . 'admin/' . str_replace('admin-', '', $fileName) . '.php');

		// Attempt each target and load if it exists.
		foreach ($target as $file) {
			if (file_exists($file)) {
				include_once($file);
			}
		}
	}
}
spl_autoload_register('webarx_autoload_classes');

if (!class_exists('Webarx')) {

	/**
	 * This is the main Patchstack class used for all Patchstack related features and to launch
	 * the Patchstack plugin.
	 */
	class Webarx
	{
		/**
		 * The plugin version.
		 * @var string
		 */
		const VERSION = '2.0.20';

		/**
		 * API URL of Patchstack to communicate with.
		 * @var    string
		 */
		const API_SERVER_URL = 'https://api.webarxsecurity.com';

		/**
		 * URL to check for updates.
		 * @var string
		 */
		const UPDATE_CHECKER_URL = 'https://update.webarxsecurity.com/wp-update-server/?action=get_metadata&slug=webarx';

		/**
		 * URL where to download the new plugin version from.
		 * @var string
		 */
		const UPDATE_DOWNLOAD_URL = 'https://update.webarxsecurity.com/wp-update-server/?action=download&slug=webarx';

		/**
		 * Client ID, this is only set when freshly downloaded from the portal.
		 * @var string
		 */
		const WEBARX_CLIENT_ID = '98764';

		/**
		 * Client private key, this is only set when freshly downloaded from the portal.
		 * @var string
		 */
		const WEBARX_PRIVATE_KEY = '9EbN2XaRhhF20J09IghYoVzUOrznpZj7tQScEW6w';

		/**
		 * Known IP addresses.
		 * @var array
		 */
		protected $ips = array('18.221.197.243', '52.15.237.250', '3.19.3.34', '3.18.238.17', '13.58.49.77', '18.220.70.233', '3.140.84.221');

		/**
		 * URL of the plugin directory.
		 * @var string
		 */
		protected $url = '';

		/**
		 * Path of the plugin directory.
		 * @var string
		 */
		protected $path = '';

		/**
		 * Plugin basename.
		 * @var string
		 */
		protected $basename = '';

		/**
		 * Plugin name.
		 * @var string
		 */
		protected $name = '';

		/**
		 * Detailed activation error messages.
		 * @var array
		 */
		protected $activation_errors = array();

		/**
		 * Singleton instance of plugin.
		 * @var Webarx
		 */
		protected static $single_instance = null;

		/**
		 * Define all the variables that will hold the Patchstack classes.
		 * These must be defined because it allows us to communicate from one class to the other.
		 */
		protected $firewall;
		protected $firewall_base;
		protected $activation;
		protected $widget;
		protected $cron;
		protected $api;
		protected $update_checker;
		protected $login;
		protected $ban;
		protected $hardening;
		protected $htaccess;
		protected $hacker_log;
		protected $upload;
		protected $rules;
		protected $hide_login;
		protected $listener;
		protected $event_log;
		protected $multisite;
		protected $notice;
		protected $admin_ajax;
		protected $admin_general;
		protected $admin_menu;
		protected $admin_options;

		/**
		 * Setup a few base variables for the plugin.
		 * Also make sure certain constants are defined.
		 * 
		 * @return void
		 */
		protected function __construct()
		{
			// Set the permission constants if not already set.
			if (!defined('FS_CHMOD_DIR')) {
				define('FS_CHMOD_DIR', (fileperms(ABSPATH) & 0777 | 0755));
			}

			if (!defined('FS_CHMOD_FILE')) {
				define('FS_CHMOD_FILE', (fileperms(ABSPATH . 'index.php') & 0777 | 0644));
			}

			// Define local variables.
			$this->basename = plugin_basename(__FILE__);
			$this->url = plugin_dir_url(__FILE__);
			$this->path = plugin_dir_path(__FILE__);
			$this->uploads = wp_upload_dir();
			$names = explode('/', $this->basename);
			$this->name = $names[0];
		}

		/**
		 * Call the constructor of all the Patchstack related classes.
		 *
		 * @return void
		 */
		public function plugin_classes()
		{
			// Define the array of the classes in the form of
			// local variable => class name
			foreach (array(
				'admin_options' => 'W_Admin_Options',
				'widget' => 'W_Widget',
				'cron' => 'W_Cron',
				'api' => 'W_Api',
				'update_checker' => 'W_Update_Checker',
				'login' => 'W_Login',
				'ban' => 'W_Ban',
				'hardening' => 'W_Hardening',
				'htaccess' => 'W_Htaccess',
				'hacker_log' => 'W_Hacker_Log',
				'upload' => 'W_Upload',
				'rules' => 'W_Rules',
				'hide_login' => 'W_Hide_Login',
				'listener' => 'W_Listener',
				'event_log' => 'W_Event_Log',
				'activation' => 'W_Activation',
				'multisite' => 'W_Multisite',
				'notice' => 'W_Cookie_Notice',
				'admin_ajax' => 'W_Admin_Ajax',
				'admin_general' => 'W_Admin_General',
				'admin_menu' => 'W_Admin_Menu'
				) as $var => $class) {
				$this->$var = new $class($this);
			}

			$this->firewall_base = new W_Firewall(true, $this, true);
		}

		/**
		 * Activate the plugin.
		 *
		 * @return void
		 */
		public function activate()
		{
			$this->plugin_classes();
			$this->activation->activate($this);
		}

		/**
		 * Deactivate the plugin.
		 *
		 * @return void
		 */
		public function deactivate()
		{
			$this->plugin_classes();
			$this->activation->deactivate();
		}

		/**
		 * Boot Patchstack and its classes.
		 *
		 * @return void
		 */
		public function hooks()
		{
            add_action('init', array($this, 'init'), ~PHP_INT_MAX);
		}
		
		/**
		 * Boot Patchstack
		 *
		 * @return void
		 */
		public function init()
		{
			// Load translated strings for plugin.
			load_plugin_textdomain('webarx', false, dirname($this->basename) . '/languages/');

			// Initialize plugin classes.
			$this->plugin_classes();

			// Perform migrations if necessary.
			$this->activation->migrate_check();

			// If license expiration has not been fetched yet while the plugin is active, update it.
			if (get_option('webarx_api_token', '') == '' && get_option('webarx_license_expiry', '') == '') {
				$this->api->update_license_status();
			}

			// Determine if the license is activated and not expired.
			if (get_option('webarx_license_activated', 0) == 1 && get_option('webarx_basic_firewall', 0) == 1) {
				$this->firewall = new W_Firewall(true, $this);
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 *
		 * @return Webarx
		 */
		public static function get_instance()
		{
			if (self::$single_instance === null) {
				self::$single_instance = new self();
			}

			return self::$single_instance;
		}

		/**
		 * Magic getter.
		 * 
		 * @param string $field
		 * @return mixed
		 */
		public function __get($field)
		{
			switch ($field) {
				case 'version':
					return self::VERSION;
				case 'api_server_url':
					return self::API_SERVER_URL;
				case 'update_checker_url':
					return self::UPDATE_CHECKER_URL;
				case 'update_download_url';
					return self::UPDATE_DOWNLOAD_URL;
				case 'client_id':
					return self::WEBARX_CLIENT_ID;
				case 'private_key':
					return self::WEBARX_PRIVATE_KEY;
				default:
					try{
						return $this->$field;
					} catch(\Exception $e) {
						return null;
					}
			}
		} 
	}
}

if (!function_exists('webarx_uninstall')) {
	/**
     * Called when the plugin is uninstalled/removed from the site.
     * This is not the same as deactivation, where the plugin still resides on the site.
     * 
     * @return void
     */
    function webarx_uninstall()
    {
        // Delete most of the Patchstack options.
        $options = array('webarx_eventlog_lastid', 'webarx_api_token', 'webarx_dashboardlock', 'webarx_pluginedit', 'webarx_move_logs', 'webarx_userenum', 'webarx_basicscanblock', 'webarx_hidewpcontent', 'webarx_hidewpversionk', 'webarx_prevent_default_file_access', 'webarx_basic_firewall', 'webarx_known_blacklist', 'webarx_block_debug_log_access', 'webarx_block_fake_bots', 'webarx_index_views', 'webarx_proxy_comment_posting', 'webarx_bad_query_strings', 'webarx_advanced_character_string_filter', 'webarx_advanced_blacklist_firewall', 'webarx_forbid_rfi', 'webarx_image_hotlinking', 'webarx_add_security_headers', 'webarx_firewall_log_lastid', 'webarx_user_log_lastid', 'webarx_captcha_public_key', 'webarx_captcha_private_key', 'webarx_scan_interval', 'webarx_scan_day', 'webarx_scan_time', 'webarx_hackers_log', 'webarx_users_log', 'webarx_visitors_log', 'external_updates-webarx', 'webarx_wp_stats', 'webarx_captcha_login_form', 'webarx_license_activated', 'webarx_license_expiry', 'webarx_software_data_hash', 'webarx_mv_wp_login', 'webarx_rename_wp_login', 'webarx_googledrive_backup_is_running', 'webarx_googledrive_upload_state', 'webarx_googledrive_access_token', 'webarx_googledrive_refresh_token', 'webarx_cron_offset', 'webarx_htaccess_rules_hash');
        foreach ($options as $option) {
            delete_option($option);

            if (is_multisite()) {
                delete_site_option($option);
            }
        }

        // Drop all Patchstack tables.
        global $wpdb;
        $tables = array('webarx_user_log', 'webarx_visitor_log', 'webarx_firewall_log', 'webarx_file_hashes', 'webarx_logic', 'webarx_ip', 'webarx_event_log');
        foreach ($tables as $table) {
            $wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . $table);
        }
    }
}

if (!function_exists('webarx')) {
	/**
	 * Grab the Patchstack object and return it.
	 *
	 * @return Webarx
	 */
	function webarx() {
		return Webarx::get_instance();
	}
}

// Kick it off.
add_action('plugins_loaded', array(webarx(), 'hooks'));

// Activation and deactivation hooks.
register_activation_hook(__FILE__, array(webarx(), 'activate'));
register_deactivation_hook(__FILE__, array(webarx(), 'deactivate'));
register_uninstall_hook(__FILE__, 'webarx_uninstall');
