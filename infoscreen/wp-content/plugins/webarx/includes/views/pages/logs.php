<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// Determine the log table that should be shown.
$logtype = (isset($_GET['logtype']) && $_GET['logtype'] == 'activity' ? 'activity' : 'firewall');
$url = htmlentities($_SERVER['REQUEST_URI'], ENT_QUOTES);
if (strpos($url, 'logtype') === false) {
    $url .= '&logtype=' . $logtype;
}
?>
<h2 class="webarx-logs-nav">
    <a href="<?php echo str_replace('activity', 'firewall', $url); ?>" class="nav-tab webarx-nav-tab <?php echo $logtype == 'firewall' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>"><?php echo __('Firewall Log', 'webarx'); ?></a>
    <a href="<?php echo str_replace('firewall', 'activity', $url); ?>" class="nav-tab webarx-nav-tab <?php echo $logtype == 'activity' ? 'nav-tab-active webarx-nav-tab-active' : ''; ?>"><?php echo __('Activity Log', 'webarx'); ?></a>
</h2>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/css/jquery.dataTables.min.css" />
<script>window.jQuery || document.write('<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js">\x3C/script>')</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.16/js/jquery.dataTables.min.js"></script>

<div class="webarx-content-inner-table">
    <div class="webarx-font" style="padding: 0 30px;">
        <h4>This will display the logs of the last 2 weeks.<br>If you would like to see the in-depth logs history of up to 90 days ago, login into your account at the <a href="https://app.patchstack.com" target="_blank">Patchstack Portal</a></h4>
        <?php if ($logtype == 'firewall') { ?>
            <table class="table table-lg table-hover table-firewall-log">
                <thead>
                    <tr>
                        <th>SEVERITY</th>
                        <th>REASON</th>
                        <th>URL REQUESTED</th>
                        <th>METHOD</th>
                        <th>ORIGIN</th>
                        <th>DATE</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        <?php } elseif ($logtype == 'activity') { ?>
            <table class="table table-lg table-hover table-user-log dt-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Object</th>
                        <th>Object name</th>
                        <th>IP</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>