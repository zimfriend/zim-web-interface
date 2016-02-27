<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->helper('detectos');

if (!defined('PRINTERLOG_STATS_TITLE_SERIAL')) {
	define('PRINTERLOG_STATS_TITLE_SERIAL',		'printersn');
	define('PRINTERLOG_STATS_TITLE_CATEGORY',	'category');
	define('PRINTERLOG_STATS_TITLE_ACTION',		'action');
	define('PRINTERLOG_STATS_TITLE_LABEL',		'label');
	define('PRINTERLOG_STATS_TITLE_VALUE',		'value');
	
	define('PRINTERLOG_STATS_MODEL',			'model');
	define('PRINTERLOG_STATS_FILA_TYPE_L',		'left_filament_type');
	define('PRINTERLOG_STATS_FILA_TYPE_R',		'right_filament_type');
	define('PRINTERLOG_STATS_FILA_COLOR_L',		'left_filament_color');
	define('PRINTERLOG_STATS_FILA_COLOR_R',		'right_filament_color');
	define('PRINTERLOG_STATS_FILA_TEMPER_L',	'left_filament_temperature');
	define('PRINTERLOG_STATS_FILA_TEMPER_R',	'right_filament_temperature');
	define('PRINTERLOG_STATS_FILA_USED_L',		'left_filament_used');
	define('PRINTERLOG_STATS_FILA_USED_R',		'right_filament_used');
	define('PRINTERLOG_STATS_PRESET',			'preset');
	define('PRINTERLOG_STATS_SLICE_ERROR',		'error');
	define('PRINTERLOG_STATS_NAVIG_URL',		'URL');
	define('PRINTERLOG_STATS_NAVIG_AGENT',		'user_agent');
	define('PRINTERLOG_STATS_NAVIG_REMOTE',		'tromboning');
	define('PRINTERLOG_STATS_SLICE_SERVER',		'server');
	
	define('PRINTERLOG_STATS_CATEGORY_PRINTER',		'printer');
	define('PRINTERLOG_STATS_CATEGORY_PRINT',		'print');
	define('PRINTERLOG_STATS_CATEGORY_SLICE',		'slice');
	define('PRINTERLOG_STATS_CATEGORY_UPLOAD',		'upload');
	define('PRINTERLOG_STATS_CATEGORY_TIMELAPSE',	'time-lapse');
	define('PRINTERLOG_STATS_CATEGORY_LIBRARY',		'library');
	define('PRINTERLOG_STATS_CATEGORY_WEB',			'web');
	
	define('PRINTERLOG_STATS_ACTION_OFF',		'off');
	define('PRINTERLOG_STATS_ACTION_START',		'start');
	define('PRINTERLOG_STATS_ACTION_END',		'end');
	define('PRINTERLOG_STATS_ACTION_CANCEL',	'cancel');
	define('PRINTERLOG_STATS_ACTION_ERROR',		'error');
	define('PRINTERLOG_STATS_ACTION_SHARE',		'share');
	define('PRINTERLOG_STATS_ACTION_MODEL',		'model');
	define('PRINTERLOG_STATS_ACTION_PRINT',		'print');
	define('PRINTERLOG_STATS_ACTION_CLICK',		'click');
	define('PRINTERLOG_STATS_ACTION_REQUEST',	'request');
	define('PRINTERLOG_STATS_ACTION_RESIZE',	'resize');
	define('PRINTERLOG_STATS_ACTION_UPLOADED',	'uploaded');
	define('PRINTERLOG_STATS_ACTION_DOWNLOAD',	'download');
	
	define('PRINTERLOG_STATS_LABEL_EMAIL',		'email');
	define('PRINTERLOG_STATS_LABEL_YOUTUBE',	'youtube');
	define('PRINTERLOG_STATS_LABEL_FACEBOOK',	'facebook');
	define('PRINTERLOG_STATS_LABEL_LOAD',		'load');
	define('PRINTERLOG_STATS_LABEL_DELETE',		'delete');
	define('PRINTERLOG_STATS_LABEL_PRINT',		'print');
	define('PRINTERLOG_STATS_LABEL_SUPPORT',	'support');
	define('PRINTERLOG_STATS_LABEL_UPGRADE',	'upgrade');
	define('PRINTERLOG_STATS_LABEL_SHOP',		'shop');
	define('PRINTERLOG_STATS_LABEL_3DSLASH',	'3dslash');
	
	define('PRINTERLOG_STATS_VALUE_ON',		'on');
	define('PRINTERLOG_STATS_VALUE_OFF',	'off');
	define('PRINTERLOG_STATS_VALUE_LOCAL',	'local');
	define('PRINTERLOG_STATS_VALUE_REMOTE',	'remote');
	
	define('PRINTERLOG_STATS_TEMPFILE_FOLDER',	'php_stats');
	define('PRINTERLOG_STATS_TEMPFILE_PREFIX',	'st_');
	define('PRINTERLOG_STATS_SITEUTILS_CMD',	' stats ');
	
	define('PRINTERLOG_STATS_TMPFILE_SLICE_UPLOAD_END',		$CI->config->item('temp') . '/remote_slice/upload_end');
	define('PRINTERLOG_STATS_TMPFILE_SLICE_DOWNLOAD_START',	$CI->config->item('temp') . '/remote_slice/download_start');
	define('PRINTERLOG_STATS_SESSION_GUID_FILE',	$CI->config->item('temp') . '/stats_session_guid.txt');
	
	if (DectectOS_checkWindows()) {
		define('PRINTERLOG_STATS_FILEPATH_OFF',	$CI->config->item('conf') . 'stats_off');
		define('PRINTERLOG_STATS_FILEPATH_LOG',	$CI->config->item('temp') . 'stats.log');
	}
	else {
		define('PRINTERLOG_STATS_FILEPATH_OFF',	'/config/conf/stats_off');
		define('PRINTERLOG_STATS_FILEPATH_LOG',	'/var/www/tmp/stats.log');
	}
}

