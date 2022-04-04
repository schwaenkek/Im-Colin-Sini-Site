<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

$wpdb->query("ALTER TABLE `" . $prefix . "webarx_ip` CHANGE `ip` `ip` VARCHAR(200) NOT NULL;");
$sql = "CREATE TABLE IF NOT EXISTS `" . $prefix . "webarx_event_log` (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            author tinytext  NULL,
            ip tinytext  NULL,
            flag tinytext NULL,
            object tinytext  NULL,
            object_id tinytext  NULL,
            object_name text  NULL,
            action tinytext NULL,
            date datetime  NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
dbDelta($sql);
update_option('webarx_db_version', '1.2');