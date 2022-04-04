<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to determine if the IP address of the 
 * user is banned. Along with that we check the IP address whitelist.
 */
class W_Ban extends W_Core
{
	/**
	 * Add the actions required for determining the ban.
	 *
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('init', array($this, 'ip_ban'), ~PHP_INT_MAX + 1);
	}

	/**
	 * Determine if the IP address of the user is blocked.
	 *
	 * @return void
	 */
	public function ip_ban()
	{
		if (!is_user_logged_in() && $this->is_ip_blocked($this->get_ip())) {
			$this->plugin->firewall_base->display_error_page(22);
		}
	}

	/**
	 * Check IP ban.
	 *
	 * @param string $ip The IP address of the user.
	 * @return boolean Whether or not the user is blocked.
	 */
	public function is_ip_blocked($ip)
	{
		global $wpdb;

		$ipRules = $this->get_option('webarx_ip_block_list', '');
		if (empty($ipRules)) {
			return false;
		}

		$blocked = false;
		$ipRules = explode("\n", $ipRules);
		foreach ($ipRules as $blocked_ip) {
			$blocked_ip = trim($blocked_ip);
            if (strpos($blocked_ip, '*') !== false) {
                $blocked = $this->check_wildcard_rule($ip, $blocked_ip);
            } elseif (strpos($blocked_ip, '-') !== false) {
                $blocked = $this->check_range_rule($ip, $blocked_ip);
            } elseif (strpos($blocked_ip, '/') !== false) {
                $blocked = $this->check_subnet_mask_rule($ip, $blocked_ip);
            } elseif ($ip == $blocked_ip) {
                $blocked = true;
            }

            if ($blocked == true) {
                return true;
            }
        }

		return $blocked;
	}

	/**
	 * Check IP whitelist for login protection.
	 * 
	 * @param string $ip The IP address of the user.
	 * @return boolean Whether or not the user is whitelisted.
	 */
	public function is_ip_whitelisted($ip)
	{
		$ipRules = explode("\n", $this->get_option('webarx_login_whitelist', ''));
		if (empty($ipRules)) {
			return true;
		}

		$whiteListed = false;
		foreach ($ipRules as $ipRule) {
            if (strpos($ipRule, '*') !== false) {
                $whiteListed = $this->check_wildcard_rule($ip, $ipRule);
            } elseif (strpos($ipRule, '-') !== false) {
                $whiteListed = $this->check_range_rule($ip, $ipRule);
            } elseif (strpos($ipRule, '/') !== false) {
                $whiteListed = $this->check_subnet_mask_rule($ip, $ipRule);
            } elseif ($ip == $ipRule) {
                $whiteListed = true;
            }

            if ($whiteListed == true) {
                return true;
            }
        }

		return $whiteListed;
	}

	/**
	 * CIDR notation IP block check.
	 * 
	 * @param string $ip The IP address of the user.
	 * @param string $range The range to check.
	 * @return boolean Whether or not the IP is in the range.
	 */
	public function check_subnet_mask_rule($ip, $range)
    {
        list($range, $netmask) = explode('/', $range, 2);
        $range_decimal = ip2long($range);
        $ip_decimal = ip2long($ip);
        $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
        $netmask_decimal = ~ $wildcard_decimal;
        return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
    }

	/**
	 * Wildcard IP block check.
	 * 
	 * @param string $ip The IP address of the user.
	 * @param string $rule The wildcard range to check against.
	 * @return boolean Whether or not the IP is in the wilcard range.
	 */
	public function check_wildcard_rule($ip, $rule)
    {
		$match = explode('*', $rule);
		$match = $match[0];
        return (substr($ip, 0, strlen($match)) == $match);
    }

	/**
	 * IP range block check.
	 * 
	 * @param string|array $ip The IP address of the user.
	 * @param string $rule The range to check against.
	 * @return boolean Whether or not the IP is in the range.
	 */
    public function check_range_rule($ip, $rule)
    {
        // Check if client has multiple IPs
        if (is_array($ip)) {
            $ip = $ip[0];
		}
		
        $firstIp = explode('-', $rule);
        $secondIp = explode('-', $rule);

        $startIp = ip2long($firstIp[0]);
        $endIp = ip2long($secondIp[1]);
        $requestIp = ip2long($ip);

        return ($requestIp >= $startIp && $requestIp <= $endIp);
    }
}
