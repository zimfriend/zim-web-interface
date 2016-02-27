<div id="wifipswd_id" data-role="page">
	<header data-role="header" class="page-header">
		<a data-icon="arrow-l" data-role="button" data-direction="reverse"
			data-rel="back">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2>{title}</h2>
			<div class="zim-error">{err_msg}</div>
			<form method="post"
				accept-charset="utf-8">
				<input type="hidden" name="ssid" id="ssid" value="{ssid}">
				<input type="hidden" name="mode" id="mode" value="{mode}">
				<label for="password">{label}</label>
				<input type="password" name="password" id="password" value="" />
				<label for="password_confirm">{confirm_password}</label>
				<input type="password" name="password_confirm" />
				<br />
				<br />
				<div>
					<label><input type="checkbox" name="show_pass" data-mini=true>{show_password}</label>
				</div>
				<br />
				<input type="submit" value="{submit}" />
			</form>
		</div>
	</div>
	<script>
	$("div#wifipswd_id").on('pageshow', function()
	{
		setTimeout(function(){$(".ui-loader").css("display", "none")}, 1);
	});
	$("input[type=submit]").on('click', function()
	{
		$(".ui-loader").css('display', 'block');
	});
	$("input[name=show_pass]").on("click", function()
	{
		if ($("input[name=show_pass]").is(':checked'))
			$("input[type=password]").attr("type", "text");
		else
		{
			$("input[name=password]").attr("type", "password");
			$("input[name=password_confirm]").attr("type", "password");
		}
	});
	</script>
</div>