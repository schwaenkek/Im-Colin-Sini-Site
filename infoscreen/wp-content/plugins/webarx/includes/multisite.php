<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to handle anything multisite related.
 */
class W_Multisite extends W_Core
{
    /**
     * Stores any license activation errors.
     * @var string
     */
    public $error = '';

    /**
     * Add the actions required for the multisite functionality.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
        parent::__construct($core);
        if (!is_super_admin()) {
            return;
        }

        // When settings are saved.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['option_page'], $_POST['WebarxNonce']) && wp_verify_nonce($_POST['WebarxNonce'], 'webarx-option-page')) {
            $this->save_settings();
        }

        // When sites are activated.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['webarx_do'], $_POST['WebarxNonce'], $_POST['sites']) && $_POST['webarx_do'] == 'do_licenses' && wp_verify_nonce($_POST['WebarxNonce'], 'webarx-multisite-activation')) {
            $this->activate_licenses();
        }
    }

    /**
     * When a user selects sites that need to be activated.
     * 
     * @return string
     */
    private function activate_licenses()
    {
        if (empty($_POST['sites'])) {
            $this->error = '<span style="color: #ff6262;">Please select at least 1 site to be activated.</span><br><br>';
            return;
        }

        // Determine which sites are already activated and skip those.
        $activate = array();
        $sites = get_sites();
        foreach ($sites as $site) {
            if (in_array($site->siteurl, $_POST['sites']) && get_blog_option($site->id, 'webarx_clientid') == '') {
                array_push($activate, $site->siteurl);
            }
        }

        // Make sure there is a site that can be activated.
        if (count($activate) == 0) {
            $this->error = '<span style="color: #ff6262;">None of the selected sites need activation.</span><br><br>';
            return;
        }

        // Add the site to the portal and retrieve the license for each site.
        $licenses = $this->plugin->api->get_site_licenses(['sites' => $activate]);

        // Did an error happen during the multisite license activation?
        if (isset($licenses['error'])) {
            $this->error = '<span style="color: #ff6262;">' . $licenses['error'] . '</span><br><br>';
            return;
        }
        
        // Activate licenses on given sites
        $sites = get_sites();
        foreach ($sites as $site) {
            if (isset($licenses[$site->siteurl])) {
                $this->plugin->activation->activate_multisite_license($site, $licenses[$site->siteurl]);
            }
        }
    }

    /**
     * When the individual or global settings are saved.
     * 
     * @return void
     */
    private function save_settings()
    {
        switch ($_POST['option_page']) {
            // Save hardening options
            case 'webarx_hardening_settings_group':
                $options = array('webarx_display_widget', 'webarx_auto_update', 'webarx_json_is_disabled', 'webarx_register_email_blacklist', 'webarx_pluginedit', 'webarx_move_logs', 'webarx_basicscanblock', 'webarx_userenum', 'webarx_hidewpversion', 'webarx_activity_log_is_enabled', 'webarx_activity_log_failed_logins', 'webarx_xmlrpc_is_disabled', 'webarx_captcha_on_comments', 'webarx_captcha_login_form', 'webarx_captcha_registration_form', 'webarx_captcha_reset_pwd_form', 'webarx_captcha_type', 'webarx_captcha_public_key', 'webarx_captcha_public_key_v3', 'webarx_captcha_private_key', 'webarx_captcha_private_key_v3', 'webarx_application_passwords_disabled');
                $this->save_options($options);
                break;

            // Save firewall settings
            case 'webarx_firewall_settings_group':
                $options = array('webarx_geo_block_countries', 'webarx_geo_block_enabled', 'webarx_geo_block_inverse', 'webarx_ip_block_list', 'webarx_basic_firewall', 'webarx_autoblock_blocktime', 'webarx_autoblock_attempts', 'webarx_autoblock_minutes', 'webarx_basic_firewall_roles', 'webarx_disable_htaccess', 'webarx_add_security_headers', 'webarx_prevent_default_file_access', 'webarx_block_debug_log_access', 'webarx_index_views', 'webarx_proxy_comment_posting', 'webarx_image_hotlinking', 'webarx_firewall_custom_rules', 'webarx_firewall_custom_rules_loc', 'webarx_blackhole_log', 'webarx_whitelist');
                $this->save_options($options);
                break;

            // Save login settings
            case 'webarx_login_settings_group':
                $options = array('webarx_mv_wp_login', 'webarx_rename_wp_login', 'webarx_block_bruteforce_ips', 'webarx_anti_bruteforce_blocktime', 'webarx_anti_bruteforce_attempts', 'webarx_anti_bruteforce_minutes', 'webarx_login_time_block', 'webarx_login_time_start', 'webarx_login_time_end', 'webarx_login_2fa', 'webarx_login_whitelist');
                $this->save_options($options);
                break;

            // Save cookie notice settings
            case 'webarx_cookienotice_settings_group':
                $options = array('webarx_enable_cookie_notice_message', 'webarx_cookie_notice_message', 'webarx_cookie_notice_accept_text', 'webarx_cookie_notice_backgroundcolor', 'webarx_cookie_notice_textcolor', 'webarx_cookie_notice_privacypolicy_enable', 'webarx_cookie_notice_privacypolicy_text', 'webarx_cookie_notice_privacypolicy_link', 'webarx_cookie_notice_cookie_expiration', 'webarx_cookie_notice_opacity', 'webarx_cookie_notice_credits');
                $this->save_options($options);
                break;
        }
    }

    /**
     * This will be called when the network admin clicks on the "Sites" button.
     * It will show all sites.
     * 
     * @return void
     */
    public function sites_section_callback()
    {
        require_once dirname(__FILE__) . '/views/pages/multisite-table.php';
    }

    /**
     * Save an array of options on a specific site.
     * 
     * @param array $options
     * @param integer|bool $site_id
     * @return void
     */
    private function save_options($options)
    {
        if (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings') {
            foreach ($options as $option) {
                $value = isset($_POST[$option]) ? $_POST[$option] : 0;
                update_site_option($option, $value);
            }
        } else {
            foreach ($options as $option) {
                $value = isset($_POST[$option]) ? $_POST[$option] : 0;
                update_option($option, $value);
            }
        }
    }
}
