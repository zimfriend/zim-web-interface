<div data-role="page">
	<header data-role="header" class="page-header">
	</header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>
	<div data-role="content">
		<div id="container">
			<div style="text-align:center">
				<p>{hint}</p>
				<br />
				<div class="ui-loader ui-corner-all ui-body-a ui-loader-default" style="display:block">
					<span class="ui-icon-loading"></span>
				</div>
			</div>
		</div>
	</div>
	<script>
		var ping = new Image();

		setTimeout(function()
		{
			var interval = setInterval(function()
			{
				ping.src = "/images/pixel.png?_=" + (new Date()).getTime();
				if (ping.height > 0)
				{
					clearInterval(interval);
					window.location.href = "/";
				}
			}, 5000);
		}, 25000);
	</script>
</div>