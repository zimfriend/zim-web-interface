<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Printerstoring extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'printerstoring',
				'url',
				'json'
		) );
	}

	public function storestl() {
		$template_data = array();
		$error = NULL;
		$response = 0;
		$button_goto_slice = NULL;
		$f1 = NULL;
		$f2 = NULL;
		$name = NULL;
		
		$this->load->library('parser');
		$this->lang->load('printerstoring/storestl', $this->config->item('language'));
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$array_model = array();
			$upload_config = array (
					'upload_path'	=> $this->config->item('temp'),
					'allowed_types'	=> '*',
					'overwrite'		=> FALSE,
					'remove_spaces'	=> TRUE,
					'encrypt_name'	=> TRUE,
			);
			$this->load->library('upload', $upload_config);
			
			if ($this->upload->do_upload('file') && ($name = $this->input->post('name')))
			{
				$f1 = $this->upload->data();
			}
			else if ($this->upload->do_upload('file_c1') && ($name = $this->input->post('name'))) {
				$first_combine = TRUE;
				$f1 = $this->upload->data();
				
				foreach (array('file_c2') as $file_key) {
					if ($this->upload->do_upload($file_key)) {
						$first_combine = FALSE;
						$f2 = $this->upload->data();
					}
					else if ($first_combine == TRUE) {
						$error = t('fail_message');
						break;
					}
				}
			}
			else {
				// treat error - missing gcode file
				$error = t('fail_message');
				//TODO get error detail by $this->upload->display_errors()
			}

			if ($f1 && ($name = $this->input->post('name'))) {
				$this->load->helper('printerstoring');
				$cr = PrinterStoring_storeStl($name, $f1, $f2);
				if ($cr === ERROR_OK) {
					header('Location: ' . '/printerstoring/liststl?uploaded=uploaded');
				}
				if ($cr === ERROR_DISK_FULL) {
					$error = t('fail_disk_full');
				}
				else {
					$error = t('fail_message');
				}
			}
		}
		// parse the main body
		$template_data = array(
				'back'			=> t('back'),
				'home'			=> t('home'),
				'select_hint'	=> t('select_hint'),
				'select_hint_multi'	=> t('select_hint_multi'),
				'header_single' => t('header_single'),
				'header_multi'	=> t('header_multi'),
				'upload_button'	=> t('upload_button'),
				'error'			=> $error,
				'name'		=> t('name')
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstoring_storestl_pagetitle'),
				$this->parser->parse('printerstoring/storestl', $template_data, TRUE));
		
		return;
	}

	public function libraries() {
		$template_data = array();

		$this->load->library('parser');

		$this->lang->load('printerstoring/libraries', $this->config->item('language'));

		$template_data = array(
				'back'			=> t('back'),
				'home'			=> t('home'),
				'libraries_info'		=> t('libraries_info'),
				'stl_models'		=> t('stl_models'),
				'gcode_models'		=> t('gcode_models'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstoring_libraries_pagetitle'),
				$this->parser->parse('printerstoring/libraries', $template_data, TRUE));
		
		return;
	}

	public function stllibrary() {
		$template_data = array();

		$this->load->library('parser');

		$this->lang->load('printerstoring/stllibrary', $this->config->item('language'));

		$template_data = array(
				'back'				=> t('back'),
				'home'				=> t('home'),
				'browse_models'		=> t('browse_models'),
				'add_model'			=> t('add_model')
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstoring_stllibrary_pagetitle'),
				$this->parser->parse('printerstoring/stllibrary', $template_data, TRUE));
		
		return;
	}

	private function array_sort($array, $on, $order=SORT_ASC)
	{
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array, SORT_NATURAL | SORT_FLAG_CASE);
            break;
            case SORT_DESC:
                arsort($sortable_array, SORT_NATURAL | SORT_FLAG_CASE);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
}

	public function liststl() {
		$template_data = array();

		$this->load->library('parser');

		$this->lang->load('printerstoring/liststl', $this->config->item('language'));
		$this->load->helper('printerstoring');

		$uploaded = null;
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
		{
			$uploaded = $this->input->get('uploaded');
		}

		$json_data = json_decode(PrinterStoring_listStl(), true);
		
		// prepare display data
		foreach ($json_data as $model_data) {
			
			$display_printlist[] = array(
					'name'	=> $model_data['name'],
					'id'	=> $model_data['id'],
					'creation_date' => $model_data[PRINTERSTORING_TITLE_CREATE_DATE],
			);
		}
		if (isset($display_printlist)) {
			$display_printlist = $this->array_sort($display_printlist, 'name');
		}

		$template_data = array(
				'back'			=> t('back'),
				'home'			=> t('home'),
				'list_info'		=> t('list_info'),
// 				'print-model'		=> t('print-model'),
				'delete-model'		=> t('delete-model'),
				'list'		=> (isset($display_printlist) ? $display_printlist : array()),
				'uploaded'		=> ($uploaded ? t('uploaded') : NULL),
				'print_error'		=> t('print_error'),
				'delete_error'	=> t('delete_error'),
				'delete_popup_text'	=> t('delete_popup_text'),
				'delete_yes'		=> t('delete_yes'),
				'delete_no'			=> t('delete_no'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstoring_liststl_pagetitle'),
				$this->parser->parse('printerstoring/liststl', $template_data, TRUE));
		
		return;
	}

	public function getpicture() {
		$id = NULL;
		$type = NULL;
		//$url_pid = '';
		$cr = 0; //return code
		
		$this->load->helper(array('printlist', 'file'));
		$this->load->helper('printerstoring');
		$id = intval($this->input->get('id')); //return false if missing
		$type = $this->input->get('type'); //return false if missing
		
		if ($id && $type) {
			global $CFG;
//			$CI = &get_instance();
			if ($type === "stl") {
				$image_file = $CFG->config['stl_library'] . sprintf('%06d', $id) . '/' . PRINTERSTORING_FILE_IMG_PNG;				
			}
			else if ($type === "gcode") {
				$image_file = $CFG->config['gcode_library'] . sprintf('%06d', $id) . '/' . PRINTERSTORING_FILE_IMG_JPG;
			}
			else {
				$this->_return_cr(ERROR_WRONG_PRM);
				return;
			}
			// get img link from info file
			try {
				if (file_exists($image_file) === false) {
					$this->load->helper('printerlog');
					PrinterLog_logError('model id not found', __FILE__, __LINE__);
					$this->_return_cr(ERROR_UNKNOWN_MODEL);
					return ;
				}
				$this->output->set_content_type(get_mime_by_extension($image_file))->set_output(@file_get_contents($image_file));
				return ;
			}
			catch (Exception $e) {
				$CI->load->helper('printerlog');
				PrinterLog_logError('could not get the picture', __FILE__, __LINE__);
				$this->_return_cr(ERROR_UNKNOWN_MODEL);
				return ;
			}
		} else {
			$cr = ERROR_MISS_PRM;
		}
		
		$this->_return_cr($cr);
		
		return;
	}

	public function listgcode() {
		$template_data = array();

		$this->load->library('parser');

		$this->lang->load('printerstoring/listgcode', $this->config->item('language'));
		$this->load->helper('printerstoring');

		$json_data = json_decode(PrinterStoring_listGcode(), true);
		// prepare display data
		foreach ($json_data as $model_data) {
			$preset_name = t('preset_not_found');
			$preset_array = array();
			
			$this->load->helper('zimapi');
			if (ERROR_OK == ZimAPI_getPresetInfoAsArray($model_data[PRINTERSTORING_TITLE_PRESET_ID], $preset_array)) {
				$preset_name = $preset_array[ZIMAPI_TITLE_PRESET_NAME];
			}
			
			$display_printlist[] = array(
					'modelname'			=> $model_data['name'],
					'mid'				=> $model_data['id'],
					'presetname'		=> $preset_name,
					'creation_date'		=> $model_data[PRINTERSTORING_TITLE_CREATE_TIME],
					'creation_datestr'	=> date('Y-m-d H:i:s', $model_data[PRINTERSTORING_TITLE_CREATE_TIME]),
			);
		}

// 		if (isset($display_printlist)) {
// 			$display_printlist = $this->array_sort($display_printlist, 'modelname');
// 		}

		$template_data = array(
				'back'					=> t('back'),
				'home'					=> t('home'),
				'list_info'				=> t('list_info'),
				'print-model'			=> t('print-model'),
				'delete-model'			=> t('delete-model'),
				//'list'		=> $display_printlist,
				'encoded_list'			=> json_encode((isset($display_printlist) ? $display_printlist : array())),
				'select_alphabetical'	=> t('select_alphabetical'),
				'select_mostrecent'		=> t('select_mostrecent'),
				'print_error'			=> t('print_error'),
				'delete_error'			=> t('delete_error'),
				'delete_popup_text'		=> t('delete_popup_text'),
				'delete_yes'			=> t('delete_yes'),
				'delete_no'				=> t('delete_no'),
				'preset_name_title'		=> t('preset_name_title'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstoring_listgcode_pagetitle'),
				$this->parser->parse('printerstoring/listgcode', $template_data, TRUE));
		
		return;
	}
	
	public function gcodedetail() {
		$data_json = array();
		$gid = $this->input->get('id');
		$array_data = array();
		$check_left = NULL;
		$check_right = NULL;
		$change_left = NULL;
		$change_right = NULL;
		$enable_print = TRUE;
		$key_suggest_temper = 'suggest_temperature';
		$bicolor = ($this->config->item('nb_extruder') >= 2);
		
		$this->load->helper(array('printerstoring', 'printerstate'));
		$data_json = PrinterStoring_getInfo('gcode', $gid);
		
		if (is_null($data_json)) {
			$this->output->set_header('Location: /printerstoring/listgcode');
			
			return;
		}
		
		$this->load->library('parser');
		$this->lang->load('printerstoring/gcodedetail', $this->config->item('language'));
		
		$check_left = $check_right = t('filament_ok');
		$change_left = $change_right = t('change_filament');
		
		foreach (array('r', 'l') as $abb_filament) {
			$data_cartridge = array();
			$tmp_ret = 0;
			$volume_need = 0;
			$key_length = NULL;
			$key_material = NULL;
			
			if ($abb_filament == 'l') {
				$key_length = PRINTERSTORING_TITLE_LENG_L;
				$key_material = PRINTERSTORING_TITLE_MATER_L;
			}
			else { // $abb_filament == 'r'
				$key_length = PRINTERSTORING_TITLE_LENG_R;
				$key_material = PRINTERSTORING_TITLE_MATER_R;
			}
			
			if ($data_json[$key_length] > 0) {
				$volume_need = $data_json[$key_length];
// 				$array_need[$abb_filament] = 'true';
			}
			else if ($abb_filament == 'l') {
				$check_left = t('filament_not_need');
			}
			else { // $abb_filament == 'r'
				$check_right = t('filament_not_need');
			}
			
			// check mono extruder case (normally, it's not necessary)
			if ($bicolor == FALSE && $abb_filament == 'l') {
				$tmp_ret = ERROR_MISS_LEFT_CART;
			}
			else {
				$tmp_ret = PrinterState_checkFilament($abb_filament, $volume_need, $data_cartridge);
			}
			
			if (in_array($tmp_ret, array(
					ERROR_OK, ERROR_MISS_LEFT_FILA, ERROR_MISS_RIGT_FILA,
					ERROR_LOW_LEFT_FILA, ERROR_LOW_RIGT_FILA,
			))) {
				$array_data[$abb_filament] = array(
						PRINTERSTATE_TITLE_COLOR		=> $data_cartridge[PRINTERSTATE_TITLE_COLOR],
						PRINTERSTATE_TITLE_EXT_TEMPER	=> $data_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER],
						PRINTERSTATE_TITLE_MATERIAL		=> $data_cartridge[PRINTERSTATE_TITLE_MATERIAL],
				);
					
				// set default temperature if pla
				if ($data_cartridge[PRINTERSTATE_TITLE_MATERIAL] == PRINTERSTATE_DESP_MATERIAL_PLA) {
					$array_data[$abb_filament][PRINTERSTATE_TITLE_EXT_TEMPER] = PRINTERSTATE_VALUE_FILAMENT_PLA_PRINT_TEMPER;
				}
			}
			else {
				$array_data[$abb_filament] = array(
						PRINTERSTATE_TITLE_COLOR		=> PRINTERSTATE_VALUE_DEFAULT_COLOR,
						PRINTERSTATE_TITLE_EXT_TEMPER	=> 0,
						PRINTERSTATE_TITLE_MATERIAL		=> NULL,
				);
			}
			$array_data[$abb_filament][PRINTERSTATE_TITLE_NEED_L]	= $volume_need;
			$array_data[$abb_filament][$key_suggest_temper]			= 0;
			
			if ($volume_need > 0 && $data_json[$key_material] != $array_data[$abb_filament][PRINTERSTATE_TITLE_MATERIAL]) {
				$required_material = t('require_' . $data_json[$key_material]);
				
				if ($abb_filament == 'l') {
					$check_left = $required_material;
				}
				else { // $abb_filament == 'r'
					$check_right = $required_material;
				}
				
				$enable_print = FALSE; // disable print when material is different
			}
			else {
				// treat error
				switch ($tmp_ret) {
					case ERROR_OK:
						// do nothing if no error
						break;
						
					case ERROR_LOW_RIGT_FILA:
						$check_right = t('filament_not_enough');
						break;
						
					case ERROR_MISS_RIGT_FILA:
						$check_right = t('filament_unloaded');
						$change_right = t('load_filament');
						break;
						
					case ERROR_MISS_RIGT_CART:
						$check_right = t('filament_empty');
						$change_right = t('load_filament');
						break;
						
					case ERROR_LOW_LEFT_FILA:
						$check_left = t('filament_not_enough');
						break;
						
					case ERROR_MISS_LEFT_FILA:
						$check_left = t('filament_unloaded');
						$change_left = t('load_filament');
						break;
						
					case ERROR_MISS_LEFT_CART:
						$check_left = t('filament_empty');
						$change_left = t('load_filament');
						break;
						
					default:
						$this->load->helper('printerlog');
						PrinterLog_logError('unexpected return when getting detail of gcode library model: ' . $cr, __FILE__, __LINE__);
						
						// assign error message if necessary
						if ($abb_filament == 'l') {
							$check_left = t('filament_error');
						}
						else { // $abb_filament == 'r'
							$check_right = t('filament_error');
						}
						break;
				}
			}
			
			// block print
			if ($enable_print == TRUE && $tmp_ret != ERROR_OK && $volume_need > 0) {
				$enable_print = FALSE;
			}
		}
		
		$template_data = array(
				'home'					=> t('home'),
				'back'					=> t('back'),
				'id'					=> $gid,
				'title'					=> $data_json['name'],
				'photo_title'			=> t('photo_title'),
				'title_current'			=> t('filament_title'),
				'msg_ok'				=> t('filament_ok'),
				'state_c_l'				=> $array_data['l'][PRINTERSTATE_TITLE_COLOR],
				'state_c_r'				=> $array_data['r'][PRINTERSTATE_TITLE_COLOR],
				'state_f_l'				=> $check_left,
				'state_f_r'				=> $check_right,
				'need_filament_l'		=> $array_data['l'][PRINTERSTATE_TITLE_NEED_L],
				'need_filament_r'		=> $array_data['r'][PRINTERSTATE_TITLE_NEED_L],
				'temper_filament_l'		=> $array_data['l'][PRINTERSTATE_TITLE_EXT_TEMPER],
				'temper_filament_r'		=> $array_data['r'][PRINTERSTATE_TITLE_EXT_TEMPER],
				'temper_suggest_l'		=> $array_data['l'][$key_suggest_temper],
				'temper_suggest_r'		=> $array_data['r'][$key_suggest_temper],
				'print_button'			=> t('print_button'),
				'change_filament_l'		=> $change_left,
				'change_filament_r'		=> $change_right,
				'enable_print'			=> $enable_print ? 'true' : 'false',
// 				'needprint_right'		=> $array_need['r'],
// 				'needprint_left'		=> $array_need['l'],
				'temp_adjustments_l'	=> t('temp_adjustments_l'),
				'temp_adjustments_r'	=> t('temp_adjustments_r'),
				'temp_adjustments'		=> t('temp_adjustments'),
				'advanced'				=> t('advanced'),
				'gcode_link'			=> t('gcode_link'),
				'2drender_link'			=> t('2drender_link'),
				'temper_max'			=> PRINTERSTATE_TEMPER_CHANGE_MAX,
				'temper_min'			=> PRINTERSTATE_TEMPER_CHANGE_MIN,
				'temper_delta'			=> PRINTERSTATE_TEMPER_CHANGE_VAL,
				'bicolor'				=> $bicolor ? 'true' : 'false',
				'extrud_multiply'		=> t('extrud_multiply'),
				'left_extrud_mult'		=> t('left_extrud_mult'),
				'right_extrud_mult'		=> t('right_extrud_mult'),
				'extrud_r'				=> PRINTERSTATE_EXT_MULTIPLY_DEFAULT,
				'extrud_l'				=> PRINTERSTATE_EXT_MULTIPLY_DEFAULT,
				'extrud_min'			=> PRINTERSTATE_EXT_MULTIPLY_MIN,
				'extrud_max'			=> PRINTERSTATE_EXT_MULTIPLY_MAX,
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstoring_gcodedetail_pagetitle'),
				$this->parser->parse('printerstoring/gcodedetail', $template_data, TRUE));
		
		return;
	}
	
	function gcode_ajax() {
		$cr = 0;
		$display = NULL;
		$gcode_path = NULL;
		$id_model = $this->input->get('id');
		
		$this->load->helper('printer');
		
		$cr = Printer_getFileFromModel(PRINTER_TYPE_GCODELIB, $id_model, $gcode_path);
		if ($cr == ERROR_OK) {
			if (file_exists($gcode_path)) {
// 				$this->load->helper('file');
// 				$this->output->set_content_type(get_mime_by_extension($gcode_path))->set_output(@file_get_contents($gcode_path));
// 				$this->load->helper('download');
// 				force_download('library.gcode', @file_get_contents($gcode_path));
				$this->_sendFileContent($gcode_path, 'library.gcode');
				
				return;
			}
			else {
				$cr = ERROR_INTERNAL;
			}
		}
		
		$display = $cr . " " . t(MyERRMSG($cr));
		$this->output->set_status_header($cr, $display);
		// 		http_response_code($cr);
		$this->output->set_content_type('text/plain; charset=UTF-8');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		return;
	}
}