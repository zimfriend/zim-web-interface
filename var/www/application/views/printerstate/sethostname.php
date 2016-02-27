<div data-role="page" data-url="/printerstate/sethostname">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home_button}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<h2 style="text-align: center;">{hint}</h2>
			<form method="post" accept-charset="utf-8">
				<input type="text" name="hostname" id="hostname" value="{hostname}" data-clear-btn="true" pattern="^[a-zA-Z0-9][a-zA-Z0-9\-]{0,7}[a-zA-Z0-9]$|^[a-zA-Z0-9]$" required title="{length_error}" />
				<input type="hidden" name="restart" id="restart" value="{restart}" />
				<br />
				{info_text}
				<br />
				<br />
				<div class="zim-error">{error}</div>
				<div>
					<input data-ajax=false type="submit" value="{set_button}" />
				</div>
			</form>
		</div>
	</div>
	<script>
		var pattern;
		var string = $("#fqdn").text();

		$("#fqdn").text(string.replace(new RegExp('xxx', 'g'), $("#hostname").val()));
		$("#fqdn2").text(string.replace(new RegExp('xxx', 'g'), $("#hostname").val()));
		pattern = $("#hostname").val();
		$("#hostname").on('keyup', function()
		{
			$("#fqdn").text(string.replace(new RegExp(pattern+'|'+'xxx', 'g'), $("#hostname").val()));
			$("#fqdn2").text(string.replace(new RegExp(pattern+'|'+'xxx', 'g'), $("#hostname").val()));
			if ($("#hostname").val() != "")
			{
				pattern = $("#hostname").val();
			}
		});
	</script>
</div>

