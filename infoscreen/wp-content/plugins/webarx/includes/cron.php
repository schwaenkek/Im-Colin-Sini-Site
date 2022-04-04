<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used to schedule the tasks used by Patchstack.
 */
class W_Cron extends W_Core
{
	/**
	 * Add the actions required for the task scheduler.
	 * 
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
		parent::__construct($core);
		add_action('init', array($this, 'schedule_events'));
		add_filter('cron_schedules', array($this, 'cron_schedules'));
	}

	/**
	 * Define our own custom cron schedules.
	 * 
	 * @param array $schedules
	 * @return array
	 */
	public function cron_schedules($schedules)
	{
		// Define the scheduled tasks.
        $schedules['webarx_now'] = array(
            'interval' => 1,
			'display' => __('Right Away')
		);

		$schedules['webarx_15minute'] = array(
			'interval' => (60 * 15),
			'display' => __('Every 15 Minutes')
		);

		$schedules['webarx_hourly'] = array(
			'interval' => (60 * 60),
			'display' => __('Every Hour')
		);

		$schedules['webarx_trihourly'] = array(
			'interval' => (60 * 60 * 3),
			'display' => __('Every 3 Hours')
		);

		$schedules['webarx_twicedaily'] = array(
			'interval' => (60 * 60 * 12),
			'display' => __('Twice Daily')
		);

        $schedules['webarx_daily'] = array(
            'interval' => (60 * 60 * 24),
            'display' => __('Once Daily')
		);
		
		return $schedules;
	}

	/**
	 * Initialize our scheduled tasks.
	 * 
	 * @return void
	 */
	public static function schedule_events()
	{
		// Random time throughout the day to make sure not all sites synchronize at the same time.
		if (!get_option('webarx_cron_offset')) {
			$crons = array(
				'webarx_daily' => strtotime('today') + mt_rand(0, 86399),
				'webarx_hourly' => strtotime('today') + mt_rand(0, 3600),
				'webarx_trihourly' => strtotime('today') + mt_rand(0, 9599),
				'webarx_twicedaily' => strtotime('today') + mt_rand(0, 43199),
				'webarx_15minute' => strtotime('today') + mt_rand(0, 899)
			);
			update_option('webarx_cron_offset', $crons);
			
			// Clear existing scheduled events so we can use the new timestamps.
			foreach (array('webarx_send_software_data', 'webarx_send_hacker_logs', 'webarx_post_firewall_rules', 'webarx_post_dynamic_firewall_rules', 'webarx_update_license_status', 'webarx_send_event_logs', 'webarx_update_plugins', 'webarx_send_ping') as $event) {
				wp_clear_scheduled_hook($event);
			}
		} else {
			$crons = get_option('webarx_cron_offset');
		}

		// Schedule the events if they are not scheduled yet.
		if (!wp_next_scheduled('webarx_send_software_data')) {
			wp_schedule_event($crons['webarx_daily'], 'webarx_hourly', 'webarx_send_software_data');
		}
		if (!wp_next_scheduled('webarx_send_hacker_logs')) {
			wp_schedule_event($crons['webarx_15minute'], 'webarx_15minute', 'webarx_send_hacker_logs');
		}
		if (!wp_next_scheduled('webarx_post_firewall_rules')) {
			wp_schedule_event($crons['webarx_twicedaily'], 'webarx_twicedaily', 'webarx_post_firewall_rules');
		}
		if (!wp_next_scheduled('webarx_post_dynamic_firewall_rules')) {
			wp_schedule_event($crons['webarx_hourly'], 'webarx_hourly', 'webarx_post_dynamic_firewall_rules');
		}
		if (!wp_next_scheduled('webarx_update_license_status')) {
			wp_schedule_event($crons['webarx_twicedaily'], 'webarx_twicedaily', 'webarx_update_license_status');
		}
		if (!wp_next_scheduled('webarx_send_event_logs')) {
            wp_schedule_event($crons['webarx_15minute'], 'webarx_15minute', 'webarx_send_event_logs');
		}
		if (!wp_next_scheduled('webarx_update_plugins')) {
			wp_schedule_event($crons['webarx_15minute'], 'webarx_15minute', 'webarx_update_plugins');
		}
		if (!wp_next_scheduled('webarx_send_ping')) {
			wp_schedule_event($crons['webarx_trihourly'], 'webarx_trihourly', 'webarx_send_ping');
		}
	}
}
