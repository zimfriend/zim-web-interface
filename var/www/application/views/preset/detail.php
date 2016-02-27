<div data-role="page" style="overflow-y:hidden;">
	<header data-role="header" class="page-header">
		<a href="javascript:history.back();" data-role="button" data-icon="back" data-ajax="false">{back}</a>
		<a href="/" data-icon="home" data-ajax="false">{home}</a>
	</header>
	<div class="logo"><div id="link_logo"></div></div>
	<div data-role="content">
		<div id="container">
			<style>
/* 				div.ui-slider-switch div.ui-slider-inneroffset a.ui-slider-handle-snapping {display: none;}
				div.ui-slider-switch {width: 150px;} */
			</style>
			<div style="clear: both;">
				<h2>{preset_title}</h2>
				<div style="float: right; margin-top: -4.5em; {hide_delete}" id="delete_container">
					<a href="/preset/delete?id={preset_id}" data-role="button" data-icon="delete" data-ajax="false" data-inline="true">Delete</a> <!-- class="ui-disabled" -->
				</div>
			</div>
			<form action="/preset/detail?id={preset_id}{preset_newurl}" method="post" data-ajax="false" id="form_preset_detail">
			<div id="save_as_container" style="{hide_submit}">
				<h3><label for="save_as">{save_as_title}</label></h3>
				<input type="text" data-clear-btn="true" name="save_as" value="{save_as_value}" id="save_as" data-oldvalue="{save_as_value}" required>
				<input type="hidden" id="save_overwrite" name="save_overwrite" value="0">
			</div>
			<div data-role="collapsible">
				<h4>{layer_perimeter_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{layer_perimeter_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="layer_height">{layer_height}</label>
						<input type="number" style="text-align:right;" step="0.005" data-clear-btn="false" name="layer_height" id="layer_height" value="{layer_height_value}" min="0.025" max="0.2">
					</div>
					<div class="ui-field-contain">
						<label for="first_layer_height">{first_layer_height}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="first_layer_height" id="first_layer_height" value="{first_layer_height_value}" min="0.05" max="0.4">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{layer_perimeter_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="perimeters">{perimeters}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="perimeters" id="perimeters" value="{perimeters_value}" min="1" max="10">
					</div>
					<div class="ui-field-contain">
						<label for="spiral_vase">{spiral_vase}</label>
						<select name="spiral_vase" id="spiral_vase" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {spiral_vase_value}>{switch_on}</option>
						</select>
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{layer_perimeter_subtitle3}</h4>
					<p>{solid_layers}</p>
					<div class="ui-field-contain left-indent">
						<label for="top_solid_layers">{top_solid_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="top_solid_layers" id="top_solid_layers" value="{top_solid_layers_value}" min="0" max="20">
					</div>
					<div class="ui-field-contain left-indent">
						<label for="bottom_solid_layers">{bottom_solid_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="bottom_solid_layers" id="bottom_solid_layers" value="{bottom_solid_layers_value}" min="0" max="20">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{layer_perimeter_subtitle4}</h4>
					<div class="ui-field-contain">
						<label for="extra_perimeters">{extra_perimeters}</label>
						<select name="extra_perimeters" id="extra_perimeters" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {extra_perimeters_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="avoid_crossing_perimeters">{avoid_crossing_perimeters}</label>
						<select name="avoid_crossing_perimeters" id="avoid_crossing_perimeters" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {avoid_crossing_perimeters_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="thin_walls">{thin_walls}</label>
						<select name="thin_walls" id="thin_walls" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {thin_walls_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="overhangs">{overhangs}</label>
						<select name="overhangs" id="overhangs" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {overhangs_value}>{switch_on}</option>
						</select>
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{layer_perimeter_subtitle5}</h4>
					<div class="ui-field-contain">
						<label for="seam_position">{seam_position}</label>
						<select name="seam_position" id="seam_position">
							<option value="random" {seam_position_value1}>{seam_position1}</option>
							<option value="nearest" {seam_position_value2}>{seam_position2}</option>
							<option value="aligned" {seam_position_value3}>{seam_position3}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="external_perimeters_first">{external_perimeters_first}</label>
						<select name="external_perimeters_first" id="external_perimeters_first" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {external_perimeters_first_value}>{switch_on}</option>
						</select>
					</div>
				</div>
			</div> <!-- layers and perimeters -->
			<div data-role="collapsible">
				<h4>{infill_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{infill_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="fill_density">{fill_density}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="fill_density" id="fill_density" value="{fill_density_value}">
					</div>
					<div class="ui-field-contain">
						<label for="fill_pattern">{fill_pattern}</label>
						<select name="fill_pattern" id="fill_pattern">
							<option value="rectilinear" {fill_pattern_value1}>{fill_pattern1}</option>
							<option value="line" {fill_pattern_value2}>{fill_pattern2}</option>
							<option value="concentric" {fill_pattern_value3}>{fill_pattern3}</option>
							<option value="honeycomb" {fill_pattern_value4}>{fill_pattern4}</option>
							<option value="hilbertcurve" {fill_pattern_value5}>{fill_pattern5}</option>
							<option value="archimedeanchords" {fill_pattern_value6}>{fill_pattern6}</option>
							<option value="octagramspiral" {fill_pattern_value7}>{fill_pattern7}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="solid_fill_pattern">{solid_fill_pattern}</label>
						<select name="solid_fill_pattern" id="solid_fill_pattern">
							<option value="rectilinear" {solid_fill_pattern_value1}>{solid_fill_pattern1}</option>
							<option value="concentric" {solid_fill_pattern_value2}>{solid_fill_pattern2}</option>
							<option value="hilbertcurve" {solid_fill_pattern_value3}>{solid_fill_pattern3}</option>
							<option value="archimedeanchords" {solid_fill_pattern_value4}>{solid_fill_pattern4}</option>
							<option value="octagramspiral" {solid_fill_pattern_value5}>{solid_fill_pattern5}</option>
						</select>
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{infill_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="infill_every_layers">{infill_every_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="infill_every_layers" id="infill_every_layers" value="{infill_every_layers_value}" min="0" max="20">
					</div>
					<div class="ui-field-contain">
						<label for="infill_only_where_needed">{infill_only_where_needed}</label>
						<select name="infill_only_where_needed" id="infill_only_where_needed" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {infill_only_where_needed_value}>{switch_on}</option>
						</select>
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{infill_subtitle3}</h4>
					<div class="ui-field-contain">
						<label for="solid_infill_every_layers">{solid_infill_every_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="solid_infill_every_layers" id="solid_infill_every_layers" value="{solid_infill_every_layers_value}" min="0" max="100">
					</div>
					<div class="ui-field-contain">
						<label for="fill_angle">{fill_angle}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="fill_angle" id="fill_angle" value="{fill_angle_value}" min="0" max="90">
					</div>
					<div class="ui-field-contain">
						<label for="solid_infill_below_area">{solid_infill_below_area}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="solid_infill_below_area" id="solid_infill_below_area" value="{solid_infill_below_area_value}" min="0" max="100">
					</div>
					<div class="ui-field-contain">
						<label for="only_retract_when_crossing_perimeters">{only_retract_when_crossing_perimeters}</label>
						<select name="only_retract_when_crossing_perimeters" id="only_retract_when_crossing_perimeters" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {only_retract_when_crossing_perimeters_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="infill_first">{infill_first}</label>
						<select name="infill_first" id="infill_first" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {infill_first_value}>{switch_on}</option>
						</select>
					</div>
				</div>
			</div> <!-- infill -->
			<div data-role="collapsible" id="collapsible_speed">
				<h4>{speed_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{speed_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="perimeter_speed">{perimeter_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="perimeter_speed" id="perimeter_speed" value="{perimeter_speed_value}" min="10" max="200">
					</div>
					<a rel="small_perimeter_speed" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="small_perimeter_speed">{small_perimeter_speed}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="small_perimeter_speed" id="small_perimeter_speed" value="{small_perimeter_speed_value}">
					</div>
					<a rel="external_perimeter_speed" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="external_perimeter_speed">{external_perimeter_speed}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="external_perimeter_speed" id="external_perimeter_speed" value="{external_perimeter_speed_value}">
					</div>
					<div class="ui-field-contain">
						<label for="infill_speed">{infill_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="infill_speed" id="infill_speed" value="{infill_speed_value}" min="10" max="200">
					</div>
					<a rel="solid_infill_speed" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="solid_infill_speed">{solid_infill_speed}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="solid_infill_speed" id="solid_infill_speed" value="{solid_infill_speed_value}">
					</div>
					<a rel="top_solid_infill_speed" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="top_solid_infill_speed">{top_solid_infill_speed}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="top_solid_infill_speed" id="top_solid_infill_speed" value="{top_solid_infill_speed_value}">
					</div>
					<div class="ui-field-contain">
						<label for="support_material_speed">{support_material_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_speed" id="support_material_speed" value="{support_material_speed_value}" min="10" max="200">
					</div>
					<a rel="top_solid_infill_speed" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="support_material_interface_speed">{support_material_interface_speed}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="support_material_interface_speed" id="support_material_interface_speed" value="{support_material_interface_speed_value}" min="10" max="200">
					</div>
					<div class="ui-field-contain">
						<label for="bridge_speed">{bridge_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="bridge_speed" id="bridge_speed" value="{bridge_speed_value}" min="10" max="200">
					</div>
					<div class="ui-field-contain">
						<label for="gap_fill_speed">{gap_fill_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="gap_fill_speed" id="gap_fill_speed" value="{gap_fill_speed_value}" min="10" max="200">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{speed_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="travel_speed">{travel_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="travel_speed" id="travel_speed" value="{travel_speed_value}" min="10" max="300">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{speed_subtitle3}</h4>
					<a rel="top_solid_infill_speed" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="first_layer_speed">{first_layer_speed}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="first_layer_speed" id="first_layer_speed" value="{first_layer_speed_value}">
					</div>
				</div>
			</div> <!-- speed -->
			<div data-role="collapsible">
				<h4>{skirt_brim_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{skirt_brim_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="skirts">{skirts}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="skirts" id="skirts" value="{skirts_value}" min="0" max="10">
					</div>
					<div class="ui-field-contain">
						<label for="skirt_distance">{skirt_distance}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="skirt_distance" id="skirt_distance" value="{skirt_distance_value}" min="1" max="20">
					</div>
					<div class="ui-field-contain">
						<label for="skirt_height">{skirt_height}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="skirt_height" id="skirt_height" value="{skirt_height_value}" min="0" max="6000">
					</div>
					<div class="ui-field-contain">
						<label for="min_skirt_length">{min_skirt_length}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="min_skirt_length" id="min_skirt_length" value="{min_skirt_length_value}" min="0" max="100">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{skirt_brim_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="brim_width">{brim_width}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="brim_width" id="brim_width" value="{brim_width_value}" min="0" max="20">
					</div>
				</div>
			</div> <!-- skirt and brim -->
			<div data-role="collapsible">
				<h4>{support_material_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{support_material_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="support_material">{support_material}</label>
						<select name="support_material" id="support_material" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {support_material_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="support_material_threshold">{support_material_threshold}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_threshold" id="support_material_threshold" value="{support_material_threshold_value}" min="0" max="90">
					</div>
					<div class="ui-field-contain">
						<label for="support_material_enforce_layers">{support_material_enforce_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_enforce_layers" id="support_material_enforce_layers" value="{support_material_enforce_layers_value}" min="0" max="1000">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{support_material_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="raft_layers">{raft_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="raft_layers" id="raft_layers" value="{raft_layers_value}" min="0" max="10">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{support_material_subtitle3}</h4>
					<div class="ui-field-contain">
						<label for="support_material_pattern">{support_material_pattern}</label>
						<select name="support_material_pattern" id="support_material_pattern">
							<option value="rectilinear" {support_material_pattern_value1}>{support_material_pattern1}</option>
							<option value="rectilinear-grid" {support_material_pattern_value2}>{support_material_pattern2}</option>
							<option value="honeycomb" {support_material_pattern_value3}>{support_material_pattern3}</option>
							<option value="pillars" {support_material_pattern_value4}>{support_material_pattern4}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="support_material_spacing">{support_material_spacing}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_spacing" id="support_material_spacing" value="{support_material_spacing_value}" min="1" step="0.01">
					</div>
					<div class="ui-field-contain">
						<label for="support_material_angle">{support_material_angle}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_angle" id="support_material_angle" value="{support_material_angle_value}" min="0" max="90">
					</div>
					<div class="ui-field-contain">
						<label for="support_material_interface_layers">{support_material_interface_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_interface_layers" id="support_material_interface_layers" value="{support_material_interface_layers_value}" min="0" />
					</div>
					<div class="ui-field-contain">
						<label for="support_material_interface_spacing">{support_material_interface_spacing}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="support_material_interface_spacing" id="support_material_interface_spacing" value="{support_material_interface_spacing_value}" min="0" max="10" step="0.01">
					</div>
					<div class="ui-field-contain">
						<label for="dont_support_bridges">{dont_support_bridges}</label>
						<select name="dont_support_bridges" id="dont_support_bridges" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {dont_support_bridges_value}>{switch_on}</option>
						</select>
					</div>
				</div>
			</div> <!-- support material -->
			<div data-role="collapsible">
				<h4>{mutiple_extruder_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{mutiple_extruder_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="perimeter_extruder">{perimeter_extruder}</label>
						<select name="perimeter_extruder" id="perimeter_extruder">
							<option value="{extruder_left_val}" {perimeter_extruder_value_left}>{extruder_left}</option>
							<option value="{extruder_right_val}" {perimeter_extruder_value_right}>{extruder_right}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="infill_extruder">{infill_extruder}</label>
						<select name="infill_extruder" id="infill_extruder">
							<option value="{extruder_left_val}" {infill_extruder_value_left}>{extruder_left}</option>
							<option value="{extruder_right_val}" {infill_extruder_value_right}>{extruder_right}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="support_material_extruder">{support_material_extruder}</label>
						<select name="support_material_extruder" id="support_material_extruder">
							<option value="{extruder_left_val}" {support_material_extruder_value_left}>{extruder_left}</option>
							<option value="{extruder_right_val}" {support_material_extruder_value_right}>{extruder_right}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="support_material_interface_extruder">{support_material_interface_extruder}</label>
						<select name="support_material_interface_extruder" id="support_material_interface_extruder">
							<option value="{extruder_left_val}" {support_material_interface_extruder_value_left}>{extruder_left}</option>
							<option value="{extruder_right_val}" {support_material_interface_extruder_value_right}>{extruder_right}</option>
						</select>
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{mutiple_extruder_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="ooze_prevention">{ooze_prevention}</label>
						<select name="ooze_prevention" id="ooze_prevention" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {ooze_prevention_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="standby_temperature_delta">{standby_temperature_delta}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="standby_temperature_delta" id="standby_temperature_delta" value="{standby_temperature_delta_value}" min="-20" max="0">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{mutiple_extruder_subtitle3}</h4>
					<div class="ui-field-contain">
						<label for="interface_shells">{interface_shells}</label>
						<select name="interface_shells" id="interface_shells" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {interface_shells_value}>{switch_on}</option>
						</select>
					</div>
				</div>
			</div> <!-- multiple extruder -->
			<div data-role="collapsible">
				<h4>{fan_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{fan_subtitle1}</h4>
					<div class="ui-field-contain">
						<label for="fan_always_on">{fan_always_on}</label>
						<select name="fan_always_on" id="fan_always_on" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {fan_always_on_value}>{switch_on}</option>
						</select>
					</div>
					<div class="ui-field-contain">
						<label for="cooling">{cooling}</label>
						<select name="cooling" id="cooling" data-role="slider" data-track-theme="a" data-theme="a">
							<option value="0">{switch_off}</option>
							<option value="1" {cooling_value}>{switch_on}</option>
						</select>
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{fan_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="min_fan_speed">{min_fan_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="min_fan_speed" id="min_fan_speed" value="{min_fan_speed_value}" min="35" max="100">
					</div>
					<div class="ui-field-contain">
						<label for="bridge_fan_speed">{max_fan_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="max_fan_speed" id="max_fan_speed" value="{max_fan_speed_value}" min="40" max="100">
					</div>
					<div class="ui-field-contain">
						<label for="bridge_fan_speed">{bridge_fan_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="bridge_fan_speed" id="bridge_fan_speed" value="{bridge_fan_speed_value}">
					</div>
					<div class="ui-field-contain">
						<label for="disable_fan_first_layers">{disable_fan_first_layers}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="disable_fan_first_layers" id="disable_fan_first_layers" value="{disable_fan_first_layers_value}" />
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{fan_subtitle3}</h4>
					<div class="ui-field-contain">
						<label for="fan_below_layer_time">{fan_below_layer_time}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="fan_below_layer_time" id="fan_below_layer_time" value="{fan_below_layer_time_value}">
					</div>
					<div class="ui-field-contain">
						<label for="slowdown_below_layer_time">{slowdown_below_layer_time}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="slowdown_below_layer_time" id="slowdown_below_layer_time" value="{slowdown_below_layer_time_value}" />
					</div>
					<div class="ui-field-contain">
						<label for="min_print_speed">{min_print_speed}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="min_print_speed" id="min_print_speed" value="{min_print_speed_value}" />
					</div>
				</div>
			</div> <!-- Fan -->
			<div data-role="collapsible" id="collapsible_advanced">
				<h4>{advanced_title}</h4>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{advanced_subtitle1}</h4>
					<a rel="extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="extrusion_width">{extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="extrusion_width" id="extrusion_width" value="{extrusion_width_value}">
					</div>
					<a rel="first_layer_extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="first_layer_extrusion_width">{first_layer_extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="first_layer_extrusion_width" id="first_layer_extrusion_width" value="{first_layer_extrusion_width_value}">
					</div>
					<a rel="perimeter_extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="perimeter_extrusion_width">{perimeter_extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="perimeter_extrusion_width" id="perimeter_extrusion_width" value="{perimeter_extrusion_width_value}">
					</div>
					<a rel="infill_extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="infill_extrusion_width">{infill_extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="infill_extrusion_width" id="infill_extrusion_width" value="{infill_extrusion_width_value}">
					</div>
					<a rel="solid_infill_extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="solid_infill_extrusion_width">{solid_infill_extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="solid_infill_extrusion_width" id="solid_infill_extrusion_width" value="{solid_infill_extrusion_width_value}">
					</div>
					<a rel="top_infill_extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="top_infill_extrusion_width">{top_infill_extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="top_infill_extrusion_width" id="top_infill_extrusion_width" value="{top_infill_extrusion_width_value}">
					</div>
					<a rel="support_material_extrusion_width" data-role="none"></a>
					<div class="ui-field-contain">
						<label for="support_material_extrusion_width">{support_material_extrusion_width}</label>
						<input type="text" style="text-align:right;" data-clear-btn="false" name="support_material_extrusion_width" id="support_material_extrusion_width" value="{support_material_extrusion_width_value}">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{advanced_subtitle2}</h4>
					<div class="ui-field-contain">
						<label for="bridge_flow_ratio">{bridge_flow_ratio}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="bridge_flow_ratio" id="bridge_flow_ratio" value="{bridge_flow_ratio_value}" min="0.75" max="1.5" step="0.01">
					</div>
				</div>
				<div data-role="collapsible" data-collapsed="false" data-theme="d">
					<h4>{advanced_subtitle3}</h4>
					<div class="ui-field-contain">
						<label for="resolution">{resolution}</label>
						<input type="number" style="text-align:right;" data-clear-btn="false" name="resolution" id="resolution" value="{resolution_value}" min="0" max="1" step="0.01">
					</div>
				</div>
			</div> <!-- advanced -->
			<div id="submit_container" style="{hide_submit}"><input type="button" value="{submit_button}" onclick="javascript: submitPreset();"></div>
			</form>
			<div class="zim-error">{error}</div>
		</div>
		<div id="overwrite_popup" data-role="popup" data-dismissible="false" class="ui-content" style="max-width: 250px; text-align: center;">
			{save_overwrite}
			<br /><br />
			<div class="ui-grid-a">
				<div class="ui-block-a">
					<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" onclick="javascript: do_overwritePreset();">{button_save_ok}</a>
				</div>
				<div class="ui-block-b">
					<a href="#" class="ui-btn ui-corner-all ui-shadow ui-btn-inline" data-rel="back">{button_save_no}</a>
				</div>
			</div>
		</div>
	</div>
<script>
var var_ajax;
var var_submit_allow = false;

if ({disable_all} == true) {
	$("input").attr('disabled', 'disabled');
	$("select").attr('disabled', 'disabled');
}

function expand_allErrorCollapsibles() {
	$('div#collapsible_speed').collapsible('expand');
	$('div#collapsible_advanced').collapsible('expand');
}

function do_overwritePreset() {
	$("input#save_overwrite").val(1);
	$("form#form_preset_detail").submit();
	
	return;
}

function submitPreset() {
	if ($("input#save_as").data("oldvalue") == $("input#save_as").val()) {
		$("form#form_preset_detail").submit();
		
		return;
	}
	
	var_ajax = $.ajax({
		url: "/preset/check_exist_ajax",
		cache: false,
		type: "POST",
		data: { name: $("input#save_as").val() },
		beforeSend: function() {
			$("#overlay").addClass("gray-overlay");
			$(".ui-loader").css("display", "block");
		},
		complete: function() {
			$("#overlay").removeClass("gray-overlay");
			$(".ui-loader").css("display", "none");
		},
	})
	.done(function() {
		$("form#form_preset_detail").submit();
	})
	.fail(function() {
		if (var_ajax.status == 403) {
			$("div#overwrite_popup").popup("open");
		}
		else {
			console.log("unexpected error case: " + var_ajax.status);
		}
	});
}
</script>
</div>
