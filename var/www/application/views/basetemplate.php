<!DOCTYPE html>
<html lang="{lang}">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=11; IE=10; IE=9" />
		<meta http-equiv="cache-control" content="max-age=0" />
		<meta http-equiv="cache-control" content="no-cache" />
		<meta http-equiv="expires" content="-1" /> <!-- use -1 instead of 0, http://support.microsoft.com/kb/234067 vs http://stackoverflow.com/questions/11357430 -->
		<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
		<meta http-equiv="pragma" content="no-cache" />
		<link rel="stylesheet" href="/styles/jquery.mobile-1.4.0.min.css" />
		<link rel="stylesheet" href="/styles/Custom-zim.min.css" />
		<script src="/scripts/jquery-1.9.1.min.js"></script>
		<script>
			$(document).bind("mobileinit", function() {
				$.mobile.defaultPageTransition = 'slide';
			});
		</script>
		<script src="/scripts/jquery.mobile-1.4.0.min.js"></script>
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, target-densitydpi=medium-dpi, user-scalable=0" />
		<link rel="stylesheet" href="/styles/Printer-zim.css" />
		<script type="text/javascript">
			var var_disable_logo_link = false;
			
			$(document).on("pageinit", function() {
				if (typeof var_disable_logo_link == 'undefined' || var_disable_logo_link == false)
					$('div#link_logo').click(function(){window.location.href='/'; return false;});
			});
		</script>
		{headers}
	</head>
	<body>
		<noscript>
			<p style="text-align: center">Your navigator has disabled Javascript support, please enable it to use zim.</p>
		</noscript>
		<div id="page_body" style="display:none">
			{contents}
		</div>
	</body>
	<script>
		$("#page_body").css("display", "block");
	</script>
	<head><meta http-equiv="pragma" content="no-cache"></head> <!-- for old IE, http://support.microsoft.com/kb/222064 -->
</html>