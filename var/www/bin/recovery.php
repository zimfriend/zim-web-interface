<?php

// get necessary file path
define('BASEPATH', '/var/www/');
require("../application/config/config.php");

// global $config;

define('ERROR_OK',				0);
define('ERROR_FILE_READ',		1);
define('ERROR_FILE_STRUCTURE',	2);
define('ERROR_FILE_WRITE',		3);
define('ERROR_NO_PRM',			4);

// define('ERROR_OK',				200);
define('ERROR_MISS_PRM',		432);
define('ERROR_WRONG_PRM',		433);
define('ERROR_LOADED_UNLOAD',	434);
define('ERROR_IN_PRINT',		435);
define('ERROR_WRONG_PWD',		436);
define('ERROR_NO_PRINT',		437);
define('ERROR_MISS_LEFT_CART',	438);
define('ERROR_MISS_RIGT_CART',	439);
define('ERROR_PRES_FILAMENT',	440);
define('ERROR_WRONG_FORMAT',	440);
define('ERROR_EMPTY_PLATFORM',	441);
define('ERROR_MISS_LEFT_FILA',	442);
define('ERROR_MISS_RIGT_FILA',	443);
define('ERROR_LOW_LEFT_FILA',	444);
define('ERROR_LOW_RIGT_FILA',	445);
define('ERROR_BUSY_PRINTER',	446);
define('ERROR_FULL_PRTLST',		447);
define('ERROR_UNKNOWN_MODEL',	448);
define('ERROR_UNKNOWN_PIC',		449);
define('ERROR_TOOBIG_FILE',		450);
define('ERROR_TOOBIG_MODEL',	451);
define('ERROR_NO_SLICED',		452);

define('ERROR_INTERNAL',		500);

define('ERROR_NORMAL_RC_OK',	0);

define('PRINTERSTATE_GET_CARTRIDGER',	' M1602');
define('PRINTERSTATE_GET_CARTRIDGEL',	' M1603');
define('PRINTERSTATE_RFID_POWER_OFF',	' M1617');
define('PRINTERSTATE_SET_EXTRUDR',		' T0');
define('PRINTERSTATE_SET_EXTRUDL',		' T1');
define('PRINTERSTATE_MAGIC_NUMBER_V1',	23567); //v1.0
define('PRINTERSTATE_MAGIC_NUMBER_V2',	23568); //v1.1
define('PRINTERSTATE_MAGIC_NUMBER_V3',	23569); //v1.2
define('PRINTERSTATE_MAGIC_NUMBER_V4',	23570); //v1.3
define('PRINTERSTATE_OFFSET_TEMPER',	100);
define('PRINTERSTATE_OFFSET_TEMPER_V2',	150);

define('CORESTATUS_TITLE_STATUS',		'State');
define('CORESTATUS_VALUE_PRINT',		'printing');
define('CORESTATUS_TITLE_SUBSTATUS',	'Substate');
define('CORESTATUS_VALUE_RECOVERY',		'recovery');
define('CORESTATUS_VALUE_IDLE',			'idle');

// define('RECOVER_FILE_CARTRIDGE_R',	'C:/wamp/sites/zim/data/hardconf/EXTRUDER_R_IN_PRINTING');
// define('RECOVER_FILE_CARTRIDGE_L',	'C:/wamp/sites/zim/data/hardconf/EXTRUDER_L_IN_PRINTING');
define('RECOVER_FILE_CARTRIDGE_R',	'/config/hardconf/EXTRUDER_R_IN_PRINTING');
define('RECOVER_FILE_CARTRIDGE_L',	'/config/hardconf/EXTRUDER_L_IN_PRINTING');
define('RECOVER_FILENAME_STATUS',	'Work.json');
define('RECOVER_FILENAME_RUNCODE',	'_printer_recovery.gcode');
define('RECOVER_CMD_PREHEAT',		'M104 S');
define('RECOVER_CMD_WAITHEAT',		'M109 S');
define('RECOVER_CMD_EXTRUDE',		'G1 E50 F50');
define('RECOVER_OFFSET_ADDTEMPER',	30);
define('RECOVER_VALUE_DEFAUTHEAT',	240);


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
	$return_path = str_replace('/var/www/', '', $filepath);
	return $return_path;
}

