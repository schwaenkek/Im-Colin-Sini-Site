<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to check for updates of the Patchstack plugin.
 */
class W_Update_Checker extends W_Core
{
	/**
	 * Add the actions required to check for updates.
	 *
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('init', array($this, 'check_for_update'));
	}

	/**
	 * Contact the Patchstack update server and check if the Patchstack plugin version is outdated.
	 * 
	 * @return void
	 */
	public function check_for_update()
	{
		// Only execute this on any type of "admin" request, including AJAX requests.
		if(!is_admin()){
			return;
		}

		// Load the library and check for updates.
		require_once $this->plugin->path . 'lib/plugin-update-checker/plugin-update-checker.php';
		Puc_v4_Factory::buildUpdateChecker(
			$this->plugin->update_checker_url, 
			$this->plugin->path . $this->plugin->name . '.php', 
			$this->plugin->name
		);
	}
}
