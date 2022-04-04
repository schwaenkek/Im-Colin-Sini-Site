<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to block a user when a bad request has been
 * detected or matches our firewall rules in the .htaccess file.
 */
class W_Hacker_Log extends W_Core
{
	/**
	 * Add the actions required for the logging of hackers.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('init', array($this, 'webarx_init_internal'));
		add_filter('query_vars', array($this, 'webarx_query_vars'));
		add_action('parse_request', array($this, 'webarx_parse_request'));
	}

	/**
	 * Register the rewrite rules we use to block suspicious activity.
	 *
	 * @return void
	 */
	public function webarx_init_internal()
	{
		add_rewrite_rule('webarx-fpage.php$', 'index.php?webarx_fpage=$matches[1]', 'top');
		add_rewrite_rule('webarx-errorpage.php$', 'index.php?webarx_errorpage=$matches[1]', 'top');
	}

	/**
	 * Register our query parameters to WordPress.
	 *
	 * @param array $query_vars
	 * @return array
	 */
	public function webarx_query_vars($query_vars)
	{
		$query_vars[] = 'webarx_errorpage';
		$query_vars[] = 'webarx_fpage';
		return $query_vars;
	}

	/**
	 * Parse the incoming request and determine if our registered
	 * parameters are set.
	 *
	 * @param WP $query
	 * @return void|WP
	 */
	public function webarx_parse_request($query)
	{
		if (is_user_logged_in()) {
			return $query;
		}

		if (array_key_exists('webarx_errorpage', $query->query_vars)) {
			$this->plugin->firewall_base->display_error_page((int) $query->query_vars['webarx_errorpage']);
		} elseif (array_key_exists('webarx_fpage', $query->query_vars)) {
			$this->plugin->firewall_base->display_error_page((int) $query->query_vars['webarx_fpage']);
		} else {
			return $query;
		}
	}
}