function PrinterLog_logDebug($msg, $file = NULL, $line = NULL, $need_trim = TRUE) {
	global $config;
	$location = '';
	if (!is_null($file) && !is_null($line)) {
		$location = "\t(" . PrinterLog__filterAppPath($file) . ' ' . $line . ')';
	}

	return PrinterLog__logToDebugFile($config['log_file'], $msg, "DBG: ", $location, $need_trim);
}

function PrinterLog_logMessage($msg, $file = NULL, $line = NULL, $need_trim = TRUE) {
	global $config;
	$location = '';
	if (!is_null($file) && !is_null($line)) {
		$location = "\t(" . PrinterLog__filterAppPath($file) . ' ' . $line . ')';
	}

	return PrinterLog__logToDebugFile($config['log_file'], $msg, "MSG: ", $location, $need_trim);
}

function PrinterLog_logError($msg, $file = NULL, $line = NULL, $need_trim = TRUE) {
	global $config;
	$location = '';
	if (!is_null($file) && !is_null($line)) {
		$location = "\t(" . PrinterLog__filterAppPath($file) . ' ' . $line . ')';
	}

	return PrinterLog__logToDebugFile($config['log_file'], $msg, "ERR: ", $location, $need_trim);
}

function PrinterState_filterOutput(&$output, $trim_ok = TRUE) {
	if (!is_array($output)) {
		return FALSE;
	}
	else if (empty($output)) {
		return TRUE;
	}
	else {
		// assign output to temp and empty output array
		$array_tmp = $output;
		$output = array();

		// filter empty line
		// 		$array_tmp = array_filter($array_tmp, "PrinterState__checkLine");

		// filter the output not necessary
		foreach($array_tmp as $line) {
			// jump the empty line
			$line = trim($line, " \t\n\r\0\x0B");
			if ($line == '') {
				continue;
			}
				
			// check it start with [<-] or [->], then filter it
			//filter the input
			if (strpos($line, '[->]') === 0) {
				continue;
			}
			// 			$line = preg_replace('[\[->\]]', '', $line, 1);
			$line = preg_replace('[\[<-\]]', '', $line, 1);
			$line = trim($line, " \t\n\r\0\x0B");
			if ($line == '') {
				continue;
			}
			$output[] = $line;
		}

		if (empty($output)) {
			PrinterLog_logMessage('no arduino return', __FILE__, __LINE__);
			return TRUE;
		}

		// filter the ok message in the end of array
		if ($trim_ok == TRUE && strtolower($output[count($output) - 1]) == 'ok') {
			unset($output[count($output) - 1]);
		}
	}

	return TRUE;
}

