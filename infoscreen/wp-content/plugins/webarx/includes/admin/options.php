<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to register all the options that Patchstack uses and anything
 * related to options.
 */
class W_Admin_Options extends W_Core
{
	/**
	 * Add the actions required for the otions.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
        add_action('admin_init', array($this, 'webarx_settings_init'));
    }

    /**
     * All options and their default values.
     * @var array
     */
    public $options = array(
		// Hardening options.
		'webarx_display_widget' => 1,
        'webarx_pluginedit' => 1,
        'webarx_userenum' => 1,
        'webarx_basicscanblock' => 1,
        'webarx_hidewpcontent' => 1,
		'webarx_hidewpversion' => 1,
		'webarx_rm_readme' => 1,
		'webarx_activity_log_is_enabled' => 1,
		'webarx_activity_log_failed_logins' => 0,
        'webarx_xmlrpc_is_disabled' => 1,
        'webarx_captcha_type' => 'v2',
        'webarx_captcha_public_key_v3' => '',
        'webarx_captcha_private_key_v3' => '',
        'webarx_captcha_public_key' => '',
        'webarx_captcha_private_key' => '',
        'webarx_captcha_login_form' => 0,
        'webarx_captcha_registration_form' => 0,
        'webarx_captcha_reset_pwd_form' => 0,
        'webarx_captcha_on_comments' => 0,
		'webarx_prevent_default_file_access' => 1,
		'webarx_register_email_blacklist' => '',
		'webarx_json_is_disabled' => 0,
		'webarx_auto_update' => array(),
		'webarx_application_passwords_disabled' => 1,

		// The firewall and whitelist rules.
		'webarx_firewall_rules' => '',
		'webarx_whitelist_rules' => '',
		'webarx_custom_whitelist_rules' => '',
		'webarx_whitelist_keys_rules' => '',

		// Firewall options.
        'webarx_basic_firewall' => 1,
        'webarx_basic_firewall_roles' => array('administrator', 'editor', 'author', 'contributor'),
        'webarx_firewall_ip_header' => '',
        'webarx_disable_htaccess' => 0,
        'webarx_known_blacklist' => 0,
        'webarx_block_debug_log_access' => 1,
        'webarx_block_fake_bots' => 1,
        'webarx_index_views' => 1,
        'webarx_proxy_comment_posting' => 1,
        'webarx_bad_query_strings' => 0,
        'webarx_advanced_character_string_filter' => 0,
        'webarx_advanced_blacklist_firewall' => 0,
        'webarx_forbid_rfi' => 0,
        'webarx_image_hotlinking' => 0,
        'webarx_firewall_custom_rules' => '',
        'webarx_firewall_custom_rules_loc' => 'bottom',
        'webarx_add_security_headers' => 1,
		'webarx_blocked_attacks' => 0,
		'webarx_ip_block_list' => '',
		'webarx_geo_block_enabled' => 0,
		'webarx_geo_block_inverse' => 0,
		'webarx_geo_block_countries' => array(),

        // Cookie notice options.
        'webarx_cookie_notice_message' => 'We use cookies for various purposes including analytics and personalized marketing. By continuing to use the service, you agree to our use of cookies.',
        'webarx_cookie_notice_backgroundcolor' => '#222222',
        'webarx_cookie_notice_textcolor' => '#ffffff',
        'webarx_cookie_notice_privacypolicy_enable' => 0,
        'webarx_cookie_notice_privacypolicy_text' => 'Cookie Policy',
        'webarx_cookie_notice_privacypolicy_link' => '#',
        'webarx_cookie_notice_cookie_expiration' => 'after_exit',
        'webarx_cookie_notice_opacity' => '100',
        'webarx_cookie_notice_accept_text' => 'I agree',
        'webarx_cookie_notice_credits' => 1,

        // Login and firewall brute force options.
        'webarx_block_bruteforce_ips' => 0,
        'webarx_anti_bruteforce_attempts' => 10,
        'webarx_anti_bruteforce_minutes' => 5,
        'webarx_anti_bruteforce_blocktime' => 60,
        'webarx_autoblock_attempts' => 10,
        'webarx_autoblock_minutes' => 30,
        'webarx_autoblock_blocktime' => 60,
        'webarx_login_time_block' => 0,
        'webarx_login_time_start' => '00:00',
        'webarx_login_time_end' => '23:59',
        'webarx_login_2fa' => 0,
        'webarx_login_whitelist' => '',

        // General options. 
        'webarx_blackhole_log' => '',
        'webarx_software_data_hash' => '',
        'webarx_clientid' => false,
        'webarx_secretkey' => false,
        'webarx_api_token' => '',
        'webarx_whitelist' => '',

        // Admin page rename options.
        'webarx_mv_wp_login' => 0,
        'webarx_rename_wp_login' => 'swlogin'
	);

