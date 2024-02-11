<!doctype html>
<html lang="en">
<head>
	<title>{{ $report->getReportName() }}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="Cube is a map of the healthcare system">
	<meta name="author" content="CareSet Team">

    <!-- standard styles -->
	<link rel="stylesheet" type="text/css" href='{{ $bootstrap_css_location }}' />
	<link href='{{ asset("vendor/CareSet/zermelo/core/font-awesome/css/all.min.css") }}' rel="stylesheet" />
	<link type="text/css" rel="stylesheet" href="/vendor/CareSet/zermelo/zermelobladegraph/css/taxonomyChooser.css">

	<!-- custom css -->
	<link type="text/css" rel="stylesheet" href="/vendor/CareSet/zermelo/zermelobladegraph/css/colors.css"/>
	<link type="text/css" rel="stylesheet" href="/vendor/CareSet/zermelo/zermelobladegraph/css/noselect.css"/>
	<link type="text/css" rel="stylesheet" href="/vendor/CareSet/zermelo/zermelobladegraph/css/print.css"/>
	<link type="text/css" rel="stylesheet" href="/vendor/CareSet/zermelo/zermelobladegraph/css/floating.feedback.css"/>

	<!-- standard javascript -->
	<script type="text/javascript" src="/vendor/CareSet/zermelo/core/js/jquery.min.js"></script>
	<script type="text/javascript" src="/vendor/CareSet/zermelo/core/bootstrap/bootstrap.bundle.min.js"></script>

	<script type='text/javascript' language='javascript' src='/vendor/CareSet/zermelo/zermelobladegraph/js/d3.3.5.17.min.js'></script>
	<script type="text/javascript" src="/vendor/CareSet/zermelo/zermelobladegraph/js/saveSvgAsPng.js"></script>


	<!-- custom javascript -->
	<script type="text/javascript" src="/vendor/CareSet/zermelo/zermelobladegraph/js/util.js"></script>
	<script type="text/javascript" src="/vendor/CareSet/zermelo/zermelobladegraph/js/careset_api.js"></script>
  	<script type="text/javascript" src="/vendor/CareSet/zermelo/zermelobladegraph/js/html2canvas.js"></script>

	<!-- font awesome js -->
	<script type="text/javascript" language="javascript" src="/vendor/CareSet/zermelo/core/font-awesome/js/all.min.js"></script>


<!-- end dust_html.tpl -->

    	<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    	<!--[if lt IE 9]>
      		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    	<![endif]-->


 <script>
  $(document).ready(function()
       {



		//a function that only calls the url in data-url, and does nothing else.
		$('body').on('click','.press_url_element',function() {
//			alert('been clicked');
     			url = $(this).attr('data-url');
     			$.get(url);
			$(this).addClass("btn-success");
		});

       }
  );

  </script>

</head>
<body>

@include('Zermelo::d3graph')

</body>
</html>
