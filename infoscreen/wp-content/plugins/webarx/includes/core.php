<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * The core class is used as a base class for all the other classes.
 * This will allow us to declare certain global methods/variables.
 */
class W_Core
{
    /**
     * This will allow us to communicate between classes.
     * @var Webarx
     */
    public $plugin;

    /**
     * Whether or not the site is a multisite.
     * @var boolean
     */
    private $isMultiSite = false;

    /**
     * @param Webarx $plugin
     * @return void
     */
    public function __construct($plugin)
    {
        $this->plugin = $plugin;
        $this->isMultiSite = is_multisite();
    }

    /**
     * In case of multisite we want to determine if there's a difference between the
     * network setting and site setting and if so, use the site setting.
     * 
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get_option($name, $default = false)
    {
        // We always want to return the site option on the default settings management page.
        if (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings' && is_super_admin()) {
            return get_site_option($name, $default);
        }

        // Get the setting of the current site.
        $secondary = get_option($name, $default);

        // Get the setting of the network and in case there's a difference,
        // return the value of site.
        $main = get_site_option($name, $default);
        return $main != $secondary ? $secondary : $main;
    }

    /**
     * In case we need to retrieve the option of a specific site, we can use this.
     * It will determine if it's on a multisite environment and if so, use get_blog_option.
     * 
     * @param int $site_id
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get_blog_option($site_id, $name, $default = false)
    {
        if ($this->isMultiSite) {
            return get_blog_option($site_id, $name, $default);
        }

        return get_option($name, $default);
    }

    /**
     * In case we need to update the option of a specific site, we can use this.
     * It will determine if it's on a multisite environment and if so, use update_blog_option.
     * 
     * @param int $site_id
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function update_blog_option($site_id, $name, $value)
    {
        if ($this->isMultiSite) {
            return update_blog_option($site_id, $name, $value);
        }

        return update_option($name, $value);
    }

    /**
     * Determine if the license is active and not expired.
     * 
     * @return boolean
     */
    public function license_is_active()
    {
        if (get_option('webarx_license_activated', 0)) {
            return true;
        }

        $expiry = get_option('webarx_license_expiry', '');
        if ($expiry != '' && (strtotime($expiry) < (time() + (3600 * 24)))) {
            return true;
        }

        return false;
    }
        
    /**
     * Determine if a given PHP function is disabled or not.
     * 
     * @param string $name Name of the function to check.
     * @return boolean Whether or not the function is available to call.
     */
    public function function_available($name)
    {
        $safe_mode = ini_get('safe_mode');
        if ($safe_mode && strtolower($safe_mode) != 'off') {
            return false;
        }

        // Determine if the function is available.
        if (in_array($name, array_map('trim', explode(',', ini_get('disable_functions'))))) {
            return false;
        }

        return true;
    }

    /**
     * Attempt to get the client IP by checking all possible IP (proxy) headers.
     * 
     * @return string
     */
    public function get_ip()
    {
        // IP address header override set?
        $ipOverride = get_site_option('webarx_firewall_ip_header', '');
        if ($ipOverride != '' && isset($_SERVER[$ipOverride])) {
            return $_SERVER[$ipOverride];
        }

        // Special case for Cloudflare.
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        // Special case for hosts that have a weird configuration.
        if ($this->function_available('php_uname')) {
            $uname = @php_uname();

            // Bluehos and Hostmonster store the real IP in $_SERVER['REMOTE_ADDR'] but the proxy IP in HTTP_X_FORWARDED_FOR.t
            if (strpos($uname, 'bluehost') !== false || strpos($uname, 'hostmonster') !== false) {
                return $_SERVER['REMOTE_ADDR'];
            }

            // Hostgator stores the real IP in $_SERVER['REMOTE_ADDR'] but the proxy IP in HTTP_X_FORWARDED_FOR.
            if ((strpos($uname, 'websitewelcome') || strpos($uname, 'hostgator')) && getenv('HTTP_X_FORWARDED_FOR') && getenv('HTTP_X_FORWARDED_FOR') != getenv('REMOTE_ADDR')) {
                return $_SERVER['REMOTE_ADDR'];
            }
        }

        // Otherwise check for existence of several IP headers.
        $ipAddress = 'UNKNOWN';
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $header) {
            if (getenv($header)) {
                $ipAddress = getenv($header);
                break;
            }
        }

        // In case of proxy, the first IP address in a comma seperated list
        // is usually the IP address of the visitor.
        if (strpos($ipAddress, ',') !== false) {
            $ips = explode(',', $ipAddress);
            return $ips[0];
        } else {
            return $ipAddress;
        }
    }
}