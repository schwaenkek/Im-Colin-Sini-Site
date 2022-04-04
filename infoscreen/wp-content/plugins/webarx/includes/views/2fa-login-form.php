<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<p>
    <label for="webarx_2fa"><?php echo __('2FA Code', 'webarx'); ?>
        <span style="font-size: 9px;">(<?php echo __('leave empty if no 2FA setup', 'webarx'); ?></span><br />
        <input type="text" name="webarx_2fa" id="webarx_2fa" class="input" value="" size="25" autocomplete="off" />
    </label>
</p>
