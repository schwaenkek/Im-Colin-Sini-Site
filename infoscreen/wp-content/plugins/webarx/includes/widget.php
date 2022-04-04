<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

/**
 * This class is used for the widget on the dashboard of wp-admin.
 */
class W_Widget extends W_Core
{
	/**
	 * Add the actions required to show the widget on the dashboard.
	 *
	 * @param Webarx $core
	 * @return void
	 */
	public function __construct($core)
	{
        parent::__construct($core);
        if (current_user_can('administrator') && $this->get_option('webarx_display_widget', true)) {
            add_action('wp_dashboard_setup', array($this, 'add_dashboard_widgets'));
        }
    }

    /**
     * Insert the widget into the dashboard.
     * 
     * @return void
     */
    public function add_dashboard_widgets()
    {
        global $wp_meta_boxes;
        wp_add_dashboard_widget('webarx_dashboard_widget', 'Patchstack' , array($this, 'dashboard_widget_function'));
        $dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
        $my_widget = array('webarx_dashboard_widget' => $dashboard['webarx_dashboard_widget']);
        unset($dashboard['webarx_dashboard_widget']);
        $sorted_dashboard = array_merge($my_widget, $dashboard);
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    }

    /**
     * Create the function to output the contents of our dashboard widget.
     * 
     * @return void
     */
    public function dashboard_widget_function()
    {
        $license_key = get_option('webarx_api_token');
        if (empty($license_key)) {
            echo '<div style="width:100%" class="webarx_license"><b>' . __('You have not entered your license.', 'webarx') . '</b></div>';
        } else {
            $this->widget_firewall();
        }
    }

    /**
     * Generate the dashboard widget.
     * 
     * @return void
     */
    public function widget_firewall()
    {
        $sum_of_firewall = $this->get_firewall_state();

        global $wpdb;
        $item_array = $wpdb->get_results("
            SELECT a.log_date, a.fid, b.description 
            FROM " . $wpdb->prefix . "webarx_firewall_log AS a
            LEFT JOIN " . $wpdb->prefix . "webarx_logic AS b ON b.id = a.fid
            ORDER BY a.id DESC
            LIMIT 0,5"
        );

        // Get attack statistics.
        $stats = $wpdb->get_results("SELECT COUNT(id) AS num, DATE(log_date) as 'dateday' FROM " . $wpdb->prefix . "webarx_firewall_log where log_date > DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY dateday ORDER BY dateday ASC", ARRAY_A);
        $countAttacks = $wpdb->get_results("SELECT COUNT(id) AS num FROM " . $wpdb->prefix . "webarx_firewall_log WHERE log_date > DATE_SUB(NOW(), INTERVAL 7 DAY)");

        // Fill in any missing days and remove unneeded stuff.
        $attackSums = $this->fill_firewall_logs($stats);
        $attacks = array();
        foreach ($attackSums as $key=>$val) {
            array_push($attacks, $val['num']);
        }

        // Generate last 7 days
        $weekDates = array();
        for ($i = 0; $i <= 6; $i++) {
            $date = date('Y-m-d', mktime(0, 0, 0, date('m'), (date('d') - $i), date('y')));
            $datetime = DateTime::createFromFormat('Y-m-d', $date);
            $weekDates[] = $datetime->format('m-d');
        }

        // If everything is enabled
        $message = 'Website is protected!';
        $backColor = '#102d42';
        $textColor = '#35c1c9';
        $icon = 'safe';
        $link = '#';

        // If some firewall settings are disabled
        if (get_site_option('webarx_prevent_default_file_access', 0) != 1 || get_site_option('webarx_block_debug_log_access', 0) != 1 || get_site_option('webarx_index_views', 0) != 1 || get_site_option('webarx_proxy_comment_posting', 0) != 1) {
            $message = 'Some firewall settings are disabled!';
            if ($this->get_option('webarx_captcha_login_form') != 1) {
                $message = "Some firewall and security settings are disabled!";
            }
            $backColor = 'rgba(187, 168, 8, 0.15)';
            $textColor = '#bba808';
            $icon = 'warning';
            $link = admin_url('admin.php?page=' . $this->plugin->name) . '&tab=firewall';
        }

        // Check if update is needed
        if ($this->has_updates()) {
            $message = "You have software that needs to be updated!";
            $backColor = "rgba(187, 168, 8, 0.15)";
            $textColor = "#bba808";
            $icon = "warning";
            $link = admin_url('update-core.php');
        }

        //Check if firewall is turned off
        if ($this->get_option('webarx_basic_firewall', 0) == 0) {
            $message = 'Firewall is turned off!';
            $backColor = 'rgba(210, 35, 44, 0.15)';
            $textColor = '#d2232c';
            $icon = 'error';
            $link = admin_url('admin.php?page=' . $this->plugin->name) . '&tab=firewall';
        }

        require_once dirname(__FILE__) . '/views/widget.php';
    }

    /**
     * Determine if the firewall is turned on or not.
     * 
     * @return integer
     */
    public function get_firewall_state()
    {
        $sum = 0;
        foreach (array('webarx_prevent_default_file_access', 'webarx_basic_firewall', 'webarx_pingback_protection', 'webarx_block_debug_log_access', 'webarx_block_fake_bots', 'webarx_index_views', 'webarx_trace_and_track', 'webarx_proxy_comment_posting', 'webarx_image_hotlinking') as $option) {
            $value = get_site_option($option, 0);
            $sum += empty($value) ? 0 : 1;
        }

        return $sum;
    }

    /**
     * Fill missing or empty firewall log entries.
     * 
     * @param array $logs The logs of the firewall.
     * @param integer $daysOffset If set, number of days to subtract from days calculation.
     * @return array Array with all missing entries filled in with count 0.
     */
    public function fill_firewall_logs($logs, $daysOffset = 0)
    {
        // Reconstruct the firewall logs array in case there are missing days.
        if (count($logs) != 7) {

            // Get the days that we need to have in the logs chart.
            $days = array();
            for ($i = 6; $i >= 0; $i--) {
                array_push($days, date('Y-m-d', strtotime('-' . ($i - $daysOffset) . ' days')));
            }

            if (count($logs) == 0) {
                // No data at all, so fill it.
                for ($i = 0; $i <= 6; $i++) {
                    array_push($logs, array('num' => 0, 'dateday' => $days[$i]));
                }
            } else {
                // Loop through the attacklogs to add missing days.
                $tempLogs = array();
                foreach ($days as $index=>$day) {
                    $foundFlag = false;

                    // Loop through each firewall log entry to find if the day exists.
                    foreach ($logs as $index=>$data) {
                        if ($day == $data['dateday']) {
                            array_push($tempLogs, array('num' => $data['num'], 'dateday' => $data['dateday']));
                            $foundFlag = true;
                            break;
                        }
                    }

                    // Day is not in the array, add it.
                    if (!$foundFlag) {
                        array_push($tempLogs, array('num' => 0, 'dateday' => $day));
                    }
                }
                $logs = $tempLogs;
            }
        }

        return $logs;
    }

    /**
     * Determine if the site has plugins, themes or a WordPress version installed
     * that needs to be updated.
     * 
     * @return boolean
     */
    public function has_updates()
    {
        $sw_data = $this->plugin->upload->get_software_data();
        foreach ($sw_data as $software) {
            if ($software['sw_new_ver'] != '') {
                return true;
            }
        }

        return false;
    }
}
