<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<form class="webarx-form-wrapp" method="post" action="<?php echo $form_action; ?>">
    <input type="hidden" value="<?php echo wp_create_nonce('webarx-option-page'); ?>" name="WebarxNonce">
    <input type="hidden" value="webarx_cookienotice_settings_group" name="option_page">
    <?php
        do_settings_sections('webarx_cookienotice_settings');
        settings_fields('webarx_cookienotice_settings_group');
        submit_button(__('Save settings', 'webarx'));
    ?>
</form>