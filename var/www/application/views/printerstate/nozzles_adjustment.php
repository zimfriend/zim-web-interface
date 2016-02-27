<div data-role="page" data-url="/printerstate/nozzles_adjustment">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<ul data-role="listview" id="listview" class="shadowBox" data-inset="true">
				<li>
					<a href="/printmodel/detail?id=calibration" data-ajax="false" onclick="javascript: load_wait();">
						<h2>{print_calibration}</h2>
					</a>
				</li>
				<li>
					<a href="/printerstate/offset_setting" data-ajax="false">
						<h2>{trim_offset}</h2>
					</a>
				</li>
			</ul>
		</div>
	</div>

<script type="text/javascript">
function load_wait() {
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
}
</script>

</div>