<div data-role="page" data-url="/connection/wifip2p">
	<header data-role="header" class="page-header">
		<a data-icon="arrow-l" data-role="button" data-direction="reverse"
			data-rel="back">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			<form method="post" accept-charset="utf-8">
				<div class="zim-error">{error}</div>
				<p>{ssid_title}</p>
				<input type="text" name="ssid" id="ssid" value=""  data-clear-btn="true" />
				<p>{pwd_title}</p>
				<input type="password" name="pwd" id="pwd" value=""  data-clear-btn="true" autocomplete="off" />
				<br />
				<br />
				<div>
					<label><input type="checkbox" name="show_pass" data-mini=true />{show_password}</label>
				</div>
				<br />
				<div>
					<input type="submit" value="{ok}" />
				</div>
			</form>
		</div>
	</div>
	<script>
	$("input[name=show_pass]").on("click", function()
	{
		if ($("input[name=show_pass]").is(':checked'))
			$("input[name=pwd]").attr("type", "text");
		else
			$("input[name=pwd]").attr("type", "password");
	});
	</script>
</div>