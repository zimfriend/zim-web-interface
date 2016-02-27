<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

//TODO move the define value into helper
if (!defined('CLASS_PRESET_EXTRUDER_RIGHT')) {
	define('CLASS_PRESET_EXTRUDER_RIGHT',	1);
	define('CLASS_PRESET_EXTRUDER_LEFT',	2);
}


class Preset extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'zimapi',
				'url',
				'json'
		) );
	}
	
	public function index() {
		$this->output->set_header('Location: /preset/listpreset');
		return;
	}
	
	public function listpreset() {
		$display_presetlist = array();
		$display_all_list = array();
		$template_data = array();
		
		$this->load->library('parser');
		$this->lang->load('preset/listpreset', $this->config->item('language'));
		
		// prepare display data
		$json_data = ZimAPI_getPresetListAsArray();
		foreach ($json_data as $preset) {
			$preset_basepath = NULL;
			$system_preset = FALSE;
			
			ZimAPI_checkPreset($preset[ZIMAPI_TITLE_PRESET_ID], $preset_basepath, $system_preset);
			
			$display_all_list[] = array(
					'name'	=> $preset[ZIMAPI_TITLE_PRESET_NAME],
					'id'	=> $preset[ZIMAPI_TITLE_PRESET_ID],
			);
			if (!$system_preset) {
				$display_presetlist[] = array(
						'name'	=> $preset[ZIMAPI_TITLE_PRESET_NAME],
						'id'	=> $preset[ZIMAPI_TITLE_PRESET_ID],
				);
			}
		}
		usort($display_presetlist, 'ZimAPI_usortComparePreset');
		usort($display_all_list, 'ZimAPI_usortComparePreset');
		
		// parse the main body
		$template_data = array(
				'home'				=> t('Home'),
				'back'				=> t('back'),
				'search_hint'		=> t('search_hint'),
// 				'baseurl_detail'	=> '/preset/detail',
				'model_lists'		=> $display_presetlist,
				'newmodel_lists'	=> $display_all_list,
				'new_preset_label'	=> t('new_preset_label'),
				'submit_button'		=> t('submit_button'),
				'delete_popup_text'	=> t('delete_popup_text'),
				'delete_yes'		=> t('delete_yes'),
				'delete_no'			=> t('delete_no'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('preset_list_title'),
				$this->parser->parse('preset/listpreset', $template_data, TRUE));
		
		return;
	}
	
	public function delete() {
		$id_preset = NULL;
		$ret_val = 0;
		
		$id_preset = $this->input->get('id');
		$ret_val = ZimAPI_deletePreset($id_preset);
		
		$this->output->set_header('Location: /preset/listpreset');
		
		return;
	}
	
	public function detail() {
		$id_preset = NULL;
		$ret_val = 0;
		$array_setting = NULL;
		$array_info = NULL;
		$template_data = array();
		$option_selected = 'selected="selected"';
		$display_hide = 'display: none;';
		$system_preset = FALSE;
		$error = NULL;
		
		// get preset id to display the correspond preset
		$id_preset = $this->input->get('id');
		$new_preset = ($this->input->get('new') !== FALSE) ? 'new' : NULL;
		
		$ret_val = ZimAPI_getPresetInfoAsArray($id_preset, $array_info, $system_preset);
		if ($ret_val != ERROR_OK) {
			// treat error as go back to preset list
			$this->output->set_header('Location: /preset/listpreset');
			return;
		}
		
		$ret_val = ZimAPI_getPresetSettingAsArray($id_preset, $array_setting);
		if ($ret_val != ERROR_OK) {
			// treat error as go back to preset list
			$this->output->set_header('Location: /preset/listpreset');
			return;
		}
		
		$this->lang->load('preset/detail', $this->config->item('language'));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			//TODO try to treat the infos here
			$this->load->library('form_validation');
			
			$this->form_validation->set_rules('layer_height', 'layer_height', 'required');
			$this->form_validation->set_rules('first_layer_height', 'first_layer_height', 'required');
			$this->form_validation->set_rules('perimeters', 'perimeters', 'required');
			$this->form_validation->set_rules('spiral_vase', 'spiral_vase', 'required');
			$this->form_validation->set_rules('top_solid_layers', 'top_solid_layers', 'required');
			$this->form_validation->set_rules('bottom_solid_layers', 'bottom_solid_layers', 'required');
			$this->form_validation->set_rules('extra_perimeters', 'extra_perimeters', 'required');
			$this->form_validation->set_rules('avoid_crossing_perimeters', 'avoid_crossing_perimeters', 'required');
			// old for 1.0
// 			$this->form_validation->set_rules('start_perimeters_at_concave_points', 'start_perimeters_at_concave_points', 'required');
// 			$this->form_validation->set_rules('start_perimeters_at_non_overhang', 'start_perimeters_at_non_overhang', 'required');
			// end of old for 1.0
			$this->form_validation->set_rules('thin_walls', 'thin_walls', 'required');
			$this->form_validation->set_rules('overhangs', 'overhangs', 'required');
// 			$this->form_validation->set_rules('randomize_start', 'randomize_start', 'required'); // old for 1.0
			$this->form_validation->set_rules('seam_position', 'seam_position', 'required'); // new for 1.1.7
			$this->form_validation->set_rules('external_perimeters_first', 'external_perimeters_first', 'required');
			$this->form_validation->set_rules('fill_density', 'fill_density', 'required');
			$this->form_validation->set_rules('fill_pattern', 'fill_pattern', 'required');
			$this->form_validation->set_rules('solid_fill_pattern', 'solid_fill_pattern', 'required');
			$this->form_validation->set_rules('infill_every_layers', 'infill_every_layers', 'required');
			$this->form_validation->set_rules('infill_only_where_needed', 'infill_only_where_needed', 'required');
			$this->form_validation->set_rules('solid_infill_every_layers', 'solid_infill_every_layers', 'required');
			$this->form_validation->set_rules('fill_angle', 'fill_angle', 'required');
			$this->form_validation->set_rules('solid_infill_below_area', 'solid_infill_below_area', 'required');
			$this->form_validation->set_rules('only_retract_when_crossing_perimeters', 'only_retract_when_crossing_perimeters', 'required');
			$this->form_validation->set_rules('infill_first', 'infill_first', 'required');
			$this->form_validation->set_rules('perimeter_speed', 'perimeter_speed', 'required');
			$this->form_validation->set_rules('small_perimeter_speed', 'small_perimeter_speed', 'required');
			$this->form_validation->set_rules('external_perimeter_speed', 'external_perimeter_speed', 'required');
			$this->form_validation->set_rules('infill_speed', 'infill_speed', 'required');
			$this->form_validation->set_rules('solid_infill_speed', 'solid_infill_speed', 'required');
			$this->form_validation->set_rules('top_solid_infill_speed', 'top_solid_infill_speed', 'required');
			$this->form_validation->set_rules('support_material_speed', 'support_material_speed', 'required');
			$this->form_validation->set_rules('bridge_speed', 'bridge_speed', 'required');
			$this->form_validation->set_rules('gap_fill_speed', 'gap_fill_speed', 'required');
			$this->form_validation->set_rules('support_material_interface_speed', 'support_material_interface_speed', 'required'); // new for 1.1.7
			$this->form_validation->set_rules('travel_speed', 'travel_speed', 'required');
			$this->form_validation->set_rules('first_layer_speed', 'first_layer_speed', 'required');
			$this->form_validation->set_rules('skirts', 'skirts', 'required');
			$this->form_validation->set_rules('skirt_distance', 'skirt_distance', 'required');
			$this->form_validation->set_rules('skirt_height', 'skirt_height', 'required');
			$this->form_validation->set_rules('min_skirt_length', 'min_skirt_length', 'required');
			$this->form_validation->set_rules('brim_width', 'brim_width', 'required');
			$this->form_validation->set_rules('support_material', 'support_material', 'required');
			$this->form_validation->set_rules('support_material_threshold', 'support_material_threshold', 'required');
			$this->form_validation->set_rules('support_material_enforce_layers', 'support_material_enforce_layers', 'required');
			$this->form_validation->set_rules('raft_layers', 'raft_layers', 'required');
			$this->form_validation->set_rules('support_material_pattern', 'support_material_pattern', 'required');
			$this->form_validation->set_rules('support_material_spacing', 'support_material_spacing', 'required');
			$this->form_validation->set_rules('support_material_angle', 'support_material_angle', 'required');
			$this->form_validation->set_rules('support_material_interface_layers', 'support_material_interface_layers', 'required');
			$this->form_validation->set_rules('support_material_interface_spacing', 'support_material_interface_spacing', 'required');
			$this->form_validation->set_rules('dont_support_bridges', 'dont_support_bridges', 'required'); // new for 1.1.7
			$this->form_validation->set_rules('perimeter_extruder', 'perimeter_extruder', 'required');
			$this->form_validation->set_rules('infill_extruder', 'infill_extruder', 'required');
			$this->form_validation->set_rules('support_material_extruder', 'support_material_extruder', 'required');
			$this->form_validation->set_rules('support_material_interface_extruder', 'support_material_interface_extruder', 'required');
			$this->form_validation->set_rules('ooze_prevention', 'ooze_prevention', 'required');
			$this->form_validation->set_rules('standby_temperature_delta', 'standby_temperature_delta', 'required');
			// new for 1.1.7
			$this->form_validation->set_rules('interface_shells', 'interface_shells', 'required');
			$this->form_validation->set_rules('fan_always_on', 'fan_always_on', 'required');
			$this->form_validation->set_rules('cooling', 'cooling', 'required');
			$this->form_validation->set_rules('min_fan_speed', 'min_fan_speed', 'required');
			$this->form_validation->set_rules('max_fan_speed', 'max_fan_speed', 'required');
			$this->form_validation->set_rules('bridge_fan_speed', 'bridge_fan_speed', 'required');
			$this->form_validation->set_rules('disable_fan_first_layers', 'disable_fan_first_layers', 'required');
			$this->form_validation->set_rules('fan_below_layer_time', 'fan_below_layer_time', 'required');
			$this->form_validation->set_rules('slowdown_below_layer_time', 'slowdown_below_layer_time', 'required');
			$this->form_validation->set_rules('min_print_speed', 'min_print_speed', 'required');
			// end of new for 1.1.7
			$this->form_validation->set_rules('extrusion_width', 'extrusion_width', 'required');
			$this->form_validation->set_rules('first_layer_extrusion_width', 'first_layer_extrusion_width', 'required');
			$this->form_validation->set_rules('perimeter_extrusion_width', 'perimeter_extrusion_width', 'required');
			$this->form_validation->set_rules('infill_extrusion_width', 'infill_extrusion_width', 'required');
			$this->form_validation->set_rules('solid_infill_extrusion_width', 'solid_infill_extrusion_width', 'required');
			$this->form_validation->set_rules('top_infill_extrusion_width', 'top_infill_extrusion_width', 'required');
			$this->form_validation->set_rules('support_material_extrusion_width', 'support_material_extrusion_width', 'required');
			$this->form_validation->set_rules('bridge_flow_ratio', 'bridge_flow_ratio', 'required');
			$this->form_validation->set_rules('resolution', 'resolution', 'required');
			
			if ($this->form_validation->run() == FALSE) {
				$error = t('miss_input');
			}
			else {
				$overwrite_confirm = $this->input->post('save_overwrite');
				$error_parameter = array();
				
				if ((int)$overwrite_confirm == 1) {
					$overwrite_confirm = TRUE;
				}
				else {
					$overwrite_confirm = FALSE;
				}
				
				//TODO try to modify or new config if true
				$array_input = array(
						'layer_height'							=> $this->input->post('layer_height'),
						'first_layer_height'					=> $this->input->post('first_layer_height'),
						'perimeters'							=> $this->input->post('perimeters'),
						'spiral_vase'							=> $this->input->post('spiral_vase'),
						'top_solid_layers'						=> $this->input->post('top_solid_layers'),
						'bottom_solid_layers'					=> $this->input->post('bottom_solid_layers'),
						'extra_perimeters'						=> $this->input->post('extra_perimeters'),
						'avoid_crossing_perimeters'				=> $this->input->post('avoid_crossing_perimeters'),
						// old for 1.0
// 						'start_perimeters_at_concave_points'	=> $this->input->post('start_perimeters_at_concave_points'),
// 						'start_perimeters_at_non_overhang'		=> $this->input->post('start_perimeters_at_non_overhang'),
						// end of old for 1.0
						'thin_walls'							=> $this->input->post('thin_walls'),
						'overhangs'								=> $this->input->post('overhangs'),
// 						'randomize_start'						=> $this->input->post('randomize_start'),
						'seam_position'							=> $this->input->post('seam_position'),
						'external_perimeters_first'				=> $this->input->post('external_perimeters_first'),
						'fill_density'							=> $this->input->post('fill_density'),
						'fill_pattern'							=> $this->input->post('fill_pattern'),
						'solid_fill_pattern'					=> $this->input->post('solid_fill_pattern'),
						'infill_every_layers'					=> $this->input->post('infill_every_layers'),
						'infill_only_where_needed'				=> $this->input->post('infill_only_where_needed'),
						'solid_infill_every_layers'				=> $this->input->post('solid_infill_every_layers'),
						'fill_angle'							=> $this->input->post('fill_angle'),
						'solid_infill_below_area'				=> $this->input->post('solid_infill_below_area'),
						'only_retract_when_crossing_perimeters'	=> $this->input->post('only_retract_when_crossing_perimeters'),
						'infill_first'							=> $this->input->post('infill_first'),
						'perimeter_speed'						=> $this->input->post('perimeter_speed'),
						'small_perimeter_speed'					=> $this->input->post('small_perimeter_speed'),
						'external_perimeter_speed'				=> $this->input->post('external_perimeter_speed'),
						'infill_speed'							=> $this->input->post('infill_speed'),
						'solid_infill_speed'					=> $this->input->post('solid_infill_speed'),
						'top_solid_infill_speed'				=> $this->input->post('top_solid_infill_speed'),
						'support_material_speed'				=> $this->input->post('support_material_speed'),
						'bridge_speed'							=> $this->input->post('bridge_speed'),
						'gap_fill_speed'						=> $this->input->post('gap_fill_speed'),
						'support_material_interface_speed'		=> $this->input->post('support_material_interface_speed'), // new for 1.1.7
						'travel_speed'							=> $this->input->post('travel_speed'),
						'first_layer_speed'						=> $this->input->post('first_layer_speed'),
						'skirts'								=> $this->input->post('skirts'),
						'skirt_distance'						=> $this->input->post('skirt_distance'),
						'skirt_height'							=> $this->input->post('skirt_height'),
						'min_skirt_length'						=> $this->input->post('min_skirt_length'),
						'brim_width'							=> $this->input->post('brim_width'),
						'support_material'						=> $this->input->post('support_material'),
						'support_material_threshold'			=> $this->input->post('support_material_threshold'),
						'support_material_enforce_layers'		=> $this->input->post('support_material_enforce_layers'),
						'raft_layers'							=> $this->input->post('raft_layers'),
						'support_material_pattern'				=> $this->input->post('support_material_pattern'),
						'support_material_spacing'				=> $this->input->post('support_material_spacing'),
						'support_material_angle'				=> $this->input->post('support_material_angle'),
						'support_material_interface_layers'		=> $this->input->post('support_material_interface_layers'),
						'support_material_interface_spacing'	=> $this->input->post('support_material_interface_spacing'),
						'dont_support_bridges'					=> $this->input->post('dont_support_bridges'), // new for 1.1.7
						'perimeter_extruder'					=> $this->input->post('perimeter_extruder'),
						'infill_extruder'						=> $this->input->post('infill_extruder'),
						'support_material_extruder'				=> $this->input->post('support_material_extruder'),
						'support_material_interface_extruder'	=> $this->input->post('support_material_interface_extruder'),
						'ooze_prevention'						=> $this->input->post('ooze_prevention'),
						'standby_temperature_delta'				=> $this->input->post('standby_temperature_delta'),
						// new for 1.1.7
						'interface_shells'						=> $this->input->post('interface_shells'),
						'fan_always_on'							=> $this->input->post('fan_always_on'),
						'cooling'								=> $this->input->post('cooling'),
						'min_fan_speed'							=> $this->input->post('min_fan_speed'),
						'max_fan_speed'							=> $this->input->post('max_fan_speed'),
						'bridge_fan_speed'						=> $this->input->post('bridge_fan_speed'),
						'disable_fan_first_layers'				=> $this->input->post('disable_fan_first_layers'),
						'fan_below_layer_time'					=> $this->input->post('fan_below_layer_time'),
						'slowdown_below_layer_time'				=> $this->input->post('slowdown_below_layer_time'),
						'min_print_speed'						=> $this->input->post('min_print_speed'),
						// end of new for 1.1.7
						'extrusion_width'						=> $this->input->post('extrusion_width'),
						'first_layer_extrusion_width'			=> $this->input->post('first_layer_extrusion_width'),
						'perimeter_extrusion_width'				=> $this->input->post('perimeter_extrusion_width'),
						'infill_extrusion_width'				=> $this->input->post('infill_extrusion_width'),
						'solid_infill_extrusion_width'			=> $this->input->post('solid_infill_extrusion_width'),
						'top_infill_extrusion_width'			=> $this->input->post('top_infill_extrusion_width'),
						'support_material_extrusion_width'		=> $this->input->post('support_material_extrusion_width'),
						'bridge_flow_ratio'						=> $this->input->post('bridge_flow_ratio'),
						'resolution'							=> $this->input->post('resolution'),
				);
				$name_preset = $this->input->post('save_as');
				
				if ($new_preset) {
					if ($this->input->post('save_as')) {
						$ret_val = ZimAPI_setPresetSetting($id_preset, $array_input, $error_parameter, $name_preset, $overwrite_confirm);
					}
					else {
						$ret_val = ERROR_MISS_PRM;
					}
				}
				else if ($array_info[ZIMAPI_TITLE_PRESET_NAME] != $name_preset) {
					$new_info = array();
					$new_system_preset = FALSE;
					
					$ret_val = ZimAPI_getPresetInfoAsArray(ZimAPI_codePresetHash($name_preset), $new_info, $new_system_preset);
					if ($new_system_preset) {
						$ret_val = ERROR_WRONG_PRM;
						$error = t('overwrite_system_preset');
					}
					else if ($ret_val == ERROR_WRONG_PRM || $ret_val == ERROR_OK) {
						$ret_val = ZimAPI_setPresetSetting($id_preset, $array_input, $error_parameter, $name_preset, $overwrite_confirm);
						
						if ($ret_val == ERROR_OK && $system_preset == FALSE) { // system_preset will never be true normally
							$ret_val = ZimAPI_deletePreset($id_preset);
						}
					}
				}
				else {
					$ret_val = ZimAPI_setPresetSetting($id_preset, $array_input, $error_parameter);
				}
				
				if ($ret_val == ERROR_FULL_PRTLST) {
					$error = t('same_user_preset');
				}
				
				if ($ret_val == ERROR_OK) {
					$this->output->set_header('Location: /preset/listpreset');
					return;
				}
				// display position and hint of wrong parameter
				else if ($ret_val == ERROR_WRONG_PRM && count($error_parameter) && !array_key_exists('input', $error_parameter)) {
					$error = t('wrong_parameter_prefix');
					foreach ($error_parameter as $title => $hint) {
						$error .= t('wrong_parameter_element', array($title, t('err_title_' . $title), $hint));
					}
					// assign correct user input setting into temporary form
					foreach ($array_input as $title => $value) {
						// for this moment, we also assign wrong input
// 						if (!array_key_exists($title, $error_parameter)) {
							$array_setting[$title] = $value;
// 						}
					}
				}
				else if (strlen($error) == 0) {
					$error = t('errorcode' . $ret_val); // test
				}
			}
		}
		
		$this->load->library('parser');
		
		// parse the main body
		$template_data = array(
				'back'			=> t('back'),
				'switch_off'	=> t('switch_off'),
				'switch_on'		=> t('switch_on'),
				'home'			=> t('Home'),
				// title
				'layer_height'							=> t('layer_height'),
				'first_layer_height'					=> t('first_layer_height'),
				'perimeters'							=> t('perimeters'),
				'spiral_vase'							=> t('spiral_vase'),
				'solid_layers'							=> t('solid_layers'),
				'top_solid_layers'						=> t('top_solid_layers'),
				'bottom_solid_layers'					=> t('bottom_solid_layers'),
				'extra_perimeters'						=> t('extra_perimeters'),
				'avoid_crossing_perimeters'				=> t('avoid_crossing_perimeters'),
				
				'thin_walls'							=> t('thin_walls'),
				'overhangs'								=> t('overhangs'),
// 				'randomize_start'						=> t('randomize_start'), // old for 1.0
				'seam_position'							=> t('seam_position'), // new for 1.1.7
				'external_perimeters_first'				=> t('external_perimeters_first'),
				'fill_density'							=> t('fill_density'),
				'fill_pattern'							=> t('fill_pattern'),
				'solid_fill_pattern'					=> t('solid_fill_pattern'),
				'infill_every_layers'					=> t('infill_every_layers'),
				'infill_only_where_needed'				=> t('infill_only_where_needed'),
				'solid_infill_every_layers'				=> t('solid_infill_every_layers'),
				'fill_angle'							=> t('fill_angle'),
				'solid_infill_below_area'				=> t('solid_infill_below_area'),
				'only_retract_when_crossing_perimeters'	=> t('only_retract_when_crossing_perimeters'),
				'infill_first'							=> t('infill_first'),
				'perimeter_speed'						=> t('perimeter_speed'),
				'small_perimeter_speed'					=> t('small_perimeter_speed'),
				'external_perimeter_speed'				=> t('external_perimeter_speed'),
				'infill_speed'							=> t('infill_speed'),
				'solid_infill_speed'					=> t('solid_infill_speed'),
				'top_solid_infill_speed'				=> t('top_solid_infill_speed'),
				'support_material_speed'				=> t('support_material_speed'),
				'bridge_speed'							=> t('bridge_speed'),
				'gap_fill_speed'						=> t('gap_fill_speed'),
				'support_material_interface_speed'		=> t('support_material_interface_speed'), // new for 1.1.7
				'travel_speed'							=> t('travel_speed'),
				'first_layer_speed'						=> t('first_layer_speed'),
				'skirts'								=> t('skirts'),
				'skirt_distance'						=> t('skirt_distance'),
				'skirt_height'							=> t('skirt_height'),
				'min_skirt_length'						=> t('min_skirt_length'),
				'brim_width'							=> t('brim_width'),
				'support_material'						=> t('support_material'),
				'support_material_threshold'			=> t('support_material_threshold'),
				'support_material_enforce_layers'		=> t('support_material_enforce_layers'),
				'raft_layers'							=> t('raft_layers'),
				'support_material_pattern'				=> t('support_material_pattern'),
				'support_material_spacing'				=> t('support_material_spacing'),
				'support_material_angle'				=> t('support_material_angle'),
				'support_material_interface_layers'		=> t('support_material_interface_layers'),
				'support_material_interface_spacing'	=> t('support_material_interface_spacing'),
				'dont_support_bridges'					=> t('dont_support_bridges'), // new for 1.1.7
				'perimeter_extruder'					=> t('perimeter_extruder'),
				'infill_extruder'						=> t('infill_extruder'),
				'support_material_extruder'				=> t('support_material_extruder'),
				'support_material_interface_extruder'	=> t('support_material_interface_extruder'),
				'ooze_prevention'						=> t('ooze_prevention'),
				'standby_temperature_delta'				=> t('standby_temperature_delta'),
				// new for 1.1.7
				'interface_shells'						=> t('interface_shells'),
				'fan_always_on'							=> t('fan_always_on'),
				'cooling'								=> t('cooling'),
				'min_fan_speed'							=> t('min_fan_speed'),
				'max_fan_speed'							=> t('max_fan_speed'),
				'bridge_fan_speed'						=> t('bridge_fan_speed'),
				'disable_fan_first_layers'				=> t('disable_fan_first_layers'),
				'fan_below_layer_time'					=> t('fan_below_layer_time'),
				'slowdown_below_layer_time'				=> t('slowdown_below_layer_time'),
				'min_print_speed'						=> t('min_print_speed'),
				// end of new for 1.1.7
				'extrusion_width'						=> t('extrusion_width'),
				'first_layer_extrusion_width'			=> t('first_layer_extrusion_width'),
				'perimeter_extrusion_width'				=> t('perimeter_extrusion_width'),
				'infill_extrusion_width'				=> t('infill_extrusion_width'),
				'solid_infill_extrusion_width'			=> t('solid_infill_extrusion_width'),
				'top_infill_extrusion_width'			=> t('top_infill_extrusion_width'),
				'support_material_extrusion_width'		=> t('support_material_extrusion_width'),
				'bridge_flow_ratio'						=> t('bridge_flow_ratio'),
				'resolution'							=> t('resolution'),
				// interface title
				'layer_perimeter_title'			=> t('layer_perimeter_title'),
				'layer_perimeter_subtitle1'		=> t('layer_perimeter_subtitle1'),
				'layer_perimeter_subtitle2'		=> t('layer_perimeter_subtitle2'),
				'layer_perimeter_subtitle3'		=> t('layer_perimeter_subtitle3'),
				'layer_perimeter_subtitle4'		=> t('layer_perimeter_subtitle4'),
				'layer_perimeter_subtitle5'		=> t('layer_perimeter_subtitle5'),
				'layer_perimeter_subtitle3.1'	=> t('layer_perimeter_subtitle3.1'),
				'infill_title'					=> t('infill_title'),
				'infill_subtitle1'				=> t('infill_subtitle1'),
				'infill_subtitle2'				=> t('infill_subtitle2'),
				'infill_subtitle3'				=> t('infill_subtitle3'),
				'speed_title'					=> t('speed_title'),
				'speed_subtitle1'				=> t('speed_subtitle1'),
				'speed_subtitle2'				=> t('speed_subtitle2'),
				'speed_subtitle3'				=> t('speed_subtitle3'),
				'skirt_brim_title'				=> t('skirt_brim_title'),
				'skirt_brim_subtitle1'			=> t('skirt_brim_subtitle1'),
				'skirt_brim_subtitle2'			=> t('skirt_brim_subtitle2'),
				'support_material_title'		=> t('support_material_title'),
				'support_material_subtitle1'	=> t('support_material_subtitle1'),
				'support_material_subtitle2'	=> t('support_material_subtitle2'),
				'support_material_subtitle3'	=> t('support_material_subtitle3'),
				'mutiple_extruder_title'		=> t('mutiple_extruder_title'),
				'mutiple_extruder_subtitle1'	=> t('mutiple_extruder_subtitle1'),
				'mutiple_extruder_subtitle2'	=> t('mutiple_extruder_subtitle2'),
				'mutiple_extruder_subtitle3'	=> t('mutiple_extruder_subtitle3'),
				'fan_title'						=> t('fan_title'),
				'fan_subtitle1'					=> t('fan_subtitle1'),
				'fan_subtitle2'					=> t('fan_subtitle2'),
				'fan_subtitle3'					=> t('fan_subtitle3'),
				'fan_subtitle2.1'				=> t('fan_subtitle2.1'),
				'advanced_title'				=> t('advanced_title'),
				'advanced_subtitle1'			=> t('advanced_subtitle1'),
				'advanced_subtitle2'			=> t('advanced_subtitle2'),
				'advanced_subtitle3'			=> t('advanced_subtitle3'),
				'save_as_title'					=> t('save_as_title'),
				'fill_pattern1'					=> t('fill_pattern1'),
				'fill_pattern2'					=> t('fill_pattern2'),
				'fill_pattern3'					=> t('fill_pattern3'),
				'fill_pattern4'					=> t('fill_pattern4'),
				'fill_pattern5'					=> t('fill_pattern5'),
				'fill_pattern6'					=> t('fill_pattern6'),
				'fill_pattern7'					=> t('fill_pattern7'),
				'solid_fill_pattern1'			=> t('solid_fill_pattern1'),
				'solid_fill_pattern2'			=> t('solid_fill_pattern2'),
				'solid_fill_pattern3'			=> t('solid_fill_pattern3'),
				'solid_fill_pattern4'			=> t('solid_fill_pattern4'),
				'solid_fill_pattern5'			=> t('solid_fill_pattern5'),
				'support_material_pattern1'		=> t('support_material_pattern1'),
				'support_material_pattern2'		=> t('support_material_pattern2'),
				'support_material_pattern3'		=> t('support_material_pattern3'),
				'support_material_pattern4'		=> t('support_material_pattern4'),
				'seam_position1'				=> t('seam_position1'),
				'seam_position2'				=> t('seam_position2'),
				'seam_position3'				=> t('seam_position3'),
				// value
				'layer_height_value'							=> $array_setting['layer_height'],
				'first_layer_height_value'						=> $array_setting['first_layer_height'],
				'perimeters_value'								=> $array_setting['perimeters'],
				'spiral_vase_value'								=> ($array_setting['spiral_vase'] == TRUE) ? $option_selected : NULL,
				'top_solid_layers_value'						=> $array_setting['top_solid_layers'],
				'bottom_solid_layers_value'						=> $array_setting['bottom_solid_layers'],
				'extra_perimeters_value'						=> ($array_setting['extra_perimeters'] == TRUE) ? $option_selected : NULL,
				'avoid_crossing_perimeters_value'				=> ($array_setting['avoid_crossing_perimeters'] == TRUE) ? $option_selected : NULL,
				// old for 1.0
// 				'start_perimeters_at_concave_points_value'		=> ($array_setting['start_perimeters_at_concave_points'] == TRUE) ? $option_selected : NULL,
// 				'start_perimeters_at_non_overhang_value'		=> ($array_setting['start_perimeters_at_non_overhang'] == TRUE) ? $option_selected : NULL,
				// end of old for 1.0
				'thin_walls_value'								=> ($array_setting['thin_walls'] == TRUE) ? $option_selected : NULL,
				'overhangs_value'								=> ($array_setting['overhangs'] == TRUE) ? $option_selected : NULL,
// 				'randomize_start_value'							=> ($array_setting['randomize_start'] == TRUE) ? $option_selected : NULL,
				'seam_position_value1'							=> NULL,
				'seam_position_value2'							=> NULL,
				'seam_position_value3'							=> NULL,
				'external_perimeters_first_value'				=> ($array_setting['external_perimeters_first'] == TRUE) ? $option_selected : NULL,
				'fill_density_value'							=> $array_setting['fill_density'],
// 				'fill_pattern_value'							=> $array_setting['fill_pattern'],
				'fill_pattern_value1'							=> NULL,
				'fill_pattern_value2'							=> NULL,
				'fill_pattern_value3'							=> NULL,
				'fill_pattern_value4'							=> NULL,
				'fill_pattern_value5'							=> NULL,
				'fill_pattern_value6'							=> NULL,
				'fill_pattern_value7'							=> NULL,
// 				'solid_fill_pattern_value'						=> $array_setting['solid_fill_pattern'],
				'solid_fill_pattern_value1'						=> NULL,
				'solid_fill_pattern_value2'						=> NULL,
				'solid_fill_pattern_value3'						=> NULL,
				'solid_fill_pattern_value4'						=> NULL,
				'solid_fill_pattern_value5'						=> NULL,
				'infill_every_layers_value'						=> $array_setting['infill_every_layers'],
				'infill_only_where_needed_value'				=> ($array_setting['infill_only_where_needed'] == TRUE) ? $option_selected : NULL,
				'solid_infill_every_layers_value'				=> $array_setting['solid_infill_every_layers'],
				'fill_angle_value'								=> $array_setting['fill_angle'],
				'solid_infill_below_area_value'					=> $array_setting['solid_infill_below_area'],
				'only_retract_when_crossing_perimeters_value'	=> ($array_setting['only_retract_when_crossing_perimeters'] == TRUE) ? $option_selected : NULL,
				'infill_first_value'							=> ($array_setting['infill_first'] == TRUE) ? $option_selected : NULL,
				'perimeter_speed_value'							=> $array_setting['perimeter_speed'],
				'small_perimeter_speed_value'					=> $array_setting['small_perimeter_speed'],
				'external_perimeter_speed_value'				=> $array_setting['external_perimeter_speed'],
				'infill_speed_value'							=> $array_setting['infill_speed'],
				'solid_infill_speed_value'						=> $array_setting['solid_infill_speed'],
				'top_solid_infill_speed_value'					=> $array_setting['top_solid_infill_speed'],
				'support_material_speed_value'					=> $array_setting['support_material_speed'],
				'bridge_speed_value'							=> $array_setting['bridge_speed'],
				'gap_fill_speed_value'							=> $array_setting['gap_fill_speed'],
				'support_material_interface_speed_value'		=> $array_setting['support_material_interface_speed'], // new for 1.1.7
				'travel_speed_value'							=> $array_setting['travel_speed'],
				'first_layer_speed_value'						=> $array_setting['first_layer_speed'],
				'skirts_value'									=> $array_setting['skirts'],
				'skirt_distance_value'							=> $array_setting['skirt_distance'],
				'skirt_height_value'							=> $array_setting['skirt_height'],
				'min_skirt_length_value'						=> $array_setting['min_skirt_length'],
				'brim_width_value'								=> $array_setting['brim_width'],
				'support_material_value'						=> ($array_setting['support_material'] == TRUE) ? $option_selected : NULL,
				'support_material_threshold_value'				=> $array_setting['support_material_threshold'],
				'support_material_enforce_layers_value'			=> $array_setting['support_material_enforce_layers'],
				'raft_layers_value'								=> $array_setting['raft_layers'],
// 				'support_material_pattern_value'				=> $array_setting['support_material_pattern'],
				'support_material_pattern_value1'				=> NULL,
				'support_material_pattern_value2'				=> NULL,
				'support_material_pattern_value3'				=> NULL,
				'support_material_pattern_value4'				=> NULL,
				'support_material_spacing_value'				=> $array_setting['support_material_spacing'],
				'support_material_angle_value'					=> $array_setting['support_material_angle'],
				'support_material_interface_layers_value'		=> $array_setting['support_material_interface_layers'],
				'support_material_interface_spacing_value'		=> $array_setting['support_material_interface_spacing'],
				'dont_support_bridges_value'					=> ($array_setting['dont_support_bridges'] == TRUE) ? $option_selected : NULL, // new for 1.1.7
// 				'perimeter_extruder_value'						=> $array_setting['perimeter_extruder'],
				'perimeter_extruder_value_left'					=> NULL,
				'perimeter_extruder_value_right'				=> NULL,
// 				'infill_extruder_value'							=> $array_setting['infill_extruder'],
				'infill_extruder_value_left'					=> NULL,
				'infill_extruder_value_right'					=> NULL,
// 				'support_material_extruder_value'				=> $array_setting['support_material_extruder'],
				'support_material_extruder_value_left'			=> NULL,
				'support_material_extruder_value_right'			=> NULL,
// 				'support_material_interface_extruder_value'		=> $array_setting['support_material_interface_extruder'],
				'support_material_interface_extruder_value_left'	=> NULL,
				'support_material_interface_extruder_value_right'	=> NULL,
				'ooze_prevention_value'							=> ($array_setting['ooze_prevention'] == TRUE) ? $option_selected : NULL,
				'standby_temperature_delta_value'				=> $array_setting['standby_temperature_delta'],
				// new for 1.1.7
				'interface_shells_value'						=> ($array_setting['interface_shells'] == TRUE) ? $option_selected : NULL,
				'fan_always_on_value'							=> ($array_setting['fan_always_on'] == TRUE) ? $option_selected : NULL,
				'cooling_value'									=> ($array_setting['cooling'] == TRUE) ? $option_selected : NULL,
				'min_fan_speed_value'							=> $array_setting['min_fan_speed'],
				'max_fan_speed_value'							=> $array_setting['max_fan_speed'],
				'bridge_fan_speed_value'						=> $array_setting['bridge_fan_speed'],
				'disable_fan_first_layers_value'				=> $array_setting['disable_fan_first_layers'],
				'fan_below_layer_time_value'					=> $array_setting['fan_below_layer_time'],
				'slowdown_below_layer_time_value'				=> $array_setting['slowdown_below_layer_time'],
				'min_print_speed_value'							=> $array_setting['min_print_speed'],
				// end of new for 1.1.7
				'extrusion_width_value'							=> $array_setting['extrusion_width'],
				'first_layer_extrusion_width_value'				=> $array_setting['first_layer_extrusion_width'],
				'perimeter_extrusion_width_value'				=> $array_setting['perimeter_extrusion_width'],
				'infill_extrusion_width_value'					=> $array_setting['infill_extrusion_width'],
				'solid_infill_extrusion_width_value'			=> $array_setting['solid_infill_extrusion_width'],
				'top_infill_extrusion_width_value'				=> $array_setting['top_infill_extrusion_width'],
				'support_material_extrusion_width_value'		=> $array_setting['support_material_extrusion_width'],
				'bridge_flow_ratio_value'						=> $array_setting['bridge_flow_ratio'],
				'resolution_value'								=> $array_setting['resolution'],
				// interface value
				'preset_title'		=> $new_preset ? t('new_preset_title') : $array_info[ZIMAPI_TITLE_PRESET_NAME], //'Name',
				'submit_button'		=> t('submit_button'),
				'preset_id'			=> $id_preset,
				'preset_newurl'		=> $new_preset ? '&new' : NULL,
				'hide_save_as'		=> $new_preset ? NULL : $display_hide,
				'hide_submit'		=> ($system_preset && $new_preset == NULL) ? $display_hide : NULL,
				'hide_delete'		=> ($system_preset || $new_preset) ? $display_hide : NULL,
				'error'				=> $error,
				'disable_all'		=> ($system_preset && $new_preset == NULL) ? 'true' : 'false',
				'save_overwrite'	=> t('save_overwrite'),
				'button_save_ok'	=> t('button_save_ok'),
				'button_save_no'	=> t('button_save_no'),
				'save_as_value'		=> $new_preset ? NULL : $array_info['name'],
				// hint for select option of extruder
				'extruder_left'			=> t('extruder_left'),
				'extruder_right'		=> t('extruder_right'),
				'extruder_left_val'		=> CLASS_PRESET_EXTRUDER_LEFT,
				'extruder_right_val'	=> CLASS_PRESET_EXTRUDER_RIGHT,
		);
		
		switch ($array_setting['perimeter_extruder']) {
			case CLASS_PRESET_EXTRUDER_LEFT:
				$template_data['perimeter_extruder_value_left'] = $option_selected;
				break;
				
			case CLASS_PRESET_EXTRUDER_RIGHT:
				$template_data['perimeter_extruder_value_right'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch ($array_setting['infill_extruder']) {
			case CLASS_PRESET_EXTRUDER_LEFT:
				$template_data['infill_extruder_value_left'] = $option_selected;
				break;
				
			case CLASS_PRESET_EXTRUDER_RIGHT:
				$template_data['infill_extruder_value_right'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch ($array_setting['support_material_extruder']) {
			case CLASS_PRESET_EXTRUDER_LEFT:
				$template_data['support_material_extruder_value_left'] = $option_selected;
				break;
				
			case CLASS_PRESET_EXTRUDER_RIGHT:
				$template_data['support_material_extruder_value_right'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch ($array_setting['support_material_interface_extruder']) {
			case CLASS_PRESET_EXTRUDER_LEFT:
				$template_data['support_material_interface_extruder_value_left'] = $option_selected;
				break;
				
			case CLASS_PRESET_EXTRUDER_RIGHT:
				$template_data['support_material_interface_extruder_value_right'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch ($array_setting['fill_pattern']) {
			case 'rectilinear':
				$template_data['fill_pattern_value1'] = $option_selected;
				break;
				
			case 'line':
				$template_data['fill_pattern_value2'] = $option_selected;
				break;
				
			case 'concentric':
				$template_data['fill_pattern_value3'] = $option_selected;
				break;
				
			case 'honeycomb':
				$template_data['fill_pattern_value4'] = $option_selected;
				break;
				
			case 'hilbertcurve':
				$template_data['fill_pattern_value5'] = $option_selected;
				break;
				
			case 'archimedeanchords':
				$template_data['fill_pattern_value6'] = $option_selected;
				break;
				
			case 'octagramspiral':
				$template_data['fill_pattern_value7'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch ($array_setting['solid_fill_pattern']) {
			case 'rectilinear':
				$template_data['solid_fill_pattern_value1'] = $option_selected;
				break;
				
			case 'concentric':
				$template_data['solid_fill_pattern_value2'] = $option_selected;
				break;
				
			case 'hilbertcurve':
				$template_data['solid_fill_pattern_value3'] = $option_selected;
				break;
				
			case 'archimedeanchords':
				$template_data['solid_fill_pattern_value4'] = $option_selected;
				break;
				
			case 'octagramspiral':
				$template_data['solid_fill_pattern_value5'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch ($array_setting['support_material_pattern']) {
			case 'rectilinear':
				$template_data['support_material_pattern_value1'] = $option_selected;
				break;
				
			case 'rectilinear-grid':
				$template_data['support_material_pattern_value2'] = $option_selected;
				break;
				
			case 'honeycomb':
				$template_data['support_material_pattern_value3'] = $option_selected;
				break;
				
			case 'pillars':
				$template_data['support_material_pattern_value4'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		switch($array_setting['seam_position']) {
			case 'random':
				$template_data['seam_position_value1'] = $option_selected;
				break;
				
			case 'nearest':
				$template_data['seam_position_value2'] = $option_selected;
				break;
				
			case 'aligned':
				$template_data['seam_position_value3'] = $option_selected;
				break;
				
			default:
				break;
		}
		
		// parse all page
		$this->_parseBaseTemplate(t('preset_detail_pagetitle'),
				$this->parser->parse('preset/detail', $template_data, TRUE));
		
		return;
	}
	
	public function check_exist_ajax() {
		$name_preset = $this->input->post('name');
		$ret_val = ZimAPI_checkPreset(ZimAPI_codePresetHash($name_preset));
		
		$this->output->set_status_header(($ret_val ? 403 : 200));
		
		return;
	}
}