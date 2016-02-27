<div data-role="page" data-url="/sliceupload/slice">
	<style>
		div.slicer_slider_adjustment_container input[type=number] { display: none !important; }
		.zeeprocanvas { background-color: rgba(0,0,0,1.0); border: 0px; }
	</style>
	<div id="overlay"></div>
	<header data-role="header" class="page-header">
		<a href="#" data-icon="back" data-ajax="false" style="visibility:hidden">{back}</a>
		<a href="#" onclick="javascript:window.location.href='/';" data-icon="home" data-ajax="false" style="float:right">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<div id="preview_zone" style="clear: both; text-align: center;">
				<div id="control_general_group" style="display: none;">
					<button id="preview_near_button" data-inline="true" data-icon="plus" data-iconpos="left" onclick="javascript: getPreviewNear();" class="ui-btn-hidden" data-disabled="false">{near_button}</button>
					<button id="preview_far_button" data-inline="true" data-icon="minus" data-iconpos="left" onclick="javascript: getPreviewFar();" class="ui-btn-hidden" data-disabled="false">{far_button}</button>
				</div>
				<div id="preview_image_zone"><canvas id="ivwindow3d" width="500px" height="500px" class="zeeprocanvas"></canvas></div>
				
			</div>
			<div id="detail_zone" style="clear: both; text-align: center; display: none;">
				<div id="model_coordinate_info" style="font-size: small;">
					X = <span id="model_xsize_info">{model_xsize}</span>mm x 
					Y = <span id="model_ysize_info">{model_ysize}</span>mm x 
					Z = <span id="model_zsize_info">{model_zsize}</span>mm
				</div>
				<div id="control_modify_group">
					<div id="control_modify_mini_group" style="display: none;">
						<div data-role="navbar" style="margin-bottom: 1em;">
							<ul>
								<li><a id="slicer_mini_size" href="#" onclick="javascript: onMiniSliderSwitched('s');" class="ui-btn-active">%</a></li>
								<li><a id="slicer_mini_rotate_x" href="#" onclick="javascript: onMiniSliderSwitched('x');">X</a></li>
								<li><a id="slicer_mini_rotate_y" href="#" onclick="javascript: onMiniSliderSwitched('y');">Y</a></li>
								<li><a id="slicer_mini_rotate_z" href="#" onclick="javascript: onMiniSliderSwitched('z');">Z</a></li>
							</ul>
							<input type="range" name="slicer_mini_control" id="slicer_mini_control" value="{model_scale}" min="1" max="{model_smax}" oninput="onMiniSliderChanged(this.value);" onchange="onMiniSliderChanged(this.value);" />
							<input type="button" class="slicer_reset_model_button" value="{reset_model_button}">
						</div>
					</div>
					<div data-role="collapsible" data-collapsed="true" id="control_modify_sliders">
						<h4>{scale_rotate_title}</h4>
						<ul data-role="listview" data-inset="true">
							<li data-role="list-divider">{scale_title}</li> <!-- <label for="slicer_size"></label> -->
							<li>
								<input type="range" name="slicer_size" id="slicer_size" value="{model_scale}" min="1" max="{model_smax}" oninput="onSliderChanged('s', this.value);" onchange="onSliderChanged('s', this.value);" />
								<div class="slicer_printer_rendering"><input type="button" id="slicer_set_model_scale_button" value="{set_model_button}"></div>
							</li>
							<li data-role="list-divider">{rotate_title}</li>
							<li>
								<label for="slicer_rotate_x">{rotate_x_title}</label>
								<input type="range" name="slicer_rotate_x" id="slicer_rotate_x" value="{model_xrot}" min="-180" max="180" oninput="onSliderChanged('x', this.value);" onchange="onSliderChanged('x', this.value);" />
								<label for="slicer_rotate_y">{rotate_y_title}</label>
								<input type="range" name="slicer_rotate_y" id="slicer_rotate_y" value="{model_yrot}" min="-180" max="180" oninput="onSliderChanged('y', this.value);" onchange="onSliderChanged('y', this.value);" />
								<label for="slicer_rotate_z">{rotate_z_title}</label>
								<input type="range" name="slicer_rotate_z" id="slicer_rotate_z" value="{model_zrot}" min="-180" max="180" oninput="onSliderChanged('z', this.value);" onchange="onSliderChanged('z', this.value);" />
								<div class="slicer_printer_rendering"><input type="button" id="slicer_set_model_rotate_button" value="{set_model_button}"></div>
							</li>
						</ul>
						<input type="button" class="slicer_reset_model_button" value="{reset_model_button}">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false">
					<h4>{preset_title}</h4> <!-- <label for="preset_menu"></label> -->
					<div data-role="fieldcontain">
						<select name="preset_menu" id="preset_menu" onchange="javascript: syncParameter();">
						{preset_list}
							<option id="p{id}" value="{id}" data-infill="{infill}" data-skirt="{skirt}" data-raft="{raft}" data-support="{support}">{name}</option>
						{/preset_list}
							<option value="_GOTO_PRESET_LIST_">{goto_preset}</option>
						</select>
						<p style="text-align: center; margin: 0; font-size: x-small;">{select_hint}</p>
						<!-- usage: $("option#p" + $("select#preset_menu").val()).attr("data-xxx"), or use /sliceupload/preset_prm_ajax?id=[PRESET_ID_HERE] to get json info -->
					</div>
				</div>
				<a href="#" id="slice_button" class="ui-disabled" data-role="button" onclick="javascript: startSlice();">{slice_button}</a>
			</div>
		</div>
	</div>

