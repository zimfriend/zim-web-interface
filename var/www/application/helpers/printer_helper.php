<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

// call error list if we want
$CI = &get_instance();
$CI->load->helper(array (
		'errorcode',
		'json',
));

if (!defined('PRINTER_FN_CHARGE')) {
	define('PRINTER_FN_CHARGE',			'_charge.gcode');
	define('PRINTER_FN_RETRACT',		'_retract.gcode');
	define('PRINTER_FN_PRINTPRIME_L',	'_print_prime_left.gcode');
	define('PRINTER_FN_PRINTPRIME_R',	'_print_prime_right.gcode');
	define('PRINTER_FN_END_PRINT',		'_print_endscript.sh');
	define('PRINTER_FN_POST_HEAT',		'_print_postheat.sh');
	define('PRINTER_FN_PRE_FINISH',		'_print_prefin.sh');
	define('PRINTER_PRM_TEMPER_L_N',	' -ll ');	// left temperature for other layer (if exist)
	define('PRINTER_PRM_TEMPER_L_F',	' -l ');	// left temperature for first layer (or all layer)
	define('PRINTER_PRM_TEMPER_R_N',	' -rr ');	// right temperature for other layer (if exist)
	define('PRINTER_PRM_TEMPER_R_F',	' -r ');	// right temperature for first layer (or all layer)
	define('PRINTER_PRM_FILE',			' -f ');	// file path
	define('PRINTER_PRM_EXCHANGE_E',	' -c ');	// exchange extrduer
	
	define('PRINTER_TYPE_MODELLIST',	'model');
	define('PRINTER_TYPE_GCODELIB',		'gcode');
	
	define('PRINTER_VALUE_MID_API_CALL',	'API');
// 	define('PRINTER_VALUE_DEFAULT_TEMPER',	230);
}

function Printer__calculateEstimation($length_filament) {
	$CI = &get_instance();
	$CI->load->helper('zimapi');
	
	return ($length_filament / ZIMAPI_VALUE_DEFAULT_SPEED + ZIMAPI_VALUE_DEFAULT_TL_OFFSET);
}

function Printer__getEstimation($array_filament, &$time_estimation) {
	$length_filament = 0;
	
	if (is_array($array_filament)) {
		foreach($array_filament as $length_cartridge) {
			$length_filament += $length_cartridge;
		}
	}
	else {
		$length_filament = $array_filament;
	}
	
	if ($length_filament == 0) {
		return FALSE;
	}
	else {
		$time_estimation = Printer__calculateEstimation($length_filament);
	}
	
	return TRUE;
}

function Printer_preparePrint($model_id, $need_prime = TRUE) {
	$cr = 0;
	$timelapse_length = 0;
	$gcode_path = '';
	
	$CI = &get_instance();
	$CI->load->helper(array('printlist', 'corestatus'));
	
	if (!in_array($model_id, array(
			CORESTATUS_VALUE_MID_CALIBRATION,
			CORESTATUS_VALUE_MID_PRIME_L,
			CORESTATUS_VALUE_MID_PRIME_R,
			PRINTER_VALUE_MID_API_CALL))) {
		$CI->load->helper('zimapi');
		
		// get printing length
		// if just sliced model, get value from temporary json file
		if ($model_id == CORESTATUS_VALUE_MID_SLICE) {
			$temp_json = array();
			
			$CI->load->helper('printerstate');
			
			if (ERROR_OK == PrinterState_getSlicedJson($temp_json)) {
				foreach($temp_json as $temp_filament) {
					if (array_key_exists(PRINTERSTATE_TITLE_NEED_L, $temp_filament)) {
						$timelapse_length += $temp_filament[PRINTERSTATE_TITLE_NEED_L];
					}
				}
			}
		}
		// if gcode file from user library
		else if (strpos($model_id, CORESTATUS_VALUE_MID_PREFIXGCODE) === 0) {
			$gcode_info = array();
			
			$CI->load->helper('printerstoring');
			$model_id = (int) substr($model_id, strlen(CORESTATUS_VALUE_MID_PREFIXGCODE));
			
			$gcode_info = PrinterStoring_getInfo("gcode", $model_id);
			if (!is_null($gcode_info) && array_key_exists(PRINTERSTORING_TITLE_LENG_R, $gcode_info)
			&& array_key_exists(PRINTERSTORING_TITLE_LENG_L, $gcode_info)) {
				$timelapse_length = $gcode_info[PRINTERSTORING_TITLE_LENG_R] + $gcode_info[PRINTERSTORING_TITLE_LENG_L];
			}
		}
		// if presliced model get from helper
		else if (strlen($model_id) == 32) {
			$model_info = array();
			
			if (ERROR_OK == ModelList__getDetailAsArray($model_id, $model_info) && !is_null($model_info)) {
				foreach (array(PRINTLIST_TITLE_LENG_F1, PRINTLIST_TITLE_LENG_F2) as $key_length) {
					if (array_key_exists($key_length, $model_info)) {
						$timelapse_length += $model_info[$key_length];
					}
				}
			}
		}
		if ($timelapse_length <= 0) {
			$timelapse_length = ZIMAPI_VALUE_DEFAULT_LENGTH;
		}
		
		// timelapse camera switch and prepare script, and write fps info into file
		if (file_exists(ZIMAPI_FILEPATH_POSTHEAT)) {
			$script_path = $CI->config->item('temp') . PRINTER_FN_POST_HEAT;
			$fps = ZIMAPI_VALUE_DEFAULT_TL_LENGTH * 10 / Printer__calculateEstimation($timelapse_length);
			$parameter = str_replace('{fps}',
					min(array(2.5, $fps * 2)), // take the limit value between 2 fps and 2 times of estimate fps
					ZIMAPI_PRM_CAMERA_PRINTSTART_TIMELAPSE);
			$command_addon = "\n" . str_replace('sudo nice', 'nice', $CI->config->item('camera')) . $parameter . "\n";
			
			copy(ZIMAPI_FILEPATH_POSTHEAT, $script_path);
			
			$fp = fopen($script_path, 'a');
			if ($fp) {
				fwrite($fp, $command_addon);
				fclose($fp);
			}
			chmod($script_path, 0775);
			//TODO think if we block processing when getting an error
		}
		else {
			$CI->load->helper('printerlog');
			PrinterLog_logError('prepare post heat script error', __FILE__, __LINE__);
		}
		
		// timelapse end part generation script + generation script
		foreach (array(
						PRINTER_FN_PRE_FINISH	=> ZIMAPI_FILEPATH_PREFINISH,
						PRINTER_FN_END_PRINT	=> ZIMAPI_FILEPATH_ENDPRINT,
				) as $tmp_file => $bin_path) {
			if (file_exists($bin_path)) {
				$script_path = $CI->config->item('temp') . $tmp_file;
				
				copy($bin_path, $script_path);
				chmod($script_path, 0775);
			}
			else {
				$CI->load->helper('printerlog');
				PrinterLog_logError('prepare script error: ' . $tmp_file, __FILE__, __LINE__);
			}
		}
	}
	else {
		foreach (array(PRINTER_FN_END_PRINT, PRINTER_FN_POST_HEAT, PRINTER_FN_PRE_FINISH) as $file_unlink) {
			@unlink($CI->config->item('temp') . $file_unlink);
		}
	}
	
	if ($need_prime == TRUE) {
		$cr = Printer_getFileFromModel(PRINTER_TYPE_MODELLIST, ModelList_codeModelHash(PRINTLIST_MODEL_PRINTPRIME_L),
				$gcode_path, PRINTER_FN_PRINTPRIME_L);
		if ($cr != ERROR_OK) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('prepare print prime left gcode error', __FILE__, __LINE__);
			return $cr;
		}
		$cr = Printer_getFileFromModel(PRINTER_TYPE_MODELLIST, ModelList_codeModelHash(PRINTLIST_MODEL_PRINTPRIME_R),
				$gcode_path, PRINTER_FN_PRINTPRIME_R);
		if ($cr != ERROR_OK) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('prepare print prime right gcode error', __FILE__, __LINE__);
			return $cr;
		}
	}
	else {
		@unlink($CI->config->item('temp') . PRINTER_FN_PRINTPRIME_L);
		@unlink($CI->config->item('temp') . PRINTER_FN_PRINTPRIME_R);
	}
	
