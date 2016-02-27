<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Printdetail extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
// 				'printerstate',
				'url',
				'json',
				'corestatus',
		) );
	}
	
	private function set_led() {
		$ret_val = 0;
		$status_strip = 0;
		$status_head = 0;
		
		$this->load->library('session');
		$this->load->helper('printerstate');

		$ret_val = PrinterState_getStripLedStatus($status_strip);
		if ($ret_val != ERROR_OK || $status_strip == FALSE) {
			$status_strip = 0;
		}
		else {
			$status_strip = 1;
		}
		$ret_val = PrinterState_getTopLedStatus($status_head);
		if ($ret_val != ERROR_OK || $status_head == FALSE) {
			$status_head = 0;
		}
		else {
			$status_head = 1;
		}
		
		$this->session->set_flashdata('led_strip', $status_strip);
		$this->session->set_flashdata('led_head', $status_head);
		
		return;
	}
	
	private function get_led(&$status_strip, &$status_head) {
		$this->load->library(array('parser', 'session'));
		
		$status_strip = $this->session->flashdata('led_strip');
		if ($status_strip === FALSE) {
			$ret_val = PrinterState_getStripLedStatus($status_strip);
			if ($ret_val != ERROR_OK) {
				$status_strip = FALSE;
			}
		}
		$status_head = $this->session->flashdata('led_head');
		if ($status_head === FALSE) {
			$ret_val = PrinterState_getTopLedStatus($status_head);
			if ($ret_val != ERROR_OK) {
				$status_head = FALSE;
			}
		}
		
		return;
	}
	
	private function get_extra_info(&$array_temper, &$exchange_extruder) {
		$temperature_r = 0;
		$temperature_l = 0;
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			// set extrusion multiply (reset to default if not exist)
			$extrude_multiply = array();
			
			$this->load->helper('printerstate');
			foreach (array(
					'r'	=> 'e_r',
					'l'	=> 'e_l',
			) as $arraykey => $postkey) {
				if (FALSE !== $this->input->post($postkey)) {
					$extrude_multiply[$arraykey] = (int) $this->input->post($postkey);
				}
				else {
					$extrude_multiply[$arraykey] = PRINTERSTATE_EXT_MULTIPLY_DEFAULT;
				}
			}
			PrinterState_setExtrusionMultiply($extrude_multiply); // ignore error case, just send command
			
			// get temperature set from interface
			$temperature_r = (int) $this->input->post('r');
			$temperature_l = (int) $this->input->post('l');
			$exchange_extruder = (int) $this->input->post('exchange');
		}
		else {
			$status_current = NULL;
			$array_status = array();
			
			$this->load->helper('corestatus');
			if (CoreStatus_checkInIdle($status_current, $array_status)) {
				$temperature_r = $array_status[CORESTATUS_TITLE_P_TEMPER_R];
				$temperature_l = $array_status[CORESTATUS_TITLE_P_TEMPER_L];
				$exchange_extruder = $array_status[CORESTATUS_TITLE_P_EXCH_BUS];
			}
		}
		
		if ($temperature_r > 0) $array_temper['r'] = $temperature_r;
		if ($temperature_l > 0) $array_temper['l'] = $temperature_l;
		$exchange_extruder = ($exchange_extruder != 0) ? TRUE : FALSE;
		
		return;
	}
	
	public function index() {
		$this->output->set_header('Location: /');
		return;
	}
	
// 	public function printcalibration() {
// 		$mid = NULL;
// 		$cr = 0;
		
// 		// check model id, and then send it to print command
// 		$this->load->helper('printer');
		
// 		$this->set_led();
// 		$cr = Printer_printFromCalibration();
// 		if ($cr != ERROR_OK) {
// 			$this->output->set_header('Location: /printmodel/listmodel');
// 			return;
// 		}
		
// 		$this->output->set_header('Location: /printdetail/status?id=calibration');
		