<script type="text/javascript">
var var_stage = "{current_stage}";
var var_preview;
var var_slice;
var var_slice_status_lock = false;
var var_model_change;
var var_current_rho = {value_rho};
var var_current_delta = {value_delta};
var var_current_theta = {value_theta};
var var_interval_rho = 100;
var var_color_right = '{color_default}';
var var_color_left = '{color_default}';
var var_printer_preview_failed = false;

var var_model_scale = {model_scale};
var var_model_zrot = {model_zrot};
var var_model_xrot = {model_xrot};
var var_model_yrot = {model_yrot};

var var_wait_preview = false;
var var_webgl_support = true;
var var_webgl_initialized = false;
var var_mini_control_slider_type = 's'; // scale as default
var var_multi_part = {multi_part};


$(document).ready(prepareDisplay());

function prepareDisplay() {
	if (var_stage == "wait_slice") {
		getPreview();
		// set goto preset listener
		$("select#preset_menu").change(function() {
			if ($(this).val() == "_GOTO_PRESET_LIST_") {
				var user_input = confirm("{goto_hint}");
				if (user_input == true) {
					window.location.href="/preset";
				}
			}
		});
		$("input.slicer_reset_model_button").click(function() {
			resetModel();
		});
		if (var_webgl_support == false) {
			$("input#slicer_set_model_scale_button").click(function() {
				changeModel('scale');
			});
			$("input#slicer_set_model_rotate_button").click(function() {
				changeModel('rotate');
			});
		}
		else { // if (document.body.clientHeight < 750)
			$("div#control_modify_sliders").hide();
			$("div#control_modify_mini_group").show();
		}
	}
	else if (var_stage == "wait_print") {
		// try to get sliced info
		$("#detail_zone").html("");
// 		getPreview(false);
		getSlice();
	}
	else {
		// treat error
		alert("unable to reach here");
	}
	
	return;
}

function onWebGL_finalized() {
	$("div#detail_zone").show();
	$("a#slice_button").removeClass("ui-disabled");
	
	// change model by current coordinates after loading model
	onSliderChanged('s', var_model_scale);
	onSliderChanged('x', var_model_xrot);
	onSliderChanged('y', var_model_yrot);
	onSliderChanged('z', var_model_zrot);
	
	if (var_stage == "wait_slice") { // add case (var_mini_control_slider_type == 's') if necessary
		$("input#slicer_mini_control").attr("max", $("input#slicer_size").attr("max"));
		$("input#slicer_mini_control").slider("refresh");
	}
	
	return;
}

function onWebGLRequest_rollback() {
	var_printer_preview_failed = true;
	var_webgl_initialized = true;
	var_webgl_support = false;
	$("div.slicer_printer_rendering").show();
	$("div#control_modify_sliders").show();
	$("div#control_modify_mini_group").hide();
	
	getPreview();
	
	return;
}

