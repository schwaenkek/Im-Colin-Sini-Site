<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<h2><?php _e('Two Factor Authentication Configuration', 'webarx'); ?></h2>
<table class="form-table">
    <tbody>
        <tr>
            <th scope="row"><?php _e('Enable 2FA', 'webarx'); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php _e('Enable 2FA', 'webarx'); ?></span></legend>
                    <label for="webarx_2fa_enabled"><input name="webarx_2fa_enabled" type="checkbox" id="webarx_2fa_enabled" value="1" <?php echo checked(1, get_user_option('webarx_2fa_enabled', $user->ID), false); ?>> <?php _e('Enable 2FA on your account.', 'webarx'); ?></label><br>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('Secret Key', 'webarx'); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php _e('Secret Key', 'webarx') ?></span></legend>
                    <label for="webarx_2fa_secretkey"><input name="webarx_2fa_secretkey" type="text" id="webarx_2fa_secretkey" value="<?php echo esc_html($secretKey); ?>"></label><br>
                </fieldset>
            </td>
        </tr>
        <tr>
            <th scope="row"><?php _e('QR Code Image', 'webarx'); ?></th>
            <td>
                <fieldset>
                    <legend class="screen-reader-text"><span><?php _e('QR Code Image', 'webarx') ?></span></legend>
                    <div id="qrcode"></div><br>
                    <?php _e('Scan this image with your 2FA app.', 'webarx') ?>
                    <script type="text/javascript">
                        jQuery(function(){
                            new QRCode(document.getElementById("qrcode"), {
                                text: "<?php echo 'otpauth://totp/WordPress:' . urlencode(get_bloginfo('name')) . '?secret=' . urlencode($secretKey) . '&issuer=WordPress'; ?>",
                                width: 128,
                                height: 128,
                            });
                        })
                    </script>
                </fieldset>
            </td>
        </tr>
    </tbody>
</table>