// call error list if we want
// $CI = &get_instance();
// $CI->load->helper(array (
// 		'errorcode',
// ));

// log for arduino part
function PrinterLog_logArduino($command, $output = '') {
	return PrinterLog__logToFile('log_arduino', $command, $output); 
}

// log for debug test
// debug 3 > message 2 > error 1 > none 0 (anything else)
function PrinterLog_logDebug($msg, $file = NULL, $line = NULL, $need_trim = TRUE) {
	global $CFG;
	if ($CFG->config['log_level'] >= 3) {
		$location = '';
		if (!is_null($file) && !is_null($line)) {
			$location = "\t(" . PrinterLog__filterAppPath($file) . ' ' . $line . ')';
		}
		
		return PrinterLog__logToDebugFile($CFG->config['log_file'], $msg, "DBG: ", $location, $need_trim);
	}
	else {
		return FALSE;
	}
}


function PrinterLog_logMessage($msg, $file = NULL, $line = NULL, $need_trim = TRUE) {
	global $CFG;
	if ($CFG->config['log_level'] >= 2) {
		$location = '';
		if (!is_null($file) && !is_null($line)) {
			$location = "\t(" . PrinterLog__filterAppPath($file) . ' ' . $line . ')';
		}
		
		return PrinterLog__logToDebugFile($CFG->config['log_file'], $msg, "MSG: ", $location, $need_trim);
	}
	else {
		return FALSE;
	}
}


function PrinterLog_logError($msg, $file = NULL, $line = NULL, $need_trim = TRUE) {
	global $CFG;
	if ($CFG->config['log_level'] >= 1) {
		$location = '';
		if (!is_null($file) && !is_null($line)) {
			$location = "\t(" . PrinterLog__filterAppPath($file) . ' ' . $line . ')';
		}
		
		return PrinterLog__logToDebugFile($CFG->config['log_file'], $msg, "ERR: ", $location, $need_trim);
	}
	else {
		return FALSE;
	}
}

function PrinterLog_logSSO($level, $code, $message) {
	$context = NULL;
	$data = array();
	$options = array();
	$CI = &get_instance();
	
	$CI->load->helper('zimapi');
	$data = array(
			'printersn'		=> ZimAPI_getSerial(),
			'printertime'	=> date("Y-m-d H:i:s\Z", time()),
			'level'			=> $level,
			'code'			=> $code,
			'message'		=> $message,
	);
	$options = array(
			'http' => array(
					'header'	=> "Content-type: application/x-www-form-urlencoded\r\n",
					'method'	=> 'POST',
					'content'	=> http_build_query($data),
			)
	);
	$context = stream_context_create($options);
	
	@file_get_contents('https://sso.zeepro.com/errorlog.ashx', false, $context);
	
	return;
}

function PrinterLog_setStats($mode) {
	$mode = strtolower($mode);
	
	switch ($mode) {
		case 'off':
			try {
				$fp = fopen(PRINTERLOG_STATS_FILEPATH_OFF, 'w');
				if ($fp) {
					fwrite($fp, 'off');
					fclose($fp);
				}
				else {
					throw new Exception('write file error');
				}
			} catch (Exception $e) {
				PrinterLog_logError('write stats file error', __FILE__, __LINE__);
				return FALSE;
			}
			break;
			
		case 'on':
			@unlink(PRINTERLOG_STATS_FILEPATH_OFF);
			break;
			
		default:
			return FALSE;
			break;
	}
	
	return TRUE;
}