function onMiniSliderSwitched(type) {
	if (typeof(type) == 'undefined') return;
	
	var var_minToChange = null;
	var var_maxToChange = null;
	var var_valToChange = null;
	var var_typeToChange = null;
	
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
	
	switch (type) {
		case 's':
			var_minToChange = 1;
			var_maxToChange = $("input#slicer_size").attr("max");
			var_valToChange = $("input#slicer_size").val();
			var_typeToChange = 's';
			break;
			
		case 'x':
			var_typeToChange = 'x';
			var_valToChange = $("input#slicer_rotate_x").val();
		case 'y':
			if (var_typeToChange == null) var_typeToChange = 'y';
			if (var_valToChange == null) var_valToChange = $("input#slicer_rotate_y").val();
		case 'z':
			if (var_typeToChange == null) var_typeToChange = 'z';
			if (var_valToChange == null) var_valToChange = $("input#slicer_rotate_z").val();
			var_minToChange = -180;
			var_maxToChange = 180;
			break;
			
		default:
			return;
			break;
	}
	
	$("input#slicer_mini_control").attr("max", var_maxToChange);
	$("input#slicer_mini_control").attr("min", var_minToChange);
	$("input#slicer_mini_control").val(var_valToChange);
	var_mini_control_slider_type = var_typeToChange;
	$("input#slicer_mini_control").slider("refresh");
	$("#overlay").removeClass("gray-overlay");
	$(".ui-loader").css("display", "none");
	
	return;
}

function onMiniSliderChanged(value) {
// 	debugger;
	switch (var_mini_control_slider_type) {
		case 's':
			$("input#slicer_size").val(value);
			$("input#slicer_size").slider("refresh");
			break;
			
		case 'x':
		case 'y':
		case 'z':
			$("input#slicer_rotate_" + var_mini_control_slider_type).val(value);
			$("input#slicer_rotate_" + var_mini_control_slider_type).slider("refresh");
			break;
			
		default:
			break;
	}
	
	return;
}

function onPrinterPreviewMinorError_rollback() {
//	alert("{preview_fail}");
	$("#preview_zone").html("{preview_fail}");
	$("div#detail_zone").show();
	$("div#control_modify_group").hide();
	$("a#slice_button").removeClass("ui-disabled");
	
	return;
}

function getPreview(exchange) {
	// try client rendering firstly
	if (var_webgl_initialized == false) {
		zpInit3d(document.getElementById('ivwindow3d'), '/rest/getrenderv1', var_color_right, var_color_left);
		var_webgl_initialized = true;
	}
	if (var_webgl_support == true) {
		$("div.slicer_printer_rendering").hide();
		
		// change color possiblity
		if (typeof(exchange) != 'undefined' && exchange == true) {
			exchangeRenderColors();
		}
		
		return;
	}
	else {
		console.log('WebGL initialization failed, fallback to printer rendering');
		$("#preview_image_zone").html("{wait_preview}");
	}
	
	if (var_slice_status_lock == true) {
		return;
	}
	else {
		var_slice_status_lock = true;
	}
	
	$("a#slice_button").addClass("ui-disabled");
	var_preview = $.ajax({
		url: "/sliceupload/preview_ajax",
		type: "GET",
		data: {
			rho: var_current_rho,
			delta: var_current_delta,
			theta: var_current_theta,
			color_right: var_color_right,
			color_left: var_color_left,
		},
		cache: false,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {	
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
			var_wait_preview = false;
		},
		timeout: 1000*60*10,
	})
	.done(function(html) {
		if (var_preview.status == 202) {
			onPrinterPreviewMinorError_rollback();
			
			return;
		}
		
		$("#preview_image_zone").html('<img src="' + html + '" style="max-width: 100%;">');
		$("div#control_general_group").show();
		$("div#detail_zone").show();
		$("a#slice_button").removeClass("ui-disabled");
	})
	.fail(function() {
		if (var_preview.status == 433) { // allow to slice only in managed rendering fail case
			onPrinterPreviewMinorError_rollback();
		}
		else {
			alert("failed preview");
			$("#preview_zone").html("fatal failed");
		}
	})
	.always(function() {
		var_slice_status_lock = false;
	});
	
	return;
}

// printer rendering function
function getPreviewNear() {
	var_current_rho = var_current_rho - var_interval_rho;
	if (var_current_rho < 0) {
		var_current_rho = 0;
	}
	getPreview();
	
	return;
}

//printer rendering function
function getPreviewFar() {
	var_current_rho = var_current_rho + var_interval_rho;
	if (var_current_rho > 5000) {
		var_current_rho = 5000;
	}
	getPreview();
	
	return;
}

