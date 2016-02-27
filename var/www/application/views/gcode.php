<div data-role="page" data-url="/sliceupload/gcode">
	<div id="overlay"></div>
<!-- 	<link rel="stylesheet" type="text/css" href="/assets/gcode/css/cupertino/jquery-ui-1.9.0.custom.css" media="screen" /> -->
	<link rel="stylesheet" type="text/css" href="/assets/gcode/lib/codemirror.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="/assets/gcode/css/style.css" media="screen" />
	
<!-- 	<script type="text/javascript" src="/assets/gcode/lib/jquery-migrate-1.2.1.min.js"></script> -->
<!-- 	<script type="text/javascript" src="/assets/gcode/lib/jquery-ui-1.9.0.custom.js"></script> -->
	
	<script type="text/javascript" src="/assets/gcode/lib/codemirror.js"></script>
	<script type="text/javascript" src="/assets/gcode/lib/mode_gcode/gcode_mode.js"></script>
	<script type="text/javascript" src="/assets/gcode/lib/bootstrap.js"></script>
	<script type="text/javascript" src="/assets/gcode/lib/modernizr.custom.09684.js"></script>
	<script type="text/javascript" src="/assets/gcode/lib/zlib.min.js"></script>
	<script type="text/javascript" src="/assets/gcode/js/ui.js"></script>
	<script type="text/javascript" src="/assets/gcode/js/gCodeReader.js"></script>
	<script type="text/javascript" src="/assets/gcode/js/renderer.js"></script>
	<script type="text/javascript" src="/assets/gcode/js/analyzer.js"></script>
	
	<style> #slider-horizontal { position: relative !important; } </style>
	<!-- <style> div#rendering_layerslider_container input[type=number] { display : none !important; } </style> -->
	
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="#" onclick="javascript: window.location.href='/';" data-icon="home" data-ajax="false" style="float:right">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
		<div data-role="content">
			<div id="container">
				<div id="gc_analyser_container" style="margin: 0 auto; width: 700px; max-width: 100%;">
					<div data-role="navbar" style="margin-bottom: 1em;">
						<ul>
							<li><a id="nav_a_gcode" href="#" onclick="javascript: nav_show_gcode();">Gcode</a></li>
							<li><a id="nav_a_rendering" href="#" onclick="javascript: nav_show_rendering();">Rendering</a></li>
						</ul>
					</div>
					<div id="rendering">
						<canvas id="canvas" width="650" height="620"></canvas>
<!-- 						<div id="slider-vertical"></div> -->
<!-- 						<div id="slider-horizontal"></div> -->
						<div id="rendering_layerslider_container" style="display: none;">
							<h3>{layer_number}</h3>
							<div id="rendering_layerslider_display_container" style="margin: 0;">
								<div style="width:10%; float:left; text-align:center;">
									<a data-role="button" data-inline="true" data-mini="true" onclick="javascript: rendering_ui_changeLayer('-');">-</a>
								</div>
								<div style="width:80%; float:left;">
									<input type="range" id="rendering_layerslider" name="layer" value="1" min="1" max="100" />
								</div>
								<div style="width:10%; float:left; text-align:center;">
									<a data-role="button" data-inline="true" data-mini="true" onclick="javascript: rendering_ui_changeLayer('+');">+</a>
								</div>
							</div>
						</div>
						<div id="rendering_rangeslider_container" style="display: none;">
							<h3>{layer_flow}</h3>
							<div data-role="rangeslider" id="rendering_rangeslider">
								<label for="range-1a" class="ui-hidden-accessible">Rangeslider:</label>
								<input id="rendering_rangeslider_start" type="range" name="range-1a" min="0" max="100" value="0">
								<label for="range-1b" class="ui-hidden-accessible">Rangeslider:</label>
								<input id="rendering_rangeslider_end" type="range" name="range-1b" min="0" max="100" value="100">
							</div>
						</div>
