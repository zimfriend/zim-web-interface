<div data-role="page" data-url="/connection/wifissid">
	<header data-role="header" class="page-header">
		<a data-icon="arrow-l" data-role="button" data-direction="reverse"
			data-rel="back">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			<ul data-role="listview" data-inset="true" id="listview" class="shadowBox">
				{list_ssid}
				<li><a href="/connection/wifipswd?ssid={link}&{wizard}" class="needSpin">{name}</a></li>
				{/list_ssid}
				<li><a href="/connection/wifinotvisiblessid?{wizard}" class="needSpin" data-prefetch>{no_visable}</a></li>
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
	</div>
	<script>
		$(document).on('pageshow', function()
		{
			$(".ui-loader").css("display", "none");
		});
		$("a.needSpin").on("click", function()
		{
			$(".ui-loader").css("display", "block");
		})

/*
// Detect Android
*/

// var ua = navigator.userAgent;
// var isAndroid = ua.indexOf("android") > -1;

// if (isAndroid)
// {
// 	var match = ua.match(/Android\s([0-9\.]*)/);
// 	if (match[1][0] < '4' || (match[1][0] == '4' && match[1][2] < '4'))
// 		window.location.href = "/connection/android_oldversions";
// }
	</script>
</div>