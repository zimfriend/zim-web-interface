<div data-role="page" data-url="/printerstate">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<div class="ui-grid-a">
				<div class="ui-block-a"><div class="ui-bar ui-bar-f" style="height:3em;">
					<label for="slider"><h2>{upgrade}</h2></label>
				</div></div>
				<div class="ui-block-b"><div class="ui-bar ui-bar-f" style="height:3em;">
					<select name="upgrade" id="upgrade" data-role="slider" data-track-theme="a" data-theme="a">
						<option value="off">{function_off}</option>
						<option value="on" {upgrade_on}>{function_on}</option>
					</select>
				</div></div>
				<div class="ui-block-a"><div class="ui-bar ui-bar-f" style="height:3em;">
					<label for="slider"><h2>{tromboning}</h2></label>
				</div></div>
				<div class="ui-block-b">
					<div class="ui-bar ui-bar-f" style="height:3em;">
						<select name="tromboning" id="tromboning" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="off">{function_off}</option>
							<option value="on" {tromboning_on}>{function_on}</option>
						</select>
					</div>
				</div>
				<div class="ui-block-a"><div class="ui-bar ui-bar-f" style="height:3em;">
					<label for="slider"><h2>{remote_control}</h2></label>
				</div></div>
				<div class="ui-block-b"><div class="ui-bar ui-bar-f" style="height:3em;">
					<select name="remote_control" id="remote_control" data-role="slider" data-track-theme="a" data-theme="a">
						<option value="off">{function_off}</option>
						<option value="on" {remote_control_on}>{function_on}</option>
					</select>
				</div></div>
			</div>
			<ul data-role="listview" id="listview" class="shadowBox" data-inset="true">
				<li><a href="/preset/listpreset">
					<h2>{set_preset}</h2></a>
				</li>
				<li class="widget_bicolor" style="display: none;"><a href="printerstate/nozzles_adjustment">
					<h2>{nozzles_adjustments}</h2></a>
				</li>
				<li><a href="/printerstate/resetnetwork">
					<h2>{reset_network}</h2></a>
				</li>
			</ul>
			<ul data-role="listview" class="shadowBox" data-inset="true">
				<li><a id="zim_support" href="http://zimsupport.zeepro.com/support/home" target="_blank">
					<h2>{support}</h2></a>
				</li>
			</ul>
			<img src="/images/listShadow.png" class="shadow" alt="shadow">
		</div>
	</div>
	
<script type="text/javascript">
var var_ajax;
var var_bicolor = {bicolor};

if (var_bicolor == true) {
	$(".widget_bicolor").show();
}

$("#upgrade").change(function() {
	var var_state = $("#upgrade").val().toString();
	var_ajax = $.ajax({
		url: "/rest/set",
		cache: false,
		data: {
			p: "upgrade",
			v: var_state,
			},
		type: "GET",
	})
	.fail(function() {
 		alert("failed");
 	});
});
$("#tromboning").change(function() {
	var var_state = $("#tromboning").val().toString();
	var_ajax = $.ajax({
		url: "/rest/set",
		cache: false,
		data: {
			p: "tromboning",
			v: var_state,
			},
		type: "GET",
	})
	.fail(function() {
 		alert("failed");
 	});
});
$("#remote_control").change(function() {
	var var_state = $("#remote_control").val().toString();
	var_ajax = $.ajax({
		url: "/rest/set",
		cache: false,
		data: {
			p: "remotecontrol",
			v: var_state,
			},
		type: "GET",
	})
	.fail(function() {
 		alert("failed");
 	});
});

$('a#zim_support').click(function(event) {
	$.get('/printerstate/stats_support', function() { console.log('stats support call sent.'); });
});
</script>

</div>
