<div data-role="page"> <!-- data-url="/printerstate/upgradenote" -->
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo">
		<div id="link_logo"></div>
	</div>
	<div data-role="content">
		<div id="container">
			<h2>{note_title}</h2>
			<p id="upgradenote_hint" style="display: none;">{note_hint}</p>
			<div id="go_uimode_part" style="display: none;">
				<a href="{ui_link}" data-role="button">{ui_button}</a>
			</div>
			<div id="upgradenote_body">{note_body}</div>
			<div id="go_reboot_part" style="display: none;">
				<a href="/manage/rebooting" data-role="button">{reboot_button}</a>
<!-- 				<a href="#" data-role="button" data-icon="arrow-u" onclick='javascript: $("html, body").animate({ scrollTop: 0 });'>Top</a> -->
			</div>
<!-- 			<a href="javascript:history.back();" data-role="button" data-ajax="false">{back}</a> -->
		</div>
	</div>

<script>
var var_reboot = {reboot_display};
var var_uimode = {ui_display};

if (var_uimode == true && $(document).width() < 800) {
	$("div#go_uimode_part").show();
}
if (var_reboot == true) {
	$("div#go_reboot_part").show();
}
else {
	$("p#upgradenote_hint").show();
}

$("#upgradenote_collapsibleset").children(":first").collapsible().collapsible("expand");
</script>
</div>