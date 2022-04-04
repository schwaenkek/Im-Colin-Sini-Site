<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// Determine the active tab and account activation state.
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'hardening'; //default active tab
$activated = ((isset($_GET['activated']) && $_GET['activated'] == 1) || (isset($_GET['active']) && $_GET['active'] == 1));
$status = (get_option('webarx_license_expiry', '') == '' || time() >= strtotime(get_option('webarx_license_expiry', '')));
if ($status && $active_tab != 'license' && $_GET['page'] != 'webarx-multisite-settings') {
    $_GET['tab'] = $active_tab = 'license';
}

// Determine the URL's.
$page = $_GET['page'] == 'webarx-multisite-settings' ? $_GET['page'] : $this->plugin->name;
?>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.0.13/css/all.css" integrity="sha384-DNOHZ68U8hZfKXOrtjWvjxusGo9WQnrNx2sqG0tfsghAvtVlRW3tvkXWZh58N9jp" crossorigin="anonymous">
<link href="https://fonts.googleapis.com/css?family=Roboto:300,400" rel="stylesheet">
<div class="webarx-content-wrapp">
    <div class="webarx-top">
        <div class="webarx-top-logo">
            <img src="<?php echo $this->plugin->url; ?>assets/images/logo.svg" alt="">
        </div>

        <?php
        if (is_multisite() && $_GET['page'] != 'webarx-multisite-settings') {
            $siteInfo = get_blog_details();
            echo "<h2 style='color:white;padding-left: 95px; margin-left: 95px;padding-top: 4px;'>" . esc_html($siteInfo->domain) . "</h2>";
        }
        ?>
    </div>

    <div class="webarx-content-table">
        <?php
            if ($activated) {
        ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                        if (is_multisite()) {
                            _e('The Patchstack plugin has been turned on!<br>If you want to activate the Patchstack plugin on more sites on this multisite/network environment, please check them below and click on the activate button.', 'webarx');
                        } else {
                            _e('The Patchstack plugin has been turned on!<br>If the status of your license below is activated, please allow 5 to 15 minutes for data to appear on the Patchstack portal.', 'webarx');
                        }
                    ?>
                </p>
            </div><br>
        <?php
            }

            if (is_multisite()) {
        ?>
            <div class="notice notice-warning">
                <p>
                    <?php
                        if (isset($_GET['page']) && $_GET['page'] == 'webarx-multisite-settings') {
                            _e('Note that because this is a multisite/network environment, this settings page will define all default settings of all sites.<br>If you would like to change the settings of a specific site, visit the administration panel of the site in question. Click <a href="' . network_admin_url('admin.php?page=webarx-multisite') . '">here</a> for an overview of sites.', 'webarx');
                        } else {
                            _e('Note that because this is a multisite/network environment, certain settings can only be managed by the super administrator of the WordPress network.', 'webarx');
                        }
                    ?>
                </p>
            </div><br>
        <?php } ?>
        <h2 class="nav-tab-wrapper webarx-nav-tab-wrapper">
            <a href="?page=<?php echo $page; ?>&tab=hardening" class="nav-tab webarx-nav-tab <?php echo $active_tab == 'hardening' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>">
                <span class="webarx-icon-wrapper"><span class="webarx-nav-tab-icon ic-services white"></span></span>
                <span class="webarx-icon-text"><?php echo __('Hardening', 'webarx'); ?><br><span>General Security Tweaks</span></span>
            </a>

            <a href="?page=<?php echo $page; ?>&tab=firewall" class="nav-tab webarx-nav-tab <?php echo $active_tab == 'firewall' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>">
                <span class="webarx-icon-wrapper"><span class="webarx-nav-tab-icon ic-firewall white"></span></span>
                <span class="webarx-icon-text"><?php echo __('Firewall', 'webarx'); ?><br><span>Whitelist & Blacklist & Firewall</span></span>
            </a>

            <a href="?page=<?php echo $page; ?>&tab=login" class="nav-tab webarx-nav-tab <?php echo $active_tab == 'login' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>">
                <span class="webarx-icon-wrapper"><span class="webarx-nav-tab-icon ic-login white"></span></span>
                <span class="webarx-icon-text"><?php echo __('Login Protection', 'webarx'); ?><br><span>Protect your login page</span></span>
            </a>

            <a href="?page=<?php echo $page; ?>&tab=cookienotice" class="nav-tab webarx-nav-tab <?php echo $active_tab == 'cookienotice' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>">
                <span class="webarx-icon-wrapper"><span class="webarx-nav-tab-icon ic-cookies white"></span></span>
                <span class="webarx-icon-text"><?php echo __('Cookie Notice', 'webarx'); ?><br><span>Inform your users</span></span>
            </a>

            <?php if ($page != 'webarx-multisite-settings') { ?>
                <a href="?page=<?php echo $page; ?>&tab=logs" class="nav-tab webarx-nav-tab <?php echo $active_tab == 'logs' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>">
                    <span class="webarx-icon-wrapper"><span class="webarx-nav-tab-icon ic-logs white"></span></span>
                    <span class="webarx-icon-text"><?php echo __('Logs', 'webarx'); ?><br><span>Firewall &amp; Activity Logs</span></span>
                </a>
            <?php } ?>

            <?php if (!is_multisite() || (isset($_GET['page']) && $_GET['page'] != 'webarx-multisite-settings')) { ?>
                <a href="?page=<?php echo $page; ?>&tab=license" class="nav-tab webarx-nav-tab <?php echo $active_tab == 'license' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>">
                    <span class="webarx-icon-wrapper"><span class="webarx-nav-tab-icon ic-license white"></span></span>
                    <span class="webarx-icon-text"><?php echo __('License', 'webarx'); ?><br><span>Your license information</span></span>
                </a>
            <?php } ?>
        </h2>
        <div class="webarx-content-inner webarx-active-tab-<?php echo htmlentities($active_tab, ENT_QUOTES); ?>">
            <div class="webarx-font">
                <?php
                if ($status && $_GET['page'] != 'webarx-multisite-settings') {
                    require 'license.php';
                } else {
                    $form_action = is_multisite() ? '' : 'options.php';
                    switch ($active_tab) {
                        case 'hardening':
                            require 'hardening.php';
                            break;
                        case 'firewall':
                            require 'firewall.php';
                            break;
                        case 'login':
                            require 'login.php';
                            break;
                        case 'cookienotice':
                            require 'cookie-notice.php';
                            break;
                        case 'logs':
                            require 'logs.php';
                            break;
                        case 'license':
                            require 'license.php';
                            break;
                        case 'multisite':
                            require 'multisite-activation.php';
                            break;
                        default:
                            break;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>