// 	$cr = Printer_getFileFromModel(PRINTER_TYPE_MODELLIST, ModelList_codeModelHash(PRINTLIST_MODEL_CHARGE),
// 			$gcode_path, PRINTER_FN_CHARGE);
// 	if ($cr != ERROR_OK) {
// 		$CI->load->helper('printerlog');
// 		PrinterLog_logError('prepare charge gcode error', __FILE__, __LINE__);
// 		return $cr;
// 	}
// 	$cr = Printer_getFileFromModel(PRINTER_TYPE_MODELLIST, ModelList_codeModelHash(PRINTLIST_MODEL_RETRACT),
// 			$gcode_path, PRINTER_FN_RETRACT);
// 	if ($cr != ERROR_OK) {
// 		$CI->load->helper('printerlog');
// 		PrinterLog_logError('prepare retract gcode error', __FILE__, __LINE__);
// 		return $cr;
// 	}
	
	return ERROR_OK;
}

function Printer_printFromPrime($abb_extruder, $first_run = TRUE) {
	$name_prime = NULL;
	$gcode_path = NULL;
	$model_id = NULL;
	$ret_val = 0;
	$id_model = '';
	$array_info = array();
	$array_cartridge = array();
	$is_pva = 0;
	
	$CI = &get_instance();
	$CI->load->helper(array('printlist', 'corestatus', 'printerstate'));
	
	$ret_val = PrinterState_getCartridgeAsArray($array_cartridge, $abb_extruder);
	if ($ret_val != ERROR_OK) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read cartridge material error in printing prime: ' . $ret_val, __FILE__, __LINE__);
		
		return $ret_val;
	}
	else if ($array_cartridge[PRINTERSTATE_TITLE_MATERIAL] == PRINTERSTATE_DESP_MATERIAL_PVA) {
		$is_pva = 1;
	}
	
	switch ($abb_extruder . $is_pva) {
		case 'l0':
			if ($first_run == TRUE) {
				$name_prime = PRINTLIST_MODEL_PRIME_L;
			}
			else {
				$name_prime = PRINTLIST_MODEL_REPRIME_L;
			}
			
		case 'l1':
			$model_id = CORESTATUS_VALUE_MID_PRIME_L;
			if (!is_null($name_prime)) {
				break;
			}
			if ($first_run == TRUE) {
				$name_prime = PRINTLIST_MODEL_PRIME_L_PVA;
			}
			else {
				$name_prime = PRINTLIST_MODEL_REPRIME_L_PVA;
			}
			break;
			
		case 'r0':
			if ($first_run == TRUE) {
				$name_prime = PRINTLIST_MODEL_PRIME_R;
			}
			else {
				$name_prime = PRINTLIST_MODEL_REPRIME_R;
			}
			
		case 'r1':
			$model_id = CORESTATUS_VALUE_MID_PRIME_R;
			if (!is_null($name_prime)) {
				break;
			}
			if ($first_run == TRUE) {
				$name_prime = PRINTLIST_MODEL_PRIME_R_PVA;
			}
			else {
				$name_prime = PRINTLIST_MODEL_REPRIME_R_PVA;
			}
			break;
			
		default:
			return ERROR_WRONG_PRM;
			break; //never reach here
	}
	$id_model = ModelList_codeModelHash($name_prime);
	
	$ret_val = Printer_getFileFromModel(PRINTER_TYPE_MODELLIST, $id_model, $gcode_path, NULL, $array_info);
	if (($ret_val == ERROR_OK) && $gcode_path) {
		$array_filament = array();
		$array_temper = array();
		
		if (!Printer__getLengthFromJson(PRINTER_TYPE_MODELLIST, $array_info, $array_filament)) {
			return ERROR_INTERNAL; // $ret_val = ERROR_INTERNAL;
		}
		
		// modify the temperature of gcode file according to cartridge info
		$ret_val = Printer__changeGcode($gcode_path, $array_filament, FALSE, $array_temper, TRUE);
		if ($ret_val != ERROR_OK) {
			return $ret_val;
		}
		
		$ret_val = Printer_printFromFile($gcode_path, $model_id, $array_info[PRINTLIST_TITLE_TIME], FALSE, FALSE,
				$array_filament, $array_temper);
	}
	
	return $ret_val;
}

