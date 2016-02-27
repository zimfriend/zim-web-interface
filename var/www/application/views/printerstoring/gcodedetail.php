<div id="overlay"></div>
<div data-role="page">
	<style>
		input[type=number] { display : none !important; }
	</style>
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
		<form action="/printdetail/printgcode_temp?id={id}" method="POST" data-ajax="false">
			<h2 style="text-align: center;">{title}</h2>
			<div data-role="collapsible" data-collapsed="false" style="text-align: center;">
				<h4>{photo_title}</h4>
				<img src="/printerstoring/getpicture?type=gcode&id={id}" style="max-width: 100%;"><br>
			</div>
			<div data-role="collapsible" data-collapsed="false" style="text-align: center;">
				<h4>{title_current}</h4>
				<div style="display: none;" class="widget_monocolor">
					<div style="width: 75px; height: 75px; background-color: {state_c_r}; margin: 0 auto;">
						<img src="/images/cartridge.png" style="width: 100%">
					</div>
					<p id="state_right">{state_f_r}</p>
					<a href="/printerstate/changecartridge?v=r&f={need_filament_r}&id=gcode{id}" data-role="button" data-ajax="false" data-iconpos="none" class="ui-shadow ui-corner-all">{change_filament_r}</a>
					<div>{temp_adjustments}</div>
					<input type="range" id="slider-mono" value="{temper_filament_r}" min="{temper_min}" max="{temper_max}" data-show-value="true">
				</div>
				<div style="height:265px; display: none;" class="widget_bicolor">
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
							<a href="/printerstate/changecartridge?v=l&f={need_filament_l}&id=gcode{id}" data-role="button" data-ajax="false" data-iconpos="none" class="ui-shadow ui-corner-all">{change_filament_l}</a>
						</div>
						<div class="ui-block-b">
							<a href="/printerstate/changecartridge?v=r&f={need_filament_r}&id=gcode{id}" data-role="button" data-ajax="false" data-iconpos="none" class="ui-shadow ui-corner-all">{change_filament_r}</a>
						</div>
					</div>
					<div class="ui-grid-a">
						<div class="ui-block-a">{temp_adjustments_l}</div>
						<div class="ui-block-b">{temp_adjustments_r}</div>
						<div class="ui-block-a">
							<input type="range" name="l" id="slider-l" value="{temper_filament_l}" min="{temper_min}" max="{temper_max}" data-show-value="true">
						</div>
						<div class="ui-block-b">
							<input type="range" name="r" id="slider-r" value="{temper_filament_r}" min="{temper_min}" max="{temper_max}" data-show-value="true">
						</div>
					</div>
				</div>
			</div>
			<div data-role="collapsible" style="clear: both; text-align: center;">
				<h4>{advanced}</h4>
				<div class="ui-grid-a">
					<div class="ui-block-a">
						<a data-role="button" href="/gcode/library/display?id={id}" data-ajax="false">{gcode_link}</a>
					</div>
					<div class="ui-block-b">
						<a data-role="button" href="/gcode/library/render?id={id}" data-ajax="false">{2drender_link}</a>
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
			<div style="clear: both;">
				<input type="hidden" name="exchange" id="exchange_extruder_hidden" value="0">
				<input type="submit" value="{print_button}" class="ui-btn ui-shadow ui-corner-all ui-btn-icon-left ui-icon-refresh" />
			</div>
			</form>
		</div>
	</div>

<script>
var var_enable_print = {enable_print};
var var_suggest_temper_right = {temper_suggest_r};
var var_suggest_temper_left = {temper_suggest_l};
var limit_min_tmp = {temper_min};
var limit_max_tmp = {temper_max};
var delta_tmp = {temper_delta};
var error_checked = false;
var var_bicolor = {bicolor};
var slider_l = "input#slider-l";
var slider_r = var_bicolor ? "input#slider-r" : "input#slider-mono";

$(document).on("pagecreate",function() {
	if (var_bicolor == true) {
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
	
	if (var_enable_print == false) {
		$("input[type=submit]").button("disable");
	}
	
	var tmp = $(slider_r).val();
	var min_tmp = tmp - delta_tmp;
	var max_tmp = parseInt(tmp) + delta_tmp;
	
	$(slider_r).attr('min', (min_tmp < limit_min_tmp) ? limit_min_tmp : min_tmp);
	$(slider_r).attr('max', (max_tmp > limit_max_tmp) ? limit_max_tmp : max_tmp);
	if (var_suggest_temper_right != $(slider_r).val()
			&& var_suggest_temper_right >= $(slider_r).attr('min')
			&& var_suggest_temper_right <= $(slider_r).attr('max')) {
		$(slider_r).val(var_suggest_temper_right);
	}
	$(slider_r).slider("refresh");
	
	tmp = $(slider_l).val();
	min_tmp = tmp - delta_tmp;
	max_tmp = parseInt(tmp) + delta_tmp;
	
	$(slider_l).attr('min', (min_tmp < limit_min_tmp) ? limit_min_tmp : min_tmp);
	$(slider_l).attr('max', (max_tmp > limit_max_tmp) ? limit_max_tmp : max_tmp);
	if (var_suggest_temper_left != $(slider_l).val()
			&& var_suggest_temper_left >= $(slider_l).attr('min')
			&& var_suggest_temper_left <= $(slider_l).attr('max')) {
		$(slider_l).val(var_suggest_temper_left);
	}
	$(slider_l).slider("refresh");
	
	$("input[type=submit]").on('click', function()
	{
		$("#overlay").addClass("gray-overlay");
		$(".ui-loader").css("display", "block");
	});
	
	if ("{state_f_r}" != "{msg_ok}") {
		$(slider_r).slider({disabled: true});
		error_checked = true;
	}
	if ("{state_f_l}" != "{msg_ok}") {
		$(slider_l).slider({disabled: true});
		error_checked = true;
	}
	if (error_checked == false) {
		if ({need_filament_l} <= 0) {
			$(slider_l).slider({disabled: true});
		}
		if ({need_filament_r} <= 0) {
			$(slider_r).slider({disabled: true});
		}
	}
});
</script>
</div>
