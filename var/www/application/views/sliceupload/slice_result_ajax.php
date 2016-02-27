
				<form action="/printdetail/printslice_temp" method="POST" data-ajax="false">
					<div data-role="collapsible" data-collapsed="false" style="clear: both;">
						<h4>{result_title}</h4>
						<div id="exchange_container" class="ui-bar ui-bar-f widget_bicolor" style="height:3em; display: none;">
							<div class="widget_monomodel" style="display: none;">
								<select id="exchange_extruder_m" data-role="slider" data-track-theme="a" data-theme="a" {enable_exchange}>
									<option value="{exchange_o1_val}">{exchange_o1}</option>
									<option value="{exchange_o2_val}" {exchange_o2_sel}>{exchange_o2}</option>
								</select>
							</div>
							<div class="widget_bimodel" style="display: none;">
								<label>
									<input type="checkbox" id="exchange_extruder_b" value="1" {enable_exchange}>{exchange_extruder}
								</label>
							</div>
						</div>
						<div class="widget_monocolor" style="display: none;">
							<div id="mono_cartridge">
								<div style="width: 75px; height: 75px; background-color: {cartridge_c_r}; margin: 0 auto;">
									<img src="/images/cartridge.png" style="width: 100%">
								</div>
								<p id="state_f_mono">{state_f_r}</p>
							</div>
							<div class="slicer_slider_adjustment_container">
								<label>{chg_temperature}</label>
								<input type="range" id="slider_mono" value="{temper_r}" min="{temper_min}" max="{temper_max}" data-show-value="true" />
							</div>
						</div>
						<div class="ui-grid-a widget_bicolor" style="display: none;">
							<div class="ui-block-a"><div id="left_cartridge" class="ui-bar ui-bar-f">
								<div style="width: 75px; height: 75px; background-color: {cartridge_c_l}; margin: 0 auto;">
									<img src="/images/cartridge.png" style="width: 100%">
								</div>
								<p id="state_f_l">{state_f_l}</p>
							</div></div>
							<div class="ui-block-b"><div id="right_cartridge" class="ui-bar ui-bar-f">
								<div style="width: 75px; height: 75px; background-color: {cartridge_c_r}; margin: 0 auto;">
									<img src="/images/cartridge.png" style="width: 100%">
								</div>
								<p id="state_f_r">{state_f_r}</p>
							</div></div>
						</div>
						<p style="text-align: left;">{error_msg}</p>
						<div class="ui-grid-a slicer_slider_adjustment_container widget_bicolor" style="display: none;">
							<div class="ui-block-a">
								<label>{left_temperature}</label>
								<div id="temper_l">
									<input type="range" id="slider_left" name="l" value="{temper_l}" min="{temper_min}" max="{temper_max}" data-show-value="true" />
								</div>
							</div>
							<div class="ui-block-b">
								<label>{right_temperature}</label>
								<div id="temper_r">
									<input type="range" id="slider_right" name="r" value="{temper_r}" min="{temper_min}" max="{temper_max}" data-show-value="true" />
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="exchange" id="exchange_extruder_hidden" value="0">
					<div data-role="collapsible" style="clear: both;">
						<h4>{advanced}</h4>
						<div class="ui-grid-a">
							<div class="ui-block-a">
								<a data-role="button" onclick='javascript: window.location.href="/gcode/slice/display"'>{gcode_link}</a>
							</div>
							<div class="ui-block-b">
								<a data-role="button" onclick='javascript: window.location.href="/gcode/slice/render"'>{2drender_link}</a>
							</div>
						</div>
						<p style="font-weight: bold;">{extrud_multiply}</p>
						<div class="widget_monocolor slicer_slider_adjustment_container" style="display: none;">
							<input type="range" id="slider_mono_em" value="{extrud_r}" min="{extrud_min}" max="{extrud_max}" data-show-value="true" />
						</div>
						<div class="widget_bicolor ui-grid-a slicer_slider_adjustment_container" style="display: none;">
							<div class="ui-block-a">
								<label>{left_extrud_mult}</label>
								<div id="extrud_l">
									<input type="range" id="slider_left_em" name="e_l" value="{extrud_l}" min="{extrud_min}" max="{extrud_max}" data-show-value="true" />
								</div>
							</div>
							<div class="ui-block-b">
								<label>{right_extrud_mult}</label>
								<div id="extrud_r">
									<input type="range" id="slider_right_em" name="e_r" value="{extrud_r}" min="{extrud_min}" max="{extrud_max}" data-show-value="true" />
								</div>
							</div>
						</div>
					</div>
					<input type="submit" id="print_slice" value="{print_button}" />
				</form>

<script>
var var_enable_print = {enable_print};
var var_reslice = {enable_reslice};
var var_need_refresh_preview = false;
var var_bicolor_model = {bicolor_model};
var var_bicolor_printer = {bicolor_printer};
var slider_l = "input#slider_left";
var slider_r = var_bicolor_printer ? "input#slider_right" : "input#slider_mono";
var widget_exchange = var_bicolor_model ? "input#exchange_extruder_b" : "select#exchange_extruder_m";
var var_need_filament_r = {need_filament_r};
var var_need_filament_l = {need_filament_l};
var var_need_print_right = (var_need_filament_r > 0) ? true : false;
var var_need_print_left = (var_need_filament_l > 0) ? true : false;