function PrinterLog_getStats() {
	if (file_exists(PRINTERLOG_STATS_FILEPATH_OFF)) {
		return FALSE;
	}
	
	return TRUE;
}

function PrinterLog_statsPowerOff() {
	$guid = @file_get_contents(PRINTERLOG_STATS_SESSION_GUID_FILE);
	
	if ((int)$guid <= 0) $guid = NULL;
	
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_PRINTER,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_OFF,
			PRINTERLOG_STATS_TITLE_LABEL	=> NULL,
			PRINTERLOG_STATS_TITLE_VALUE	=> $guid,
	));
}

function PrinterLog_statsPrint($action, $label) {
	$guid = NULL;
	$CI = &get_instance();
	
	$CI->load->helper('corestatus');
	if (!CoreStatus_getRandomGUID($guid)) {
		return FALSE;
	}
	
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_PRINT,
			PRINTERLOG_STATS_TITLE_ACTION	=> $action,
			PRINTERLOG_STATS_TITLE_LABEL	=> $label,
			PRINTERLOG_STATS_TITLE_VALUE	=> $guid,
	));
}

function PrinterLog_statsSlice($action, $label) {
	$guid = NULL;
	$CI = &get_instance();
	
	$CI->load->helper('corestatus');
	if (!CoreStatus_getRandomGUID($guid)) {
		return FALSE;
	}
	
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_SLICE,
			PRINTERLOG_STATS_TITLE_ACTION	=> $action,
			PRINTERLOG_STATS_TITLE_LABEL	=> $label,
			PRINTERLOG_STATS_TITLE_VALUE	=> $guid,
	));
}

function PrinterLog_statsUpload($filesize, $source = NULL) {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_UPLOAD,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_MODEL,
			PRINTERLOG_STATS_TITLE_LABEL	=> $source,
			PRINTERLOG_STATS_TITLE_VALUE	=> $filesize,
	));
}

function PrinterLog_statsUploadResize() {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_UPLOAD,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_RESIZE,
			PRINTERLOG_STATS_TITLE_LABEL	=> NULL,
			PRINTERLOG_STATS_TITLE_VALUE	=> NULL,
	));
}

function PrinterLog_statsShareEmail($nb_email) {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_TIMELAPSE,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_SHARE,
			PRINTERLOG_STATS_TITLE_LABEL	=> PRINTERLOG_STATS_LABEL_EMAIL,
			PRINTERLOG_STATS_TITLE_VALUE	=> $nb_email,
	));
}

function PrinterLog_statsShareVideo($label) {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_TIMELAPSE,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_SHARE,
			PRINTERLOG_STATS_TITLE_LABEL	=> $label,
			PRINTERLOG_STATS_TITLE_VALUE	=> NULL,
	));
}

function PrinterLog_statsLibrarySTL($label, $nb_model) {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_LIBRARY,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_MODEL,
			PRINTERLOG_STATS_TITLE_LABEL	=> $label,
			PRINTERLOG_STATS_TITLE_VALUE	=> $nb_model,
	));
}

function PrinterLog_statsLibraryGcode($label, $nb_model) {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_LIBRARY,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_PRINT,
			PRINTERLOG_STATS_TITLE_LABEL	=> $label,
			PRINTERLOG_STATS_TITLE_VALUE	=> $nb_model,
	));
}

function PrinterLog_statsWebClick($label) {
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_WEB,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_CLICK,
			PRINTERLOG_STATS_TITLE_LABEL	=> $label,
			PRINTERLOG_STATS_TITLE_VALUE	=> NULL,
	));
}

function PrinterLog_statsWebAgent() {
	$CI = &get_instance();
	$CI->load->helper('corestatus');
	
	return PrinterLog__logStats(array(
			PRINTERLOG_STATS_TITLE_CATEGORY	=> PRINTERLOG_STATS_CATEGORY_WEB,
			PRINTERLOG_STATS_TITLE_ACTION	=> PRINTERLOG_STATS_ACTION_REQUEST,
			PRINTERLOG_STATS_TITLE_LABEL	=> array (
					PRINTERLOG_STATS_NAVIG_URL		=> $_SERVER['REQUEST_URI'],
					PRINTERLOG_STATS_NAVIG_AGENT	=> isset($_SERVER['HTTP_USER_AGENT'])
							? $_SERVER['HTTP_USER_AGENT'] : NULL,
					PRINTERLOG_STATS_NAVIG_REMOTE	=> CoreStatus_checkTromboning()
							? PRINTERLOG_STATS_VALUE_ON : PRINTERLOG_STATS_VALUE_OFF,
			),
			PRINTERLOG_STATS_TITLE_VALUE	=> NULL,
	));

}

