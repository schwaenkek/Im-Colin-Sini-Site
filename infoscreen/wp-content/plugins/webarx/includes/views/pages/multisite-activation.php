<?php

// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}

// Determine which sites need activation or not.
$i = 0;
$checkbox_list = '';
$activated = '';
$sites = get_sites();
foreach ($sites as $site) {
    if (get_blog_option($site->id, 'webarx_clientid') == '') {
        $checkbox_list .= '<input type="checkbox" name="sites[]" value="' . $site->siteurl . '">' . $site->siteurl . '<br>';
        $i++;
    } else {
        $activated .= $site->siteurl . '<br>';
    }
}
?>
<h2 style="padding: 0;">Multisite Activation</h2>
<p><?php echo $this->plugin->multisite->error; ?>
Select the sites on which you would like to activate the Patchstack plugin. These sites must be accessible from the public internet.<br>
Note that if these sites have not been added to your Patchstack account yet, they will be added for you. Keep in mind that this might affect your upcoming bill depending on your current subscription plan.<br><br>
If you are an AppSumo user or have a limited amount of sites you can add, you must select the proper number of sites that can still be added to your account.</p>

<h2 style="padding: 20px 0 0 0; display: <?php echo $i > 0 ? 'block' : 'none'; ?>;">Not Activated</h2>
<form action="" method="POST" style="display: <?php echo $i > 0 ? 'block' : 'none'; ?>;">
    <input type="hidden" name="webarx_do" value="do_licenses">
    <input type="hidden" value="<?php echo wp_create_nonce('webarx-multisite-activation'); ?>" name="WebarxNonce">
    <?php echo $checkbox_list; ?>
    <br/>
    <button type="submit" class="button">Activate</button>
</form>

<br>
<h2 style="padding: 0;">Activated</h2>
<?php echo $activated; ?>