<!doctype html>
<html lang="en">

<head>
    <meta name="home_url" content="<?= home_url() ?>">
    <meta name="_token" content="<?= csrf_token() ?>">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="content-language" content="vi" />
    <title><?= isset($title) ? $title : '' ?></title>
    <link rel="stylesheet" href="https://getbootstrap.com/docs/4.0/dist/css/bootstrap.min.css" />
    <script src="https://getbootstrap.com/docs/4.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    @style
</head>

<body>
    @include partials.header
    <div class="container">
        @main_content
    </div>
</body>

</html>