//printer rendering function
function changeModel(changeType) {
	var var_model_id = 0;
	var var_ajax_data = {id: var_model_id};
	
	if (typeof changeType == 'undefined' || var_slice_status_lock == true) {
		return;
	}
	
	switch (changeType) {
	case 'scale':
		if ($("input#slicer_size").val() == var_model_scale) {
			// no change
			console.log("set model without any changes");
			return;
		}
		else {
			var_ajax_data.s = $("input#slicer_size").val();
		}
		break;
		
	case 'rotate':
		if ($("input#slicer_rotate_x").val() == var_model_xrot
				&& $("input#slicer_rotate_y").val() == var_model_yrot
				&& $("input#slicer_rotate_z").val() == var_model_zrot) {
			// no change
			console.log("set model without any changes");
			return;
		}
		else {
			var_ajax_data.xrot = $("input#slicer_rotate_x").val();
			var_ajax_data.yrot = $("input#slicer_rotate_y").val();
			var_ajax_data.zrot = $("input#slicer_rotate_z").val();
		}
		break;
		
	default:
		console.log("unknown changeType");
		return;
		break;
	}
	
	var_slice_status_lock = true;
	
	var_model_change = $.ajax({
		url: "/sliceupload/preview_change_ajax",
		type: "GET",
		cache: false,
		data: var_ajax_data,
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			if (var_wait_preview == false) {
				$("#overlay").removeClass("gray-overlay");
				$(".ui-loader").css("display", "none");
			}
		},
	})
	.done(function(data) {
		var_slice_status_lock = false;
		var_wait_preview = true;
		if (changeType == 'scale') {
			var_model_scale = parseInt($("input#slicer_size").val());
		}
		else { // changeType == 'rotate'
			var_model_xrot = parseInt($("input#slicer_rotate_x").val());
			var_model_yrot = parseInt($("input#slicer_rotate_y").val());
			var_model_zrot = parseInt($("input#slicer_rotate_z").val());
		}
		
		getPreview();
		
		try {
			var response = JSON.parse(data);
			
			if (typeof response.{model_key_smax} == 'undefined'
					|| typeof response.{model_key_xsize} == 'undefined'
					|| typeof response.{model_key_ysize} == 'undefined'
					|| typeof response.{model_key_zsize} == 'undefined') {
				throw 'response json does not contain max scale or size info';
			}
			else {
				var value_toChange = Math.floor(response.{model_key_smax});
				
				$("span#model_xsize_info").html(response.{model_key_xsize}.toFixed(1));
				$("span#model_ysize_info").html(response.{model_key_ysize}.toFixed(1));
				$("span#model_zsize_info").html(response.{model_key_zsize}.toFixed(1));
				
				$("input#slicer_size").attr("max", value_toChange);
				console.log("change model max scale: " + value_toChange);
				if (var_model_scale > value_toChange) { // check max size with current value (normally it's impossible)
					var_model_scale = value_toChange;
					// assign and refresh is already in ajax always function
				}
			}
		}
		catch (err) {
			console.log("set model return json data treatment trigger error: " + err);
		}
	})
	.fail(function() { // not allowed
		alert("{setmodel_fail}");
		
		// reverse the original state is in ajax always function
	})
	.always(function() {
		var_slice_status_lock = false;
		
		$("input#slicer_size").val(var_model_scale);
		$("input#slicer_rotate_x").val(var_model_xrot);
		$("input#slicer_rotate_y").val(var_model_yrot);
		$("input#slicer_rotate_z").val(var_model_zrot);
		$("input#slicer_rotate_x").slider("refresh");
		$("input#slicer_rotate_y").slider("refresh");
		$("input#slicer_rotate_z").slider("refresh");
		$("input#slicer_size").slider("refresh");
	});
	
	return;
}

