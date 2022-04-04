<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

$fs = new WP_Filesystem_Direct('');
$fs->delete($this->plugin->path . 'data/rules.json');
$fs->delete($this->plugin->path . 'data/firewall-whitelist.php');
update_option('webarx_db_version', '1.3.5');