	/**
	 * Register all the options, if not set already.
	 *
	 * @return void
	 */
	public function webarx_settings_init() {

        // Add the options.
        foreach ($this->options as $name=>$value) {
			add_option($name, $value);
			
			// Clone settings for multisite so we can set defaults.
			if (is_multisite()) {
				add_site_option($name, $value);
			}
        }

		// Multisite options
		add_network_option(null, 'webarx_multisite_installed', 0);

		// All (sub)sections that show up.
		add_settings_section('webarx_settings_section_hardening', __('Security Configurations', 'webarx'), false, 'webarx_hardening_settings');
		add_settings_section('webarx_settings_section_hardening_captcha', __('<hr style="height:1px;border:none;background-color: rgba(170, 189, 215, 0.1);"><br> reCAPTCHA<br><span style="font-size: 13px; color: #d0d0d0;">It should be noted that the reCAPTCHA feature only applies to WordPress its core features at this time. Not custom forms or of third party plugins.</span>', 'webarx'), false, 'webarx_hardening_settings');
		add_settings_section('webarx_settings_section_firewall', __('Firewall settings', 'webarx'), false, 'webarx_firewall_settings');
		if (!is_multisite() || (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings')) {
			add_settings_section('webarx_settings_section_firewall_htaccess', __('.htaccess Features', 'webarx'), false, 'webarx_firewall_settings');
		}
		add_settings_section('webarx_settings_section_firewall_geo', __('Country Blocking', 'webarx'), false, 'webarx_firewall_settings');
		add_settings_section('webarx_settings_section_firewall_wlbl', __('IP Whitelist &amp; Blacklist', 'webarx'), false, 'webarx_firewall_settings');
		add_settings_section('webarx_settings_section_cookienotice', __('Cookie Notice Settings', 'webarx'), false, 'webarx_cookienotice_settings');
		add_settings_section('webarx_settings_section_login', __('Login Protection', 'webarx'), false, 'webarx_login_settings');
		add_settings_section('webarx_settings_section_login_2fa', __('<hr style="height:1px;border:none;background-color: rgba(170, 189, 215, 0.1);"><br> Two Factor Authentication', 'webarx'), false, 'webarx_login_settings');
		add_settings_section('webarx_settings_section_login_blocked', __('<hr style="height:1px;border:none;background-color: rgba(170, 189, 215, 0.1);"><br> Currently Blocked IP Addresses', 'webarx'), false, 'webarx_login_settings');
		add_settings_section('webarx_settings_section_login_whitelist', __('<hr style="height:1px;border:none;background-color: rgba(170, 189, 215, 0.1);"><br> Whitelisted IP Addresses', 'webarx'), false, 'webarx_login_settings');

		// Hardening.
		add_settings_field('webarx_display_widget', __('Display Dashboard Widget', 'webarx'), array($this, 'webarx_display_widget_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		if (!is_multisite() || (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings')) {
			add_settings_field('webarx_pluginedit', __('Disable plugin/theme edit', 'webarx'), array($this, 'webarx_pluginedit_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
			add_settings_field('webarx_rm_readme', __('Remove readme.html', 'webarx'), array($this, 'webarx_rm_readme_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
			add_settings_field('webarx_auto_update', __('Auto Update Software', 'webarx'), array($this, 'webarx_auto_update_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		}
		add_settings_field('webarx_basicscanblock', __('Stop WPScan', 'webarx'), array($this, 'webarx_basicscanblock_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		add_settings_field('webarx_userenum', __('Disable user enumeration', 'webarx'), array($this, 'webarx_userenum_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		add_settings_field('webarx_hidewpversion', __('Hide WordPress version', 'webarx'), array($this, 'webarx_hidewpversion_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		add_settings_field('webarx_activity_log_is_enabled', __('Enable activity log', 'webarx'), array($this, 'webarx_activity_log_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		add_settings_field('webarx_activity_log_failed_logins', __('Log failed logins', 'webarx'), array($this, 'webarx_activity_log_failed_logins_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
        add_settings_field('webarx_application_passwords_disabled', __('Block Application Passwords', 'webarx'), array($this, 'webarx_application_passwords_disabled_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
        add_settings_field('webarx_xmlrpc_is_disabled', __('Restrict XML-RPC Access', 'webarx'), array($this, 'webarx_xmlrpc_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
        add_settings_field('webarx_json_is_disabled', __('Restrict WP REST API Access', 'webarx'), array($this, 'webarx_json_is_disabled_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');
		add_settings_field('webarx_register_email_blacklist', __('Registration Email Blacklist', 'webarx'), array($this, 'webarx_register_email_blacklist_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening');

        // reCAPTCHA.
        add_settings_field('webarx_captcha_on_comments', __('Post comments form', 'webarx'), array($this, 'webarx_captcha_on_comments_callback'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');
        add_settings_field('webarx_captcha_login_form', __('Login form', 'webarx'), array($this, 'webarx_captcha_login_form_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');
		add_settings_field('webarx_captcha_registration_form', __('Registration form', 'webarx'), array($this, 'webarx_captcha_registration_form_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');
		add_settings_field('webarx_captcha_reset_pwd_form', __('Password reset form', 'webarx'), array($this, 'webarx_captcha_reset_pwd_form_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');
		add_settings_field('webarx_captcha_type', __('reCAPTCHA version (invisible/normal)'), array($this, 'webarx_captcha_type_callback'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');
		add_settings_field('webarx_captcha_public_key', __('Site Key ', 'webarx'), array($this, 'webarx_captcha_public_key_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');
		add_settings_field('webarx_captcha_private_key', __('Secret Key', 'webarx'), array($this, 'webarx_captcha_private_key_input'), 'webarx_hardening_settings', 'webarx_settings_section_hardening_captcha');

		// Firewall.
		add_settings_field('webarx_basic_firewall', __('Enable firewall', 'webarx'), array($this, 'webarx_basic_firewall_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall');
		add_settings_field('webarx_basic_firewall_roles', __('Firewall user role whitelist', 'webarx'), array($this, 'webarx_basic_firewall_roles_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall');
		add_settings_field('webarx_basic_firewall_geo_enabled', __('Country Blocking Enabled', 'webarx'), array($this, 'webarx_basic_firewall_geo_enabled_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_geo');
		add_settings_field('webarx_basic_firewall_geo_inverse', __('Inversed Check', 'webarx'), array($this, 'webarx_basic_firewall_geo_inverse_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_geo');
		add_settings_field('webarx_basic_firewall_geo_countries', __('Countries To Block', 'webarx'), array($this, 'webarx_basic_firewall_geo_countries_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_geo');
		if (!is_multisite() || (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings')) {
			add_settings_field('webarx_firewall_ip_header', __('IP Address Header Override', 'webarx'), array($this, 'webarx_firewall_ip_header_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall');
			add_settings_field('webarx_disable_htaccess', __('Disable .htaccess features', 'webarx'), array($this, 'webarx_disable_htaccess_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_add_security_headers', __('Add security headers', 'webarx'), array($this, 'webarx_add_security_headers_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_prevent_default_file_access', __('Prevent default wordpress file access', 'webarx'), array($this, 'webarx_prevent_default_file_access_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_block_debug_log_access', __('Block access to debug.log file', 'webarx'),array($this, 'webarx_block_debug_log_access_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_index_views', __('Disable index views', 'webarx'), array($this, 'webarx_index_views_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_proxy_comment_posting', __('Forbid proxy comment posting', 'webarx'), array($this, 'webarx_proxy_comment_posting_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_image_hotlinking', __('Prevent image hotlinking', 'webarx'), array($this, 'webarx_image_hotlinking_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_firewall_custom_rules', __('Add custom .htaccess rules here', 'webarx'), array($this, 'webarx_firewall_custom_rules_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');
			add_settings_field('webarx_firewall_custom_rules_loc', __('Custom .htaccess rules location', 'webarx'), array($this, 'webarx_firewall_custom_rules_loc_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_htaccess');	
		}
		add_settings_field('webarx_blackhole_log', __('Block IP List', 'webarx'), array($this, 'webarx_blackhole_log_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_wlbl');
		add_settings_field('webarx_whitelist', __('Whitelist', 'webarx'), array($this, 'webarx_whitelist_input'), 'webarx_firewall_settings', 'webarx_settings_section_firewall_wlbl');
		
		// Login protection.
		if ((!is_multisite() || (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings')) && floatval(substr(phpversion(), 0, 5)) > 5.5) {
			add_settings_field('webarx_mv_wp_login', __('Move and rename login page', 'webarx'), array($this, 'webarx_hidewplogin_input'), 'webarx_login_settings', 'webarx_settings_section_login');
			add_settings_field('webarx_rename_wp_login', '', array($this, 'webarx_hidewplogin_rename_input'), 'webarx_login_settings', 'webarx_settings_section_login');
		}
		add_settings_field('webarx_block_bruteforce_ips', __('Automatic brute-force IP ban', 'webarx'), array($this, 'webarx_block_bruteforce_ips_input'), 'webarx_login_settings', 'webarx_settings_section_login');
		add_settings_field('webarx_login_time_block', __('Logon hours', 'webarx'), array($this, 'webarx_login_time_block_input'), 'webarx_login_settings', 'webarx_settings_section_login');
		add_settings_field('webarx_login_2fa', __('Two Factor Authentication', 'webarx'), array($this, 'webarx_login_2fa_input'), 'webarx_login_settings', 'webarx_settings_section_login_2fa');
		add_settings_field('webarx_login_blocked', __('Blocked', 'webarx'), array($this, 'webarx_login_blocked_input'), 'webarx_login_settings', 'webarx_settings_section_login_blocked');
		add_settings_field('webarx_login_whitelist', __('Whitelist', 'webarx'), array($this, 'webarx_login_whitelist_input'), 'webarx_login_settings', 'webarx_settings_section_login_whitelist');

		// Cookie notice.
        add_settings_field('webarx_enable_cookie_notice_message', 'Enable Cookie Notice', array($this, 'webarx_enable_cookie_notice_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_message', 'Enter message for displaying', array($this, 'webarx_cookie_notice_message_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_accept_text', 'Cookie acceptance button text', array($this, 'webarx_cookie_notice_accept_text_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_backgroundcolor', 'Background color (HEX)', array($this, 'webarx_cookie_notice_backgroundcolor_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_textcolor', 'Text color (HEX)', array($this, 'webarx_cookie_notice_textcolor_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_privacypolicy_enable', 'Enable Policy Link', array($this, 'webarx_cookie_notice_privacypolicy_enable_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_privacypolicy_text', 'Enter Policy Text', array($this, 'webarx_cookie_notice_privacypolicy_text_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_privacypolicy_link', 'Enter Policy Link', array($this, 'webarx_cookie_notice_privacypolicy_link_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_cookie_expiration', 'When to ask user permission again', array($this, 'webarx_cookie_notice_cookie_expiration_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_opacity', 'Background opacity (in percentage)', array($this, 'webarx_cookie_notice_opacity_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');
        add_settings_field('webarx_cookie_notice_credits', 'Display Patchstack credits', array($this, 'webarx_cookie_notice_credits_callback'), 'webarx_cookienotice_settings', 'webarx_settings_section_cookienotice');

        // Register the group settings.
		$settings = array(
			'hardening' => array('webarx_display_widget', 'webarx_auto_update', 'webarx_json_is_disabled', 'webarx_register_email_blacklist', 'webarx_pluginedit', 'webarx_basicscanblock', 'webarx_userenum', 'webarx_rm_readme', 'webarx_hidewpcontent', 'webarx_hidewpversion', 'webarx_activity_log_is_enabled', 'webarx_activity_log_failed_logins', 'webarx_movewpconfig', 'webarx_captcha_on_comments', 'webarx_captcha_login_form', 'webarx_captcha_registration_form', 'webarx_captcha_reset_pwd_form', 'webarx_captcha_public_key', 'webarx_captcha_private_key', 'webarx_captcha_type', 'webarx_captcha_public_key_v3', 'webarx_captcha_private_key_v3', 'webarx_xmlrpc_is_disabled', 'webarx_application_passwords_disabled'),
			'firewall' => array('webarx_geo_block_enabled', 'webarx_geo_block_inverse', 'webarx_basic_firewall_geo_countries', 'webarx_ip_block_list', 'webarx_prevent_default_file_access', 'webarx_basic_firewall', 'webarx_firewall_ip_header', 'webarx_basic_firewall_roles', 'webarx_disable_htaccess', 'webarx_add_security_headers', 'webarx_known_blacklist', 'webarx_block_debug_log_access', 'webarx_block_fake_bots', 'webarx_index_views', 'webarx_proxy_comment_posting', 'webarx_bad_query_strings', 'webarx_advanced_character_string_filter', 'webarx_advanced_blacklist_firewall', 'webarx_forbid_rfi', 'webarx_image_hotlinking', 'webarx_firewall_custom_rules', 'webarx_firewall_custom_rules_loc', 'webarx_blackhole_log', 'webarx_whitelist', 'webarx_autoblock_blocktime', 'webarx_autoblock_attempts', 'webarx_autoblock_minutes'),
			'cookienotice' => array('webarx_enable_cookie_notice_message', 'webarx_cookie_notice_message', 'webarx_cookie_notice_backgroundcolor', 'webarx_cookie_notice_textcolor', 'webarx_cookie_notice_privacypolicy_enable', 'webarx_cookie_notice_privacypolicy_text', 'webarx_cookie_notice_privacypolicy_link', 'webarx_cookie_notice_cookie_expiration', 'webarx_cookie_notice_opacity', 'webarx_cookie_notice_accept_text', 'webarx_cookie_notice_credits'),
			'login' => array('webarx_mv_wp_login', 'webarx_rename_wp_login', 'webarx_block_bruteforce_ips', 'webarx_anti_bruteforce_attempts', 'webarx_anti_bruteforce_minutes', 'webarx_anti_bruteforce_blocktime', 'webarx_login_time_block', 'webarx_login_time_start', 'webarx_login_time_end', 'webarx_login_2fa', 'webarx_login_blocked', 'webarx_login_whitelist')
		);

		foreach ($settings as $key=>$setting) {
			foreach ($setting as $option) {
				register_setting('webarx_' . $key . '_settings_group', $option);
			}
		}
	}

	public function webarx_auto_update_input()
    {
		if (defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED) {
			echo ('<p style="color: red;">The auto update feature cannot be enabled because a plugin or code change forces automatic updates to be disabled. (AUTOMATIC_UPDATER_DISABLED)</p>');
			return;
		}

		$selected = get_site_option('webarx_auto_update', array());
		$selected = !is_array($selected) ? array() : $selected;
		$options = array('core' => 'WordPress Core', 'plugin' => 'Plugins', 'theme' => 'Themes', 'vulnerable' => 'Vulnerable Plugins');
		$out = '';
		foreach ($options as $option=>$text) {
			$out .= '<input type="checkbox" id="webarx_auto_update_' . $option . '" name="webarx_auto_update[]" value="' . $option . '" ' . checked(1, in_array($option, $selected), false) . '/>'
			   	       . '<label for="webarx_auto_update_' . $option . '"><i>' . $text . '</i></label><div style="clear:both;display:block;margin:5px 0;"></div>';
		}
		
		$string1 = __('Select what needs to be automatically updated each time WordPress looks for updates in the background.<br>Keep in mind that if a plugin update contains a bug or a fatal error, it could break your site.', 'webarx');
		echo ('<label for="webarx_auto_update"><i>' . $string1 . '</i></label><br><br>' . $out);
	}

    public function webarx_basic_firewall_geo_enabled_input()
    {
	    $string1 = __('If enabled and valid countries are specified to be blocked, will block these countries.', 'webarx');
	    echo('<input type="checkbox" name="webarx_geo_block_enabled" id="webarx_geo_block_enabled" value="1" ' . checked(1, $this->get_option('webarx_geo_block_enabled'), false) . '/>'
	          . '<label for="webarx_geo_block_enabled"><i>' . $string1 . '</i></label>');
	}

    public function webarx_basic_firewall_geo_inverse_input()
    {
	    $string1 = __('If enabled, instead of checking if the country of the visitor is in the list, check if it is not in the list instead.', 'webarx');
	    echo('<input type="checkbox" name="webarx_geo_block_inverse" id="webarx_geo_block_inverse" value="1" ' . checked(1, $this->get_option('webarx_geo_block_inverse'), false) . '/>'
	          . '<label for="webarx_geo_block_inverse"><i>' . $string1 . '</i></label>');
	}

	public function webarx_basic_firewall_geo_countries_input()
	{
		echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/css/selectize.min.css" integrity="sha256-EhmqrzYSImS7269rfDxk4H+AHDyu/KwV1d8FDgIXScI=" crossorigin="anonymous" /><script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.12.6/js/standalone/selectize.min.js" integrity="sha256-+C0A5Ilqmu4QcSPxrlGpaZxJ04VjsRjKu+G82kl5UJk=" crossorigin="anonymous"></script>';
		$string1 = __('Specify which countries should be blocked.<br>Note that this will also block any type of (legitimate) bot traffic coming from this country. IP to country resolution might also not be 100% accurate.', 'webarx');
		echo '<select id="geo-countries" name="webarx_geo_block_countries[]" placeholder="Select a country..."><option value="">Select a country...</option><option value="AF">Afghanistan</option> <option value="AX">&Aring;land Islands</option> <option value="AL">Albania</option> <option value="DZ">Algeria</option> <option value="AS">American Samoa</option> <option value="AD">Andorra</option> <option value="AO">Angola</option> <option value="AI">Anguilla</option> <option value="AQ">Antarctica</option> <option value="AG">Antigua and Barbuda</option> <option value="AR">Argentina</option> <option value="AM">Armenia</option> <option value="AW">Aruba</option> <option value="AU">Australia</option> <option value="AT">Austria</option> <option value="AZ">Azerbaijan</option> <option value="BS">Bahamas</option> <option value="BH">Bahrain</option> <option value="BD">Bangladesh</option> <option value="BB">Barbados</option> <option value="BY">Belarus</option> <option value="BE">Belgium</option> <option value="BZ">Belize</option> <option value="BJ">Benin</option> <option value="BM">Bermuda</option> <option value="BT">Bhutan</option> <option value="BO">Bolivia, Plurinational State of</option> <option value="BA">Bosnia and Herzegovina</option> <option value="BW">Botswana</option> <option value="BV">Bouvet Island</option> <option value="BR">Brazil</option> <option value="IO">British Indian Ocean Territory</option> <option value="BN">Brunei Darussalam</option> <option value="BG">Bulgaria</option> <option value="BF">Burkina Faso</option> <option value="BI">Burundi</option> <option value="KH">Cambodia</option> <option value="CM">Cameroon</option> <option value="CA">Canada</option> <option value="CV">Cape Verde</option> <option value="KY">Cayman Islands</option> <option value="CF">Central African Republic</option> <option value="TD">Chad</option> <option value="CL">Chile</option> <option value="CN">China</option> <option value="CX">Christmas Island</option> <option value="CC">Cocos (Keeling) Islands</option> <option value="CO">Colombia</option> <option value="KM">Comoros</option> <option value="CG">Congo</option> <option value="CD">Congo, the Democratic Republic of the</option> <option value="CK">Cook Islands</option> <option value="CR">Costa Rica</option> <option value="CI">C&ocirc;te d\'Ivoire</option> <option value="HR">Croatia</option> <option value="CU">Cuba</option> <option value="CY">Cyprus</option> <option value="CZ">Czech Republic</option> <option value="DK">Denmark</option> <option value="DJ">Djibouti</option> <option value="DM">Dominica</option> <option value="DO">Dominican Republic</option> <option value="EC">Ecuador</option> <option value="EG">Egypt</option> <option value="SV">El Salvador</option> <option value="GQ">Equatorial Guinea</option> <option value="ER">Eritrea</option> <option value="EE">Estonia</option> <option value="ET">Ethiopia</option> <option value="FK">Falkland Islands (Malvinas)</option> <option value="FO">Faroe Islands</option> <option value="FJ">Fiji</option> <option value="FI">Finland</option> <option value="FR">France</option> <option value="GF">French Guiana</option> <option value="PF">French Polynesia</option> <option value="TF">French Southern Territories</option> <option value="GA">Gabon</option> <option value="GM">Gambia</option> <option value="GE">Georgia</option> <option value="DE">Germany</option> <option value="GH">Ghana</option> <option value="GI">Gibraltar</option> <option value="GR">Greece</option> <option value="GL">Greenland</option> <option value="GD">Grenada</option> <option value="GP">Guadeloupe</option> <option value="GU">Guam</option> <option value="GT">Guatemala</option> <option value="GG">Guernsey</option> <option value="GN">Guinea</option> <option value="GW">Guinea-Bissau</option> <option value="GY">Guyana</option> <option value="HT">Haiti</option> <option value="HM">Heard Island and McDonald Islands</option> <option value="VA">Holy See (Vatican City State)</option> <option value="HN">Honduras</option> <option value="HK">Hong Kong</option> <option value="HU">Hungary</option> <option value="IS">Iceland</option> <option value="IN">India</option> <option value="ID">Indonesia</option> <option value="IR">Iran, Islamic Republic of</option> <option value="IQ">Iraq</option> <option value="IE">Ireland</option> <option value="IM">Isle of Man</option> <option value="IL">Israel</option> <option value="IT">Italy</option> <option value="JM">Jamaica</option> <option value="JP">Japan</option> <option value="JE">Jersey</option> <option value="JO">Jordan</option> <option value="KZ">Kazakhstan</option> <option value="KE">Kenya</option> <option value="KI">Kiribati</option> <option value="KP">Korea, Democratic People\'s Republic of</option> <option value="KR">Korea, Republic of</option> <option value="KW">Kuwait</option> <option value="KG">Kyrgyzstan</option> <option value="LA">Lao People\'s Democratic Republic</option> <option value="LV">Latvia</option> <option value="LB">Lebanon</option> <option value="LS">Lesotho</option> <option value="LR">Liberia</option> <option value="LY">Libyan Arab Jamahiriya</option> <option value="LI">Liechtenstein</option> <option value="LT">Lithuania</option> <option value="LU">Luxembourg</option> <option value="MO">Macao</option> <option value="MK">Macedonia, the former Yugoslav Republic of</option> <option value="MG">Madagascar</option> <option value="MW">Malawi</option> <option value="MY">Malaysia</option> <option value="MV">Maldives</option> <option value="ML">Mali</option> <option value="MT">Malta</option> <option value="MH">Marshall Islands</option> <option value="MQ">Martinique</option> <option value="MR">Mauritania</option> <option value="MU">Mauritius</option> <option value="YT">Mayotte</option> <option value="MX">Mexico</option> <option value="FM">Micronesia, Federated States of</option> <option value="MD">Moldova, Republic of</option> <option value="MC">Monaco</option> <option value="MN">Mongolia</option> <option value="ME">Montenegro</option> <option value="MS">Montserrat</option> <option value="MA">Morocco</option> <option value="MZ">Mozambique</option> <option value="MM">Myanmar</option> <option value="NA">Namibia</option> <option value="NR">Nauru</option> <option value="NP">Nepal</option> <option value="NL">Netherlands</option> <option value="AN">Netherlands Antilles</option> <option value="NC">New Caledonia</option> <option value="NZ">New Zealand</option> <option value="NI">Nicaragua</option> <option value="NE">Niger</option> <option value="NG">Nigeria</option> <option value="NU">Niue</option> <option value="NF">Norfolk Island</option> <option value="MP">Northern Mariana Islands</option> <option value="NO">Norway</option> <option value="OM">Oman</option> <option value="PK">Pakistan</option> <option value="PW">Palau</option> <option value="PS">Palestinian Territory, Occupied</option> <option value="PA">Panama</option> <option value="PG">Papua New Guinea</option> <option value="PY">Paraguay</option> <option value="PE">Peru</option> <option value="PH">Philippines</option> <option value="PN">Pitcairn</option> <option value="PL">Poland</option> <option value="PT">Portugal</option> <option value="PR">Puerto Rico</option> <option value="QA">Qatar</option> <option value="RE">R&eacute;union</option> <option value="RO">Romania</option> <option value="RU">Russian Federation</option> <option value="RW">Rwanda</option> <option value="BL">Saint Barth&eacute;lemy</option> <option value="SH">Saint Helena, Ascension and Tristan da Cunha</option> <option value="KN">Saint Kitts and Nevis</option> <option value="LC">Saint Lucia</option> <option value="MF">Saint Martin (French part)</option> <option value="PM">Saint Pierre and Miquelon</option> <option value="VC">Saint Vincent and the Grenadines</option> <option value="WS">Samoa</option> <option value="SM">San Marino</option> <option value="ST">Sao Tome and Principe</option> <option value="SA">Saudi Arabia</option> <option value="SN">Senegal</option> <option value="RS">Serbia</option> <option value="SC">Seychelles</option> <option value="SL">Sierra Leone</option> <option value="SG">Singapore</option> <option value="SK">Slovakia</option> <option value="SI">Slovenia</option> <option value="SB">Solomon Islands</option> <option value="SO">Somalia</option> <option value="ZA">South Africa</option> <option value="GS">South Georgia and the South Sandwich Islands</option> <option value="ES">Spain</option> <option value="LK">Sri Lanka</option> <option value="SD">Sudan</option> <option value="SR">Suriname</option> <option value="SJ">Svalbard and Jan Mayen</option> <option value="SZ">Swaziland</option> <option value="SE">Sweden</option> <option value="CH">Switzerland</option> <option value="SY">Syrian Arab Republic</option> <option value="TW">Taiwan, Province of China</option> <option value="TJ">Tajikistan</option> <option value="TZ">Tanzania, United Republic of</option> <option value="TH">Thailand</option> <option value="TL">Timor-Leste</option> <option value="TG">Togo</option> <option value="TK">Tokelau</option> <option value="TO">Tonga</option> <option value="TT">Trinidad and Tobago</option> <option value="TN">Tunisia</option> <option value="TR">Turkey</option> <option value="TM">Turkmenistan</option> <option value="TC">Turks and Caicos Islands</option> <option value="TV">Tuvalu</option> <option value="UG">Uganda</option> <option value="UA">Ukraine</option> <option value="AE">United Arab Emirates</option> <option value="GB">United Kingdom</option> <option value="US">United States</option> <option value="UM">United States Minor Outlying Islands</option> <option value="UY">Uruguay</option> <option value="UZ">Uzbekistan</option> <option value="VU">Vanuatu</option> <option value="VE">Venezuela, Bolivarian Republic of</option> <option value="VN">Viet Nam</option> <option value="VG">Virgin Islands, British</option> <option value="VI">Virgin Islands, U.S.</option> <option value="WF">Wallis and Futuna</option> <option value="EH">Western Sahara</option> <option value="YE">Yemen</option> <option value="ZM">Zambia</option> <option value="ZW">Zimbabwe</option></select>';
		$countries = $this->get_option('webarx_geo_block_countries', array());
		$countryList = "";
		if(!empty($countries)){
			foreach($countries as $country){
				$countryList .= "'" . htmlspecialchars($country, ENT_QUOTES) . "', ";
			}
		}

		echo '	<script>
				jQuery(function(){
					jQuery(\'#geo-countries\').selectize({
						maxItems: null,
						delimiter: \',\',
						plugins: [\'remove_button\'],
						items: [' . substr($countryList, 0, -1) . ']
					});
				});
				</script>';
	    echo('<label for="webarx_geo_block_enabled"><i>' . $string1 . '</i></label>');
	}

    public function webarx_display_widget_input()
    {
        $string1 = __('Display the Patchstack widget on the dashboard of the admin environment.', 'webarx');
        echo('<input type="checkbox" name="webarx_display_widget" id="webarx_display_widget" value="1" ' . checked(1, $this->get_option('webarx_display_widget'), false) . '/>'
            . '<label for="webarx_display_widget"><i>' . $string1 . '</i></label>');
	}
	
    public function webarx_xmlrpc_input()
    {
        $string1 = __('Restrict access to xmlrpc.php by only allowing authenticated users to access it.', 'webarx');
        echo('<input type="checkbox" name="webarx_xmlrpc_is_disabled" id="webarx_xmlrpc_is_disabled" value="1" ' . checked(1, $this->get_option('webarx_xmlrpc_is_disabled'), false) . '/>'
            . '<label for="webarx_xmlrpc_is_disabled"><i>' . $string1 . '</i></label>');
	}

    public function webarx_application_passwords_disabled_input()
    {
        $string1 = __('Disables the application passwords feature introduced in WordPress 5.6.', 'webarx');
        echo('<input type="checkbox" name="webarx_application_passwords_disabled" id="webarx_application_passwords_disabled" value="1" ' . checked(1, $this->get_option('webarx_application_passwords_disabled'), false) . '/>'
            . '<label for="webarx_application_passwords_disabled"><i>' . $string1 . '</i></label>');
	}

	public function webarx_json_is_disabled_input()
    {
        $string1 = __('Restrict access to the WP Rest API by only allowing authenticated users to access it.', 'webarx');
        echo('<input type="checkbox" name="webarx_json_is_disabled" id="webarx_json_is_disabled" value="1" ' . checked(1, $this->get_option('webarx_json_is_disabled'), false) . '/>'
            . '<label for="webarx_json_is_disabled"><i>' . $string1 . '</i></label>');
	}

	public function webarx_register_email_blacklist_input() {
		$string1 = __('<br><br>Enter patterns here, seperated by commas, which email addresses we should block upon registration.<br>For example if you enter @badsite.com it will block all email addresses that contain @badsite.com.', 'webarx');
		echo('<input type="text" name="webarx_register_email_blacklist" id="webarx_register_email_blacklist" value="' . htmlspecialchars($this->get_option('webarx_register_email_blacklist', ''), ENT_QUOTES) . '"/>'
			. '<label for="webarx_register_email_blacklist"><i>' . $string1 . '</i></label>');
	}

    public function webarx_activity_log_input()
    {
	    $string1 = __('If enabled, every user action will be recorded and put to activity logs', 'webarx');
	    echo('<input type="checkbox" name="webarx_activity_log_is_enabled" id="webarx_activity_log_is_enabled" value="1" ' . checked(1, $this->get_option('webarx_activity_log_is_enabled'), false) . '/>'
	          . '<label for="webarx_activity_log_is_enabled"><i>' . $string1 . '</i></label>');
	}
	
    public function webarx_activity_log_failed_logins_input()
    {
	    $string1 = __('If this is checked along with the activity logs, we will also log failed login attempts.', 'webarx');
	    echo('<input type="checkbox" name="webarx_activity_log_failed_logins" id="webarx_activity_log_failed_logins" value="1" ' . checked(1, $this->get_option('webarx_activity_log_failed_logins'), false) . '/>'
	          . '<label for="webarx_activity_log_failed_logins"><i>' . $string1 . '</i></label>');
	}
	
    public function webarx_captcha_on_comments_callback()
    {
        $string1 = __('Check this if you want to enable reCAPTCHA on post comments.', 'webarx');
        echo('<input type="checkbox" name="webarx_captcha_on_comments" id="webarx_captcha_on_comments" value="1" ' . checked(1, $this->get_option('webarx_captcha_on_comments'), false) . '/>'
            . '<label for="webarx_captcha_on_comments"><i>' . $string1 . '</i></label>');
    }

	public function webarx_cookie_notice_credits_callback()
    {
        $string1 = __('Check this if you want to display "Powered by Patchstack"', 'webarx');
        echo('<input type="checkbox" name="webarx_cookie_notice_credits" id="webarx_cookie_notice_credits" value="1" ' . checked(1, $this->get_option('webarx_cookie_notice_credits'), false) . '/>'
            . '<label for="webarx_cookie_notice_credits"><i>' . $string1 . '</i></label>');
    }

	public function webarx_enable_cookie_notice_callback()
    {
        $string1 = __('Check this if you want to enable cookie notice message.', 'webarx');
        echo('<input type="checkbox" name="webarx_enable_cookie_notice_message" id="webarx_enable_cookie_notice_message" value="1" ' . checked(1, $this->get_option('webarx_enable_cookie_notice_message'), false) . '/>'
            . '<label for="webarx_index_views"><i>' . $string1 . '</i></label>');
    }

    public function webarx_cookie_notice_message_callback()
    {
        echo '<textarea name="webarx_cookie_notice_message" id="webarx_cookie_notice_message" rows="20" cols="50">' . htmlspecialchars($this->get_option('webarx_cookie_notice_message'), ENT_QUOTES) . '</textarea>';
    }

    public function webarx_cookie_notice_accept_text_callback()
    {
        echo "<input type='text' name='webarx_cookie_notice_accept_text' id='webarx_cookie_notice_accept_text' value='" . htmlspecialchars($this->get_option('webarx_cookie_notice_accept_text'), ENT_QUOTES) . "'>";
    }

    public function webarx_cookie_notice_backgroundcolor_callback()
    {
        echo "<script src='" . $this->plugin->url . "/assets/js/jscolor.js'></script>";
        echo "<input type='text' class='jscolor' name='webarx_cookie_notice_backgroundcolor' id='webarx_cookie_notice_backgroundcolor' value='" . htmlspecialchars($this->get_option('webarx_cookie_notice_backgroundcolor'), ENT_QUOTES) . "'>";

    }

    public function webarx_cookie_notice_textcolor_callback()
    {
        echo "<input type='text' class='jscolor' name='webarx_cookie_notice_textcolor' id='webarx_cookie_notice_textcolor' value='" . htmlspecialchars($this->get_option('webarx_cookie_notice_textcolor'), ENT_QUOTES) . "'>";
    }

    public function webarx_cookie_notice_privacypolicy_enable_callback()
    {
        $string1 = __('Check this if you want to enable policy link.', 'webarx');
        echo('<input type="checkbox" name="webarx_cookie_notice_privacypolicy_enable" id="webarx_cookie_notice_privacypolicy_enable" value="1" ' . checked(1, $this->get_option('webarx_cookie_notice_privacypolicy_enable'), false) . '/>'
            . '<label for="webarx_index_views"><i>' . $string1 . '</i></label>');
    }

    public function webarx_cookie_notice_privacypolicy_text_callback()
    {
        echo "<input type='text' name='webarx_cookie_notice_privacypolicy_text' id='webarx_cookie_notice_privacypolicy_text' value='" . htmlspecialchars($this->get_option('webarx_cookie_notice_privacypolicy_text'), ENT_QUOTES) . "'>";
    }

    public function webarx_cookie_notice_privacypolicy_link_callback()
    {
        echo("<input type='text' name='webarx_cookie_notice_privacypolicy_link' id='webarx_cookie_notice_privacypolicy_link' value='" . htmlspecialchars($this->get_option('webarx_cookie_notice_privacypolicy_link'), ENT_QUOTES) . "'>");
        echo '<br><label for="webarx_cookie_notice_privacypolicy_link"><i>Starting with http(s)://</i></label>';
    }

    public function webarx_cookie_notice_cookie_expiration_callback()
    {
        echo ('
            <select name="webarx_cookie_notice_cookie_expiration" id="webarx_cookie_notice_cookie_expiration">
              <option ' . ($this->get_option('webarx_cookie_notice_cookie_expiration') == 'after_exit' ? 'selected="selected"' : '') . ' value="after_exit">After user re-open browser</option>
              <option ' . ($this->get_option('webarx_cookie_notice_cookie_expiration') == '1week' ? 'selected="selected"' : '') . ' value="1week">After 1 week</option>
              <option ' . ($this->get_option('webarx_cookie_notice_cookie_expiration') == '1month' ? 'selected="selected"' : '') . ' value="1month">After 1 month</option>
              <option ' . ($this->get_option('webarx_cookie_notice_cookie_expiration') == '1year' ? 'selected="selected"' : '') . ' value="1year">After 1 year</option>
            </select>
        ');
    }

    public function webarx_cookie_notice_opacity_callback()
    {
        echo "<input min=1 max=100 type='number' name='webarx_cookie_notice_opacity' id='webarx_cookie_notice_opacity' value='" . htmlspecialchars($this->get_option('webarx_cookie_notice_opacity'), ENT_QUOTES) . "'>";
        echo '<br><label for="webarx_cookie_notice_opacity"><i>min: 1 - max: 99 - no opacity: 100</i></label>';
	}
	
	public function webarx_hidewplogin_input() {
		$string1 = __('Move login page to hide it from hackers and seekers.', 'webarx');
		$string1 = __('Hide wp-login and wp-admin page from attackers.', 'webarx');
		echo('<input type="checkbox" name="webarx_mv_wp_login" id="webarx_mv_wp_login" value="1" ' . checked(1, $this->get_option('webarx_mv_wp_login'), false) . '/>'
			. '<label for="webarx_mv_wp_login"><i>' . $string1 . '</i></label>');
	}

    public function webarx_hidewplogin_rename_input() {
        echo('<label><i style="color:red;">We do not recommend enabling this when you already have renamed your admin folder or when you make use of a system that allows regular users to login.</i></label><br><br><label style="font-family: Roboto; font-weight: 300; color: #d0d0d0;"> ' . get_site_url() . '/</label><input type="text" name="webarx_rename_wp_login" id="webarx_rename_wp_login" value="' . htmlspecialchars($this->get_option('webarx_rename_wp_login'), ENT_QUOTES) . '" />  <i style="color: #fff">New login page</i>');
        if ($this->get_option('webarx_mv_wp_login') && $this->get_option('webarx_rename_wp_login')) {
            $remoteGET = wp_remote_get(get_site_url() . '/' . $this->get_option('webarx_rename_wp_login'), array('sslverify' => false));
			$response = wp_remote_retrieve_body($remoteGET);
            if (!strpos($response, $this->get_option('webarx_rename_wp_login'))) {
                update_site_option('webarx_mv_wp_login', 0);
                echo '<div class="error notice is-dismissible" style="margin-left: 0px;"><p style="color: #000000;">' . __('Patchstack custom login page could not be activated due to your environment setup, it may be conflict with other plugin or specific .htaccess rules.', 'webarx') . '</p></div>';
            } else {
                echo '<br /><br /><div style="font-family: Roboto; font-weight: 300; color: #d0d0d0;">Your login page is now here:  <a href="' . get_site_url() . '/' . htmlspecialchars($this->get_option('webarx_rename_wp_login'), ENT_QUOTES) . '">' . get_site_url() . '/' . htmlspecialchars($this->get_option('webarx_rename_wp_login'), ENT_QUOTES) . '</div></a>';
                echo '<br /><input type="submit" id="webarx_send_mail_url" name="webarx_send_mail_url" value="Send the link to your admin email." class="button-primary" />';
            }
        }
    }

	public function webarx_pluginedit_input() {
		$string1 = __('Disable direct editing of themes or plugins code from Wordpress admin view.', 'webarx');
		echo('<input type="checkbox" name="webarx_pluginedit" id="webarx_pluginedit" value="1" ' . checked(1, $this->get_option('webarx_pluginedit'), false) . '/>'
			. '<label for="webarx_pluginedit"><i>' . $string1 . '</i></label>');
	}

	public function webarx_add_security_headers_input() {
		$string1 = __('Add security headers to the response by your webserver.', 'webarx');
		echo('<input type="checkbox" name="webarx_add_security_headers" id="webarx_add_security_headers" value="1" ' . checked(1, $this->get_option('webarx_add_security_headers'), false) . '/>'
			. '<label for="webarx_add_security_headers"><i>' . $string1 . '</i></label>');
	}

	/** input for scanblock settings */
	public function webarx_basicscanblock_input() {
		$string1 = __('This will attempt to stop basic WPScan scans.', 'webarx');
		echo('<input type="checkbox" name="webarx_basicscanblock" id="webarx_basicscanblock" value="1" ' . checked(1, $this->get_option('webarx_basicscanblock'), false) . '/>'
			. '<label for="webarx_basicscanblock"><i>' . $string1 . '</i></label>');
	}

	public function webarx_userenum_input() {
		$string1 = __('Disable user enumeration to block hackers from getting your usernames.', 'webarx');
		echo('<input type="checkbox" name="webarx_userenum" id="webarx_userenum" value="1" ' . checked(1, $this->get_option('webarx_userenum'), false) . '/>'
			. '<label for="webarx_userenum"><i>' . $string1 . '</i></label>');
	}

	public function webarx_hidewpcontent_input() {
		$string1 = __('Move wp-content folder into facebook.com folder and link to it in wp-config.php. saving settings after ticking this will take more time than usual. Just let it reload the page on its own and be patient.', 'webarx');
		$string2 = __('If this setting brakes your website then go to your server files through FTP, rename facebook.com folder to wp-content and erase Patchstack section from the top of wp-config.php file.', 'webarx');
		echo('<input type="checkbox" name="webarx_hidewpcontent" id="webarx_hidewpcontent" value="1" ' . checked(1, $this->get_option('webarx_hidewpcontent'), false) . '/>'
			. '<label for="webarx_hidewpcontent"><i>' . $string1 . '</i></br><span style="color: red; font-weight: bold">' . $string2 . '</span></label>');
	}

	public function webarx_hidewpversion_input() {
		$string1 = __('Removes the WordPress version in the <meta> tag in the HTML output.', 'webarx');
		echo('<input type="checkbox" name="webarx_hidewpversion" id="webarx_hidewpversion" value="1" ' . checked(1, $this->get_option('webarx_hidewpversion'), false) . '/>'
			. '<label for="webarx_hidewpversion"><i>' . $string1 . '</i></label>');
	}

	public function webarx_rm_readme_input() {
		$string1 = __('Removes the readme.html file from the WordPress root folder.', 'webarx');
		echo('<input type="checkbox" name="webarx_rm_readme" id="webarx_rm_readme" value="1" ' . checked(1, $this->get_option('webarx_rm_readme'), false) . '/>'
			. '<label for="webarx_rm_readme"><i>' . $string1 . '</i></label>');
	}

	/** input for firewall settings */
	public function webarx_prevent_default_file_access_input() {
		$string1 = __('Prevent access to such files as license.txt, readme.html and wp-config-sample.php', 'webarx');
		echo('<input type="checkbox" name="webarx_prevent_default_file_access" id="webarx_prevent_default_file_access" value="1" ' . checked(1, $this->get_option('webarx_prevent_default_file_access'), false) . '/>'
			. '<label for="webarx_prevent_default_file_access"><i>' . $string1 . '</i></label>');
	}

	public function webarx_basic_firewall_input() {
		$string1 = __('Check this if you want to turn on the advanced firewall protection on your site.', 'webarx');
		echo('<input type="checkbox" name="webarx_basic_firewall" id="webarx_basic_firewall" value="1" ' . checked(1, $this->get_option('webarx_basic_firewall'), false) . '/>'
            . '<label for="webarx_basic_firewall"><i>' . $string1 . '</i></label><br>'

            . '<br><i style="color:#d0d0d0">Block IP for <input style="width: 50px;" type="number" name="webarx_autoblock_blocktime" value="' . htmlspecialchars($this->get_option('webarx_autoblock_blocktime', 60), ENT_QUOTES) . '" id="webarx_autoblock_blocktime"> minutes after <input style="width: 50px;" type="number" name="webarx_autoblock_attempts" value="' . htmlspecialchars($this->get_option('webarx_autoblock_attempts', 10), ENT_QUOTES) . '" id="webarx_autoblock_attempts"> blocked requests over a period of <input style="width: 50px;" type="number" name="webarx_autoblock_minutes" value="' . htmlspecialchars($this->get_option('webarx_autoblock_minutes', 30), ENT_QUOTES) .'" id="webarx_autoblock_minutes"> minutes</i>'
        );
	}

    public function webarx_basic_firewall_roles_input()
    {
		$selected = $this->get_option('webarx_basic_firewall_roles', array('administrator', 'editor', 'author'));
		$selected = !is_array($selected) ? array() : $selected;
		$roles = wp_roles();
		$roles = $roles->get_names();
		$roleText = '';
		foreach ($roles as $key=>$val) {
			$roleText .= '<input type="checkbox" name="webarx_basic_firewall_roles[]" value="' . $key . '" ' . checked(1, in_array($key, $selected), false) . '/>'
			   	       . '<label for="webarx_basic_firewall_roles"><i>' . htmlspecialchars($val, ENT_QUOTES) . '</i></label><div style="clear:both;display:block;margin:5px 0;"></div>';
		}
		
		$string1 = __('Against which user roles should the firewall not run against?<br>The firewall will always run against guests.<br>', 'webarx');
		echo ('<label for="webarx_basic_firewall_roles"><i>' . $string1 . '</i></label><br>' . $roleText);
	}

	public function webarx_known_blacklist_input() {
		$string1 = __('Check this if you want to block known malicious connections.', 'webarx');
		echo('<input type="checkbox" name="webarx_known_blacklist" id="webarx_known_blacklist" value="1" ' . checked(1, $this->get_option('webarx_known_blacklist'), false) . '/>'
			. '<label for="webarx_known_blacklist"><i>' . $string1 . '</i></label>');
	}

	public function webarx_firewall_ip_header_input() {
		$string1 = __('If you would like to override the IP address header that we use to grab the IP address of the visitor, enter the value here. This must be a valid value in the $_SERVER array, for example HTTP_X_FORWARDED_FOR. If the $_SERVER value you enter does not exist, it will fallback to the Patchstack IP grab function so ask your hosting company if you are unsure. Leave this empty to use the Patchstack IP address grabbing function.', 'webarx');
		echo('<input type="text" name="webarx_firewall_ip_header" id="webarx_firewall_ip_header" value="' . esc_attr($this->get_option('webarx_firewall_ip_header')) . '"/>'
			. '<br><br><label for="webarx_firewall_ip_header"><i>' . $string1 . '</i></label>');
	}

	public function webarx_disable_htaccess_input() {
		$string1 = __('Check this if you want to stop us from writing to your .htaccess file. Note that the current changes to the .htaccess file will remain.', 'webarx');
		echo('<input type="checkbox" name="webarx_disable_htaccess" id="webarx_disable_htaccess" value="1" ' . checked(1, $this->get_option('webarx_disable_htaccess'), false) . '/>'
			. '<label for="webarx_disable_htaccess"><i>' . $string1 . '</i></label>');
	}

	public function webarx_block_debug_log_access_input() {
		$string1 = __('Check this if you want to block access to the debug.log file that WordPress creates when debug logging is enabled.', 'webarx');
		echo('<input type="checkbox" name="webarx_block_debug_log_access" id="webarx_block_debug_log_access" value="1" ' . checked(1, $this->get_option('webarx_block_debug_log_access'), false) . '/>'
			. '<label for="webarx_block_debug_log_access"><i>' . $string1 . '</i></label>');
	}

	public function webarx_index_views_input() {
		$string1 = __('Check this if you want to disable directory and file listing.', 'webarx');
		echo('<input type="checkbox" name="webarx_index_views" id="webarx_index_views" value="1" ' . checked(1, $this->get_option('webarx_index_views'), false) . '/>'
			. '<label for="webarx_index_views"><i>' . $string1 . '</i></label>');
	}

	public function webarx_proxy_comment_posting_input() {
		$string1 = __('Check this if you want to forbid proxy comment posting.', 'webarx');
		echo('<input type="checkbox" name="webarx_proxy_comment_posting" id="webarx_proxy_comment_posting" value="1" ' . checked(1, $this->get_option('webarx_proxy_comment_posting'), false) . '/>'
			. '<label for="webarx_proxy_comment_posting"><i>' . $string1 . '</i></label>');
	}

    public function webarx_block_bruteforce_ips_input() {
        $string1 = __('Check this if you want to automatically ban IP addresses that fail to login multiple times in a short span of time.', 'webarx');
        echo('<input type="checkbox" name="webarx_block_bruteforce_ips" id="webarx_block_bruteforce_ips" value="1" ' . checked(1, $this->get_option('webarx_block_bruteforce_ips'), false) . '/>'
            . '<label for="webarx_block_bruteforce_ips"><i>' . $string1 . '</i></label><br>' .
            '<br><i style="color:#d0d0d0">Block IP for <input style="width: 50px;" type="number" name="webarx_anti_bruteforce_blocktime" value="' . htmlspecialchars($this->get_option('webarx_anti_bruteforce_blocktime', 60), ENT_QUOTES) . '" id="webarx_anti_bruteforce_blocktime"> minutes after <input style="width: 50px;" type="number" name="webarx_anti_bruteforce_attempts" value="' . htmlspecialchars($this->get_option('webarx_anti_bruteforce_attempts', 10), ENT_QUOTES) . '" id="webarx_anti_bruteforce_attempts"> failed login attempts over a period of <input style="width: 50px;" type="number" name="webarx_anti_bruteforce_minutes" value="' . htmlspecialchars($this->get_option('webarx_anti_bruteforce_minutes', 5), ENT_QUOTES) .'" id="webarx_anti_bruteforce_minutes"> minutes</i>'
        );
	}
	
    public function webarx_login_time_block_input() {
        $string1 = __('Check this if you want to enforce specific logon hours.', 'webarx');
        echo('<input type="checkbox" name="webarx_login_time_block" id="webarx_login_time_block" value="1" ' . checked(1, $this->get_option('webarx_login_time_block'), false) . '/>'
            . '<label for="webarx_login_time_block"><i>' . $string1 . '</i></label><br>' .
            '<br><i style="color:#d0d0d0">Allow login between <input style="width: 70px;" type="text" name="webarx_login_time_start" value="' . htmlspecialchars($this->get_option('webarx_login_time_start', '00:00'), ENT_QUOTES) . '" id="webarx_login_time_start" autocomplete="off"> and <input style="width: 70px;" type="text" name="webarx_login_time_end" value="' . htmlspecialchars($this->get_option('webarx_login_time_end', '23:59'), ENT_QUOTES) . '" id="webarx_login_time_end" autocomplete="off"><br>Times must be in the 24 hour clock format.<br>The logon hours are also based on the current time of your site: ' . current_time('H:i:s') . '</i>'
        );
	}

    public function webarx_login_2fa_input() {
        $string1 = __('Check this if you want to make it possible for users to enable two factor authentication (2FA) on their account.', 'webarx');
        echo('<input type="checkbox" name="webarx_login_2fa" id="webarx_login_2fa" value="1" ' . checked(1, $this->get_option('webarx_login_2fa'), false) . '/>'
			. '<label for="webarx_login_2fa"><i>' . $string1 . '<br>Once enabled, users can configure 2FA on the "Edit My Profile" page which is located <a href="' . admin_url('profile.php') . '">here</a>.</i></label><br>'
		);
	}

    public function webarx_login_blocked_input() {
		global $wpdb;

		// Check if X failed login attempts were made.
		$results = $wpdb->get_results(
			$wpdb->prepare("SELECT id, ip, date FROM " . $wpdb->prefix . "webarx_event_log WHERE action = 'failed login' AND date >= ('" . current_time('mysql') . "' - INTERVAL %d MINUTE) GROUP BY ip HAVING COUNT(ip) >= %d ORDER BY date DESC", array(($this->get_option('webarx_anti_bruteforce_blocktime', 60) + $this->get_option('webarx_anti_bruteforce_minutes', 5)), $this->get_option('webarx_anti_bruteforce_attempts', 10)))
		, OBJECT);

		// Render the rows.
		$rows = '';
		if (count($results) == 0) {
			$rows = '<tr><td>No blocked IP addresses.</td><td></td><td></td><td></td></tr>';
		} else {
			$nonce = wp_create_nonce('webarx-nonce-alter-ips');
			foreach ($results as $result) {
				$rows .= '<tr><td>' . htmlspecialchars($result->ip, ENT_QUOTES) . '</td><td>' . (isset($result->log_date) ? $result->log_date : $result->date) . '</td><td><a href="' . add_query_arg(array('WebarxNonce' => $nonce, 'action' => 'webarx_unblock', 'id' => $result->id)) . '">Unblock</a></td><td><a href="' . add_query_arg(array('WebarxNonce' => $nonce, 'action' => 'webarx_unblock_whitelist', 'id' => $result->id)) . '">Unblock &amp; Whitelist</a></td></tr>';
			}
		}

        $string1 = __('These are the IP addresses that are currently blocked because of too many failed login attempts.<br>These are not the IP addresses banned by the firewall itself.<br><br>', 'webarx');
		echo '<label><i>' . $string1 . '</i></label>';
		echo '<div class="webarx-content-inner-table"><table class="table dataTable webarx-bi" style="margin: 0 !important;"><thead><tr><th>IP Address</th><th style="padding-left: 0 !important;">Last Attempt</th><th style="padding-left: 0 !important;">Unblock</th><th style="padding-left: 0 !important;">Unbock &amp; Whitelist</th></tr></thead><tbody>' . $rows . '</table></div>';
	}

	public function webarx_login_whitelist_input() {
		$ip_list = htmlspecialchars(rtrim($this->get_option('webarx_login_whitelist', '')), ENT_QUOTES);
		echo('<label><i>These IP addresses will never be blocked from logging in, no matter the amount of failed logins.</i></label><br><br><p><textarea rows="5" id="webarx_login_whitelist"  name="webarx_login_whitelist">'. $ip_list . '</textarea>Each entry must be on its own line.<br>Your current IP address is: ' . esc_html($this->get_ip()) . '<br><br>
					<strong>Following formats are accepted:</strong>
					<p>127.0.0.1</p>
					<p>127.0.0.*</p>
					<p>127.0.0.0/24</p>
					<p>127.0.0.0-127.0.0.255</p>
				</p>');
	}

	public function webarx_image_hotlinking_input() {
		$string1 = __('Check this if you want to prevent hotlinking to images on your site.', 'webarx');
		echo('<input type="checkbox" name="webarx_image_hotlinking" id="webarx_image_hotlinking" value="1" ' . checked(1, $this->get_option('webarx_image_hotlinking'), false) . '/>'
			. '<label for="webarx_image_hotlinking"><i>' . $string1 . '</i></label>');
	}

	public function webarx_firewall_custom_rules_input() {
		$string1 = __('Add custom .htaccess rules here if you know what you are doing, otherwise you may break your site. So be careful.', 'webarx');
		echo '<textarea name="webarx_firewall_custom_rules" id="webarx_firewall_custom_rules" rows="20" cols="50" placeholder="' . $string1 . '">';
		$rules = $this->get_option('webarx_firewall_custom_rules');
		if (isset($rules)) {
		    if (is_array($rules)) {
                foreach ($rules as $rule) {
                    echo htmlspecialchars($rule, ENT_QUOTES);
		        }
            } else {
                echo htmlspecialchars($rules, ENT_QUOTES);
            }
		}
		echo '</textarea>';
		echo '<p style="color:red;">If the custom .htaccess rules above cause an error, they will be removed automatically.</p>';
	}

    public function webarx_firewall_custom_rules_loc_input() {
        echo ('
            <select name="webarx_firewall_custom_rules_loc" id="webarx_firewall_custom_rules_loc">
              <option ' . ($this->get_option('webarx_firewall_custom_rules_loc') == 'top' ? 'selected="selected"' : '') . ' value="top">Top - above Patchstack rules</option>
              <option ' . ($this->get_option('webarx_firewall_custom_rules_loc') == 'bottom' ? 'selected="selected"' : '') . ' value="bottom">Bottom - under Patchstack rules</option>
           </select>
        ');
	}
	
	/** input for captcha settings */
	public function webarx_captcha_login_form_input() {
		$string1 = __('Check this if you want to enable reCAPTCHA on user login.', 'webarx');
		echo('<input type="checkbox" name="webarx_captcha_login_form" id="webarx_captcha_login_form" value="1" ' . checked(1, $this->get_option('webarx_captcha_login_form'), false) . '/>'
			. '<label for="webarx_captcha_login_form"><i>' . $string1 . '</i></label>');
	}

	public function webarx_captcha_registration_form_input() {
		$string1 = __('Check this if you want to enable reCAPTCHA on registration.', 'webarx');
		echo('<input type="checkbox" name="webarx_captcha_registration_form" id="webarx_captcha_registration_form" value="1" ' . checked(1, $this->get_option('webarx_captcha_registration_form'), false) . '/>'
			. '<label for="webarx_captcha_registration_form"><i>' . $string1 . '</i></label>');
	}

	public function webarx_captcha_reset_pwd_form_input() {
		$string1 = __('Check this if you want to enable reCAPTCHA on password reset.', 'webarx');
		echo('<input type="checkbox" name="webarx_captcha_reset_pwd_form" id="webarx_captcha_reset_pwd_form" value="1" ' . checked(1, $this->get_option('webarx_captcha_reset_pwd_form'), false) . '/>'
			. '<label for="webarx_captcha_reset_pwd_form"><i>' . $string1 . '</i></label>');
	}

	public function webarx_captcha_type_callback() {
        echo ('
            <select name="webarx_captcha_type" id="webarx_captcha_type">
              <option ' . ($this->get_option('webarx_captcha_type') == 'v2' ? 'selected="selected"' : '') . ' value="v2">Normal/Checkbox (v2)</option>
              <option ' . ($this->get_option('webarx_captcha_type') == 'invisible' ? 'selected="selected"' : '') . ' value="invisible">Invisible (v2)</option>
           </select>
        
        <script>
            jQuery(document).ready(function() {           
                if(jQuery("#webarx_captcha_type").val() == "v2"){
                    jQuery("#webarx_captcha_public_key_v3, #webarx_captcha_private_key_v3").hide();
                    jQuery("#webarx_captcha_public_key, #webarx_captcha_private_key").show();
                }else{
                    jQuery("#webarx_captcha_public_key, #webarx_captcha_private_key").hide();
                    jQuery("#webarx_captcha_public_key_v3, #webarx_captcha_private_key_v3").show();
                }
                
				jQuery("#webarx_captcha_type").change(function() {
					if(jQuery("#webarx_captcha_type").val() == "v2"){
						jQuery("#webarx_captcha_public_key_v3, #webarx_captcha_private_key_v3").hide();						
						jQuery("#webarx_captcha_public_key, #webarx_captcha_private_key").show();           
					}else{
						jQuery("#webarx_captcha_public_key, #webarx_captcha_private_key").hide();						
						jQuery("#webarx_captcha_public_key_v3, #webarx_captcha_private_key_v3").show();
					}
				});
            });
        </script>
        ');
    }

	public function webarx_captcha_public_key_input() {
		$string1 = __('<br><br>Enter the reCAPTCHA site key here.<br>Click <a href="https://support.patchstack.com/technical-support-and-troubleshooting/plugin/how-to-get-the-site-and-secret-key-for-the-recaptcha-feature" target="_blank">here</a> for a guide on how to get the site / secret key.', 'webarx');
		echo('<input style="display:none;" type="text" name="webarx_captcha_public_key" id="webarx_captcha_public_key" value="' . htmlspecialchars($this->get_option('webarx_captcha_public_key', ''), ENT_QUOTES) . '"/>'
            . '<input style="display:none;" type="text" name="webarx_captcha_public_key_v3" id="webarx_captcha_public_key_v3" value="' . htmlspecialchars($this->get_option('webarx_captcha_public_key_v3', ''), ENT_QUOTES) . '"/>'
			. '<label for="webarx_captcha_public_key"><i>' . $string1 . '</i></label>');
	}

	public function webarx_captcha_private_key_input() {
		$string1 = __('<br><br>Enter the reCAPTCHA secret key here.<br>Click <a href="https://support.patchstack.com/technical-support-and-troubleshooting/plugin/how-to-get-the-site-and-secret-key-for-the-recaptcha-feature" target="_blank">here</a> for a guide on how to get the site / secret key.', 'webarx');
		echo('<input style="display:none;" type="text" name="webarx_captcha_private_key" id="webarx_captcha_private_key" value="' . htmlspecialchars($this->get_option('webarx_captcha_private_key', ''), ENT_QUOTES) . '"/>'
            . '<input style="display:none;" type="text" name="webarx_captcha_private_key_v3" id="webarx_captcha_private_key_v3" value="' . htmlspecialchars($this->get_option('webarx_captcha_private_key_v3', ''), ENT_QUOTES) . '"/>'
			. '<label for="webarx_captcha_private_key"><i>' . $string1 . '</i></label>');
	}

	public function webarx_blackhole_log_input() {
        $ip_list = htmlspecialchars(rtrim($this->get_option('webarx_ip_block_list', '')), ENT_QUOTES);
		echo('<p><textarea rows="5" id="webarx_ip_block_list"  name="webarx_ip_block_list">'. $ip_list . '</textarea>Each entry must be on its own line.<br><br>
        <strong>Following formats are accepted:</strong>
            <p>127.0.0.1</p>
            <p>127.0.0.*</p>
            <p>127.0.0.0/24</p>
            <p>127.0.0.0-127.0.0.255</p>');
	}

	public function webarx_whitelist_input() {
		echo '<textarea name="webarx_whitelist" id="webarx_whitelist" rows="20" cols="50">' . htmlspecialchars($this->get_option('webarx_whitelist'), ENT_QUOTES) . '</textarea>';
		echo __('<p>Each rule must be on a new line.<br><br><strong>The following keywords are accepted</strong><br>IP:IPADDRESS<br>PAYLOAD:someval<br>URL:/someurl<br><br><strong>Definitions</strong><br>IP = firewall will not run against the IP<br>PAYLOAD = if the entire payload contains the keyword, the firewall will not proceed<br>URL = if the URL contains given URL, firewall will not proceed<br><br><strong>Example</strong><br>IP:192.168.1.1<br>PAYLOAD:contact_form<br>URL:water<br>URL:/some-form<br><br>In this scenario, the firewall will not run if the IP address is 192.168.1.1 or if the payload contains contact_form or if the URL contains water or if the URL contains /some-form.</p>', 'webarx');
	}
}