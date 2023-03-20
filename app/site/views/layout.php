<!DOCTYPE html>
<html lang="vi">

<head>
    <meta name="root_url" content="<?= root_url() ?>">
    <meta name="_token" content="<?= csrf_token() ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="vi" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="robots" content="noodp,index,follow" />
    <meta itemprop="name" content="" />
    <meta itemprop="description" content="" />
    <meta itemprop="image" content="" />
    <meta property="og:site_name" content="" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= root_url() ?>" />
    <meta property="og:locale" content="vi_VN" />
    <meta property="og:locale:alternate" content="en_US" />
    <meta property="og:title" content="" />
    <meta property="og:description" content="" />
    <meta property="og:image" content="<?= asset('image/logo_web_social.jpg') ?>" />
    <meta name="google-site-verification" content="" />
    <title><?= isset($title) ? $title : '' ?></title>
    <link rel="shortcut icon" href="">
</head>

<body>
    <div class="main-page clearfix">
        <?= $html_child_page ?>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
</body>

</html>
