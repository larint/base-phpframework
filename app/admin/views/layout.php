<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AdminLTE 3 | Dashboard</title>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback" />
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css" />
    <link rel="stylesheet" href="@asset admin/plugins/fontawesome-free/css/all.min.css" />
    <link rel="stylesheet" href="//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" />
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <link rel="stylesheet" href="@asset admin/plugins/toastr/toastr.min.css" />
    <link rel="stylesheet" href="@asset admin/css/adminlte.min.css" />
    <link rel="stylesheet" href="@asset admin/css/main.css" />
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        @include partials/main_sidebar

        <div class="content-wrapper">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <h1 class="m-0">Dashboard</h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Dashboard v1</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>

            <section class="content">
                @main_content
            </section>
        </div>

        @include partials/footer
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>
    <script src="//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.umd.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="@asset admin/plugins/toastr/toastr.min.js"></script>
    <script src="@asset admin/js/adminlte.min.js"></script>
    <script src="@asset js/main.js"></script>
</body>

</html>