<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class used for the admin menu and to enqueue styles and/or scripts.
 */
class W_Admin_Menu extends W_Core
{
	/**
	 * Add the actions required for the admin menu.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('admin_head', array($this, 'webarx_add_meta_nonce'));
		add_action('admin_menu', array($this, 'add_menu_pages'));
		add_action('network_admin_menu', array($this, 'network_menu'));
		add_action('admin_enqueue_scripts', array($this, 'webarx_enqueue_styles'));
		add_action('admin_enqueue_scripts', array($this, 'webarx_enqueue_scripts'));
		add_filter('plugin_action_links_' . plugin_basename(plugin_dir_path(__FILE__) . 'webarx.php'), array($this, 'admin_settings'));
	}

	/**
	 * Add Patchstack nonce as meta tag so we can access it in our JavaScript files.
	 * 
	 * @return void
	 */
	public function webarx_add_meta_nonce()
	{
		$screen = get_current_screen();
		if (current_user_can('manage_options') && isset($screen->base) && ($screen->base == 'dashboard' || stripos($screen->base, 'webarx') !== false)) {
			echo '<meta name="webarx_nonce" value="' . wp_create_nonce('webarx-nonce') . '">';
		}
	}
		
	/**
	 * Register the wp-admin menu items.
	 *
	 * @return void
	 */
	public function add_menu_pages() 
	{
		add_submenu_page($this->plugin->name, 'Main', __('Settings', 'webarx'), 'manage_options',  $this->plugin->name, array($this, 'render_settings_page'));
		add_options_page('Security', 'Security', 'manage_options', $this->plugin->name);
	}

    /**
     * Register the menu pages for multisite/networks.
     * 
     * @return void
     */
    public function network_menu()
    {
        add_menu_page('Patchstack', 'Patchstack', 'manage_options', 'webarx-multisite', array($this->plugin->multisite, 'sites_section_callback'));
        add_submenu_page('webarx-multisite', 'Activate', 'Activate', 'manage_options', 'webarx-multisite-settings&tab=multisite', array($this->plugin->multisite, 'settings'));
        add_submenu_page('webarx-multisite', 'Sites', 'Sites', 'manage_options', 'webarx-multisite', array($this->plugin->multisite, 'sites_section_callback'));
        add_submenu_page('webarx-multisite', 'Settings', 'Settings', 'manage_options', 'webarx-multisite-settings', array($this, 'render_settings_page'));
	}
	
	/**
	 * Render the settings page.
	 * 
	 * @return void
	 */
	public function render_settings_page()
	{
		require dirname(__FILE__) . '/../views/pages/settings.php';
	}

	/**
	 * Register the stylesheets for the backend.
	 *
	 * @return void
	 */
	public function webarx_enqueue_styles()
	{
		$screen = get_current_screen();

		// Load the Patchstack style on all Patchstack pages except site overview.
		if (isset($screen->base, $_GET['page']) && stripos($screen->base, 'webarx') !== false && $_GET['page'] != 'webarx-multisite') {
			wp_enqueue_style('webarx', $this->plugin->url . 'assets/css/webarx.min.css', array(), $this->plugin->version);
		}

		// Only load the widget CSS file on the dashboard.
		if (isset($screen->base) && $screen->base == 'dashboard' && $this->get_option('webarx_display_widget', true)) {
			wp_enqueue_style('webarx', $this->plugin->url . 'assets/css/widget.min.css', array(), $this->plugin->version);
		}

		// Load font-awesome on multisite when on dashboard or Patchstack pages.
        if (is_multisite() && isset($screen, $screen->base) && (($screen->base == 'dashboard' && $this->get_option('webarx_display_widget', true)) || stripos($screen->base, 'webarx') !== false)) {
            wp_enqueue_style('style', 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
        }
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @return void
	 */
	public function webarx_enqueue_scripts()
	{
		$screen = get_current_screen();
		if (isset($screen->base) && (($screen->base == 'dashboard' && $this->get_option('webarx_display_widget', true)) || stripos($screen->base, 'webarx') !== false) && current_user_can('manage_options')) {
			wp_enqueue_script('webarx', $this->plugin->url . 'assets/js/webarx.min.js', array('jquery'), $this->plugin->version);
			wp_enqueue_script('google-jsapi', 'https://www.google.com/jsapi');
			wp_localize_script('webarx', 'WebarxVars', array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce('webarx-nonce'),
					'error_message' => __('Sorry, there was a problem processing your request.', 'webarx'),
				)
			);
		}
	}

	/**
	 * Add the "Settings" hyperlink to the Patchstack section on the plugins page of WordPress.
	 *
	 * @param array $links
	 * @return array
	 */
	public function admin_settings($links)
	{
		return array_merge($links, array('<a href="admin.php?page=webarx&tab=firewall">' . __('Settings', 'webarx') . '</a>'));
	}
}