function PrinterState_setRFIDPower($on = TRUE) {
	global $config;
	$arcontrol_fullpath = $config['arcontrol_c'];
	$output = array();
	$command = '';
	$ret_val = 0;

	if ($on == TRUE) {
		$command = $arcontrol_fullpath . PRINTERSTATE_RFID_POWER_ON;
	}
	else {
		$command = $arcontrol_fullpath . PRINTERSTATE_RFID_POWER_OFF;
	}

	exec($command, $output, $ret_val);
	if (!PrinterState_filterOutput($output)) {
		return ERROR_INTERNAL;
	}
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		PrinterLog_logError('rfid power control command error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}

	return ERROR_OK;
}

function PrinterState_getCartridgeCode(&$code_cartridge, $abb_cartridge, $power_off = TRUE) {
	global $config;
	$arcontrol_fullpath = $config['arcontrol_c'];
	$command = '';
	$output = array();
	$ret_val = 0;

	switch ($abb_cartridge) {
		case 'l':
			$command = $arcontrol_fullpath . PRINTERSTATE_GET_CARTRIDGEL;
			break;
				
		case 'r':
			$command = $arcontrol_fullpath . PRINTERSTATE_GET_CARTRIDGER;
			break;
				
		default:
			return ERROR_WRONG_PRM;
			break;
	}

	exec($command, $output, $ret_val);
	if (!PrinterState_filterOutput($output)) {
		return ERROR_INTERNAL;
	}
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		PrinterLog_logError('rfid read command error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	else {
		$code_cartridge = $output ? $output[0] : NULL;
	}

	if ($power_off == TRUE) {
		$ret_val = PrinterState_setRFIDPower(FALSE);
		if ($ret_val != ERROR_OK) {
			return ERROR_INTERNAL;
		}
	}

	return ERROR_OK;
}

function PrinterState_getCartridgeTemper(&$temper_cartridge, $abb_cartridge, $power_off = TRUE) {
	$last_output = NULL;
	$ret_val = 0;

	$ret_val = PrinterState_getCartridgeCode($last_output, $abb_cartridge, $power_off);
	if ($ret_val != ERROR_OK) {
		return $ret_val;
	}

	// check and treat output data
	if ($last_output) {
		$version_rfid = 0;
		$string_tmp = NULL;
		$hex_checksum = 0;
		$hex_cal = 0;
		$hex_tmp = 0;
		$offset_temp = 0;
		$length_temp = 0;
		$data_json = array();

		// checksum 0 to 14
		for($i=0; $i<=14; $i++) {
			$string_tmp = substr($last_output, $i*2, 2);
			$hex_tmp = hexdec($string_tmp);
			$hex_cal = $hex_cal ^ $hex_tmp;
		}
		$hex_checksum = hexdec(substr($last_output, 30, 2));
		if ($hex_cal != $hex_checksum) {
			PrinterLog_logError('checksum error, $hex_cal: ' . $hex_cal . ', $hex_data: ' . $hex_checksum, __FILE__, __LINE__);
			return ERROR_INTERNAL; // checksum failed
		}

		// magic number
		$string_tmp = substr($last_output, 0, 4);
		switch (hexdec($string_tmp)) {
			case PRINTERSTATE_MAGIC_NUMBER_V1:
				$version_rfid = 1;
				break;

			case PRINTERSTATE_MAGIC_NUMBER_V2:
				$version_rfid = 2;
				break;

			case PRINTERSTATE_MAGIC_NUMBER_V3:
				$version_rfid = 3;
				break;

			case PRINTERSTATE_MAGIC_NUMBER_V4:
				$version_rfid = 4;
				break;

			default:
				PrinterLog_logError('magic number error', __FILE__, __LINE__);
				return ERROR_INTERNAL;
				break;
		}

		if ($version_rfid == 1) {
			$offset_temp = 22;
			$length_temp = 2;
		}
		else if ($version_rfid == 2) { //$version_rfid == 2
			$offset_temp = 24;
			$length_temp = 2;
		}
		else if ($version_rfid == 3) { //$version_rfid == 3
			$offset_temp = 24;
			$length_temp = 1;
		}
		else { //$version_rfid == 4
			$offset_temp = 22;
			$length_temp = 2;
		}

		// normal extrusion temperature
		$string_tmp = substr($last_output, $offset_temp, $length_temp);
		if ($version_rfid == 3) {
			$temper_cartridge = hexdec($string_tmp) + PRINTERSTATE_OFFSET_TEMPER_V2;
		}
		else {
			$temper_cartridge = hexdec($string_tmp) + PRINTERSTATE_OFFSET_TEMPER;
		}
	} else {
		PrinterLog_logMessage('missing cartridge', __FILE__, __LINE__);
		$temp_cartridge = NULL;
		if ($abb_cartridge == 'l') {
			return ERROR_MISS_LEFT_CART;
		}
		else {
			return ERROR_MISS_RIGT_CART;
		}
	}

	return ERROR_OK;
}

// assign initial value
$sdcard = FALSE;
$status_file = NULL;
$json_status = array();
$array_extruder = array(
		'r' => PRINTERSTATE_SET_EXTRUDR,
		'l' => PRINTERSTATE_SET_EXTRUDL,
);
$array_checkfile = array(
		'r' => RECOVER_FILE_CARTRIDGE_R,
		'l' => RECOVER_FILE_CARTRIDGE_L,
);
$array_common_start = array(
	'G28 X', 'G28 Y', 'G90', 'M83', 'G1 X75 Y75 Z150'
);
$array_action = array();
$parameter = NULL;


if (empty($_GET) && !empty($argv)) {
	$parameter = $argv[1];
}
else if (!empty($_GET)) {
	$parameter = $_GET['p'];
}
else {
	exit(ERROR_NO_PRM);
}

// delete old recovery gcode file if we have it
@unlink($config['temp'] . RECOVER_FILENAME_RUNCODE);

// check sd status and assign the right status json file to be checked
if (is_writable($config['sdcard'])) {
	$cr = 0;
	$command = 'echo writable' . $config['sdcard'] . '.root_phptest.tmp';
	$output = array();
	
	exec($command, $output, $cr);
	
	if ($cr == 0) {
		$sdcard = TRUE;
	}
}

if ($sdcard) {
	$status_file = $config['sdcard'] . 'conf/' . RECOVER_FILENAME_STATUS;
}
else {
	$status_file = $config['conf'] . RECOVER_FILENAME_STATUS;
}

// try to read json file to get data
if (file_exists($status_file)) {
	$retry = 3;
	
	while ($retry >= 0) {
		$content = @file_get_contents($status_file);
		
		if ($content === FALSE) {
			usleep(500000);
			--$retry;
		}
		else {
			$json_status = json_decode($content, TRUE);
			break;
		}
	}
	
	if ($retry < 0) {
		PrinterLog_logError('read json file error with retry ' . $status_file, __FILE__, __LINE__);
		exit(ERROR_FILE_READ);
	}
}
else {
	PrinterLog_logError('read json file error ' . $status_file, __FILE__, __LINE__);
	exit(ERROR_FILE_READ);
}

if ($json_status === FALSE) {
	PrinterLog_logError('invalid json format');
	exit(ERROR_FILE_STRUCTURE);
}

if ($parameter == 'stop') {
	// change the status in work json file back to idle
	$fp = fopen($status_file, 'w');
	if ($fp) {
		$json_status[CORESTATUS_TITLE_SUBSTATUS] = NULL;
		$json_status[CORESTATUS_TITLE_STATUS] = CORESTATUS_VALUE_IDLE;
		fwrite($fp, json_encode($json_status));
		fclose($fp);
	}
	else {
		PrinterLog_logError('status file error', __FILE__, __LINE__);
		exit(ERROR_FILE_WRITE);
	}
	
	exit(ERROR_OK);
}

// exit if not in printing when closing printer
if ($json_status[CORESTATUS_TITLE_STATUS] != CORESTATUS_VALUE_PRINT
		|| ($json_status[CORESTATUS_TITLE_STATUS] == CORESTATUS_VALUE_RECOVERY
				&& $json_status[CORESTATUS_TITLE_SUBSTATUS] != CORESTATUS_VALUE_PRINT)) {
	exit(ERROR_OK);
}

// get the extruder array to do recovery action
foreach ($array_checkfile as $abb => $checkfile) {
	if (file_exists($checkfile)) {
		$temper_cartridge = NULL;
		$ret_val = PrinterState_getCartridgeTemper($temper_cartridge, $abb);
		if ($ret_val == ERROR_OK) {
			$array_action[$abb] = $temper_cartridge + RECOVER_OFFSET_ADDTEMPER;
		}
		else {
			$array_action[$abb] = RECOVER_VALUE_DEFAUTHEAT;
		}
	}
}

if (count($array_action)) {
	$fp = NULL;
	
	// change the status in work json file to recovery
	$fp = fopen($status_file, 'w');
	if ($fp) {
		$json_status[CORESTATUS_TITLE_SUBSTATUS] = CORESTATUS_VALUE_PRINT;
		$json_status[CORESTATUS_TITLE_STATUS] = CORESTATUS_VALUE_RECOVERY;
		fwrite($fp, json_encode($json_status));
		fclose($fp);
	}
	else {
		PrinterLog_logError('status file error', __FILE__, __LINE__);
		exit(ERROR_FILE_WRITE);
	}
	
	// generate common part
	$fp = fopen($config['temp'] . RECOVER_FILENAME_RUNCODE, 'w');
	if ($fp) {
		foreach($array_common_start as $command_run) {
			fwrite($fp, $command_run . "\n");
		}
		foreach($array_action as $abb => $temper_cartridge) {
			$command_extruder = $array_extruder[$abb];
			fwrite($fp, RECOVER_CMD_PREHEAT . $temper_cartridge . $command_extruder . "\n");
		}
		
		// try to prime a fixed length to recover for each extruder
		foreach ($array_action as $abb => $temper_cartridge) {
			$command_extruder = $array_extruder[$abb];
			// change extruder
			fwrite($fp, trim($command_extruder) . "\n");
			// wait temperature
			fwrite($fp, RECOVER_CMD_WAITHEAT . $temper_cartridge . $command_extruder . "\n");
			// extrude length
			fwrite($fp, RECOVER_CMD_EXTRUDE . "\n");
		}
		
		fclose($fp);
		
		exit(ERROR_OK);
	}
	else {
		PrinterLog_logError('write gcode file error', __FILE__, __LINE__);
		exit(ERROR_FILE_WRITE);
	}
}
else {
	// do not change the status in work json file in order to log in mobile site side
	exit(ERROR_OK);
}