function resetModel() {
	if (var_webgl_support == true) {
		view3d.cm_reset();
		onMiniSliderSwitched(var_mini_control_slider_type);
		return;
	}
	
	var var_model_id = 0;
	
	if (var_slice_status_lock == true) {
		return;
	}
	else if ((var_model_scale == 100 || (var_model_scale < 100 && var_model_scale == {model_smax}))
			&& $("input#slicer_rotate_x").val() == 0
			&& $("input#slicer_rotate_y").val() == 0
			&& $("input#slicer_rotate_z").val() == 0) {
		// no change
		console.log("reset model without any changes");
		return;
	}
	else {
		var_slice_status_lock = true;
	}
	
	var_model_change = $.ajax({
		url: "/sliceupload/preview_reset_ajax",
		type: "GET",
		cache: false,
		data: {
			id:	var_model_id,
		},
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {	
			if (var_wait_preview == false) {
				$("#overlay").removeClass("gray-overlay");
				$(".ui-loader").css("display", "none");
			}
		},
	})
	.done(function(data) {
		var value_smax = 100;
		
		try {
			var response = JSON.parse(data);
			
			if (typeof response.{model_key_smax} == 'undefined'
					|| typeof response.{model_key_xsize} == 'undefined'
					|| typeof response.{model_key_ysize} == 'undefined'
					|| typeof response.{model_key_zsize} == 'undefined') {
				throw 'response json does not contain max scale or size info';
			}
			else {
				$("span#model_xsize_info").html(response.{model_key_xsize}.toFixed(1));
				$("span#model_ysize_info").html(response.{model_key_ysize}.toFixed(1));
				$("span#model_zsize_info").html(response.{model_key_zsize}.toFixed(1));
				value_smax = Math.floor(response.{model_key_smax});
				$("input#slicer_size").attr("max", value_smax);
				console.log("change model max scale: " + value_smax);
			}
		}
		catch (err) {
			console.log("set model return json data treatment trigger error: " + err);
		}
		
		var_slice_status_lock = false;
		var_wait_preview = true;
		var_model_scale = (value_smax >= 100) ? 100 : value_smax;
		var_model_xrot = 0;
		var_model_yrot = 0;
		var_model_zrot = 0;
		
		getPreview();
	})
	.fail(function() { // not allowed
		alert("{setmodel_fail}");
	})
	.always(function() {
		$("input#slicer_rotate_x").val(var_model_xrot);
		$("input#slicer_rotate_y").val(var_model_yrot);
		$("input#slicer_rotate_z").val(var_model_zrot);
		$("input#slicer_size").val(var_model_scale);
		$("input#slicer_rotate_x").slider("refresh");
		$("input#slicer_rotate_y").slider("refresh");
		$("input#slicer_rotate_z").slider("refresh");
		$("input#slicer_size").slider("refresh");
		
		var_slice_status_lock = false;
	});
	
	return;
}

function startSlice(var_restart) {
	if (var_slice_status_lock == true) {
		return;
	}
	else {
		var_slice_status_lock = true;
	}
	
	if (var_webgl_support == true) {
		var_model_change = $.ajax({
			url: "/rest/setmodelv1",
			type: "GET",
			cache: false,
			async: false,
			data: {
				s:		$("input#slicer_size").val(),
				xrot:	$("input#slicer_rotate_x").val(),
				yrot:	$("input#slicer_rotate_y").val(),
				zrot:	$("input#slicer_rotate_z").val(),
			},
			beforeSend: function() {
				$("#overlay").addClass("gray-overlay");
				$(".ui-loader").css("display", "block");
			},
			complete: function() {
				if (var_wait_preview == false) {
					$("#overlay").removeClass("gray-overlay");
					$(".ui-loader").css("display", "none");
				}
			},
		})
		.fail(function() { // not allowed
			alert("{setmodel_fail}");
			view3d.cm_reset(); // reset slider and rendering
			var_slice_status_lock = false;
		});
		
		if (var_slice_status_lock == false) {
			return;
		}
	}
	else if (var_printer_preview_failed == true) {
		var slicer_risk_confirm = confirm("{slice_risk_confirm}");
		if (slicer_risk_confirm == false) {
			var_slice_status_lock = false;
			
			return;
		}
	}
	
	var_restart = typeof var_restart !== 'undefined' ? var_restart : false;
	var var_id_preset = (var_restart == true) ? 'previous' : $("select#preset_menu").val();
	
	// disable slice button
	$("a#slice_button").addClass("ui-disabled");
	$("#preview_zone").hide();
	
	$("#detail_zone").html("{wait_slice}");
	var_slice = $.ajax({
		url: "/sliceupload/slice_model_ajax",
		type: "GET",
		data: {
			id: var_id_preset,
		},
		cache: false,
	})
	.done(function(html) {
		var_slice_status_lock = false;
		window.location.href='/sliceupload/slicestatus';
	})
	.fail(function() { // not allowed
		alert("failed slice");
		$("#detail_zone").html("failed");
	})
	.always(function() {
		var_slice_status_lock = false;
	});
}

function getSlice() {
	var_slice_status = $.ajax({
		url: "/sliceupload/slice_result_ajax",
		type: "GET",
		data: {
			callback: 1,
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
		$("div#detail_zone").show();
		if (var_slice_status.status == 202) { // finished checking, wait user to input
			$("#detail_zone").html(html);
		}
		else if (var_slice_status.status == 200) { // in checking
			$("#detail_zone").html("{wait_in_slice} " + html);
		}
	})
	.fail(function() { // not allowed
		window.location.replace("/");
//		alert("failed");
	})
	.always(function() {
		var_slice_status_lock = false;
	});
}

function syncParameter() {
	var var_preset_id = $('select#preset_menu');
	var var_preset_option = $('option#p' + var_preset_id.val());
	var var_preset_infill = var_preset_option.data('infill');
}
</script>

</div>