// function Printer_printFromCalibration() {
// 	$CI = &get_instance();
// 	$CI->load->helper('printlist');
	
// 	return Printer_printFromModel(ModelList_codeModelHash(PRINTLIST_MODEL_CALIBRATION));
// }

// function Printer_printFromModel($id_model, $stop_printing = FALSE) {
function Printer_printFromModel($id_model, $model_calibration, $exchange_extruder = FALSE, $array_temper = array()) {
	$gcode_path = NULL;
	$ret_val = 0;
	$array_info = array();
	
	$ret_val = Printer_getFileFromModel(PRINTER_TYPE_MODELLIST, $id_model, $gcode_path, NULL, $array_info);
	if (($ret_val == ERROR_OK) && $gcode_path) {
		$array_filament = array();
		
		if (Printer__getLengthFromJson(PRINTER_TYPE_MODELLIST, $array_info, $array_filament)) {
			if ($exchange_extruder) {
				Printer__inverseFilament($array_filament);
			}
			if ($model_calibration == TRUE) {
				$CI = &get_instance();
				$CI->load->helper('corestatus');
				
				$id_model = CORESTATUS_VALUE_MID_CALIBRATION;
			}
		}
		else {
			return ERROR_INTERNAL; // $ret_val = ERROR_INTERNAL;
		}
		
		// modify the temperature of gcode file according to cartridge info
		$ret_val = Printer__changeGcode($gcode_path, $array_filament, $exchange_extruder, $array_temper);
		if ($ret_val != ERROR_OK) {
			return $ret_val;
		}
		
		$ret_val = Printer_printFromFile($gcode_path, $id_model, $array_info[PRINTLIST_TITLE_TIME], TRUE,
				$exchange_extruder, $array_filament, $array_temper);
	}
	
	return $ret_val;
}

function Printer_printFromSlice($exchange_extruder = FALSE, $array_temper = array()) {
	$ret_val = 0;
	$file_temp_data = NULL;
	$data_json = array();
	$array_filament = array();
	
	$CI = &get_instance();
	$CI->load->helper('slicer');
	$gcode_path = $CI->config->item('temp') . SLICER_FILE_MODEL;
	
	if (!file_exists($gcode_path)) {
		return ERROR_NO_SLICED;
	}
	
	$CI->load->helper(array('printerstate', 'corestatus'));
	
	if (ERROR_OK != PrinterState_getSlicedJson($data_json)) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read temp data file error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	else {
		// move all the verification of filament into printFromFile by array_filament
		foreach ($data_json as $abb_filament => $array_temp) {
			$array_filament[$abb_filament] = $array_temp[PRINTERSTATE_TITLE_NEED_L];
		}
		if ($exchange_extruder) {
			Printer__inverseFilament($array_filament);
		}
	}
	
	// modify the temperature of gcode file according to cartridge info
	$ret_val = Printer__changeGcode($gcode_path, $array_filament, $exchange_extruder, $array_temper);
	if ($ret_val != ERROR_OK) {
		return $ret_val;
	}
	
	$ret_val = Printer_printFromFile($gcode_path, CORESTATUS_VALUE_MID_SLICE, 0, TRUE, $exchange_extruder,
			$array_filament, $array_temper);
	
	return $ret_val;
}

