<div id="overlay"></div>
<div id="{random_prefix}printModel_detailPage" data-role="page">
	<style> input[type=number] { display : none !important; } </style>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
		<form action="/printdetail/printmodel_temp?id={model_id}" method="POST" data-ajax="false">
			<h2 style="text-align: center;">{title}</h2>
			<div data-role="collapsible" data-collapsed="false">
				<h4>{desp_title}</h4>
				<p>{desp}</p>
			</div>
			<div data-role="collapsible" data-collapsed="false" style="text-align: center;">
				<h4>{preview_title}</h4>
				<img src="{image}" style="max-width: 100%;"><br>
				<p>{color_suggestion}</p>
				<div class="widget_monomodel" style="display: none;">
					<div style="width: 75px; height: 75px; background-color: {model_c_r}; margin: 0 auto;">
						<img src="/images/cartridge.png" style="width: 100%">
					</div>
				</div>
				<div class="widget_bimodel" style="display: none;">
					<div class="ui-grid-a">
						<div class="ui-block-a">
							<div style="width: 75px; height: 75px; background-color: {model_c_l}; margin: 0 auto;">
								<img src="/images/cartridge.png" style="width: 100%">
							</div>
						</div>
						<div class="ui-block-b">
							<div style="width: 75px; height: 75px; background-color: {model_c_r}; margin: 0 auto;">
								<img src="/images/cartridge.png" style="width: 100%">
							</div>
						</div>
					</div>
				</div>
				<p>{time}</p>
			</div>
			<div data-role="collapsible" data-collapsed="false" style="text-align: center;">
				<h4>{title_current}</h4>
				<div class="widget_monocolor" style="display: none;">
					<div style="width: 75px; height: 75px; background-color: {state_c_r}; margin: 0 auto;">
						<img src="/images/cartridge.png" style="width: 100%">
					</div>
					<p id="state_mono">{state_f_r}</p>
					<a href="#" data-role="button" data-ajax="false" data-iconpos="none" class="ui-shadow ui-corner-all" onclick="javascript: changecartridge('r');">{change_filament_r}</a>
					{chg_temperature}
					<input type="range" id="slider-mono" value="{temper_filament_r}" min="{temper_min}" max="{temper_max}" data-show-value="true">
				</div>
				<div class="widget_bicolor" style="display: none;">
					<div class="widget_monomodel switch-larger" style="display: none;">
						<select id="exchange_extruder_m" data-role="slider" data-track-theme="a" data-theme="a" {enable_exchange}>
							<option value="1">{exchange_on}</option>
							<option value="0" selected="selected">{exchange_off}</option>
						</select>
					</div>
					<div class="widget_bimodel" style="display: none;">
						<label>
							<input type="checkbox" id="exchange_extruder_b" value="1" {enable_exchange}>{exchange_extruder}
						</label>
					</div>
					<div class="ui-grid-a">
						<div class="ui-block-a">
							<div style="width: 75px; height: 75px; background-color: {state_c_l}; margin: 0 auto;">
								<img src="/images/cartridge.png" style="width: 100%">
							</div>
						</div>
						<div class="ui-block-b">
							<div style="width: 75px; height: 75px; background-color: {state_c_r}; margin: 0 auto;">
								<img src="/images/cartridge.png" style="width: 100%">
							</div>
						</div>
						<div class="ui-block-a">
							<p id="state_left">{state_f_l}</p>
						</div>
						<div class="ui-block-b">
							<p id="state_right">{state_f_r}</p>
						</div>
						<div class="ui-block-a" style="padding-left:0px">
							<a href="#" data-role="button" data-ajax="false" data-iconpos="none" class="ui-shadow ui-corner-all" onclick="javascript: changecartridge('l');">{change_filament_l}</a>
						</div>
						<div class="ui-block-b">
							<a href="#" data-role="button" data-ajax="false" data-iconpos="none" class="ui-shadow ui-corner-all" onclick="javascript: changecartridge('r');">{change_filament_r}</a>
						</div>
					</div>
					<div class="ui-grid-a">
						<div class="ui-block-a">
							{temp_adjustments_l}
						</div>
						<div class="ui-block-b">
							{temp_adjustments_r}
						</div>
						<div class="ui-block-a" id="div-slider1">
							<input type="range" name="l" id="slider-l" value="{temper_filament_l}" min="{temper_min}" max="{temper_max}" data-show-value="true">
						</div>
						<div class="ui-block-b" id="div-slider2">
							<input type="range" name="r" id="slider-r" value="{temper_filament_r}" min="{temper_min}" max="{temper_max}" data-show-value="true">
						</div>
					</div>
				</div>
			</div>
			<div data-role="collapsible" style="text-align: center;">
				<h4>{advanced}</h4>
				<p style="font-weight: bold;">{extrud_multiply}</p>
				<div class="widget_monocolor" style="display: none;">
					<input type="range" id="slider_mono_em" value="{extrud_r}" min="{extrud_min}" max="{extrud_max}" data-show-value="true" />
				</div>
				<div class="widget_bicolor ui-grid-a" style="display: none;">
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
			<div style="clear: both;">
				<input type="hidden" name="exchange" id="exchange_extruder_hidden" value="0">
				<input type="submit" value="{print_model}" class="ui-btn ui-shadow ui-corner-all ui-btn-icon-left ui-icon-refresh" />
			</div>
			</form>
		</div>
	</div>

