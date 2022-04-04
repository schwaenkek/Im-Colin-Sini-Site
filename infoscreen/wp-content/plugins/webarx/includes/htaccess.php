<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to perform interactions with the 
 * .htaccess file.
 */
class W_Htaccess extends W_Core
{
	/**
	 * Add the actions required for htaccess interactions.
	 *
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('updated_option', array($this, 'update_option_extras'), 10, 3);
	}

	/**
	 * If option is updated, write to .htaccess file.
	 * 
	 * @return void
	 */
	public function update_option_extras($option_name, $old_value, $value)
	{
		if (in_array($option_name, array('webarx_prevent_default_file_access', 'webarx_basic_firewall', 'webarx_pingback_protection', 'webarx_block_debug_log_access', 'webarx_block_fake_bots', 'webarx_index_views', 'webarx_trace_and_track', 'webarx_proxy_comment_posting', 'webarx_image_hotlinking', 'webarx_firewall_custom_rules'))) {
			$this->write_to_htaccess();
		}
	}

	/**
	 * Get the turned on .htaccess firewall settings.
	 * 
	 * @return array
	 */
	public function get_firewall_rule_settings()
	{
		$settings = array();
		$options = array('webarx_prevent_default_file_access', 'webarx_basic_firewall', 'webarx_block_debug_log_access', 'webarx_block_fake_bots', 'webarx_index_views', 'webarx_proxy_comment_posting', 'webarx_image_hotlinking', 'webarx_basicscanblock');
		foreach ($options as $option) {
			if (get_site_option($option)) {
				$settings[] = ($option == 'webarx_basicscanblock' ? 'webarx_wpscan_block' : $option);
			}
		}

		return $settings;
	}

	/**
	 * Determine the current state of the firewall.
	 * 
	 * @return boolean
	 */
	public function firewall() {
		// Update the options.
		$sum_of_firewall = $this->plugin->widget->get_firewall_state();
		$onoff = $sum_of_firewall > 1 ? 0 : 1;
		foreach (array('webarx_prevent_default_file_access', 'webarx_basic_firewall', 'webarx_block_debug_log_access', 'webarx_index_views', 'webarx_proxy_comment_posting') as $option) {
			update_site_option($option, $onoff);
		}
		update_site_option('webarx_block_fake_bots', 0);
		update_site_option('webarx_image_hotlinking', 0);

		// Pull the rules or cleanup the .htaccess file?
		if ($onoff == 1) {
			$this->plugin->rules->post_firewall_rules();
		} else {
			$this->cleanup_htaccess_file();
		}

		return true;
	}

	/**
	 * Write to the .htaccess file.
	 * 
	 * @param string $rules The rules to write to the .htaccess file.
	 * @return boolean.
	 */
	public function write_to_htaccess($rules = '')
	{
		if (!$this->is_server_supported() || get_site_option('webarx_disable_htaccess', 0)) {
			return false;
		}

		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
		$fs = new WP_Filesystem_Direct('');
		if (!$fs->exists(ABSPATH . '.htaccess')) {
			$fs->touch(ABSPATH . '.htaccess');
		}

		return $this->plugin->htaccess->self_check($rules);
	}