function Printer_printFromLibrary($id_gcode, $exchange_extruder = FALSE, $array_temper = array()) {
	$ret_val = 0;
	$array_info = NULL;
	$array_filament = array();
	$CI = &get_instance();
	
	$ret_val = Printer_getFileFromModel(PRINTER_TYPE_GCODELIB, $id_gcode, $gcode_path, NULL, $array_info);
	if (($ret_val == ERROR_OK) && $gcode_path) {
		$array_filament = array();
		
		if (Printer__getLengthFromJson(PRINTER_TYPE_GCODELIB, $array_info, $array_filament)) {
			if ($exchange_extruder) {
				Printer__inverseFilament($array_filament);
			}
		}
		else {
			return ERROR_INTERNAL; // $ret_val = ERROR_INTERNAL;
		}
		
		// modify the temperature of gcode file according to cartridge info
		$ret_val = Printer__changeGcode($gcode_path, $array_filament, $exchange_extruder, $array_temper);
		if ($ret_val != ERROR_OK) {
			return $ret_val;
		}
		
		$CI->load->helper('corestatus');
		$ret_val = Printer_printFromFile($gcode_path, CORESTATUS_VALUE_MID_PREFIXGCODE . $id_gcode, 0,
				TRUE, $exchange_extruder, $array_filament, $array_temper);
		
		// stats info
		$CI->load->helper('printerlog');
		PrinterLog_statsLibraryGcode(PRINTERLOG_STATS_LABEL_PRINT, count($array_filament));
	}
	
	return $ret_val;
}

function Printer_printFromFile($gcode_path, $model_id, $time_estimation, $need_prime = TRUE,
		$exchange_extruder = FALSE, $array_filament = array(), $array_temper = array()) {
	global $CFG;
	$command = '';
	$output = array();
	$temper_json = array();
	$ret_val = 0;
	$stats_info = array();
	
	$CI = &get_instance();
	$CI->load->helper(array('printerstate', 'errorcode', 'corestatus', 'printerlog', 'detectos'));
	
	// check if we have no file
	if (!file_exists($gcode_path)) {
		return ERROR_INTERNAL;
	}

	// only check if we are in printing when we are not called stopping printing
// 	if ($stop_printing == FALSE) {
		// check if in printing
		$ret_val = PrinterState_checkInPrint();
		if ($ret_val == TRUE) {
// 			return ERROR_IN_PRINT;
			PrinterLog_logMessage('already in printing', __FILE__, __LINE__);
			return ERROR_BUSY_PRINTER;
		}
// 	}
	
	// check extruder number
	if ($CI->config->item('nb_extruder') < 2) {
		$tmp_array = array();
		
		$command = $CFG->config['gcanalyser'] . $gcode_path;
		exec($command, $output, $ret_val);
		if ($ret_val != ERROR_NORMAL_RC_OK) {
			PrinterLog_logError('gcanalyser error', __FILE__, __LINE__);
			return ERROR_INTERNAL;
		}
		$tmp_array = json_decode($output[0], TRUE);
		if ($tmp_array['N'] > $CI->config->item('nb_extruder')) {
			PrinterLog_logMessage('no enough extruder', __FILE__, __LINE__);
			return ERROR_INTERNAL;
		}
	}

	// check if having enough filament
	$ret_val = PrinterState_checkFilaments($array_filament);
	if ($ret_val != ERROR_OK) {
		return $ret_val;
	}
	
	if ($time_estimation == 0) {
		$ret_val = Printer__getEstimation($array_filament, $time_estimation);
		if ($ret_val != TRUE) {
			PrinterLog_logError('system can not get estimation time');
			return ERROR_INTERNAL;
		}
	}
	
	// prepare subprinting gcode files and scripts
	$ret_val = Printer_preparePrint($model_id, $need_prime);
	if ($ret_val != ERROR_OK) {
		return $ret_val;
	}
	
// 	if ($stop_printing == FALSE) {
		if ($CFG->config['simulator']) {
			// just set temperature for simulation
			PrinterState_setExtruder('r');
			PrinterState_setTemperature(210);
			PrinterState_setExtruder('l');
			PrinterState_setTemperature(200);
			PrinterState_setExtruder('r');
		}
		
		// change status json file
		foreach($array_temper as $abb_filament => $tmp_temper) {
			if (!array_key_exists($abb_filament, $array_filament) || $array_filament[$abb_filament] <= 0) {
				$temper_json[$abb_filament] = NULL;
			}
			else {
				$temper_json[$abb_filament] = $array_temper[$abb_filament];
			}
		}
		$ret_val = CoreStatus_setInPrinting($model_id, $time_estimation, $exchange_extruder, $temper_json);
// 	}
// 	else {
// 		$ret_val = CoreStatus_setInCanceling();
// 	}
	if ($ret_val == FALSE) {
		return ERROR_INTERNAL;
	}
	
	// stats info
	$stats_info[PRINTERLOG_STATS_MODEL] = $model_id;
	foreach($temper_json as $abb_filament => $tmp_temper) {
		if (isset($tmp_temper)) {
			$json_cartridge = array();
			$arrkey_type = PRINTERLOG_STATS_FILA_TYPE_R;
			$arrkey_color = PRINTERLOG_STATS_FILA_COLOR_R;
			
			if ($abb_filament == 'l') {
				$arrkey_type = PRINTERLOG_STATS_FILA_TYPE_L;
				$arrkey_color = PRINTERLOG_STATS_FILA_COLOR_L;
			}
			
			if (ERROR_OK == PrinterState_getCartridgeAsArray($json_cartridge, $abb_filament)) {
				$stats_info[$arrkey_type] = $json_cartridge[PRINTERSTATE_TITLE_MATERIAL];
				$stats_info[$arrkey_color] = $json_cartridge[PRINTERSTATE_TITLE_COLOR];
			}
		}
	}
	PrinterLog_statsPrint(PRINTERLOG_STATS_ACTION_START, $stats_info);
	
	// pass gcode to printer
//	if (!PrinterState_beforeFileCommand()) {
//		return ERROR_INTERNAL;
//	}
	// use different command for priming
	if ($need_prime == FALSE) {
		$command = PrinterState_getPrintCommand($array_filament, TRUE, TRUE) . $gcode_path;
	}
	else {
		$command = PrinterState_getPrintCommand($array_filament) . $gcode_path;
	}
	// 		exec($command, $output, $ret_val);
	// 		if ($ret_val != ERROR_NORMAL_RC_OK) {
	// 			return ERROR_INTERNAL;
	// 		}
	if ($CFG->config['simulator'] && DectectOS_checkWindows()) {
		pclose(popen($command, 'r')); // only for windows arcontrol client
		PrinterLog_logArduino($command);
	}
	else {
// 		exec($command, $output, $ret_val);
		pclose(popen($command . ' > ' . PRINTERSTATE_FILE_PRINTLOG . ' &', 'r'));
// 		if (!PrinterState_filterOutput($output)) {
// 			PrinterLog_logError('filter arduino output error', __FILE__, __LINE__);
// 			return ERROR_INTERNAL;
// 		}
// 		if ($ret_val != ERROR_NORMAL_RC_OK) {
// 			return $ret_val;
// 		}
// 		PrinterLog_logArduino($command, $output);
		PrinterLog_logArduino($command);
	}
//	if (!PrinterState_afterFileCommand()) {
//		return ERROR_INTERNAL;
//	}

	return ERROR_OK;
}

