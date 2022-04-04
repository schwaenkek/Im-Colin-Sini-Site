<?php
// Do not allow the file to be called directly.
if (!defined('ABSPATH')) {
	exit;
}
?>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Access Denied</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.100.2/css/materialize.min.css">
    <style type="text/css">
        .container{ margin-top: 50px; width: 768px; }
        .grey > div{font-size: 18px; font-weight: 400; text-shadow: 0px 1px 1px #fff; color: #726f6f;}
        h4{ font-size: 20px; }
        .card-content > a:last-child{ margin-top: 20px; display: inline-block; font-size: 12px; }
        .webarx-logo { background-image: url("data:image/svg+xml,%3Csvg width='50' height='36' viewBox='0 0 50 36' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M0 36H12.4997V27H0V36Z' fill='%23AFE614'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M12.502 36H25.0017V27H12.502V36Z' fill='%236EBE00'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M25 26.9999H49.9995V0H25V26.9999Z' fill='%23AFE614'/%3E%3Cpath fill-rule='evenodd' clip-rule='evenodd' d='M0 18.0002H24.9995V0.000244141H0V18.0002Z' fill='%236EBE00'/%3E%3C/svg%3E"); display: block; width: 50px; height: 36px; position: absolute; top: 17px; right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col s12">
                <div class="card">
                    <div class="card-content grey lighten-4">
                        <div style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis; padding-right:30px;">Access Denied - <?php echo htmlentities('http' . (($_SERVER['SERVER_PORT'] == 443) ? 's://' : '://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], ENT_QUOTES); ?>
                            <div>
                                <a href="https://patchstack.com" class="webarx-logo" target="_blank"></a>
                            </div>
                        </div>
                    </div>
                    <div class="card-content">
                        <h4>Error Code <?php echo $fid; ?></h4>
                        <p>This request has been blocked by <a href="https://patchstack.com" target="_blank">Patchstack</a> Web Application Firewall .</p>
                        <p>If you are a legitimate user, contact the administrator of the site with above error code if this message persists.</p>
                        <a href="<?php echo get_site_url(); ?>">Return To Homepage</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>