	/**
	 * Get the web-server type.
	 *
	 * @return boolean
	 */
	public function is_server_supported()
	{
		$server = strtolower(filter_var($_SERVER['SERVER_SOFTWARE'], FILTER_SANITIZE_STRING));
		foreach (array('apache', 'nginx', 'litespeed') as $webserver) {
			if (strstr($server, $webserver)) {
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Write .htaccess directly without causing a server missconfiguration (500)
	 * (PHP will end the process even if the browser-window was closed.)
	 * 
	 * @param string $newRules The rules that we received from the API.
	 * @return boolean
	 */
	public function self_check($newRules)
	{
		// Don't continue if we have no rules
		if (empty($newRules)) {
			return false;
		}
		$newRules = PHP_EOL . PHP_EOL . '# BEGIN WebARX' . PHP_EOL . $newRules . PHP_EOL . '# END WebARX' . PHP_EOL . PHP_EOL;

		// Require the filesystem libraries.
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
		$fs = new WP_Filesystem_Direct('');

		// Don't continue if .htaccess does not exist or cannot be written to.
		if (!$fs->exists(ABSPATH . '.htaccess')) {
			return false;
		}

		// Get the current data in the .htaccess file.
		$currentRules = $oldRules = $fs->get_contents(ABSPATH . '.htaccess');

		// Delete all Patchstack related stuff so we can properly re-inject it.
		$currentRules = $this->delete_all_between('# BEGIN WebARX', '# END WebARX', $currentRules);
		$currentRules = $this->delete_all_between('# CUSTOM WEBARX RULES', '# END CUSTOM WEBARX RULES', $currentRules);
		$newRules = $this->delete_all_between('# BEGIN WordPress', '# END WordPress', $newRules);

		// Get the custom .htaccess rules, if any are set.
        $customRules = $this->get_custom_rules();

        if (get_site_option('webarx_firewall_custom_rules_loc', 'bottom') == 'top') {
            $newRules = $customRules . $newRules;
        } else {
            $newRules = $newRules . $customRules;
		}

		// Determine if the new rules even need to be saved.
		$rulesHash = sha1($newRules);
		if (get_site_option('webarx_htaccess_rules_hash', '') == $rulesHash && stripos($oldRules, 'begin webarx') !== false) {
			return false;
		}
		
		// Save the new rules and adjust # and newline of our own rules.
		update_site_option('webarx_htaccess_rules_hash', $rulesHash);
		$newRules = preg_replace("/[\r\n]+/", "\r\n", $newRules);
		$newRules = preg_replace("/#/", "\r\n#", $newRules);

		// In order to support all Patchstack plugin versions with newline fix, we have to remove this part ourselves.
		$newRules = str_replace("\r\n\r\n# BEGIN WebARX", "# BEGIN WebARX", $newRules);
		$newRules = str_replace("# END WebARX\r\n", "# END WebARX", $newRules);

		// Remove RewriteBase / from the Patchstack rules.
		$newRules = str_replace("\r\n  RewriteBase /", '', $newRules);
		$newRules = str_replace('/index.php', 'index.php', $newRules);

		// Merge the rules together.
		$newRules = $newRules . "\n" . $currentRules;

		// Determine if the Patchstack rules starts on its own line.
		$lines = explode("\n", $newRules);
		foreach ($lines as $line) {
			if (stripos($line, 'begin webarx') !== false && trim(strtolower($line)) != '# begin webarx') {
				$newRules = str_replace('# BEGIN WebARX', "\r\n# BEGIN WebARX", $newRules);
			}
		}

		// Put the contents into the .htaccess file.
        $fs->put_contents(ABSPATH . '.htaccess', $newRules, FS_CHMOD_FILE);

		// Check if the new rules work.
		// 500 internal server error - did not work. Restore old rules.
		$status = $this->get_site_status_code();
		if ($status >= 500) {
			$fs->put_contents(ABSPATH . '.htaccess', $oldRules , FS_CHMOD_FILE);
			update_site_option('webarx_firewall_custom_rules', '');
		}

		return $status < 500;
	}

	/**
	 * Retrieve the custom .htaccess rules and inject into the .htaccess file.
	 * 
	 * @return string
	 */
	public function get_custom_rules()
    {
        $customRules = get_site_option('webarx_firewall_custom_rules', '');
        if (empty($customRules) || is_array($customRules) || $customRules == 'Array') {
            $customRules = '';
        }

		// Do we have any custom rules to inject?
		$tmp = '';
        if ($customRules != '') {
            $tmp = PHP_EOL . "# CUSTOM WEBARX RULES" . PHP_EOL;
            $tmp .= $customRules . PHP_EOL;
            $tmp .= "# END CUSTOM WEBARX RULES";
        }
        return $tmp;
    }

	/**
	 * Retrieve the status code of the site.
	 * This is done to determine if the .htaccess rules do not
	 * trigger an error.
	 * 
	 * @return integer
	 */
	public function get_site_status_code()
    {
        $handle = curl_init(get_site_url());
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_exec($handle);
        $httpcode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return $httpcode;
    }

	/**
	 * Remove all Patchstack rules from the .htaccess file.
	 * 
	 * @return void
	 */
	public function cleanup_htaccess_file()
	{
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
		require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');
		$fs = new WP_Filesystem_Direct('');

		if ($fs->exists(ABSPATH . '.htaccess')) {
			$curdata = $fs->get_contents(ABSPATH . '.htaccess');
			$rules = $this->delete_all_between('# BEGIN WebARX', '# END WebARX', $curdata);
			$rules = $this->delete_all_between('# CUSTOM WEBARX RULES', '# END CUSTOM WEBARX RULES', $rules);
			$fs->put_contents(ABSPATH . '.htaccess', $rules , FS_CHMOD_FILE);
		}
	}

	/**
	 * Delete characters between a begin and end string.
	 * 
	 * @param string $beginning Begin string.
	 * @param string $end End string.
	 * @param string $string The string to delete data from.
	 * @return string
	 */
	public function delete_all_between($beginning, $end, $string)
	{
		$beginningPos = strpos($string, $beginning);
		$endPos = strpos($string, $end);
		if ($beginningPos === false || $endPos === false) {
			return $string;
		}

		$textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
		return str_replace($textToDelete, '', $string);
	}
}