function Printer_stopPrint() {
// 	$stats_info = array();
	$CI = &get_instance();
	$CI->load->helper('corestatus');
	
	// check if we are in canceling / printing in json file
	$cr = CoreStatus_checkInIdle($status_current);
	if ($cr == FALSE) {
		if ($status_current == CORESTATUS_VALUE_CANCEL) {
			// in canceling
			return TRUE;
		}
		else if ($status_current != CORESTATUS_VALUE_PRINT) {
			// in other status
			$CI->load->helper('printerlog');
			PrinterLog_logError('no printing / canceling status when calling canceling', __FILE__, __LINE__);
			return FALSE;
		}
		else {
			// in printing
// 			$CI->load->helper(array('printlist', 'printerstate', 'zimapi'));
			$CI->load->helper(array('printerstate', 'zimapi'));
			
			// change end print script if we cancel printing to not generate timelapse
			if (file_exists($CI->config->item('temp') . PRINTER_FN_END_PRINT) && file_exists(ZIMAPI_FILEPATH_ENDCANCEL)) {
				copy(ZIMAPI_FILEPATH_ENDCANCEL, $CI->config->item('temp') . PRINTER_FN_END_PRINT);
				chmod($CI->config->item('temp') . PRINTER_FN_END_PRINT, 0775);
			}
			
// 			//stats info
// 			$stats_info = PrinterState_prepareStatsPrintLabel();
// 			PrinterLog_statsPrint(PRINTERLOG_STATS_ACTION_CANCEL, $stats_info);
			
			// call stop printing gcode status
			$cr = PrinterState_stopPrinting();
			if ($cr != ERROR_OK) {
				// log error here
				$CI->load->helper('printerlog');
				PrinterLog_logError('stop gcode failed', __FILE__, __LINE__);
				return FALSE;
			}
			
			// set status in cancelling
			if (!CoreStatus_setInCanceling()) {
				$CI->load->helper('printerlog');
				PrinterLog_logError('can not set status in cancel', __FILE__, __LINE__);
				return FALSE;
			}
// 			// start to call printing of a special model to reset printer
// 			$cr = Printer_printFromModel(ModelList_codeModelHash(PRINTLIST_MODEL_CANCEL), TRUE);
// 			if ($cr == ERROR_OK) {
// 				return TRUE;
// 			}
// 			else {
// 				// log error here
// 				$CI->load->helper('printerlog');
// 				PrinterLog_logError('start printing canceling model failed', __FILE__, __LINE__);
// 				return FALSE;
// 			}
			return TRUE;
		}
	}
	else {
		// in idle
		$CI->load->helper('printerlog');
		PrinterLog_logError('in idle when calling canceling', __FILE__, __LINE__);
		return FALSE;
	}
	
	return FALSE; // never reach here
}

function Printer_pausePrint() {
	$CI = &get_instance();
	$CI->load->helper(array('corestatus', 'printerstate'));
	
	if (CoreStatus_checkInPause()) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('call pause when in pausing', __FILE__, __LINE__);
		return TRUE;
	}
	else {
		$cr = CoreStatus_checkInIdle($status_current);
		if ($cr == FALSE && $status_current == CORESTATUS_VALUE_PRINT) {
			$cr = PrinterState_pausePrinting();
			if ($cr == ERROR_OK) {
				CoreStatus_setInPause();
				return TRUE;
			}
			else {
				$CI->load->helper('printerlog');
				PrinterLog_logError('pause printing error', __FILE__, __LINE__);
			}
		}
		else {
			$CI->load->helper('printerlog');
			PrinterLog_logError('call pause when not in printing: ' . $status_current, __FILE__, __LINE__);
		}
	}
	
	return FALSE;
}

function Printer_resumePrint() {
	$CI = &get_instance();
	$CI->load->helper(array('corestatus', 'printerstate'));

	$cr = CoreStatus_checkInIdle($status_current);
	if ($cr == FALSE && $status_current == CORESTATUS_VALUE_PRINT) {
		if (CoreStatus_checkInPause()) {
			$cr = PrinterState_resumePrinting();
			if ($cr == ERROR_OK) {
				CoreStatus_setInPause(FALSE);
				return TRUE;
			}
			else {
				$CI->load->helper('printerlog');
				PrinterLog_logError('resume printing error', __FILE__, __LINE__);
			}
		}
		else {
			$CI->load->helper('printerlog');
			PrinterLog_logError('call resume when not in pausing', __FILE__, __LINE__);
			return TRUE;
		}
	}
	else {
		$CI->load->helper('printerlog');
		PrinterLog_logError('call resume when not in printing: ' . $status_current, __FILE__, __LINE__);
	}
	
	return FALSE;
}