function changecartridge(side) {
	if (typeof(side) == 'undefined') {
		console.log("changecartridge call api error");
		return;
	}
	else {
		var quantity = null;
		switch (side) {
			case 'r':
				quantity = var_need_filament_r;
				if (var_need_print_right == false) {
					quantity = var_need_filament_l;
				}
				
			case 'l':
				if (quantity === null) {
					quantity = var_need_filament_l;
					if (var_need_print_left == false) {
						quantity = var_need_filament_r;
					}
				}
				window.location.href="/printerstate/changecartridge?v=" + side + "&f=" + quantity + "&id=slice";
				break;
				
			default:
				console.log("unknown side of cartridge");
				break;
		}
	}
	return;
}

if (var_bicolor_model == true) {
	$(".widget_bimodel").show();
}
else {
	$(".widget_monomodel").show();
	$('#exchange_container').addClass("switch-larger");
}

if (var_bicolor_printer == true) {
	$(".widget_bicolor").show();
	
	$('<div>').appendTo('#left_cartridge')
	.attr({'id': 'change_left', 'data-icon': 'refresh', 'data-iconpos':'right', 'onclick': 'javascript: changecartridge("l");'}).html('{change_left}')
	.button().button('refresh');
	$('<div>').appendTo('#right_cartridge')
	.attr({'id': 'change_right', 'data-icon': 'refresh', 'data-iconpos':'right', 'onclick': 'javascript: changecartridge("r");'}).html('{change_right}')
	.button().button('refresh');
}
else {
	$(".widget_monocolor").show();
	$('<div>').appendTo('#mono_cartridge')
	.attr({'id': 'change_right', 'data-icon': 'refresh', 'data-iconpos':'right', 'onclick': 'javascript: changecartridge("r");'}).html('{change_right}')
	.button().button('refresh');
}

$("input[type=submit]").on('click', function()
{
	$("#overlay").addClass("gray-overlay");
	$(".ui-loader").css("display", "block");
});

var limit_min_tmp = {temper_min};
var limit_max_tmp = {temper_max};
var delta_tmp = {temper_delta};
var tmp = $(slider_r).val();
var min_tmp = tmp - delta_tmp;
var max_tmp = parseInt(tmp) + delta_tmp;

$(slider_r).attr('min', (min_tmp < limit_min_tmp) ? limit_min_tmp : min_tmp); 
$(slider_r).attr('max', (max_tmp > limit_max_tmp) ? limit_max_tmp : max_tmp);

tmp = $(slider_l).val();
min_tmp = tmp - delta_tmp;
max_tmp = parseInt(tmp) + delta_tmp;

$(slider_l).attr('min', (min_tmp < limit_min_tmp) ? limit_min_tmp : min_tmp); 
$(slider_l).attr('max', (max_tmp > limit_max_tmp) ? limit_max_tmp : max_tmp);

$('#detail_zone').trigger("create");

if (var_need_print_right == false) {
	$(slider_r).slider({disabled: true});
}
if (var_need_print_left == false) {
	$(slider_l).slider({disabled: true});
}

if (var_enable_print == false) {
	$("#print_slice").button("disable");
}
if (var_reslice == true) {
	$('<div>').appendTo('#detail_zone')
	.attr({'id': 'reslice_button', 'onclick': 'javascript: startSlice(true);'}).html('{reslice_button}')
	.button().button('refresh');
}

if (var_bicolor_printer != true) {
	$(slider_r).change(function() { // must done after trigger of create
		$("input#slider_right").val($(slider_r).val());
	});
	$("input#slider_mono_em").change(function() {
		$("input#slider_right_em").val($("input#slider_mono_em").val());
	});
}

// assign new preview color
var_color_right = '{cartridge_c_r}';
var_color_left = '{cartridge_c_l}';

$("#preview_zone").show();
// if (var_need_refresh_preview) {
	getPreview();
// }

// assign trigger for exchange extruder
$(widget_exchange).change(function() {
	var hidden_input_exchange = "input#exchange_extruder_hidden";
	var tmp_quantity = 0;
	
	// exchange quantity for changing cartridge
	tmp_quantity = var_need_filament_r;
	var_need_filament_r = var_need_filament_l;
	var_need_filament_l = tmp_quantity;
	
	// switch print on and exchange off in some special cases
	if (var_enable_print == false) {
		$(widget_exchange).slider({disabled: true});
		$(widget_exchange).slider("refresh");
		$("#print_slice").button("enable");
	}
	
	// switch temperature slider and state message if it's mono-color model
	if (var_bicolor_model == false) {
		if (var_need_print_right) {
			$(slider_r).slider({disabled: true});
			$(slider_l).slider({disabled: false});
			$("p#state_f_l").html('{filament_ok}');
			$("p#state_f_r").html('{filament_not_need}');
			var_need_print_right = false;
			var_need_print_left = true;
		}
		else { // var_need_print_left
			$(slider_r).slider({disabled: false});
			$(slider_l).slider({disabled: true});
			$("p#state_f_r").html('{filament_ok}');
			$("p#state_f_l").html('{filament_not_need}');
			var_need_print_left = false;
			var_need_print_right = true;
		}
	}
	else {
		$("p#state_f_r").html('{filament_ok}');
		$("p#state_f_l").html('{filament_ok}');
	}
	
	// inverse color and get new preview image if necessary
	if (var_color_right != var_color_left) {
		var temp_color = var_color_right;
		
		var_color_right = var_color_left;
		var_color_left = temp_color;
		getPreview(true);
	}
	
	if (var_bicolor_model == true) {
		if ($(widget_exchange).is(":checked")) {
			$(hidden_input_exchange).val("1");
		}
		else {
			$(hidden_input_exchange).val("0");
		}
	}
	else {
		$(hidden_input_exchange).val($(widget_exchange).val());
	}
});
</script>