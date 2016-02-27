<div data-role="page" data-url="/pronterface">
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<div data-role="collapsible" data-collapsed="true" style="align: center;" id="temper_collaspible">
				<h4>Temperature</h4>
				<div class="widget_monocolor" style="display: none;">
					<label for="temper_mono">Mono</label>
					<div style="width:40px;float:left;margin-right:10px;">
						<input type="text" data-clear-btn="false" name="temper_mono_current" id="temper_mono_current" value="0" disabled>
					</div>
					<div style="width:60px;float:left;margin-right:10px;">
						<input type="number" style="text-align:right;" data-clear-btn="false" name="temper_mono" id="temper_mono" value="200">
					</div>
					<a href="#" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" onclick="heat('r');">Set</a>
					<a href="#" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" onclick="heat('r', 0);">Stop</a>
					<a href="#" data-role="button" data-icon="refresh" data-iconpos="left" data-inline="true" onclick="refreshCheckTemper();">Refresh</a>
				</div>
				<div class="ui-grid-a widget_bicolor" style="display: none;">
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f">
							<label for="temper_l">Left</label>
							<div style="width:40px;float:left;margin-right:10px;">
								<input type="text" data-clear-btn="false" name="temper_l_current" id="temper_l_current" value="0" disabled>
							</div>
							<div style="width:60px;float:left;margin-right:10px;">
								<input type="number" style="text-align:right;" data-clear-btn="false" name="temper_l" id="temper_l" value="200">
							</div>
							<a href="#" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" onclick="heat('l');">Set</a>
							<a href="#" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" onclick="heat('l', 0);">Stop</a>
							<a href="#" data-role="button" data-icon="refresh" data-iconpos="left" data-inline="true" onclick="refreshCheckTemper();">Refresh</a>
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-barfc">
							<label for="temper_r">Right</label>
							<div style="width:40px;float:left;margin-right:10px;">
								<input type="text" data-clear-btn="false" name="temper_r_current" id="temper_r_current" value="0" disabled>
							</div>
							<div style="width:60px;float:left;margin-right:10px;">
								<input type="number" style="text-align:right;" data-clear-btn="false" name="temper_r" id="temper_r" value="200">
							</div>
							<a href="#" data-role="button" data-icon="check" data-iconpos="left" data-inline="true" onclick="heat('r');">Set</a>
							<a href="#" data-role="button" data-icon="delete" data-iconpos="left" data-inline="true" onclick="heat('r', 0);">Stop</a>
							<a href="#" data-role="button" data-icon="refresh" data-iconpos="left" data-inline="true" onclick="refreshCheckTemper();">Refresh</a>
						</div>
					</div>
				</div>
			</div>
			<div data-role="collapsible" data-collapsed="true" style="align: center;">
				<h4>Filament</h4>
				<div class="widget_monocolor" style="display: none; height: 120px;">
					<label for="extrude_mono">Mono</label>
					<br style="clear:left;" />
					<div style="width:60px;float:left;margin-right:10px;">
						<input type="number" style="text-align:right;" data-clear-btn="false" name="extrude_mono" id="extrude_mono" value="20"> mm
					</div>
					<div style="width:60px;float:left;margin-right:10px;">
						<input type="number" style="text-align:right;" data-clear-btn="false" name="speed_mono" id="speed_mono" value="100"> mm/min
					</div>
					<a href="#" data-role="button" data-icon="arrow-u" data-iconpos="left" data-inline="true" onclick="extrude('r', '-');">Reverse</a>
					<a href="#" data-role="button" data-icon="arrow-d" data-iconpos="left" data-inline="true" onclick="extrude('r', '+');">Extrude</a>
				</div>
				<div class="ui-grid-a widget_bicolor" style="display: none;">
					<div class="ui-block-a">
						<div class="ui-bar ui-bar-f">
							<label for="extrude_l">Left</label>
							<br style="clear:left;" />
							<div style="width:60px;float:left;margin-right:10px;">
								<input type="number" style="text-align:right;" data-clear-btn="false" name="extrude_l" id="extrude_l" value="20"> mm
							</div>
							<div style="width:60px;float:left;margin-right:10px;">
								<input type="number" style="text-align:right;" data-clear-btn="false" name="speed_l" id="speed_l" value="100"> mm/min
							</div>
							<a href="#" data-role="button" data-icon="arrow-u" data-iconpos="left" data-inline="true" onclick="extrude('l', '-');">Reverse</a>
							<a href="#" data-role="button" data-icon="arrow-d" data-iconpos="left" data-inline="true" onclick="extrude('l', '+');">Extrude</a>
						</div>
					</div>
					<div class="ui-block-b">
						<div class="ui-bar ui-bar-f">
							<label for="extrude_r">Right</label>
							<br style="clear:left;" />
							<div style="width:60px;float:left;margin-right:10px;">
								<input type="number" style="text-align:right;" data-clear-btn="false" name="extrude_r" id="extrude_r" value="20"> mm
							</div>
							<div style="width:60px;float:left;margin-right:10px;">
								<input type="number" style="text-align:right;" data-clear-btn="false" name="speed_r" id="speed_r" value="100"> mm/min
							</div>
							<a href="#" data-role="button" data-icon="arrow-u" data-iconpos="left" data-inline="true" onclick="extrude('r', '-');">Reverse</a>
							<a href="#" data-role="button" data-icon="arrow-d" data-iconpos="left" data-inline="true" onclick="extrude('r', '+');">Extrude</a>
						</div>
					</div>
				</div>
			</div>
			<div data-role="collapsible" data-collapsed="true" style="align: center;">
				<h4>G-code</h4>
				<label for="oneline_v">Verbatim one line G-code</label>
				<input type="text" name="oneline_v" id="oneline_v" value="">
				<a href="#" data-role="button" data-icon="arrow-r" data-iconpos="right" onclick="runGcodeGet();">Send</a>
				<button class="ui-btn ui-shadow ui-corner-all ui-btn-icon-right ui-icon-delete" onclick="javascript: window.location.href='/exrusion_control/stop';">Stop print</button>
			</div>
			<label for="gcode_detail_info">Output</label>
			<textarea name="gcode_detail_info" id="gcode_detail_info" style="height: 300px !important;" disabled></textarea>
