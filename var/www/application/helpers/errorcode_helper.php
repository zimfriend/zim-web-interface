<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

// $CI = &get_instance();
// $CI->lang->load('errorcode', $CI->config->item('language'));

if (!defined('ERROR_OK')) {
	define('ERROR_OK',				200);
	define('ERROR_MISS_PRM',		432);
	define('ERROR_WRONG_PRM',		433);
	define('ERROR_LOADED_UNLOAD',	434);
// 	define('ERROR_IN_PRINT',		435);
	define('ERROR_WRONG_PWD',		436);
	define('ERROR_NO_PRINT',		437);
	define('ERROR_MISS_LEFT_CART',	438);
	define('ERROR_MISS_RIGT_CART',	439);
// 	define('ERROR_PRES_FILAMENT',	440);
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
	define('ERROR_REMOTE_REFUSE',	453);
	define('ERROR_NO_SLICING',		454);
	define('ERROR_DISK_FULL',		456);
	define('ERROR_GCODE_NOTFOUND',	457);
	define('ERROR_IMG_NOTFOUND',	458);
	
	define('ERROR_REMOTE_SLICE',	498);
	define('ERROR_UNDER_CONSTRUCT',	499);
	define('ERROR_INTERNAL',		500);
	
	// normal program return code
	define('ERROR_NORMAL_RC_OK',	0);
	
	global $MY_ERRMSG_ARRAY;
	$MY_ERRMSG_ARRAY = array (
			ERROR_OK				=> 'Ok',
			ERROR_MISS_PRM			=> 'Missing parameter',
			ERROR_WRONG_PRM			=> 'Incorrect parameter',
			ERROR_LOADED_UNLOAD		=> 'Already loaded / unloaded',
// 			ERROR_IN_PRINT			=> 'Printing in progress',
			ERROR_WRONG_PWD			=> 'Missing / Incorrect password',
			ERROR_NO_PRINT			=> 'No current printing',
			ERROR_MISS_LEFT_CART	=> 'Left cartridge missing',
			ERROR_MISS_RIGT_CART	=> 'right cartridge missing',
// 			ERROR_PRES_FILAMENT		=> 'Filament present',
	 		ERROR_WRONG_FORMAT		=> 'Incorrect format',
			ERROR_EMPTY_PLATFORM	=> 'Platform empty',
			ERROR_MISS_LEFT_FILA	=> 'Left filament missing',
			ERROR_MISS_RIGT_FILA	=> 'Right filament missing',
			ERROR_LOW_LEFT_FILA		=> 'Not enough left filament',
			ERROR_LOW_RIGT_FILA		=> 'Not enough right filament',
			ERROR_BUSY_PRINTER		=> 'Printer busy',
			ERROR_FULL_PRTLST		=> 'Print list full',
			ERROR_UNKNOWN_MODEL		=> 'Unknown model',
			ERROR_UNKNOWN_PIC		=> 'Unknown picture',
			ERROR_TOOBIG_FILE		=> 'File too big',
			ERROR_TOOBIG_MODEL		=> 'Model too big',
			ERROR_NO_SLICED			=> 'No sliced model',
			ERROR_REMOTE_REFUSE		=> 'Remote control not allowed',
			ERROR_NO_SLICING		=> 'No current slicing',
			ERROR_DISK_FULL			=> 'Disk Full',
			ERROR_GCODE_NOTFOUND	=> 'G-code file not found',
			ERROR_IMG_NOTFOUND		=> 'Image file not found',
			
			ERROR_INTERNAL			=> 'Internal API error',
			ERROR_UNDER_CONSTRUCT	=> 'UNDER CONSTRUCTION',
			ERROR_REMOTE_SLICE		=> 'REMOTE SLICING ERROR',
			404						=> 'Not found',
			403						=> 'Forbidden',
	);
	
	function MyERRMSG($CODEERR) {
		global $MY_ERRMSG_ARRAY;
		if (isset($MY_ERRMSG_ARRAY[$CODEERR])) {
			return t($MY_ERRMSG_ARRAY[$CODEERR]);
		}
		
		return NULL;
	}
	
	// example:
	// printf('%d: %s', ERROR_OK, MyERRMSG(ERROR_OK));
	// echo (ERROR_OK . ': ' . MyERRMSG(ERROR_OK));
}

/* another way to build an error list
 * but too long to call this class
if (!class_exists(MY_ErrorCode)) {
	abstract class MY_ErrorCode {
		const ERROR_MISS_PRM		= 432;
		const ERROR_WRONG_PRM		= 433;
		const ERROR_LOADED_UNLOAD	= 434;
		const ERROR_IN_PRINT		= 435;
		const ERROR_WRONG_PWD		= 436;
		const ERROR_NO_PRINT		= 437;
		const ERROR_MISS_CARTRIDGE	= 438;
		const ERROR_MISS_FILAMENT	= 439;
		const ERROR_PRES_FILAMENT	= 440;
		const ERROR_WRONG_FORMAT	= 440;
		
		const ERRMSG_ARRAY = array (
			ERROR_MISS_PRM		=> 'Missing parameter',
		);
	}
	// example:
	// MY_ErrorCode::ERROR_IN_PRINT;
	// MY_ErrorCode::ERRMSG_ARRAY[MY_ErrorCode::ERROR_IN_PRINT];
}
 */
