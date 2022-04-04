<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to pull the firewall/whitelist rules from our API.
 */
class W_Rules extends W_Core
{
	/**
	 * Add the actions required to pull the rules.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('webarx_post_firewall_rules', array($this, 'post_firewall_rules'));
		add_action('webarx_post_dynamic_firewall_rules', array($this, 'dynamic_firewall_rules'));
	}

	/**
	 * Pull the .htaccess rules from the API.
	 * Then apply it to the .htaccess file after we create a backup.
	 *
	 * @return void
	 */
	public function post_firewall_rules()
	{
		$rules = $this->plugin->htaccess->get_firewall_rule_settings();
		$settings = wp_json_encode($rules);
		$results = $this->plugin->api->post_firewall_rule(array('settings' => $settings));
		
		// If no rules returned, we assume all settings are turned off.
		if (empty($results)) {
			$results['rules'] = '';
		}	

		// We have rules so apply it to the .htaccess file.
		if (isset($results['rules'])) {
			$this->plugin->htaccess->write_to_htaccess($results['rules']);
			return;
		}
	}

	/**
	 * Pull the firewall/whitelist rules from the API.
	 *
	 * @return void
	 */
	public function dynamic_firewall_rules()
	{
		// Get the firewall and whitelist rules.
		$results = $this->plugin->api->post_firewall_rule_json();
		if (!isset($results['firewall'])) {
			return;
		}

		// Update firewall rules.
		update_option('webarx_firewall_rules', json_encode($results['firewall']));

		// Update whitelist rules.
		update_option('webarx_whitelist_rules', json_encode($results['whitelists']));

		// Update secondary whitelist rules.
		if (isset($results['whitelist_keys'])) {
			update_option('webarx_whitelist_keys_rules', json_encode($results['whitelist_keys']));
		}
	}
}