// internal function
function PrinterLog__logStats($array_stats = array()) {
	$json_data = array();
	$temp_filename = FALSE;
	$folder_path = NULL;
	$CI = &get_instance();
	
	// ignore invalid or empty input
	if (!is_array($array_stats) || count($array_stats) == 0) {
		return FALSE;
	}
	// return if stats is off
	if (FALSE == PrinterLog_getStats()) {
		return TRUE;
	}
	//TODO think if we take DNT into account or not (Do not track me)
// 	if (isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT'] == 1) {
// 		return TRUE;
// 	}
	
	// treat required items: category and action
	if (isset($array_stats[PRINTERLOG_STATS_TITLE_CATEGORY]) && isset($array_stats[PRINTERLOG_STATS_TITLE_ACTION])) {
		$json_data[PRINTERLOG_STATS_TITLE_CATEGORY] = PRINTERLOG_STATS_TITLE_CATEGORY . '=' . $array_stats[PRINTERLOG_STATS_TITLE_CATEGORY];
		$json_data[PRINTERLOG_STATS_TITLE_ACTION] = PRINTERLOG_STATS_TITLE_ACTION . '=' . $array_stats[PRINTERLOG_STATS_TITLE_ACTION];
	}
	else {
		return FALSE;
	}
		
	// treat optional items: label and value
	if (isset($array_stats[PRINTERLOG_STATS_TITLE_LABEL])) {
		$item_data = $array_stats[PRINTERLOG_STATS_TITLE_LABEL];
		
		if (is_array($item_data)) {
			$item_data = json_encode($item_data);
		}
		
		$json_data[PRINTERLOG_STATS_TITLE_LABEL] = PRINTERLOG_STATS_TITLE_LABEL . '=' . $item_data;
	}
	if (isset($array_stats[PRINTERLOG_STATS_TITLE_VALUE])) {
		$json_data[PRINTERLOG_STATS_TITLE_VALUE] = PRINTERLOG_STATS_TITLE_VALUE . '=' . $array_stats[PRINTERLOG_STATS_TITLE_VALUE];
	}
	
	// add serial
	$CI->load->helper('zimapi');
	$json_data[PRINTERLOG_STATS_TITLE_SERIAL] = PRINTERLOG_STATS_TITLE_SERIAL . '=' . ZimAPI_getSerial();
	
	// generate temp curl json file
	$folder_path = $CI->config->item('temp') . PRINTERLOG_STATS_TEMPFILE_FOLDER;
	if (!file_exists($folder_path)) {
		mkdir($folder_path);
	}
	
	$temp_filename = tempnam($folder_path, PRINTERLOG_STATS_TEMPFILE_PREFIX);
	if ($temp_filename == FALSE || !file_exists($temp_filename)) {
		return FALSE;
	}
	
	$fp = fopen($temp_filename, 'w');
	if ($fp) {
		//TODO check if we need unescapse unicode
// 		$CI->load->helper('json');
// 		fwrite($fp, json_encode_unicode($json_data));
		fwrite($fp, json_encode($json_data));
		fclose($fp);
		chmod($temp_filename, 0777);
	}
	else {
		return FALSE;
	}
	
	exec($CI->config->item('siteutil') . PRINTERLOG_STATS_SITEUTILS_CMD . $temp_filename);
	
	return TRUE;
}

function PrinterLog__logToFile($file_index, $command, $output = '') {
	global $CFG;
	if (is_array($output)) { // if several lines
		$tmp_string = '';
		foreach ($output as $line) {
			$tmp_string .= $line . '; ';
		}
		$output = trim($tmp_string, " ;\t\n\r\0\x0B");
	}
	$msg = date("[Y-m-d\TH:i:s\Z]\t", time()) . $command . "\t[" . $output . "]\n";
	
	$fp = fopen($CFG->config[$file_index], 'a');
	if ($fp) {
		fwrite($fp, $msg);
		fclose($fp);
		return TRUE;
	}
	else {
		return FALSE;
	}
}

function PrinterLog__logToDebugFile($file, $msg, $prefix, $suffix, $need_trim) {
	if ($need_trim == TRUE) {
		$msg = trim($msg, " \t\n\r\0\x0B");
	}
// 	$msg = time() . $prefix . $msg . "\n";
	$msg = date("[Y-m-d\TH:i:s\Z]\t", time()) . $prefix . $msg . $suffix . "\n";
	$fp = fopen($file, 'a');
	if ($fp) {
		fwrite($fp, $msg);
		fclose($fp);
		return TRUE;
	}
	else {
		return FALSE;
	}
}

function PrinterLog__filterAppPath($filepath) {
	$return_path = str_replace(FCPATH, '', $filepath);
	return $return_path;
}
