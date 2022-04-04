<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// Set the cron job offsets if it's not defined.
if (!get_option('webarx_cron_offset')) {
    $crons = array(
        'webarx_daily' => strtotime('today') + mt_rand(0, 86399),
        'webarx_hourly' => strtotime('today') + mt_rand(0, 3600),
        'webarx_trihourly' => strtotime('today') + mt_rand(0, 9599),
        'webarx_twicedaily' => strtotime('today') + mt_rand(0, 43199),
        'webarx_15minute' => strtotime('today') + mt_rand(0, 899)
    );
    update_option('webarx_cron_offset', $crons);
}else{
    // It is defined, so add the trihourly cron job if it does not exist yet.
    $crons = get_option('webarx_cron_offset');
    if(!isset($crons['webarx_trihourly'])){
        $crons['webarx_trihourly'] = strtotime('today') + mt_rand(0, 9599);
        update_option('webarx_cron_offset', $crons);
    }
}

update_option('webarx_db_version', '2.1.0');