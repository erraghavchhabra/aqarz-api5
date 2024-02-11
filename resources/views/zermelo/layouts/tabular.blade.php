<!doctype html>
<html lang="ar">
<head>

    <title>{{ $report->getReportName()  }}</title>

    <link href='{{ asset("vendor/CareSet/zermelobladetabular/fontawesome-free-5.10.2-web/css/all.css") }}' rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href='{{ $bootstrap_css_location }}' />
    <link rel="stylesheet" type="text/css" href='{{ asset("vendor/CareSet/zermelobladetabular/css/caresetreportengine.report.css") }}' />
    <link rel="stylesheet" type="text/css" href='{{ asset("vendor/CareSet/zermelobladetabular/datatables/datatables.min.css") }}' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
    <!--end::Fonts-->
    <!--begin::Global Stylesheets Bundle(used by all pages)-->
    <link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />

</head>
<body>


@include('Zermelo::tabular')

</body>
</html>

