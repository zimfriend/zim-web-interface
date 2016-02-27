<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Printmodel extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'url',
				'json' 
		) );
	}
	
	private function _model_usortCompare($a, $b) {
		return strcasecmp($a['name'], $b['name']);
	}
	
	public function index() {
// 		$this->listmodel();
		$this->output->set_header('Location: /printmodel/listmodel');
		return;
	}
	
	public function listmodel() {
		$display_printlist = array();
		$template_data = array();
		$json_data = array();
		$nb_extruder = $this->config->item('nb_extruder');
		
		$this->load->helper('printlist');
		$this->load->library('parser');
		$this->lang->load('printlist', $this->config->item('language'));
		
		$json_data = ModelList__listAsArray(TRUE);
		
		// prepare display data
		foreach ($json_data[PRINTLIST_TITLE_MODELS] as $model_data) {
			$nb_image = count($model_data[PRINTLIST_TITLE_PIC]);
			
			// ignore bi-color model in mono-extruder mode
			if ($nb_extruder < 2 && $model_data[PRINTLIST_TITLE_LENG_F2] > 0) {
				continue;
			}
			
			$display_printlist[] = array(
					'name'	=> $model_data[PRINTLIST_TITLE_NAME],
					'id'	=> $model_data[PRINTLIST_TITLE_ID],
					'image'	=> $model_data[PRINTLIST_TITLE_PIC][0],
			);
		}
		// sort list by name of translation, by name of folder if not do so
		usort($display_printlist, 'Printmodel::_model_usortCompare');
		
		// parse the main body
		$template_data = array(
// 				'title'				=> t('Print'),
				'search_hint'		=> t('Select a model'),
				'baseurl_detail'	=> '/printmodel/detail',
				'model_lists'		=> $display_printlist,
				'back'				=> t('back'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Quick printing list'),
				$this->parser->parse('printlist/listmodel', $template_data, TRUE));
		
		return;
	}
	
	public function detail() {
		$model_data = array();
		$template_data = array();
		$cr = 0;
		$check_filament = array();
		$change_filament = array();
		$array_data = array();
		$time_estimation = NULL;
		$body_page = NULL;
		$mono_model = FALSE;
		$bicolor = ($this->config->item('nb_extruder') >= 2);
		$enable_print = 'true';
		$enable_exchange = 'disabled="disabled"'; // select disable
		$calibration = FALSE;
		
		$this->load->helper(array('printlist', 'printerstate', 'slicer', 'timedisplay'));
		$this->load->library('parser');
		$this->lang->load('printlist', $this->config->item('language'));
		$this->lang->load('timedisplay', $this->config->item('language'));
		
		$mid = $this->input->get('id');
		
		// check model id, resend user to if not valid
		if ($mid) {
			if ($mid == 'calibration') {
				$mid = ModelList_codeModelHash(PRINTLIST_MODEL_CALIBRATION);
				$calibration = TRUE;
			}
			$cr = ModelList__getDetailAsArray($mid, $model_data, TRUE);
			if (($cr != ERROR_OK) || is_null($model_data)) {
				$this->output->set_header('Location: /printmodel/listmodel');
				return;
			}
		}
		else {
			$this->output->set_header('Location: /printmodel/listmodel');
			return;
		}
		
		// check the model is mono-color or 2 colors
		if (isset($model_data[PRINTLIST_TITLE_LENG_F2]) && $model_data[PRINTLIST_TITLE_LENG_F2] > 0) {
			$mono_model = FALSE;
		}
		else {
			$mono_model = TRUE;
		}
		
		// initialize variables
		$check_filament = array('l' => t('ok'), 'r' => t('ok'));
		$change_filament = array('l' => t('Change'), 'r' => t('Change'));
		
		// check quantity of filament and get cartridge information (color)
		// color1 => right, color2 => left
		foreach (array('r', 'l') as $abb_filament) {
			$data_cartridge = array();
			$key_length = ($abb_filament == 'l') ? PRINTLIST_TITLE_LENG_F2 : PRINTLIST_TITLE_LENG_F1;
			$key_color = ($abb_filament == 'l') ? PRINTLIST_TITLE_COLOR_F2 : PRINTLIST_TITLE_COLOR_F1;
			
			if ($model_data[$key_length] <= 0) {
				$check_filament[$abb_filament] = t('filament_not_need');
			}
			
			// check mono extruder case (normally, it's not necessary)
			if ($bicolor == FALSE && $abb_filament == 'l') {
				$cr = ERROR_MISS_LEFT_CART;
			}
			else {
				$cr = PrinterState_checkFilament($abb_filament, $model_data[$key_length], $data_cartridge);
			}
			
			if (in_array($cr, array(
					ERROR_OK, ERROR_MISS_LEFT_FILA, ERROR_MISS_RIGT_FILA,
					ERROR_LOW_LEFT_FILA, ERROR_LOW_RIGT_FILA,
			))) {
				$array_data[$abb_filament] = array(
						PRINTERSTATE_TITLE_COLOR		=> $data_cartridge[PRINTERSTATE_TITLE_COLOR],
						PRINTERSTATE_TITLE_EXT_TEMPER	=> $data_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER],
				);
				
				// set default temperature if pla
				if ($data_cartridge[PRINTERSTATE_TITLE_MATERIAL] == PRINTERSTATE_DESP_MATERIAL_PLA) {
					$array_data[$abb_filament][PRINTERSTATE_TITLE_EXT_TEMPER] = PRINTERSTATE_VALUE_FILAMENT_PLA_PRINT_TEMPER;
				}
			}
			else {
				$array_data[$abb_filament] = array(
						PRINTERSTATE_TITLE_COLOR		=> PRINTERSTATE_VALUE_DEFAULT_COLOR,
						PRINTERSTATE_TITLE_EXT_TEMPER	=> SLICER_VALUE_DEFAULT_TEMPER,
				);
			}
				$array_data[$abb_filament][PRINTERSTATE_TITLE_NEED_L] = $model_data[$key_length];
			
			// treat error
			switch ($cr) {
				case ERROR_OK:
					// do nothing if no error
					break;
					
				case ERROR_LOW_RIGT_FILA:
					$check_filament['r'] = t('not enough');
					break;
					
				case ERROR_MISS_RIGT_FILA:
					$check_filament['r'] = t('unloaded');
					$change_filament['r'] = t('Load');
					break;
					
				case ERROR_MISS_RIGT_CART:
					$check_filament['r'] = t('empty');
					$change_filament['r'] = t('Load');
					break;
					
				case ERROR_LOW_LEFT_FILA:
					$check_filament['l'] = t('not enough');
					break;
					
				case ERROR_MISS_LEFT_FILA:
					$check_filament['l'] = t('unloaded');
					$change_filament['l'] = t('Load');
					break;
					
				case ERROR_MISS_LEFT_CART:
					$check_filament['l'] = t('empty');
					$change_filament['l'] = t('Load');
					break;
					
				default:
					$this->load->helper('printerlog');
					PrinterLog_logError('not previewed return code for checking filament ' . $abb_filament, __FILE__, __LINE__);
					
					// assign error message if necessary
					$check_filament[$abb_filament] = t('error');
					break;
			}
			
			if ($cr != ERROR_OK && $model_data[$key_length] > 0) {
				$enable_print = 'false';
			}
		}
		
		// get a more legible time of estimation
		$time_estimation = TimeDisplay__convertsecond(
				$model_data[PRINTLIST_TITLE_TIME], t('Time estimation: '), t('unknown'));
		
		// check if we can inverse filament / exchange extruder or not
		$cr = PrinterState_checkFilaments(array(
				'l'	=> $array_data['r'][PRINTERSTATE_TITLE_NEED_L],
				'r'	=> $array_data['l'][PRINTERSTATE_TITLE_NEED_L],
		));
		if ($cr == ERROR_OK && $calibration == FALSE) {
			$enable_exchange = NULL;
		}
		// display not enough even if filament is unused (in mono-color model)
		else if ($mono_model == TRUE) {
			if ($array_data['l'][PRINTERSTATE_TITLE_NEED_L] == 0) {
				$check_filament['l'] = t('filament_not_enough_for_switch');
			}
			else { // ($array_data['r'][PRINTERSTATE_TITLE_NEED_L] == 0)
				$check_filament['r'] = t('filament_not_enough_for_switch');
			}
		}
		
		// show detail page if valid, parse the body of page
		$template_data = array(
				'home'					=> t('Home'),
				'title'					=> $model_data[PRINTLIST_TITLE_NAME],
				'image'					=> $model_data[PRINTLIST_TITLE_PIC][0],
				'model_c_r'				=> $model_data[PRINTLIST_TITLE_COLOR_F1],
				'model_c_l'				=> $model_data[PRINTLIST_TITLE_COLOR_F2],
				'time'					=> $time_estimation,
				'desp'					=> $model_data[PRINTLIST_TITLE_DESP],
				'state_c_l'				=> $array_data['l'][PRINTERSTATE_TITLE_COLOR],
				'state_c_r'				=> $array_data['r'][PRINTERSTATE_TITLE_COLOR],
				'state_f_l'				=> $check_filament['l'],
				'state_f_r'				=> $check_filament['r'],
				'model_id'				=> $mid,
				'title_current' 		=> t('Filament'),
				'change_filament_l'		=> $change_filament['l'],
				'change_filament_r'		=> $change_filament['r'],
				'need_filament_l'		=> $model_data[PRINTLIST_TITLE_LENG_F2],
				'need_filament_r'		=> $model_data[PRINTLIST_TITLE_LENG_F1],
				'temper_filament_l'		=> $array_data['l'][PRINTERSTATE_TITLE_EXT_TEMPER],
				'temper_filament_r'		=> $array_data['r'][PRINTERSTATE_TITLE_EXT_TEMPER],
				'print_model'			=> t('Print'),
				'back'					=> t('back'),
				'preview_title'			=> t('Preview'),
				'desp_title'			=> t('Description'),
				'color_suggestion'		=> t('color_suggestion'),
				'temp_adjustments_l'	=> t('temp_adjustments_l'),
				'temp_adjustments_r'	=> t('temp_adjustments_r'),
				'chg_temperature'		=> t('chg_temperature'),
				'error'					=> t('error'),
				'filament_not_need'		=> t('filament_not_need'),
				'filament_ok'			=> t('ok'),
				'temper_max'			=> PRINTERSTATE_TEMPER_CHANGE_MAX,
				'temper_min'			=> PRINTERSTATE_TEMPER_CHANGE_MIN,
				'temper_delta'			=> PRINTERSTATE_TEMPER_CHANGE_VAL,
				'exchange_on'			=> t('exchange_left'),
				'exchange_off'			=> t('exchange_right'),
				'exchange_extruder'		=> t('exchange_extruder'),
				'random_prefix'			=> $mid . '_' . rand() . '_',
				'enable_exchange'		=> $enable_exchange,
				'enable_print'			=> $enable_print,
				'bicolor_model'			=> $mono_model ? 'false' : 'true',
				'bicolor_printer'		=> $bicolor ? 'true' : 'false',
				'advanced'				=> t('advanced'),
				'extrud_multiply'		=> t('extrud_multiply'),
				'left_extrud_mult'		=> t('left_extrud_mult'),
				'right_extrud_mult'		=> t('right_extrud_mult'),
				'extrud_r'				=> PRINTERSTATE_EXT_MULTIPLY_DEFAULT,
				'extrud_l'				=> PRINTERSTATE_EXT_MULTIPLY_DEFAULT,
				'extrud_min'			=> PRINTERSTATE_EXT_MULTIPLY_MIN,
				'extrud_max'			=> PRINTERSTATE_EXT_MULTIPLY_MAX,
		);
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Printing details'),
				$this->parser->parse('printlist/detail', $template_data, TRUE));
		
		return;
	}
}
