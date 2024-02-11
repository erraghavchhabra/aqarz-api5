<!doctype html>
<html lang="ar">
<head>

    <title>{{ $report->getReportName()  }}</title>

    <link href='{{ asset("vendor/CareSet/zermelo/core/font-awesome/css/all.min.css") }}' rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href='{{ $bootstrap_css_location }}' />
    <link rel="stylesheet" type="text/css" href='{{ asset("vendor/CareSet/zermelo/core/css/caresetreportengine.report.css") }}' />
    <link rel="stylesheet" type="text/css" href='{{ asset("vendor/CareSet/zermelo/zermelobladetabular/datatables/datatables.min.css") }}' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{asset('assets/plugins/global/plugins.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{asset('assets/css/style.bundle.rtl.css')}}" rel="stylesheet" type="text/css" />

<!-- inline styles foce the headings on the table to be more dense for smaller columns -->
<style type="text/css">
.yadcf-filter {
    width: 60px !important;
    max-width: 60px !important;
}

.yadcf-filter-wrapper {
    display: block !important;
}


</style>


</head>
<body>


@include('Zermelo::tabular')

</body>
</html>

