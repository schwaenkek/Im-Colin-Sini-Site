<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

$wpdb->query("ALTER TABLE `" . $prefix . "webarx_firewall_log` ADD `post_data` LONGTEXT NULL");
update_option('webarx_db_version', $this->plugin->version);