// 		return;
// 	}
	
	public function printprime() {
		//TODO it's better to stock callback model in json file
		$abb_cartridge = NULL;
		$first_run = FALSE;
		$cr = 0;
		
		// check model id, and then send it to print command
		$this->load->helper('printer');
		$abb_cartridge = $this->input->get('v');
		$first_run = $this->input->get('r');
		$callback = $this->input->get('cb');
		
		if ($abb_cartridge) {
			$first_run = ($first_run === FALSE) ? TRUE : FALSE;
			$this->set_led();
			$cr = Printer_printFromPrime($abb_cartridge, $first_run);
// 			$cr = Printer_startPrintingStatusFromModel($mid);
			if ($cr != ERROR_OK) {
				$this->output->set_header('Location: /printmodel/listmodel');
				return;
			}
		}
		else {
			$this->output->set_header('Location: /printmodel/listmodel');
			return;
		}
		
		if ($callback) {
			$this->output->set_header('Location: /printdetail/status?v=' . $abb_cartridge . '&cb=' . $callback);
		}
		else {
			$this->output->set_header('Location: /printdetail/status?v=' . $abb_cartridge);
		}
		
		return;
	}
	
	public function printgcode() {
		$this->printgcode_temp();
		
		return;
	}
	
	public function printgcode_temp() {
		$exchange_extruder = 0;
		$array_temper = array();
		$gid = (int) $this->input->get('id');
		
		$this->get_extra_info($array_temper, $exchange_extruder);
		
		if ($gid) {
			$gcode_info = array();
			
			$this->load->helper(array('printerstoring', 'printer', 'corestatus'));
			
			$gcode_info = PrinterStoring_getInfo("gcode", $gid);
			if (!is_null($gcode_info)) {
				$this->set_led();
				$cr = Printer_printFromLibrary($gid, $exchange_extruder, $array_temper);
				
				if ($cr == ERROR_OK) {
					$this->output->set_header('Location: /printdetail/status?id=' . CORESTATUS_VALUE_MID_PREFIXGCODE . $gid);
					
					return;
				}
			}
		}
		
		$this->output->set_header('Location: /printerstoring/listgcode');
		
		return;
	}
	
	public function printmodel() {
		$this->printmodel_temp();
		
		return;
	}
	
	public function printmodel_temp() {
		$cr = 0;
		$model_calibration = FALSE;
		$mid = $this->input->get('id');
		$exchange_extruder = 0;
		$array_temper = array();
		
		// check model id, and then send it to print command
		$this->load->helper(array('printer', 'printlist', 'corestatus'));
		
		$this->get_extra_info($array_temper, $exchange_extruder);
		
		if ($mid) {
			if (strpos($mid, CORESTATUS_VALUE_MID_PREFIXGCODE) === 0) {
				$gid = (int) substr($mid, strlen(CORESTATUS_VALUE_MID_PREFIXGCODE) - 1);
				
				$this->printgcode_temp($gid);
				
				return;
			}
			
			if ($mid == ModelList_codeModelHash(PRINTLIST_MODEL_CALIBRATION)) {
// 				$this->output->set_header('Location: /printdetail/printcalibration');
// 				return;
				$model_calibration = TRUE;
			}
			$this->set_led();
			$cr = Printer_printFromModel($mid, $model_calibration, $exchange_extruder, $array_temper);
			if ($cr != ERROR_OK) {
				if ($model_calibration) {
					$this->output->set_header('Location: /printmodel/detail?id=calibration');
				}
				else {
					$this->output->set_header('Location: /printmodel/listmodel');
				}
				return;
			}
		}
		else {
			$this->output->set_header('Location: /printmodel/listmodel');
			return;
		}
		
		if ($model_calibration) {
// 			$this->output->set_header('Location: /printdetail/status?id=calibration');
			$this->output->set_header('Location: /printdetail/status?id=' . CORESTATUS_VALUE_MID_CALIBRATION);
		}
		else {
			$this->output->set_header('Location: /printdetail/status?id=' . $mid);
		}
		
		return;
	}
	
	public function printslice() {
		$this->printslice_temp();
		
		return;
	}
	
	public function printslice_temp() {
		$cr = 0;
		$exchange_extruder = 0;
		$array_temper = array();
		
		$this->load->helper('printer');
		
		$this->get_extra_info($array_temper, $exchange_extruder);
		
		$this->set_led();
		$cr = Printer_printFromSlice($exchange_extruder, $array_temper);
		if ($cr != ERROR_OK) {
			$this->output->set_header('Location: /sliceupload/slice?callback');
			return;
		}
		else {
// 			$this->output->set_header('Location: /printdetail/status?id=slice');
			$this->output->set_header('Location: /printdetail/status?id=' . CORESTATUS_VALUE_MID_SLICE);
		}
		
		return;
	}
	
	public function status() {
		$time_remain = NULL;
		$pagetitle = NULL;
		$template_data = array();
		$data_status = array();
		$temper_status = array();
		$print_slice = FALSE;
		$print_calibration = FALSE;
		$status_strip = FALSE;
		$status_head = FALSE;
		$ret_val = 0;
		$option_selected = 'selected="selected"';
		$status_current = NULL;
		
		$this->load->library('parser');
		$this->lang->load('printdetail', $this->config->item('language'));
		$this->lang->load('printerstate/index', $this->config->item('language'));
		
		$this->load->helper(array('zimapi', 'printerstate', 'corestatus'));
		
		$id = $this->input->get('id');
		$callback = $this->input->get('cb');
		$abb_cartridge = $this->input->get('v');
		$array_status = array();
		$model_displayname = t('timelapse_info_modelname_unknown');
		
		// check if we are in printing or not to continue, do redirection if not
		$data_status = PrinterState_checkStatusAsArray(FALSE);
		if ($data_status[PRINTERSTATE_TITLE_STATUS] != CORESTATUS_VALUE_PRINT) {
			if (in_array($data_status[PRINTERSTATE_TITLE_STATUS], array(CORESTATUS_VALUE_IDLE, CORESTATUS_VALUE_SLICED))
					&& file_exists(ZIMAPI_FILEPATH_TIMELAPSE)) {
				$this->output->set_header('Location: /printdetail/timelapse');
			}
			else {
				$this->output->set_header('Location: /');
			}
			
			return;
		}
		
		if (!ZimAPI_checkCameraInBlock()) {
			// only launch camera video for heating parse (camera is in use for other parses)
			if (!ZimAPI_cameraOn(ZIMAPI_PRM_CAMERA_PRINTSTART)) {
				$this->load->helper('printerlog');
				PrinterLog_logError('can not set camera', __FILE__, __LINE__);
			}
		}
		
		// pass the real value of LED later
		$this->get_led($status_strip, $status_head);
		
		if ($id == CORESTATUS_VALUE_MID_SLICE) {
			$print_slice = TRUE;
		}
		else if ($id == CORESTATUS_VALUE_MID_CALIBRATION) {
			$print_calibration = TRUE;
		}
		
		// get model name
		CoreStatus_getStatusArray($array_status);
		if (strpos($array_status[CORESTATUS_TITLE_PRINTMODEL], CORESTATUS_VALUE_MID_PREFIXGCODE) === 0) {
			// gcode library model
			$gcode_info = array();
			$gid = (int) substr($array_status[CORESTATUS_TITLE_PRINTMODEL], strlen(CORESTATUS_VALUE_MID_PREFIXGCODE));
			
			$this->load->helper('printerstoring');
			
			$gcode_info = PrinterStoring_getInfo("gcode", $gid);
			if (!is_null($gcode_info) && array_key_exists("name", $gcode_info)) {
				$model_displayname = $gcode_info["name"];
			}
		}
		else {
			$model_id = NULL;
			
			switch ($array_status[CORESTATUS_TITLE_PRINTMODEL]) {
				case CORESTATUS_VALUE_MID_SLICE:
					$preset_id = NULL;
					$model_filename = array();
					
					$this->load->helper('slicer');
					if (ERROR_OK == Slicer_getModelFile(0, $model_filename, TRUE)) {
						$model_displayname = NULL;
						
						foreach($model_filename as $model_basename) {
							if (strlen($model_displayname)) {
								$model_displayname .= ' + ' . $model_basename;
							}
							else {
								$model_displayname = $model_basename;
							}
						}
					}
					else {
						$model_displayname = t('timelapse_info_modelname_slice');
					}
					break;
					
				case CORESTATUS_VALUE_MID_PRIME_R:
				case CORESTATUS_VALUE_MID_PRIME_L:
					$model_displayname = t('timelapse_info_modelname_prime');
					break;
					
				case CORESTATUS_VALUE_MID_CALIBRATION:
					$this->load->helper('printlist');
					$model_id = ModelList_codeModelHash(PRINTLIST_MODEL_CALIBRATION);
					// treat as a normal pre-sliced model
					
				default:
					// treat as pre-sliced model
					$model_data = array();
					
					if (is_null($model_id)) {
						$this->load->helper('printlist');
						$model_id = $array_status[CORESTATUS_TITLE_PRINTMODEL];
					}
					
					if (ERROR_OK == ModelList__getDetailAsArray($model_id, $model_data, TRUE)) {
						$model_displayname = $model_data[PRINTLIST_TITLE_NAME];
					}
					break;
			}
		}
		
		// parse the main body
		$template_data = array(
				'title'				=> t('Control your printing'),
				'print_detail'		=> t('Printing details'),
				'print_stop'		=> t('Cancel'),
				'cancel_confirm'	=> t('cancel_confirm'),
 				'wait_info'			=> t('Waiting for starting...'),
				'finish_info'		=> t('Congratulation, your printing is complete!'),
				'return_button'		=> t('Home'),
				'return_url'		=> '/',
// 				'restart_url'		=> '/printdetail/printprime?r&v=' . $abb_cartridge . '&cb=' . $callback,
				'restart_url'		=> '/printdetail/printmodel?id=' . $id,
				'var_prime'			=> 'false',
				'var_slice'			=> 'false',
				'var_calibration'	=> $print_calibration ? 'true' : 'false',
				'again_button'		=> t('Print again'),
				'video_url'			=> $this->config->item('video_url'),
				'strip_led'			=> t('strip_led'),
				'head_led'			=> t('head_led'),
				'led_on'			=> t('led_on'),
				'led_off'			=> t('led_off'),
				'lighting'			=> t('lighting'),
				'initial_strip'		=> ($status_strip == TRUE) ? $option_selected : NULL,
				'initial_head'		=> ($status_head == TRUE) ? $option_selected : NULL,
				'video_error'		=> t('video_error'),
				'loading_player'	=> t('loading_player'),
				'model_name_title'	=> t('timelapse_info_modelname_title'),
				'model_name_value'	=> $model_displayname,
// 				'reloading_player'	=> t('reloading_player'),
		);
		
		if ($print_slice == TRUE) {
			$template_data['restart_url']	= '/printdetail/printslice';
			$template_data['var_slice']		= 'true';
// 			$template_data['return_url']	= '/sliceupload/slice?callback';
		}
		else if ($print_calibration == TRUE) {
// 			$template_data['restart_url'] = '/printdetail/printcalibration';
			$template_data['restart_url'] = '/printmodel/detail?id=calibration';
			$template_data['return_url']	= '/printerstate/offset_setting';
			$template_data['return_button']	= t('button_set_offset');
		} else if ($abb_cartridge) {
			$template_data['finish_info']	= t('Restart?');
// 			$template_data['return_url']	= '/printmodel/detail?id=' . $callback;
			$template_data['restart_url']	= '/printdetail/printprime?r&v=' . $abb_cartridge . '&cb=' . $callback;
			$template_data['return_button']	= t('No');
			$template_data['var_prime']		= 'true';
			$template_data['again_button']	= t('Yes');
			
			// change wording
			$template_data['title'] = t('title_prime');
			$template_data['print_detail'] = t('print_detail_prime');
			$template_data['cancel_confirm'] = t('cancel_confirm_prime');
// 			$template_data['finish_info'] = t('finish_info_prime');
			$template_data['wait_info'] = t('wait_info_prime');
			
			if ($callback) {
				if ($callback == 'slice') {
					$template_data['return_url']	= '/sliceupload/slice?callback';
				}
				else {
					$template_data['return_url']	= '/printmodel/detail?id=' . $callback;
				}
			}
		}
		
		// parse all page
		$pagetitle = ($abb_cartridge) ? t('pagetitle_prime') : t('ZeePro Personal Printer 21 - Printing details');
		$this->_parseBaseTemplate($pagetitle,
				$this->parser->parse('printdetail/status', $template_data, TRUE));
		
		return;
	}
	