// return TRUE only when we are in printing
function Printer_checkPrintStatus(&$return_data) {
	global $CFG;
	$data_status = array();
	$temper_l = 0;
	$temper_r = 0;
	$current_phase = -1;
	
	$CI = &get_instance();
	$CI->load->helper(array('printerstate', 'corestatus'));
	
	// check status if we are not in printing
	$data_status = PrinterState_checkStatusAsArray();
	if ($data_status[PRINTERSTATE_TITLE_STATUS] != CORESTATUS_VALUE_PRINT) {
		return FALSE;
	}
	
	// get temperatures of extruders
	if (array_key_exists(PRINTERSTATE_TITLE_EXTEND_PRM, $data_status)) {
		if (array_key_exists(PRINTERSTATE_TITLE_EXT_TEMP_L, $data_status[PRINTERSTATE_TITLE_EXTEND_PRM])) {
			$temper_l = $data_status[PRINTERSTATE_TITLE_EXTEND_PRM][PRINTERSTATE_TITLE_EXT_TEMP_L];
		}
		if (array_key_exists(PRINTERSTATE_TITLE_EXT_TEMP_R, $data_status[PRINTERSTATE_TITLE_EXTEND_PRM])) {
			$temper_r = $data_status[PRINTERSTATE_TITLE_EXTEND_PRM][PRINTERSTATE_TITLE_EXT_TEMP_R];
		}
	}
	else {
		// log internal error
		$CI->load->helper('printerlog');
		PrinterLog_logMessage('getting temperatures in printing returns no tempers', __FILE__, __LINE__);
// 		return FALSE;
	}
	
	if (isset($data_status[PRINTERSTATE_TITLE_EXTEND_PRM][PRINTERSTATE_TITLE_EXT_OPER])) {
		switch ($data_status[PRINTERSTATE_TITLE_EXTEND_PRM][PRINTERSTATE_TITLE_EXT_OPER]) {
			case PRINTERSTATE_VALUE_PRINT_OPERATION_HEAT:
				$current_phase = 0;
				break;
				
			case PRINTERSTATE_VALUE_PRINT_OPERATION_PRINT:
				$current_phase = 1;
				break;
				
			case PRINTERSTATE_VALUE_PRINT_OPERATION_END:
				$current_phase = 2;
				break;
				
			default:
				$current_phase = -1;
				break;
		}
	}
	
	$return_data = array(
			'print_percent'	=> $data_status[PRINTERSTATE_TITLE_PERCENT],
			'print_temperL'	=> $temper_l,
			'print_temperR'	=> $temper_r,
			'print_tpassed'	=> $data_status[PRINTERSTATE_TITLE_PASSTIME],
			'print_inPhase'	=> $current_phase,
	);
	
	// get time remaining if exists
	if (isset($data_status[PRINTERSTATE_TITLE_DURATION])) {
		$return_data['print_remain'] = $data_status[PRINTERSTATE_TITLE_DURATION];
	}
	
	return TRUE;
}

function Printer_checkCancelStatus() {
	$data_status = array();
	$temper_status = array();

	$CI = &get_instance();
	$CI->load->helper(array('printerstate', 'corestatus'));

	// check status if we are not in canceling
	$data_status = PrinterState_checkStatusAsArray(FALSE);
	if ($data_status[PRINTERSTATE_TITLE_STATUS] != CORESTATUS_VALUE_CANCEL) {
		$CI->load->helper('printerlog');
		PrinterLog_logMessage('not in canceling when checking cancel status', __FILE__, __LINE__);
		return FALSE;
	}
	
	return TRUE;
}

function Printer_checkPauseStatus() {
	$status_current = NULL;
	$temper_status = array();

	$CI = &get_instance();
	$CI->load->helper('corestatus');

	// check status if we are not in canceling
	CoreStatus_checkInIdle($status_current);
	if ($status_current == CORESTATUS_VALUE_PRINT && CoreStatus_checkInPause()) {
		return TRUE;
	}
	
	return FALSE;
}

