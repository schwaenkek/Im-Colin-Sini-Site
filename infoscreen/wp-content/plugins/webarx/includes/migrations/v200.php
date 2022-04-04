<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// Add the apply_ban column to the firewall_log table.
$wpdb->query("ALTER TABLE `" . $wpdb->prefix . "webarx_firewall_log` ADD `apply_ban` INT NOT NULL DEFAULT '1'");

// Migrate manual IP blocks to the new setting.
$ipRules = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "webarx_ip");
$ipList = '';
if (!empty($ipRules)) {
    foreach ($ipRules as $ip) {
        $ipList .= $ip->ip . "\n";
    }

    update_option('webarx_ip_block_list', $ipList);
}

// We put this here because it was not properly added to a past migration.
$column = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s AND COLUMN_NAME = %s",
    DB_NAME, $prefix . 'webarx_firewall_log', 'block_type'
));

if (empty($column)) {
    $wpdb->query("ALTER TABLE " . $prefix . "webarx_firewall_log ADD block_type VARCHAR (255) NOT NULL DEFAULT ''");
    $wpdb->query("ALTER TABLE " . $prefix . "webarx_firewall_log ADD block_params VARCHAR (255) NOT NULL DEFAULT ''");
}

$hasLogsTable = $wpdb->get_var("SHOW TABLES LIKE '" . $prefix . 'webarx_event_log' . "'") == $prefix . 'webarx_event_log';
if (!$hasLogsTable) {
    require 'v12.php';
}

// Now migrate the firewall and whitelist rules to the database.
if (file_exists($this->plugin->path . 'data/rules.json')) {
    $rules = file_get_contents($this->plugin->path . 'data/rules.json');
    update_option('webarx_firewall_rules', $fw_rules);
}

if (file_exists($this->plugin->path . 'data/firewall-whitelist.php')) {
    $rules = file_get_contents($this->plugin->path . 'data/firewall-whitelist.php');
    update_option('webarx_whitelist_rules', $fw_rules);
}

if (file_exists($this->plugin->path . 'data/firewall-whitelist-custom.php')) {
    $rules = file_get_contents($this->plugin->path . 'data/firewall-whitelist-custom.php');
    update_option('webarx_custom_whitelist_rules', $fw_rules);
}

update_option('webarx_db_version', '2.0.0');