// 	public function slice() {
// 		$this->load->library('parser');
// 		$this->parser->parse('plaintxt', array('display' => 'IN CONSTRUCTION, goto /rest/status or any rest service'));
// 	}
	
	public function cancel() {
		$ret_val = NULL;
		$this->load->helper('printer');
		
		$ret_val = Printer_stopPrint();
		if ($ret_val == TRUE) {
			$template_data = array();
			$array_status = array();
			
			$this->load->library('parser');
			$this->lang->load('printdetail', $this->config->item('language'));
			
			$this->load->helper('zimapi');
			if (Printer_checkCancelStatus() || !file_exists(ZIMAPI_FILEPATH_VIDEO_TS)) {
				if (ZimAPI_checkCamera($mode_current) && $mode_current == ZIMAPI_VALUE_MODE_HLS_IMG) {
					$this->load->helper('printerlog');
					PrinterLog_logMessage('detected in hls image timelapse mode, do not set camera', __FILE__, __LINE__);
				}
				else if (!ZimAPI_cameraOn(ZIMAPI_PRM_CAMERA_PRINTSTART)) {
					$this->load->helper('printerlog');
					PrinterLog_logError('can not set camera', __FILE__, __LINE__);
				}
			}
			
			// parse the main body
			$template_data = array(
					'title'				=> t('Control your printing'),
					'loading_player'	=> t('loading_player'),
					'wait_info'			=> t('wait_hint_cancel'),
					'finish_info'		=> t('finish_hint_cancel'),
					'return_button'		=> t('Home'),
					'return_url'		=> '/',
					'video_url'			=> $this->config->item('video_url'),
					'restart_url'		=> NULL,
					'again_button'		=> t('Print again'),
			);
			
			if (CoreStatus_getStatusArray($array_status) && is_array($array_status)
					&& isset($array_status[CORESTATUS_TITLE_PRINTMODEL])) {
				if (strpos($array_status[CORESTATUS_TITLE_PRINTMODEL], CORESTATUS_VALUE_MID_PREFIXGCODE) === 0) {
					// gcode library model
					$gid = (int) substr($array_status[CORESTATUS_TITLE_PRINTMODEL], strlen(CORESTATUS_VALUE_MID_PREFIXGCODE));
					
					$template_data['restart_url'] = '/printdetail/printgcode?id=' . $gid;
				}
				else {
					$abb_cartridge = NULL;
					$restart_url = NULL;
					
					switch ($array_status[CORESTATUS_TITLE_PRINTMODEL]) {
						case CORESTATUS_VALUE_MID_SLICE:
							$restart_url = '/printdetail/printslice';
							break;
							
						case CORESTATUS_VALUE_MID_PRIME_L:
							$abb_cartridge = 'l';
							
						case CORESTATUS_VALUE_MID_PRIME_R:
							$abb_cartridge = is_null($abb_cartridge) ? 'r' : $abb_cartridge;
							
							$restart_url = '/printdetail/printprime?v=' . $abb_cartridge;
							$template_data['title'] = t('title_prime');
							$template_data['again_button'] = t('prime_agin');
							break;
							
						case CORESTATUS_VALUE_MID_CALIBRATION:
							$restart_url = '/printmodel/detail?id=calibration';
							break;
							
						default:
							// treat as pre-sliced model
							$restart_url = '/printdetail/printmodel?id=' . $array_status[CORESTATUS_TITLE_PRINTMODEL];
							break;
					}
					$template_data['restart_url'] = $restart_url;
				}
			}
			
			// parse all page
			$this->_parseBaseTemplate(t('printdetail_cancel_pagetitle'),
					$this->parser->parse('printdetail/cancel', $template_data, TRUE));
			
			return;
		}
		else {
			$this->load->helper('printerlog');
			PrinterLog_logError('can not stop printing', __FILE__, __LINE__);
			$this->output->set_status_header(403);
			return;
		}
		
		return;
	}
	
	public function recovery() {
		$ret_val = NULL;
		$template_data = array();
		
		//TODO finish me for recovery
		$this->load->helper('printer');
		$this->load->library('parser');
		$this->lang->load('printdetail', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'title'			=> t('Control your printing'),
				'wait_info'		=> t('wait_hint_recovery'),
				'finish_info'	=> t('finish_hint_recovery'),
				'return_button'	=> t('Home'),
				'return_url'	=> '/',
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printdetail_recovery_pagetitle'),
				$this->parser->parse('printdetail/recovery', $template_data, TRUE));
		
		return;
	}
	
	public function timelapse() {
		$template_data = array();
		$array_info = array();
		$status_current = NULL;
		$array_status = array();
		$restart_url = NULL;
		$model_displayname = NULL;
		$show_storegcode = FALSE;
		
		$this->load->library('parser');
		$this->load->helper('zimapi');
		$this->lang->load('printdetail', $this->config->item('language'));
		
		if (CoreStatus_checkInIdle($status_current, $array_status) && array_key_exists(CORESTATUS_TITLE_PRINTMODEL, $array_status)) {
			$model_id = NULL;
			$abb_cartridge = NULL;
			
			if (strpos($array_status[CORESTATUS_TITLE_PRINTMODEL], CORESTATUS_VALUE_MID_PREFIXGCODE) === 0) {
				// gcode library model
				$gcode_info = array();
				$gid = (int) substr($array_status[CORESTATUS_TITLE_PRINTMODEL], strlen(CORESTATUS_VALUE_MID_PREFIXGCODE));
				$model_displayname = t('timelapse_info_modelname_unknown');
				
				$this->load->helper('printerstoring');
				
				$gcode_info = PrinterStoring_getInfo("gcode", $gid);
				if (!is_null($gcode_info) && array_key_exists("name", $gcode_info)) {
					$model_displayname = $gcode_info["name"];
				}
				
				$array_info[] = array(
						'title'	=> t('timelapse_info_modelname_title'),
						'value'	=> $model_displayname,
				);
				$restart_url = '/printdetail/printgcode?id=' . $gid;
			}
			else {
				switch ($array_status[CORESTATUS_TITLE_PRINTMODEL]) {
					case CORESTATUS_VALUE_MID_SLICE:
						$preset_id = NULL;
						$model_filename = array();
						$preset_name = t('timelapse_info_presetname_unknown');
						
						$this->load->helper('slicer');
						if (ERROR_OK == Slicer_getModelFile(0, $model_filename, TRUE)) {
							foreach($model_filename as $model_basename) {
								if (strlen($model_displayname)) {
									$model_displayname .= ' + ' . $model_basename;
								}
								else {
									$model_displayname = $model_basename;
								}
							}
						}
						else {
							$model_displayname = t('timelapse_info_modelname_slice');
						}
						$array_info[] = array(
								'title'	=> t('timelapse_info_modelname_title'),
								'value'	=> $model_displayname,
						);
						
						if (ZimAPI_getPreset($preset_id)) {
							$array_json = array();
							
							if (ERROR_OK == ZimAPI_getPresetInfoAsArray($preset_id, $array_json)) {
								$preset_name = $array_json[ZIMAPI_TITLE_PRESET_NAME];
							}
						}
						$array_info[] = array(
								'title'	=> t('timelapse_info_presetname_title'),
								'value'	=> $preset_name,
						);
						
						$restart_url = '/printdetail/printslice';
						$show_storegcode = TRUE;
						break;
						
					case CORESTATUS_VALUE_MID_PRIME_R:
						$abb_cartridge = 'r';
						// treat priming in the same way
						
					case CORESTATUS_VALUE_MID_PRIME_L:
						// never reach here normally (no timelapse for priming in principe, just for safety)
						$array_info[] = array(
								'title'	=> t('timelapse_info_modelname_title'),
								'value'	=> t('timelapse_info_modelname_prime'),
						);
						
						if (is_null($abb_cartridge)) {
							$abb_cartridge = 'l';
						}
						$restart_url = '/printdetail/printprime?r&v=' . $abb_cartridge;
						//TODO we lose callback info here
						break;
						
					case CORESTATUS_VALUE_MID_CALIBRATION:
						// never reach here normally (no timelapse for calibration model, just for safety)
						$this->load->helper('printlist');
						$model_id = ModelList_codeModelHash(PRINTLIST_MODEL_CALIBRATION);
						$restart_url = '/printmodel/detail?id=calibration';
						// treat as a normal pre-sliced model
						
					default:
						// treat as pre-sliced model
						$model_data = array();
						$model_displayname = t('timelapse_info_modelname_unknown');
						
						if (is_null($model_id)) {
							$this->load->helper('printlist');
							$model_id = $array_status[CORESTATUS_TITLE_PRINTMODEL];
						}
						
						if (ERROR_OK == ModelList__getDetailAsArray($model_id, $model_data, TRUE)) {
							$model_displayname = $model_data[PRINTLIST_TITLE_NAME];
						}
						$array_info[] = array(
								'title'	=> t('timelapse_info_modelname_title'),
								'value'	=> $model_displayname,
						);
						
						if (is_null($restart_url)) {
							$restart_url = '/printdetail/printmodel?id=' . $model_id;
						}
						break;
				}
			}
			
			if (array_key_exists(CORESTATUS_TITLE_ELAPSED_TIME, $array_status)) {
				$display_time = NULL;
				
				$this->load->helper('timedisplay');
				$display_time = TimeDisplay__convertsecond($array_status[CORESTATUS_TITLE_ELAPSED_TIME], '');
				
				$array_info[] = array(
						'title'	=> t('timelapse_info_elapsedtime_title'),
						'value'	=> $display_time,
				);
			}
			
			if ($this->config->item('nb_extruder') < 2
					&& array_key_exists(CORESTATUS_TITLE_P_TEMPER_R, $array_status)
					&& $array_status[CORESTATUS_TITLE_P_TEMPER_R] > 0) {
				$array_info[] = array(
						'title'	=> t('timelapse_info_temperature_title'),
						'value'	=> t('timelapse_info_temperature_value_mono', array(
								$array_status[CORESTATUS_TITLE_P_TEMPER_R],
						)),
				);
			}
			else if (array_key_exists(CORESTATUS_TITLE_P_TEMPER_L, $array_status)
					&& $array_status[CORESTATUS_TITLE_P_TEMPER_L] > 0
					&& array_key_exists(CORESTATUS_TITLE_P_TEMPER_R, $array_status)
					&& $array_status[CORESTATUS_TITLE_P_TEMPER_R] > 0) {
				$array_info[] = array(
						'title'	=> t('timelapse_info_temperature_title'),
						'value'	=> t('timelapse_info_temperature_values', array(
								$array_status[CORESTATUS_TITLE_P_TEMPER_L],
								$array_status[CORESTATUS_TITLE_P_TEMPER_R],
						)),
				);
			}
			else if (array_key_exists(CORESTATUS_TITLE_P_TEMPER_R, $array_status)
					&& $array_status[CORESTATUS_TITLE_P_TEMPER_R] > 0) {
				$array_info[] = array(
						'title'	=> t('timelapse_info_temperature_title'),
						'value'	=> t('timelapse_info_temperature_value_r', array(
								$array_status[CORESTATUS_TITLE_P_TEMPER_R],
						)),
				);
			}
			else if (array_key_exists(CORESTATUS_TITLE_P_TEMPER_L, $array_status)
					&& $array_status[CORESTATUS_TITLE_P_TEMPER_L] > 0) {
				$array_info[] = array(
						'title'	=> t('timelapse_info_temperature_title'),
						'value'	=> t('timelapse_info_temperature_value_l', array(
								$array_status[CORESTATUS_TITLE_P_TEMPER_L],
						)),
				);
			}
		}
		else {
			$this->load->helper('printerlog');
			PrinterLog_logError('unintended status detected in timelapse page: ' . $status_current, __FILE__, __LINE__);
			$this->output->set_header('Location: /');
			
			return;
		}
		
		// parse the main body
		$template_data = array(
				'internet_ok'			=> (@file_get_contents("https://sso.zeepro.com/login.ashx") === FALSE) ? 'false' : 'true',
				'loading_player'		=> t('timelapse_info'),
				'finish_info'			=> t('Congratulation, your printing is complete!'),
				'home_button'			=> t('Home'),
				'home_popup_text'		=> t('home_popup_text'),
				'yes'					=> t('Yes'),
				'no'					=> t('No'),
				'video_error'			=> t('video_error'),
				'timelapse_title'		=> t('timelapse_title'),
// 				'timelapse_button'		=> t('timelapse_button'),
				'send_email_button'		=> t('send_email_button'),
				'send_yt_button'		=> t('send_yt_button'),
				'send_fb_button'		=> t('send_fb_button'),
				'send_email_hint'		=> t('send_email_hint'),
				'send_email_action'		=> t('send_email_action'),
				'send_email_error'		=> t('send_email_error'),
				'send_email_wrong'		=> t('send_email_wrong'),
				'send_email_multi'		=> t('send_email_multi'),
				'video_url'				=> '/tmp/' . ZIMAPI_FILENAME_TIMELAPSE . '?_=' . time(),
				'timelapse_info_title'	=> t('timelapse_info_title'),
				'timelapse_info'		=> $array_info,
				'again_button'			=> t('Print again'),
				'restart_url'			=> $restart_url ? $restart_url : '/',
				'send_email_modelname'	=> $model_displayname,
				'display_storegocde'	=> $show_storegcode ? 'true' : 'false',
				'storegcode_checkbox'	=> t('storegcode_info'),
				'storegcode_hint'		=> t('storegcode_name'),
				'storegcode_err_cfm'	=> t('storegcode_err_cfm'),
				'storegcode_title'		=> t('storegcode_title'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Printing details'),
				$this->parser->parse('printdetail/timelapse', $template_data, TRUE));
		
		return;
	}
	
	public function status_ajax() {
		$template_data = array();
// 		$printing_status = '';
		$ret_val = 0;
		$data_status = array();
		$time_remain = NULL;
		$time_passed = NULL;
		$temper_l = 0;
		$temper_r = 0;
		$finish_hint = NULL;
		$hold_temper = 'false';
// 		$status_current = NULL;
		$array_status = array();
		$reload_player_times = 0;
		$bicolor = ($this->config->item('nb_extruder') >= 2);
		
		$this->load->helper(array('printer', 'timedisplay'));
		$this->load->library('parser');
		$this->lang->load('printdetail', $this->config->item('language'));
		$this->lang->load('timedisplay', $this->config->item('language'));
		
		$ret_val = Printer_checkPrintStatus($data_status);
		if ($ret_val == FALSE) {
			$this->load->helper('corestatus');
			$ret_val = CoreStatus_setInIdle();
			if ($ret_val == FALSE) {
				// log internal error
				$this->load->helper('printerlog');
				PrinterLog_logError('can not set idle after printing', __FILE__, __LINE__);
			}
			
			if ($this->config->item('simulator')) {
				// just set temperature for simulation
				$this->load->helper('printerstate');
				PrinterState_setExtruder('r');
				PrinterState_setTemperature(20);
				PrinterState_setExtruder('l');
				PrinterState_setTemperature(20);
				PrinterState_setExtruder('r');
			}
			
			$this->output->set_status_header(202);
			return;
		}
		
		// treat time remaining for display
		if (isset($data_status['print_remain'])) {
			$time_remain = TimeDisplay__convertsecond(
					$data_status['print_remain'], t('Time remaining: '), t('under calculating'));
		}
		else {
			$time_remain = t('Time remaining: ') . t('in_progress');
		}
		$time_passed = TimeDisplay__convertsecond($data_status['print_tpassed'], t('time_elapsed'));
		
// 		CoreStatus_checkInIdle($status_current, $array_status);
		CoreStatus_getStatusArray($array_status);
		if (isset($array_status[CORESTATUS_TITLE_PRINTMODEL]) && !in_array($array_status[CORESTATUS_TITLE_PRINTMODEL],
						array(CORESTATUS_VALUE_MID_PRIME_L, CORESTATUS_VALUE_MID_PRIME_R, CORESTATUS_VALUE_MID_CALIBRATION))) {
			$reload_player_times = $data_status['print_inPhase'];
		}
		
		if ($data_status['print_percent'] == 100) {
			if (isset($array_status[CORESTATUS_TITLE_PRINTMODEL]) && in_array($array_status[CORESTATUS_TITLE_PRINTMODEL],
							array(CORESTATUS_VALUE_MID_PRIME_L, CORESTATUS_VALUE_MID_PRIME_R)
					)) {
				$finish_hint = t('in_finish_prime');
			}
			else {
				$finish_hint = t('in_finish');
			}
			
			$hold_temper = 'true';
		}
		else {
// 			$hold_temper = 'false';
			$temper_l = $data_status['print_temperL'];
			$temper_r = $data_status['print_temperR'];
		}
		
		// parse the ajax part
		$template_data = array(
// 				'print_percent'	=> t('Percentage: %d%%', array($data_status['print_percent'])),
				'percent_title'	=> t('percent_title'),
				'value_percent'	=> $data_status['print_percent'],
				'print_remain'	=> $time_remain,
				'print_passed'	=> $time_passed,
				'hold_temper'	=> $hold_temper,
				'print_temperL'	=> $bicolor ? t('Temperature of the left extruder: %d °C', array($temper_l)) : NULL,
				'print_temperR'	=> $bicolor
						? t('Temperature of the right extruder: %d °C', array($temper_r))
						: t('print_temper_msg_mono', array($temper_r)),
				'value_temperL'	=> $bicolor ? $temper_l : 'null',
				'value_temperR'	=> $temper_r,
				'in_finish'		=> $finish_hint,
				'reload_player'	=> $reload_player_times,
		);
		$this->parser->parse('printdetail/status_ajax', $template_data);
		
		$this->output->set_content_type('text/plain; charset=UTF-8');
		
		return;
	}
	
	public function cancel_ajax() {
		$template_data = array();
		$ret_val = 0;
// 		$data_status = array();
		
// 		$this->load->helper(array('printer', 'timedisplay'));
		$this->load->helper('printer');
		$this->load->library('parser');
		$this->lang->load('printdetail', $this->config->item('language'));
// 		$this->lang->load('timedisplay', $this->config->item('language'));
		
// 		$ret_val = Printer_checkCancelStatus($data_status);
		$ret_val = Printer_checkCancelStatus();
		if ($ret_val == FALSE) {
			$this->load->helper('corestatus');
			$ret_val = CoreStatus_setInIdle();
			if ($ret_val == FALSE) {
				// log internal error
				$this->load->helper('printerlog');
				PrinterLog_logError('can not set idle after canceling', __FILE__, __LINE__);
			}
			
			if ($this->config->item('simulator')) {
				// just set temperature for simulation
				$this->load->helper('printerstate');
				PrinterState_setExtruder('r');
				PrinterState_setTemperature(20);
				PrinterState_setExtruder('l');
				PrinterState_setTemperature(20);
				PrinterState_setExtruder('r');
			}
			
			$this->output->set_status_header(202);
			return;
		}
		
// 		// treat time remaining for display
// 		if (isset($data_status['print_remain'])) {
// 			$time_remain = TimeDisplay__convertsecond(
// 					$data_status['print_remain'], t('Time remaining: '), t('under calculating'));
// 		}
// 		else {
// 			$time_remain = t('Time remaining: ') . t('unknown');
// 		}
		
		// parse the ajax part
		$template_data = array(
				'wait_info'	=> t('wait_hint_cancel'),
		);
		$this->parser->parse('printdetail/cancel_ajax', $template_data);
		
		$this->output->set_content_type('text/plain; charset=UTF-8');
		
		return;
	}
	
	public function recovery_ajax() {
		//TODO finish me for recovery
		$template_data = array();
		$ret_val = 0;
		$status_current = NULL;
		$data_status = array();
		
		$this->load->helper('corestatus');
		$this->load->library('parser');
		$this->lang->load('printdetail', $this->config->item('language'));
		
		$template_data = array(
				'wait_info'	=> t('wait_hint_recovery'),
		);
		
		$ret_val = CoreStatus_checkInIdle($status_current, $data_status);
		if ($ret_val == TRUE) {
			// log recovery finish
			$this->load->helper('printerlog');
			PrinterLog_logMessage('recovery status finish', __FILE__, __LINE__);
			
			$this->output->set_status_header(202);
			return;
		}
		else if ($status_current == CORESTATUS_VALUE_RECOVERY) {
			if ($data_status[CORESTATUS_TITLE_SUBSTATUS] == CORESTATUS_VALUE_PRINT) {
				$template_data['wait_info'] = t('wait_hint_recovery_printing');
			}
			else {
				$template_data['wait_info'] = t('wait_hint_recovery_unknown');
				$this->load->helper('printerlog');
				PrinterLog_logError('unknown substatus value in recovery ' . $data_status[CORESTATUS_TITLE_SUBSTATUS], __FILE__, __LINE__);
			}
		}
		else {
			$this->output->set_status_header(403);
			
			$this->load->helper('printerlog');
			PrinterLog_logError('call recovery status check when not in recovery', __FILE__, __LINE__);
			return;
		}
		
		// parse the ajax part
		$this->parser->parse('printdetail/cancel_ajax', $template_data); // we can use the same view for recovery
		
		$this->output->set_content_type('text/plain; charset=UTF-8');
		
		return;
	}
	
	public function timelapse_ready_ajax() {
		$is_ready = FALSE;
		$status_code = 200;
		
		if (CoreStatus_checkInPrinted($is_ready)) {
			if ($is_ready == TRUE) {
				$status_code = 202;
			}
			else {
				$status_code = 200;
			}
		}
		else {
			$status_code = 403;
		}
		
		$this->output->set_status_header($status_code);
		
		return;
	}
	
	public function timelapse_end_ajax() {
		$status_code = 200;
		
		if (!CoreStatus_checkInPrinted()) {
			$status_code = 403;
		}
		else if (!ZimAPI_removeTimelapse()) {
			$status_code = 500;
		}
		
		$this->output->set_status_header($status_code);
		
		return;
	}
	
	public function sendemail_ajax() {
		$cr = 0;
		$display = NULL;
		
		$this->load->helper('zimapi');
		
		$email = $this->input->post('email');
		$model = $this->input->post('model');
		
		if ($email && $model) {
			$emails = explode(',', $email);
			// check parenthesis surround, add them if not exist
			if (strlen($model) && ($model[0] != '(' || $model[strlen($model) - 1] != ')')) {
				$model = '(' . $model . ')';
			}
			
			$cr = ZimAPI_sendTimelapse($emails, $model);
		}
		else {
			$cr = ERROR_MISS_PRM;
		}
		
		$display = $cr . " " . t(MyERRMSG($cr));
		$this->output->set_status_header($cr, $display);
		
		return;
	}
}