function Printer_getFileFromModel($type_model, $id_model, &$gcode_path, $filename = NULL, &$array_info = NULL) {
	$model_path = NULL;
	$bz2_path = NULL;
	$command = '';
	$output = array();
	$ret_val = 0;
	$filename_json = NULL;
	$filename_bz2 = NULL;
	$filename_gcode = NULL;
	
	$CI = &get_instance();
	switch ($type_model) {
		case PRINTER_TYPE_MODELLIST:
			$CI->load->helper('printlist');
			
			$filename_json = PRINTLIST_FILE_JSON;
			$filename_bz2 = PRINTLIST_FILE_GCODE_BZ2;
			$filename_gcode = PRINTLIST_FILE_GCODE;
			
			$model_cr = ModelList__find($id_model, $model_path);
			
			// get json info
			if (is_array($array_info)) {
				$json_data = array();
				
				try {
					$json_data = json_read($model_path . $filename_json, TRUE);
					if ($json_data['error']) {
						throw new Exception('read json error');
					}
				} catch (Exception $e) {
					return ERROR_INTERNAL;
				}
				
				$array_info = $json_data['json'];
			}
			break;
			
		case PRINTER_TYPE_GCODELIB:
			$CI->load->helper('printerstoring');
			
			$tmp_array = NULL;
			$filename_json = PRINTERSTORING_FILE_INFO_JSON;
			$filename_bz2 = PRINTERSTORING_FILE_GCODE_BZ2;
			$filename_gcode = PRINTERSTORING_FILE_GCODE_EXT;
			
			$array_info = PrinterStoring_getInfo('gcode', $id_model, $model_path);
			if (is_null($array_info)) {
				$model_cr = ERROR_WRONG_PRM;
			}
			else {
				$model_cr = ERROR_OK;
			}
		
	}
	
	if (($model_cr == ERROR_OK) && $model_path) {
		$ret_val = 0;
		
//		//if we don't fix the filename of gcode
// 		try {
// 			$json_data = json_read($model_path . PRINTLIST_FILE_JSON);
// 			if ($json_data['error']) {
// 				throw new Exception('read json error');
// 			}
// 		} catch (Exception $e) {
// 			return ERROR_INTERNAL;
// 		}
// 		$gcode_path = $json_data['json'][PRINTLIST_TITLE_GCODE];
		$bz2_path = $model_path . $filename_bz2;
		$filename = is_null($filename) ? $filename_gcode : $filename;
		$gcode_path = $CI->config->item('temp') . $filename;
		$command = 'bzip2 -dkcf ' . $bz2_path . ' > ' . $gcode_path;
		@unlink($gcode_path); // delete old file
		exec($command, $output, $ret_val);
		if ($ret_val != ERROR_NORMAL_RC_OK) {
			return ERROR_INTERNAL;
		}
		
		return ERROR_OK;
	}
	else {
		return ERROR_UNKNOWN_MODEL;
	}
	
	return ERROR_OK; // never reach here
}

// internal function
function Printer__getLengthFromJson($array_type, $array_info, &$array_filament) {
	$key_length_r = NULL;
	$key_length_l = NULL;
	$CI = &get_instance();
	
	switch($array_type) {
		case PRINTER_TYPE_MODELLIST:
			$CI->load->helper('printlist');
			$key_length_r = PRINTLIST_TITLE_LENG_F1;
			$key_length_l = PRINTLIST_TITLE_LENG_F2;
			break;
			
		case PRINTER_TYPE_GCODELIB:
			$CI->load->helper('printerstoring');
			$key_length_r = PRINTERSTORING_TITLE_LENG_R;
			$key_length_l = PRINTERSTORING_TITLE_LENG_L;
			break;
			
		default:
			return FALSE;
			break; // never reach here
	}
	
	if (!is_array($array_info)
			|| !array_key_exists($key_length_r, $array_info)
			|| !array_key_exists($key_length_l, $array_info)) {
		return FALSE;
	}
	$array_filament = array();
	
	if ($array_info[$key_length_r] > 0) {
		$array_filament['r'] = $array_info[$key_length_r];
	}
	if ($array_info[$key_length_l] > 0) {
		$array_filament['l'] = $array_info[$key_length_l];
	}
	
	return TRUE;
}

function Printer__inverseFilament(&$array_filament) {
	$array_temp = $array_filament;
	
	$array_filament = array(); // reset array
	foreach ($array_temp as $abb_filament => $value_filament) {
		switch($abb_filament) {
			case 'r':
				$array_filament['l'] = $value_filament;
				break;
				
			case 'l':
				$array_filament['r'] = $value_filament;
				break;
				
			default:
				$CI = &get_instance();
				$CI->load->helper('printerlog');
				PrinterLog_logError('unknown abb filament: ' . $abb_filament, __FILE__, __LINE__);
				break;
		}
	}
	
	return;
}