<!-- 			<div id="gcode_detail_info"></div> -->
		</div>
	</div>

<script type="text/javascript">
var var_ajax;
var var_refreshCheckTemper = 0;
var var_ajax_lock = false;
var var_checked_rfid = false;
var var_verifyRFIDInterval = 0;
var var_bicolor = {bicolor};

if (var_bicolor == true) {
	$("div.widget_bicolor").show();
}
else {
	$("div.widget_monocolor").show();
	$("input#temper_mono").change(function() {
		$("input#temper_r").val($("input#temper_mono").val());
	});
	$("input#extrude_mono").change(function() {
		$("input#extrude_r").val($("input#extrude_mono").val());
	});
	$("input#temper_mono").change(function() {
		$("input#speed_r").val($("input#speed_mono").val());
	});
}

$(document).ready(checkTemper());

function checkTemper() {
		$("#temper_collaspible").collapsible({
			expand: function(event, ui) {
				if (var_refreshCheckTemper == 0) {
					refreshCheckTemper();
					var_refreshCheckTemper = setInterval(refreshCheckTemper, 10000);
				}
			},
			collapse: function(event, ui) {
				if (var_refreshCheckTemper != 0) {
					clearInterval(var_refreshCheckTemper);
					var_refreshCheckTemper = 0;
				}
			},
		});
	}

function refreshCheckTemper() {
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}
	
	var_ajax = $.ajax({
		url: "/extrusion_control/temper_ajax",
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		var response = JSON.parse(html);
		$("#temper_l_current").val(response.left);
		$("#temper_r_current").val(response.right);
		$("#temper_mono_current").val(response.right);
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});
}

function heat(var_extruder, var_value) {
	var var_url;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_extruder = typeof var_extruder !== 'undefined' ? var_extruder : 'error';
	var_value = typeof var_value !== 'undefined' ? var_value : 'get';

	if (var_extruder == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	if (var_value == 'get') {
		if (var_extruder == 'l') {
			var_value = $("#temper_l").val();
		}
		else {
			var_value = $("#temper_r").val();
		}
	}

	var_url = "/extrusion_control/heat/" + var_extruder + '/' + var_value;
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function extrude(var_extruder, var_direction) {
	var var_url, var_value, var_speed;
	var id_extrude, id_speed;
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	var_extruder = typeof var_extruder !== 'undefined' ? var_extruder : 'error';
	var_direction = typeof var_direction !== 'undefined' ? var_direction : 'error';

	if (var_extruder == 'error' || var_direction == 'error') {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	if (var_extruder == 'l') {
		id_extrude = "#extrude_l";
		id_speed = "#speed_l";
	} else if (var_extruder == 'r') {
		id_extrude = "#extrude_r";
		id_speed = "#speed_r";
	} else {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	if (var_direction == '+') {
		var_value = $(id_extrude).val();
	} else if (var_direction == '-') {
		var_value = -parseFloat($(id_extrude).val());
	} else {
		$("#gcode_detail_info").html('ERROR');
		return false;
	}
	var_speed = $(id_speed).val();

	var_url = "/extrusion_control/extrude/" + var_extruder + '/' + var_value + '/' + var_speed;
	var_ajax = $.ajax({
		url: var_url,
		type: "GET",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html('OK');
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

function runGcodeGet() {
	var var_gcode = $('#oneline_v').val();
	if (var_ajax_lock == true) {
		return;
	}
	else {
		var_ajax_lock = true;
	}

	if (var_gcode.indexOf('M109') != -1
			|| var_gcode.indexOf('M1606') != -1
			|| var_gcode.indexOf('M1607') != -1) {
		alert("M109 / M1606 / M1607 include!");
		return false;
	}
	var_ajax = $.ajax({
		url: "/rest/gcode",
		type: "GET",
		data: {
			v: var_gcode,
		},
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function(html) {
		$("#gcode_detail_info").html(html);
	})
	.fail(function() {
		$("#gcode_detail_info").html('ERROR');
	})
	.always(function() {
		var_ajax_lock = false;
	});

	return false;
}

</script>

</div>
