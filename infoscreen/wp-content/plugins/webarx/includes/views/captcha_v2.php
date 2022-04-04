<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($site_key, ENT_QUOTES); ?>"></div>
<noscript>
    <iframe src="https://www.google.com/recaptcha/api/fallback?k=<?php echo htmlspecialchars($site_key, ENT_QUOTES); ?>"></iframe>
    <textarea id="g-recaptcha-response" name="g-recaptcha-response" class="g-recaptcha-response"></textarea>
</noscript>
<script id="gRecaptchaSrc" src="https://www.google.com/recaptcha/api.js"></script>