function Printer__changeGcode(&$gcode_path, $array_filament = array(), $exchange_extruder = FALSE, &$array_temper = array(), $temper_material = FALSE) {
	$temp_r = 0; // right normal temper
	$temp_rs = 0; // right start temper
	$temp_l = 0; // left normal temper
	$temp_ls = 0; // left start temper
	$cr = 0;
	$command = NULL;
	$output = array();
	$json_cartridge = array();
	$CI = &get_instance();
	
	$CI->load->helper('printerstate');
	
	if ($exchange_extruder) {
		$command = $CI->config->item('gcdaemon')
				. PRINTER_PRM_EXCHANGE_E . PRINTER_PRM_FILE . $gcode_path . ' > ' . $gcode_path . '.new';
		
		// debug message for test
		$CI->load->helper('printerlog');
		PrinterLog_logDebug('change extruder: ' . $command, __FILE__, __LINE__);
		
		@unlink($gcode_path . '.new'); // delete old file
		exec($command, $output, $cr);
		if ($cr != ERROR_NORMAL_RC_OK) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('change extruder error', __FILE__, __LINE__);
			return ERROR_INTERNAL;
		}
		
		$gcode_path = $gcode_path . '.new';
	}
	
	// temporary change - make it possible to change temperature not according to cartridge
	//TODO remove me when it is necessary
	if (array_key_exists('r', $array_temper) && $array_temper['r'] > 0) {
		$temp_r = $array_temper['r'];
		$temp_rs = $temp_r + 10;
// 		if ($temp_r > $temp_rs) {
// 			$temp_rs = $temp_r;
// 		}
	}
	// temporary change end
	else if (!array_key_exists('r', $array_filament) || $array_filament['r'] <= 0) {
		// ignore the cartridge which we do not need
		$CI->load->helper('slicer');
		$temp_r = SLICER_VALUE_DEFAULT_TEMPER;
		$temp_rs = SLICER_VALUE_DEFAULT_FIRST_TEMPER;
	}
	else {
		$cr = PrinterState_getCartridgeAsArray($json_cartridge, 'r');
		if ($cr == ERROR_OK) {
			if ($temper_material) {
				//TODO need to reunion all getting temperature functions
				switch ($json_cartridge[PRINTERSTATE_TITLE_MATERIAL]) {
					case PRINTERSTATE_DESP_MATERIAL_PLA:
						$temp_rs = PRINTERSTATE_VALUE_FILAMENT_PLA_LOAD_TEMPER;
						break;
						
					case PRINTERSTATE_DESP_MATERIAL_ABS:
						$temp_rs = PRINTERSTATE_VALUE_FILAMENT_ABS_LOAD_TEMPER;
						break;
						
					case PRINTERSTATE_DESP_MATERIAL_PVA:
						$temp_rs = PRINTERSTATE_VALUE_FILAMENT_PVA_LOAD_TEMPER;
						break;
						
					default:
						PrinterLog_logError('unknown filament type in priming', __FILE__, __LINE__);
// 						return ERROR_INTERNAL;
						$temp_rs = SLICER_VALUE_DEFAULT_FIRST_TEMPER;
						break;
				}
				$temp_r = $temp_rs;
			}
			else {
				$temp_r = $json_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER];
				$temp_rs = $json_cartridge[PRINTERSTATE_TITLE_EXT_TEMP_1];
			}
		}
		else if ($cr == ERROR_MISS_RIGT_CART) {
			$CI->load->helper('slicer');
			$temp_r = SLICER_VALUE_DEFAULT_TEMPER;
			$temp_rs = SLICER_VALUE_DEFAULT_FIRST_TEMPER;
// 			$temp_r = $temp_rs = PRINTER_VALUE_DEFAULT_TEMPER;
		}
	}
	
	if ($temp_r * $temp_rs == 0) {
		// we have at least one value not initialised to call change temper program
		return ($cr == ERROR_OK) ? ERROR_INTERNAL : $cr;
	}
	else {
		$array_temper['r'] = $temp_r;
	}
	
	if ($CI->config->item('nb_extruder') >= 2) {
		// make it possible to change temperature not according to cartridge
		if (array_key_exists('l', $array_temper) && $array_temper['l'] > 0) {
			$temp_l = $array_temper['l'];
			$temp_ls = $temp_l + 10;
		}
		else if (!array_key_exists('l', $array_filament) || $array_filament['l'] <= 0) {
			// ignore the cartridge which we do not need
			$CI->load->helper('slicer');
			$temp_l = SLICER_VALUE_DEFAULT_TEMPER;
			$temp_ls = SLICER_VALUE_DEFAULT_FIRST_TEMPER;
		}
		else {
			$cr = PrinterState_getCartridgeAsArray($json_cartridge, 'l');
			if ($cr == ERROR_OK) {
				if ($temper_material) {
					//TODO need to reunion all getting temperature functions
					switch ($json_cartridge[PRINTERSTATE_TITLE_MATERIAL]) {
						case PRINTERSTATE_DESP_MATERIAL_PLA:
							$temp_ls = PRINTERSTATE_VALUE_FILAMENT_PLA_LOAD_TEMPER;
							break;
							
						case PRINTERSTATE_DESP_MATERIAL_ABS:
							$temp_ls = PRINTERSTATE_VALUE_FILAMENT_ABS_LOAD_TEMPER;
							break;
							
						case PRINTERSTATE_DESP_MATERIAL_PVA:
							$temp_ls = PRINTERSTATE_VALUE_FILAMENT_PVA_LOAD_TEMPER;
							break;
							
						default:
							PrinterLog_logError('unknown filament type in priming', __FILE__, __LINE__);
// 							return ERROR_INTERNAL;
							$temp_ls = SLICER_VALUE_DEFAULT_FIRST_TEMPER;
							break;
					}
					$temp_l = $temp_ls;
				}
				else {
					$temp_l = $json_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER];
					$temp_ls = $json_cartridge[PRINTERSTATE_TITLE_EXT_TEMP_1];
				}
			}
			else if ($cr == ERROR_MISS_LEFT_CART) {
				$CI->load->helper('slicer');
				$temp_l = SLICER_VALUE_DEFAULT_TEMPER;
				$temp_ls = SLICER_VALUE_DEFAULT_FIRST_TEMPER;
			}
		}
		
		if ($temp_l * $temp_ls == 0) {
			// we have at least one value not initialised to call change temper program
			return ($cr == ERROR_OK) ? ERROR_INTERNAL : $cr;
		}
		else {
			$array_temper['l'] = $temp_l;
		}
	}
	
	$command = $CI->config->item('gcdaemon')
			. PRINTER_PRM_TEMPER_R_F . $temp_rs . PRINTER_PRM_TEMPER_R_N . $temp_r
			. PRINTER_PRM_TEMPER_L_F . $temp_ls . PRINTER_PRM_TEMPER_L_N . $temp_l
			. PRINTER_PRM_FILE . $gcode_path . ' > ' . $gcode_path . '.new';
	
	// debug message for test
	$CI->load->helper('printerlog');
	PrinterLog_logDebug('change temperature: ' . $command, __FILE__, __LINE__);
	
	@unlink($gcode_path . '.new'); // delete old file
	exec($command, $output, $cr);
	if ($cr != ERROR_NORMAL_RC_OK) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('change temperature error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	$gcode_path = $gcode_path . '.new';
	
	return ERROR_OK;
}
