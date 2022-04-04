<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// Determine if the subscription of the account is expired.
$status = (get_option('webarx_license_expiry', '') == '' || time() >= strtotime(get_option('webarx_license_expiry', '')));
if (isset($_GET['activated']) && $status) {
    echo "<script>window.location = 'admin.php?page=webarx&tab=license&active=1';</script>";
}
?>
<h2 class="webarx-license-h2"><?php echo __('License Information', 'webarx'); ?></h2>
<p>Your license is currently <span style="color:<?php echo (!$status ? '#00ca00': 'red'); ?>;"><?php echo (!$status ? '' : 'not '); ?>activated</span>.</p>
<br>

<h2 class="webarx-license-h2"><?php echo __('License Key', 'webarx'); ?></h2>
<p>You can enter your client ID and secret key values below.<br>You generally do not have to touch these values.<br><br>If both are empty and you do not know your client ID and secret key, you can find it by going to your site on the Patchstack portal, then scroll down and click on the "API ID/Secret Key" tab.</p>

<div id="hiddenstatusbox" class="webarxInfo webarx-font blue">
    <span class="label label-warning" id="webarx_license_key_result"></span>
</div>

<table class="form-table webarx-form-table">
    <tr>
        <th><label for="webarx_license_key"><?php echo __('API Client ID', 'webarx'); ?></label></th>
        <td><input class="regular-text" type="text" id="webarx_api_client_id" name="webarx_api_client_id"  value="<?php echo esc_attr(get_option('webarx_clientid', false)); ?>" ></td>
    </tr>
    <tr>
        <th><label for="webarx_license_key"><?php echo __('API Client Secret Key', 'webarx'); ?></label></th>
        <td>
            <table>
                <tr>
                    <td><input class="regular-text" type="text" id="webarx_api_client_secret_key" name="webarx_api_client_secret_key"  value="<?php echo esc_attr(get_option('webarx_secretkey', false)); ?>"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <p class="submit">
                <input type="submit" id="webarx-activate-license" name="webarx-activate-license" value="<?php echo __('Re-Activate', 'webarx'); ?>" class="button-primary" />
                <input type="submit" style="display:<?php echo get_option('webarx_api_token', false) ? 'inline' : 'none'; ?>;" id="webarx-test-api" name="webarx-test-api" value="<?php echo __('Test API', 'webarx'); ?>" class="button" />&nbsp;
            </p>
        </td>
    </tr>
</table>