<!doctype html>
<html lang="en">
<head>

<title>{{ $report->getReportName()  }}</title>

<link rel="stylesheet" type="text/css" href='{{ $bootstrap_css_location }}'/>
<link rel="stylesheet" type="text/css" href='{{ asset("vendor/CareSet/zermelobladecard/datatables/datatables.min.css") }}'/>
<link href='{{ asset("vendor/CareSet/zermelo/core/font-awesome/css/all.min.css") }}' rel="stylesheet" />
<style type="text/css">
    h1.card-title {
        display: inline-block;
    }
    button.view-data-options {
        margin-left: 20px;
        margin-top: auto;
        margin-bottom: auto;
        float: right;
        width: auto;
        height: min-content;
        vertical-align: middle;
    }

    .alternate_row {
        background-color: whitesmoke;
        border-radius: 10px;
        padding-top: 5px;

    }

    @media print {
	.alternate_row {
		background-color: white;
	}

     }


</style>
</head>
<body>

@include('Zermelo::menu')

@include('Zermelo::card')

</body>
</html>