<!-- 							<div class="ui-block-b" id="rendering_layerNb"></div> -->
						<div id="rendering_option_container" style="display: none;">
							<h3>{speed_label}</h3>
							<label for="speedDisplayRadio"><input type="radio" name="speedDisplay" id="speedDisplayRadio" value="1" data-mini="true" onclick="GCODE.ui.processOptions()" checked>{speedDisplay}</label>
							<label for="exPerMMRadio"><input type="radio" name="speedDisplay" id="exPerMMRadio" value="1" data-mini="true" onclick="GCODE.ui.processOptions()" >{exPerMM}</label>
							<label for="volPerSecRadio"><input type="radio" name="speedDisplay" id="volPerSecRadio" value="1" data-mini="true" onclick="GCODE.ui.processOptions()" >{volPerSec}</label>
							<h3>{option_others}</h3>
							<label for="showMovesCheckbox"><input type="checkbox" id="showMovesCheckbox" value="1" data-mini="true" onclick="GCODE.ui.processOptions()">{showMoves}</label>
							<label for="showRetractsCheckbox"><input type="checkbox" id="showRetractsCheckbox" value="2" data-mini="true" onclick="GCODE.ui.processOptions()">{showRetracts}</label>
							<label for="moveModelCheckbox"><input type="checkbox" id="moveModelCheckbox" value="3" data-mini="true" onclick="GCODE.ui.processOptions()" checked>{moveModel}</label>
							<label for="differentiateColorsCheckbox"><input type="checkbox" id="differentiateColorsCheckbox" value="7" data-mini="true" onclick="GCODE.ui.processOptions()" checked>{differentiateColors}</label>
							<label for="thickExtrusionCheckbox"><input type="checkbox" id="thickExtrusionCheckbox" value="8" data-mini="true" onclick="GCODE.ui.processOptions()">{thickExtrusion}</label>
							<label for="alphaCheckbox"><input type="checkbox" id="alphaCheckbox" value="10" data-mini="true" onclick="GCODE.ui.processOptions()" >{alpha}</label>
							<label for="showNextLayer"><input type="checkbox" id="showNextLayer" value="9" data-mini="true" onclick="GCODE.ui.processOptions()" >{showNextLayer}</label>
						</div>
					</div>
					<div id="gCodeContainer" style="display: none;"></div>
				</div>
				<a data-role="button" id="gcode_back_print" href="{back_print_url}" data-ajax="false" style="display: none;">{back_print_button}</a>
			</div>
		</div>
	</div>
	<div id="errorList"></div>
<script type="text/javascript">
var var_show_rendering = {js_render};
var var_rendering_done = false;
var var_gcode_ajax = null;

function rendering_resizeAnalyser() {
	var newWidth = $('#gc_analyser_container').width();
	
	if ($('#rendering').is(':visible')) {
		$('#canvas').width(newWidth);
	}
	else if ($('#gCodeContainer').is(':visible')) {
		GCODE.ui.changeCodeMirrorWidth(newWidth);
	}
}

function nav_show_rendering() {
	$('#rendering').show();
	$('#gCodeContainer').hide();
	
	$('#canvas').width($('#gc_analyser_container').width());
	
	if (var_gcode_ajax !== null && var_rendering_done == false) {
		$("#overlay").addClass("gray-overlay");
		$(".ui-loader").css("display", "block");
	}
}

function nav_show_gcode() {
	$('#rendering').hide();
	$('#gCodeContainer').show();
	GCODE.ui.tabGCode_click();
	rendering_resizeAnalyser();
}

function gcode_hide_wait_spinner() {
	var_rendering_done = true;
	$("#overlay").removeClass("gray-overlay");
	$(".ui-loader").css("display", "none");
// 	rendering_change_layerNb_display(0);
	$('div#rendering_rangeslider_container').show();
	$('div#rendering_layerslider_container').show();
	$('div#rendering_option_container').show();
// 	$('div#rendering_option_container').trigger('create');
}

function rendering_ui_changeLayer(var_direction) {
	if (typeof var_direction == 'undefined') {
		return;
	}

	switch (var_direction) {
	case '+':
		var_max = parseInt($('#rendering_layerslider').attr('max'));
		var_now = parseInt($('#rendering_layerslider').val());
		if (++var_now <= var_max) {
			$('#rendering_layerslider').val(var_now);
			$('#rendering_layerslider').slider('refresh');
		}
		break;

	case '-':
		var_min = parseInt($('#rendering_layerslider').attr('min'));
		var_now = parseInt($('#rendering_layerslider').val());
		if (--var_now >= var_min) {
			$('#rendering_layerslider').val(var_now);
			$('#rendering_layerslider').slider('refresh');
		}
		break;

	default:
		break;
	}

	return;
}

// function rendering_change_layerNb_display(layerNb) {
// 	var layerDisplay = 1;
	
// 	if (typeof(layerNb) == 'undefined') {
// 		return;
// 	}
// 	else {
// 		layerDisplay = layerNb + 1;
// 	}
	
// 	$('div#rendering_layerNb').html('{layer_prefix}' + layerDisplay);
// }

GCODE.ui.initHandlers();
window.addEventListener('resize', rendering_resizeAnalyser, false);
$(window).on('orientationchange', rendering_resizeAnalyser);

if (var_show_rendering == true) {
	$("a#nav_a_rendering").addClass("ui-btn-active");
	nav_show_rendering();
}
else {
	$("a#nav_a_gcode").addClass("ui-btn-active");
	nav_show_gcode();
}

// $("div#rangeslider_test").on("change", function() {
// 	if (var_rendering_done == true) {
// 		console.log('v1: ' + $('#rangeslider_test_v1').val());
// 		console.log('v2: ' + $('#rangeslider_test_v2').val());
// 	}
// });

$(document).ready(function() {
	var_gcode_ajax = $.ajax({
		url: "{gcode_request_url}",
		dataType: "text",
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			if (var_show_rendering == false) {
				$("#overlay").removeClass("gray-overlay");
				$(".ui-loader").css("display", "none");
			}
			$('a#gcode_back_print').show();
		},
	}).done(function(data) {
		console.log("Charge gcode file done");
		GCODE.ui.customLoadData(data);
		rendering_resizeAnalyser();
	});
});
</script>
</div>