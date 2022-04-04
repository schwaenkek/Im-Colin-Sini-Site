<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<div id="webarx-cookie-notice" role="banner" style="opacity: <?php echo ($this->get_option('webarx_cookie_notice_opacity') < 100 ? '.' : '') . (int) $this->get_option('webarx_cookie_notice_opacity'); ?>;color: #<?php echo htmlspecialchars($this->get_option('webarx_cookie_notice_textcolor'), ENT_QUOTES); ?>; background-color: #<?php echo htmlspecialchars($this->get_option('webarx_cookie_notice_backgroundcolor'), ENT_QUOTES); ?>; visibility: hidden;">
    <div class="webarx-cookie-notice-container">
        <div class="webarx-cn-notice-text-container">
            <span id="webarx-cn-notice-text">
                <?php
                    echo htmlspecialchars($this->get_option('webarx_cookie_notice_message'), ENT_QUOTES);
                    echo ($this->get_option('webarx_cookie_notice_privacypolicy_enable') == 1 ? ' - <a class="webarx-cn-notice-link" style="text-decoration: underline !important; color: #' . htmlspecialchars($this->get_option('webarx_cookie_notice_textcolor'), ENT_QUOTES) . '; " href="' . htmlspecialchars($this->get_option('webarx_cookie_notice_privacypolicy_link'), ENT_QUOTES) . '">' . htmlspecialchars($this->get_option('webarx_cookie_notice_privacypolicy_text'), ENT_QUOTES) . '</a>' : '');
                ?>
            </span>
        </div>
        <div class="webarx-cn-notice-button-container">
            <button style="border-color: #<?php echo htmlspecialchars($this->get_option('webarx_cookie_notice_textcolor'), ENT_QUOTES); ?>; color: #<?php echo htmlspecialchars($this->get_option('webarx_cookie_notice_textcolor'), ENT_QUOTES); ?>; <?php echo ($this->get_option('webarx_cookie_notice_credits') == 1 ? ' margin-bottom: 20px; ' : ' '); ?>" onclick="setCookieForNotice('<?php echo htmlspecialchars($this->get_option('webarx_cookie_notice_cookie_expiration'), ENT_QUOTES); ?>')" id="webarx-cn-accept-cookie" data-cookie-set="accept" class="webarx-cn-set-cookie webarx-cn-button button"><?php echo htmlspecialchars($this->get_option('webarx_cookie_notice_accept_text'), ENT_QUOTES); ?></button>
            <?php
                echo ($this->get_option('webarx_cookie_notice_credits') == 1 ? '<a class="webarx-cn-protected-by" target="_blank" style="color: #' . htmlspecialchars($this->get_option('webarx_cookie_notice_textcolor'), ENT_QUOTES) . ';" href="https://patchstack.com" target="_blank"><img style="width:13px; float: left; margin-right: 5px; margin-top: 5px;" src="' . $this->plugin->url . '/assets/images/logo-mini.svg" alt=""> Protected by Patchstack</a>' : " ");
            ?>
        </div>
    </div>
</div>