<script>
var var_enable_print = {enable_print};
var limit_min_tmp = {temper_min};
var limit_max_tmp = {temper_max};
var delta_tmp = {temper_delta};
var var_bicolor_model = {bicolor_model};
var var_bicolor_printer = {bicolor_printer};
var slider_l = "input#slider-l";
var slider_r = var_bicolor_printer ? "input#slider-r" : "input#slider-mono";
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

$("#{random_prefix}printModel_detailPage").on("pagecreate",function() {
	if (var_bicolor_model == true) {
		$(".widget_bimodel").show();
	}
	else {
		$(".widget_monomodel").show();
	}
	
	if (var_bicolor_printer == true) {
		$(".widget_bicolor").show();
	}
	else {
		$(".widget_monocolor").show();
		
		$(slider_r).change(function() {
			$("input#slider-r").val($(slider_r).val());
		});
		$("input#slider_mono_em").change(function() {
			$("input#slider_right_em").val($("input#slider_mono_em").val());
		});
	}
	
	var tmp = $(slider_r).val();
	var min_tmp = tmp - delta_tmp;
	var max_tmp = parseInt(tmp) + delta_tmp;
	
	$(slider_r).attr('min', (min_tmp < limit_min_tmp) ? limit_min_tmp : min_tmp);
	$(slider_r).attr('max', (max_tmp > limit_max_tmp) ? limit_max_tmp : max_tmp);
	$(slider_r).slider("refresh");
	
	tmp = $(slider_l).val();
	min_tmp = tmp - delta_tmp;
	max_tmp = parseInt(tmp) + delta_tmp;
	
	$(slider_l).attr('min', (min_tmp < limit_min_tmp) ? limit_min_tmp : min_tmp);
	$(slider_l).attr('max', (max_tmp > limit_max_tmp) ? limit_max_tmp : max_tmp);
	$(slider_l).slider("refresh");
	
	$("input[type=submit]").on('click', function()
	{
		$("#overlay").addClass("gray-overlay");
		$(".ui-loader").css("display", "block");
	});
	
	if (var_need_print_right == false || "{state_f_r}" == "{error}") {
		$(slider_r).slider({disabled: true});
	}
	if (var_need_print_left == false || "{state_f_l}" == "{error}") {
		$(slider_l).slider({disabled: true});
	}
	
	if (var_enable_print == false) {
		$("input[type=submit]").button("disable");
	}
	
	//assign trigger for exchange extruder
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
			$("input[type=submit]").button("enable");
			$("input[type=submit]").button("refresh");
		}
		
		if (var_bicolor_model == true) {
			$("p#state_right").html('{filament_ok}');
			$("p#state_left").html('{filament_ok}');
			
			if ($(widget_exchange).is(":checked")) {
				$(hidden_input_exchange).val("1");
			}
			else {
				$(hidden_input_exchange).val("0");
			}
		}
		else {
			// switch temperature slider and state message if it's mono-color model
			if (var_need_print_right) {
				$(slider_r).slider({disabled: true});
				$(slider_l).slider({disabled: false});
				$("p#state_left").html('{filament_ok}');
				$("p#state_right").html('{filament_not_need}');
				var_need_print_right = false;
				var_need_print_left = true;
			}
			else { // var_need_print_left
				$(slider_r).slider({disabled: false});
				$(slider_l).slider({disabled: true});
				$("p#state_right").html('{filament_ok}');
				$("p#state_left").html('{filament_not_need}');
				var_need_print_left = false;
				var_need_print_right = true;
			}
			
			$(hidden_input_exchange).val($(widget_exchange).val());
		}
	});
});
</script>
</div>
