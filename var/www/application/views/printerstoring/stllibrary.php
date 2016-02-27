<div data-role="page" data-url="/printerstoring/stllibrary">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<a href="/printerstoring/liststl/" data-ajax="false" data-role="button">
				{browse_models}
			</a>
			<a href="/printerstoring/storestl/" data-ajax="false" data-role="button" id="add_model">
				{add_model}
			</a>
		</div>
	</div>
	<script>
		var iOS = (navigator.userAgent.match(/(iPad|iPhone|iPod)/g));

		if (iOS)
			$("#add_model").remove();
	</script>
</div>