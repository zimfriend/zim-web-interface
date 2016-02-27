<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

// call error list if we want
$CI = &get_instance();
$CI->load->helper(array (
		'errorcode',
		'file',
		'directory',
		'json',
		'url',
		'corestatus',
));

if (!defined('PRINTLIST_MAX_PIC_SIZE')) {
	define('PRINTLIST_MAX_PIC_SIZE',	1024);
	define('PRINTLIST_MAX_GCODE_SIZE',	1024*100);
	define('PRINTLIST_MAX_FILE_PIC',	5);
	
	define('PRINTLIST_TITLE_ID',		'mid');
	define('PRINTLIST_TITLE_NAME',		'name');
	define('PRINTLIST_TITLE_DESP',		'description');
	define('PRINTLIST_TITLE_TIME',		'duration');
	define('PRINTLIST_TITLE_LENG_F1',	'l1');
	define('PRINTLIST_TITLE_LENG_F2',	'l2');
	define('PRINTLIST_TITLE_COLOR_F1',	'c1');
	define('PRINTLIST_TITLE_COLOR_F2',	'c2');
	define('PRINTLIST_TITLE_PIC',		'picture');
// 	define('PRINTLIST_TITLE_GCODE',		'gcode');
	define('PRINTLIST_TITLE_LANG_EN',	'en');
	define('PRINTLIST_TITLE_LANG_FR',	'fr');
	define('PRINTLIST_TITLE_MODELS',	'models');
	define('PRINTLIST_TITLE_VERSION',	'ver');

	define('PRINTLIST_VALUE_VERSION',	2);
	
	define('PRINTLIST_MODEL_PREFIX_S',		'_');
	define('PRINTLIST_MODEL_PRIME_L',		'_prime_left');
	define('PRINTLIST_MODEL_PRIME_R',		'_prime_right');
	define('PRINTLIST_MODEL_REPRIME_L',		'_reprime_left');
	define('PRINTLIST_MODEL_REPRIME_R',		'_reprime_right');
	define('PRINTLIST_MODEL_PRIME_L_PVA',	'_prime_left_pva');
	define('PRINTLIST_MODEL_PRIME_R_PVA',	'_prime_right_pva');
	define('PRINTLIST_MODEL_REPRIME_L_PVA',	'_reprime_left_pva');
	define('PRINTLIST_MODEL_REPRIME_R_PVA',	'_reprime_right_pva');
	define('PRINTLIST_MODEL_PRINTPRIME_L',	'_print_prime_left');
	define('PRINTLIST_MODEL_PRINTPRIME_R',	'_print_prime_right');
// 	define('PRINTLIST_MODEL_CHARGE',		'_charge');
// 	define('PRINTLIST_MODEL_RETRACT',		'_retract');
// 	define('PRINTLIST_MODEL_CANCEL',		'_cancel');
	define('PRINTLIST_MODEL_CALIBRATION',	'_nozzle_calibration');
	
	define('PRINTLIST_FILE_GCODE',		'model.gcode');
	define('PRINTLIST_FILE_GCODE_BZ2',	'model.gcode.bz2');
	define('PRINTLIST_FILE_JSON',		'model.json');
	define('PRINTLIST_GLOBAL_URL_RDV',	'zeepro.com');
	
// 	define('PRINTLIST_GETPIC_BASE_WEB',	base_url() . 'getpicture');
	//for IIS, we use only HTTP_HOST?
// 	if ($_SERVER['SERVER_PORT'] != 80) {
//  		define('PRINTLIST_GETPIC_BASE_WEB',	'http://' . $_SERVER['HTTP_HOST'] . ':' . $_SERVER['SERVER_PORT'] . base_url('rest/getpicture'));
// 	} else {
// 		define('PRINTLIST_GETPIC_BASE_WEB',	'http://' . $_SERVER['HTTP_HOST'] . base_url('rest/getpicture'));
// 	}
// 	if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) {
// 	if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']
// 			&& substr($_SERVER['HTTP_HOST'], -strlen(PRINTLIST_GLOBAL_URL_RDV)) === PRINTLIST_GLOBAL_URL_RDV) {
	if (CoreStatus_checkTromboning()) {
		// SSL connection
		define('PRINTLIST_GETPIC_BASE_WEB',	'https://' . $_SERVER['HTTP_HOST'] . base_url('rest/getpicture'));
	}
	else {
		define('PRINTLIST_GETPIC_BASE_WEB',	'http://' . $_SERVER['HTTP_HOST'] . base_url('rest/getpicture'));
	}
	define('PRINTLIST_GETPIC_PRM_MID',	'id');
	define('PRINTLIST_GETPIC_PRM_PIC',	'p');
}


function ModelList_add($data_array) {
	global $CFG;
	$printlist_basepath	= $CFG->config['printlist'];
	$model_path			= '';
	$model_pictures		= array();
	$model_names		= array();
	$model_desps		= array();
	$json_data			= NULL;
	
	$command	= '';
	$output		= array();
	$ret_val	= 0;
	
	$model_name			= NULL;	// string by $data_array['n'], string, 1th in the json structure
// 	$model_desp			= '';	// string by $data_array['d'], string
	$model_printtime	= 0;	// int by $data_array['t'], int
	$model_filament1	= 0;	// int by $data_array['l1'], int
	$model_filament2	= 0;	// int by $data_array['l2'], int
	$model_color1		= NULL;	// string by $data_array['c1'], string
	$model_color2		= NULL;	// string by $data_array['c2'], string
	$model_gcode		= NULL;	// file array by $data_array['f'], array
	//other parameters
	//$data_array['p1'] ~ $data_array['p5']: file array which contain images

	//==========================================================
	//check parameters
	//==========================================================
	if (is_array($data_array)) {
		if (!isset($data_array['n']) || !isset($data_array['f'])) {
			return ERROR_MISS_PRM;
		} else {
			$tmp_array = array();
			
			//model name
			$tmp_array = json_decode($data_array['n']);
			if (is_null($tmp_array)) {
				return ERROR_WRONG_PRM;
			}
			$model_names = $tmp_array;
			foreach ($tmp_array as $lang => $name_lang) {
				if (is_null($model_name)) {
					$name_lang = str_replace(' ', '_', $name_lang);
					$model_name = ModelList__codeModelName($name_lang);
				}
				if (strlen($name_lang) > 50 || strlen($name_lang) == 0) {
					return ERROR_WRONG_PRM;
				}
			}
			
			//model gcode
			if (is_array($data_array['f'])) {
				$model_gcode = $data_array['f'];
			} else {
				return ERROR_INTERNAL;
			}
			if ($model_gcode['file_size'] > PRINTLIST_MAX_GCODE_SIZE) {
				return ERROR_TOOBIG_MODEL;
			}
			if (($model_gcode['file_type'] != 'application/octet-stream' 
					 && $model_gcode['file_type'] != 'text/plain')
					|| $model_gcode['file_ext'] != '.gcode') {
				return ERROR_WRONG_FORMAT;
			}
			
			//model description
			$tmp_array = json_decode($data_array['d']);
			if (is_null($tmp_array)) {
				return ERROR_WRONG_PRM;
			}
			$model_desps = $tmp_array;
// 			if (isset($data_array['d'])) {
			foreach($tmp_array as $lang => $model_desp) {
// 				$model_desp = $data_array['d'];
				if (strlen($model_desp) > 512) { // || strlen($model_name) == 0 //already check isset()
					return ERROR_WRONG_PRM;
				}
 			}
			
			//model print time
			if (isset($data_array['t'])) {
				$model_printtime = (int) $data_array['t'];
				if ($model_printtime <= 0) {
					return ERROR_WRONG_PRM;
				}
			}

			//model filament1 length
			if (isset($data_array['l1'])) {
				$model_filament1 = (int) $data_array['l1'];
				if ($model_filament1 <= 0) {
					return ERROR_WRONG_PRM;
				}
			}

			//model filament2 length
			if (isset($data_array['l2'])) {
				$model_filament2 = (int) $data_array['l2'];
				if ($model_filament2 <= 0) {
					return ERROR_WRONG_PRM;
				}
			}

			//model filament1 color
			if (isset($data_array['c1'])) {
				$model_color1 = $data_array['c1'];
				if (ModelList__changeColorName($model_color1) != ERROR_OK) {
					return ERROR_WRONG_PRM;
				}
			}

			//model filament1 color
			if (isset($data_array['c2'])) {
				$model_color2 = $data_array['c2'];
				if (ModelList__changeColorName($model_color2) != ERROR_OK) {
					return ERROR_WRONG_PRM;
				}
			}
			
			//model picture 1
			if (isset($data_array['p1'])) {
				if (is_array($data_array['p1'])) {
					$model_pictures[] = $data_array['p1'];
				} else {
					return ERROR_INTERNAL;
				}
			}
			//model picture 2
			if (isset($data_array['p2'])) {
				if (is_array($data_array['p2'])) {
					$model_pictures[] = $data_array['p2'];
				} else {
					return ERROR_INTERNAL;
				}
			}
			//model picture 3
			if (isset($data_array['p3'])) {
				if (is_array($data_array['p3'])) {
					$model_pictures[] = $data_array['p3'];
				} else {
					return ERROR_INTERNAL;
				}
			}
			//model picture 4
			if (isset($data_array['p4'])) {
				if (is_array($data_array['p4'])) {
					$model_pictures[] = $data_array['p4'];
				} else {
					return ERROR_INTERNAL;
				}
			}
			//model picture 1
			if (isset($data_array['p5'])) {
				if (is_array($data_array['p5'])) {
					$model_pictures[] = $data_array['p5'];
				} else {
					return ERROR_INTERNAL;
				}
			}
			
			//model pictures
			foreach ($model_pictures as $picture) {
				if ($picture['file_size'] > PRINTLIST_MAX_PIC_SIZE) {
					return ERROR_TOOBIG_FILE;
				}
				if ($picture['is_image'] != TRUE
						|| ($picture['image_type'] != 'jpeg'
						 && $picture['image_type'] != 'png')) {
					return ERROR_WRONG_FORMAT;
				}
			}
		}
	} else {
		return ERROR_INTERNAL;
	}

	//==========================================================
	//treat parameters
	//==========================================================
	//model name, description, duration, filament1+2
	$json_data = array(
			PRINTLIST_TITLE_ID			=> ModelList_codeModelHash($model_name),
			PRINTLIST_TITLE_NAME		=> $model_names,
			PRINTLIST_TITLE_DESP		=> $model_desps,
			PRINTLIST_TITLE_TIME		=> $model_printtime,
			PRINTLIST_TITLE_LENG_F1		=> $model_filament1,
			PRINTLIST_TITLE_LENG_F2		=> $model_filament2,
			PRINTLIST_TITLE_COLOR_F1	=> $model_color1,
			PRINTLIST_TITLE_COLOR_F2	=> $model_color2,
// 			PRINTLIST_TITLE_GCODE		=> NULL,
			PRINTLIST_TITLE_PIC			=> array(),
	);
	$model_path = $printlist_basepath . $model_name . '/';
// 	$model_path = utf8_decode($model_path); //decode path for accent and special character
	//always create a new folder to overwrite the old one
	if (file_exists($model_path)) {
		delete_files($model_path, TRUE); //there are no folders inside normally, but we delete all
		rmdir($model_path);
		usleep(3); //to make sure the folder is deleted
	}
	mkdir($model_path);
	
	//model gcode
// 	rename($model_gcode['full_path'], $model_path . PRINTLIST_FILE_GCODE);
	$command = 'bzip2 -zcf "' . $model_gcode['full_path'] . '" > "' . $model_path . PRINTLIST_FILE_GCODE_BZ2 . '"';
	exec($command, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		return ERROR_INTERNAL;
	}
// 	//if we don't want to fix the filename of gcode, and then store it in json info
// 	$tmp_string = 'gcode' . time() . $model_gcode['file_ext']; //new gcode name
// 	rename($model_gcode['full_path'], $model_path . $tmp_string);
// 	$json_data[PRINTLIST_TITLE_GCODE] = $model_path . $tmp_string; //full path in json
	
	//model picture
	foreach ($model_pictures as $picture) {
		$i_tmp = isset($i_tmp) ? ++$i_tmp : 1;
		$tmp_string = 'img' . $i_tmp . '_' . time() . $picture['file_ext']; //new picture name
		rename($picture['full_path'], $model_path . $tmp_string);
		$json_data[PRINTLIST_TITLE_PIC][] = $tmp_string;
	}
	
	//write model json info
	try {
		$fp = fopen($model_path . PRINTLIST_FILE_JSON, 'w');
		if ($fp) {
			fwrite($fp, json_encode_unicode($json_data));
// 			fwrite($fp, json_encode($json_data));
			fclose($fp);
		}
		else {
			return ERROR_INTERNAL;
		}
	} catch (Exception $e) {
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ModelList_delete($id_model) {
	$model_cr = ModelList__find($id_model, $model_path);
	if (($model_cr == ERROR_OK) && $model_path) {
		delete_files($model_path, TRUE); //there are no folders inside normally, but we delete all
		rmdir($model_path);
		return ERROR_OK;
	} else {
		return ERROR_UNKNOWN_MODEL;
	}
}

function ModelList_list() {
	$array_data = ModelList__listAsArray();
	
	return json_encode_unicode($array_data);
}

function ModelList_getPic($id_model, $id_picture, &$path_pid) {
	$json_data = NULL;
	$model_path = NULL;
	$model_cr = 0;
	
	if ($id_picture <= 0 || $id_picture > PRINTLIST_MAX_FILE_PIC) {
		return ERROR_UNKNOWN_PIC;
	}
	--$id_picture; //adapt id number

	$model_cr = ModelList__find($id_model, $model_path);
	if (($model_cr == ERROR_OK) && $model_path) {
		try {
			$json_data = json_read($model_path . PRINTLIST_FILE_JSON, TRUE);
			if ($json_data['error']) {
				throw new Exception('read json error');
			}
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
		
		if (isset($json_data['json'][PRINTLIST_TITLE_PIC][$id_picture])) {
			$path_pid = $model_path . $json_data['json'][PRINTLIST_TITLE_PIC][$id_picture]; //image file full path
			return ERROR_OK;
		} else {
			return ERROR_UNKNOWN_PIC;
		}
	} else {
		return ERROR_UNKNOWN_MODEL;
	}
}

// leave this function here for having no interface change
function ModelList_print($id_model, $exchange_extruder = FALSE, $array_temper = array()) {
	$ret_val = 0;
	$model_calibration = FALSE;
	
	if ($id_model == ModelList_codeModelHash(PRINTLIST_MODEL_CALIBRATION)) {
		$model_calibration = TRUE;
	}
	
	$CI = &get_instance();
	$CI->load->helper('printer');
	
	$ret_val = Printer_printFromModel($id_model, $model_calibration, $exchange_extruder, $array_temper);
	
	return $ret_val;
}

function ModelList_codeModelHash($raw_name) {
	$CI = &get_instance();
	$CI->load->helper(array('detectos'));
	
	if (DectectOS_checkWindows()) {
		return md5(utf8_encode($raw_name));
	}
	else {
		return md5($raw_name);
	}
}

//internal function
function ModelList__find($id_model_find, &$model_path) {
	global $CFG;
	$printlist_basepath	= $CFG->config['printlist'];
	$model_path = NULL;
	
	if (strlen($id_model_find) != 32) { //default length of md5
		return ERROR_UNKNOWN_MODEL;
	}

	$model_array = directory_map($printlist_basepath, 1);
	foreach ($model_array as $model_name) {
		if (!is_dir($printlist_basepath . $model_name)) { //check whether it is a folder or not
			continue;
		}
		$id_model_cal = ModelList_codeModelHash($model_name);
		if ($id_model_cal == $id_model_find) {
			$model_path = $printlist_basepath . $model_name . '/';
			return ERROR_OK; //leave directly the loop when finding the correct folder
			break; // never reach here
		}
	}
	
	return ERROR_UNKNOWN_MODEL;
}

function ModelList__listAsArray($set_localization = FALSE) {
	global $CFG;
	$printlist_basepath	= $CFG->config['printlist'];
	$json_data = array();
	$tmp_array = NULL;
	
	$model_array = directory_map($printlist_basepath, 1);
// 	sort($model_array); // force make the order of model by alphabet of folder
	
	foreach ($model_array as $model_name) {
		$model_path = $printlist_basepath . $model_name . '/';
		$nb_pic = 0;
		
		// jump through the special models
// 		if ($model_name == PRINTLIST_MODEL_PRIME_L || $model_name == PRINTLIST_MODEL_PRIME_R) {
		if ($model_name[0] == PRINTLIST_MODEL_PREFIX_S) {
			continue;
		}
		// jump through the files (no folder object)
		if (!is_dir($model_path)) {
			continue;
		}
		
		try {
			$tmp_array = json_read($model_path . PRINTLIST_FILE_JSON, TRUE);
			if ($tmp_array['error']) {
				throw new Exception('read json error');
			}
		} catch (Exception $e) {
			// log internal error
			$CI = &get_instance();
			$CI->load->helper('printerlog');
			PrinterLog_logError('catch exception when getting print model ' . $model_name, __FILE__, __LINE__);
			continue; // just jump through the wrong data file
// 			return json_encode($json_data);
		}
// 		$tmp_array['json'][PRINTLIST_TITLE_ID] = ModelList_codeModelHash($model_name); //add model id to data array
		
		//blind picture url
		ModelList__blindUrl($tmp_array['json']);
		if ($set_localization) {
			ModelList__setLocalization($tmp_array['json']);
		}
		
		$json_data[PRINTLIST_TITLE_MODELS][] = $tmp_array['json']; //asign final data
	}
	$json_data[PRINTLIST_TITLE_VERSION] = PRINTLIST_VALUE_VERSION;
	
	return $json_data;
}

function ModelList__blindUrl(&$array_json) {
	$model_id = $array_json[PRINTLIST_TITLE_ID];
	
	if (isset($array_json[PRINTLIST_TITLE_PIC])
			&& count($array_json[PRINTLIST_TITLE_PIC])) {
		$nb_pic = count($array_json[PRINTLIST_TITLE_PIC]);
		for ($i=0; $i < $nb_pic; $i++) { //we cannot use foreach to change value
			$array_json[PRINTLIST_TITLE_PIC][$i] = PRINTLIST_GETPIC_BASE_WEB
				. '?' . PRINTLIST_GETPIC_PRM_MID . '=' . $model_id
				. '&' . PRINTLIST_GETPIC_PRM_PIC . '=' . ($i + 1);
// 				. '&name=' . $model_id . '.jpg';
		}
	}
	
	return;
}

function ModelList__setLocalization(&$array_json) {
	$CI = &get_instance();
	$lang_current = $CI->config->item('language_abbr');
	
	if (isset($array_json[PRINTLIST_TITLE_NAME][$lang_current])) {
		$array_json[PRINTLIST_TITLE_NAME] = $array_json[PRINTLIST_TITLE_NAME][$lang_current];
	}
	else {
		$array_json[PRINTLIST_TITLE_NAME] = $array_json[PRINTLIST_TITLE_NAME]['en'];
	}
	
	if (isset($array_json[PRINTLIST_TITLE_DESP][$lang_current])) {
		$array_json[PRINTLIST_TITLE_DESP] = $array_json[PRINTLIST_TITLE_DESP][$lang_current];
	}
	else {
		$array_json[PRINTLIST_TITLE_DESP] = $array_json[PRINTLIST_TITLE_DESP]['en'];
	}
	
	return;
}

function ModelList__getDetailAsArray($id_model, &$array_data, $set_localization = FALSE) {
	$array_data = NULL;
	$tmp_array = NULL;
	$json_path = NULL;
	$model_path = NULL;

	$model_cr = ModelList__find($id_model, $model_path);
	if (($model_cr == ERROR_OK) && $model_path) {
		$json_path = $model_path . PRINTLIST_FILE_JSON;
		$tmp_array = json_read($json_path, TRUE);
		if ($tmp_array['error']) {
			return ERROR_INTERNAL;
		}
		$array_data = $tmp_array['json'];
		ModelList__blindUrl($array_data);
		if ($set_localization) {
			ModelList__setLocalization($array_data);
		}
		
		return ERROR_OK;
	} else {
		return ERROR_UNKNOWN_MODEL;
	}
	
	return;
}

function ModelList__getDuration($id_model, &$duration) {
	//TODO finish this function if necessary
	// but now, print presliced file always finish in 20s (random select between 0 and 20)
	$json_data = NULL;

	$model_cr = ModelList__getDetailAsArray($id_model, $json_data);
	if (($model_cr == ERROR_OK) && $json_data) {
		$duration = $json_data[PRINTLIST_TITLE_TIME];
	
		return ERROR_OK;
	} else {
		return $model_cr;
	}
	
	return;
}

function ModelList__codeModelName($raw_name) {
	$CI = &get_instance();
	$CI->load->helper(array('detectos'));
	
	if (DectectOS_checkWindows()) {
		return utf8_decode($raw_name);
	}
	else {
		return $raw_name;
	}
}

function ModelList__changeColorName(&$color_code) {
	if ($color_code[0] == '#') {
		return ERROR_OK;
	} // return directly if color is already in hex code
	
	$array_color = array (
			'aliceblue'				=> '#f0f8ff',
			'antiquewhite'			=> '#faebd7',
			'aqua'					=> '#00ffff',
			'aquamarine'			=> '#7fffd4',
			'azure'					=> '#f0ffff',
			'beige'					=> '#f5f5dc',
			'bisque'				=> '#ffe4c4',
			'black'					=> '#000000',
			'blanchedalmond'		=> '#ffebcd',
			'blue'					=> '#0000ff',
			'blueviolet'			=> '#8a2be2',
			'brown'					=> '#a52a2a',
			'burlywood'				=> '#deb887',
			'cadetblue'				=> '#5f9ea0',
			'chartreuse'			=> '#7fff00',
			'chocolate'				=> '#d2691e',
			'coral'					=> '#ff7f50',
			'cornflowerblue'		=> '#6495ed',
			'cornsilk'				=> '#fff8dc',
			'crimson'				=> '#dc143c',
			'cyan'					=> '#00ffff',
			'darkblue'				=> '#00008b',
			'darkcyan'				=> '#008b8b',
			'darkgoldenrod'			=> '#b8860b',
			'darkgray'				=> '#a9a9a9',
			'darkgreen'				=> '#006400',
			'darkkhaki'				=> '#bdb76b',
			'darkmagenta'			=> '#8b008b',
			'darkolivegreen'		=> '#556b2f',
			'darkorange'			=> '#ff8c00',
			'darkorchid'			=> '#9932cc',
			'darkred'				=> '#8b0000',
			'darksalmon'			=> '#e9967a',
			'darkseagreen'			=> '#8fbc8f',
			'darkslateblue'			=> '#483d8b',
			'darkslategray'			=> '#2f4f4f',
			'darkturquoise'			=> '#00ced1',
			'darkviolet'			=> '#9400d3',
			'deeppink'				=> '#ff1493',
			'deepskyblue'			=> '#00bfff',
			'dimgray'				=> '#696969',
			'dodgerblue'			=> '#1e90ff',
			'firebrick'				=> '#b22222',
			'floralwhite'			=> '#fffaf0',
			'forestgreen'			=> '#228b22',
			'fuchsia'				=> '#ff00ff',
			'gainsboro'				=> '#dcdcdc',
			'ghostwhite'			=> '#f8f8ff',
			'gold'					=> '#ffd700',
			'goldenrod'				=> '#daa520',
			'gray'					=> '#808080',
			'green'					=> '#008000',
			'greenyellow'			=> '#adff2f',
			'honeydew'				=> '#f0fff0',
			'hotpink'				=> '#ff69b4',
			'indianred'				=> '#cd5c5c',
			'indigo '				=> '#4b0082',
			'ivory'					=> '#fffff0',
			'khaki'					=> '#f0e68c',
			'lavender'				=> '#e6e6fa',
			'lavenderblush'			=> '#fff0f5',
			'lawngreen'				=> '#7cfc00',
			'lemonchiffon'			=> '#fffacd',
			'lightblue'				=> '#add8e6',
			'lightcoral'			=> '#f08080',
			'lightcyan'				=> '#e0ffff',
			'lightgoldenrodyellow'	=> '#fafad2',
			'lightgrey'				=> '#d3d3d3',
			'lightgreen'			=> '#90ee90',
			'lightpink'				=> '#ffb6c1',
			'lightsalmon'			=> '#ffa07a',
			'lightseagreen'			=> '#20b2aa',
			'lightskyblue'			=> '#87cefa',
			'lightslategray'		=> '#778899',
			'lightsteelblue'		=> '#b0c4de',
			'lightyellow'			=> '#ffffe0',
			'lime'					=> '#00ff00',
			'limegreen'				=> '#32cd32',
			'linen'					=> '#faf0e6',
			'magenta'				=> '#ff00ff',
			'maroon'				=> '#800000',
			'mediumaquamarine'		=> '#66cdaa',
			'mediumblue'			=> '#0000cd',
			'mediumorchid'			=> '#ba55d3',
			'mediumpurple'			=> '#9370d8',
			'mediumseagreen'		=> '#3cb371',
			'mediumslateblue'		=> '#7b68ee',
			'mediumspringgreen'		=> '#00fa9a',
			'mediumturquoise'		=> '#48d1cc',
			'mediumvioletred'		=> '#c71585',
			'midnightblue'			=> '#191970',
			'mintcream'				=> '#f5fffa',
			'mistyrose'				=> '#ffe4e1',
			'moccasin'				=> '#ffe4b5',
			'navajowhite'			=> '#ffdead',
			'navy'					=> '#000080',
			'oldlace'				=> '#fdf5e6',
			'olive'					=> '#808000',
			'olivedrab'				=> '#6b8e23',
			'orange'				=> '#ffa500',
			'orangered'				=> '#ff4500',
			'orchid'				=> '#da70d6',
			'palegoldenrod'			=> '#eee8aa',
			'palegreen'				=> '#98fb98',
			'paleturquoise'			=> '#afeeee',
			'palevioletred'			=> '#d87093',
			'papayawhip'			=> '#ffefd5',
			'peachpuff'				=> '#ffdab9',
			'peru'					=> '#cd853f',
			'pink'					=> '#ffc0cb',
			'plum'					=> '#dda0dd',
			'powderblue'			=> '#b0e0e6',
			'purple'				=> '#800080',
			'red'					=> '#ff0000',
			'rosybrown'				=> '#bc8f8f',
			'royalblue'				=> '#4169e1',
			'saddlebrown'			=> '#8b4513',
			'salmon'				=> '#fa8072',
			'sandybrown'			=> '#f4a460',
			'seagreen'				=> '#2e8b57',
			'seashell'				=> '#fff5ee',
			'sienna'				=> '#a0522d',
			'silver'				=> '#c0c0c0',
			'skyblue'				=> '#87ceeb',
			'slateblue'				=> '#6a5acd',
			'slategray'				=> '#708090',
			'snow'					=> '#fffafa',
			'springgreen'			=> '#00ff7f',
			'steelblue'				=> '#4682b4',
			'tan'					=> '#d2b48c',
			'teal'					=> '#008080',
			'thistle'				=> '#d8bfd8',
			'tomato'				=> '#ff6347',
			'turquoise'				=> '#40e0d0',
			'violet'				=> '#ee82ee',
			'wheat'					=> '#f5deb3',
			'white'					=> '#ffffff',
			'whitesmoke'			=> '#f5f5f5',
			'yellow'				=> '#ffff00',
			'yellowgreen'			=> '#9acd32',
	);
	
	if (isset($array_color[$color_code])) {
		$color_code = $array_color[$color_code];
		return ERROR_OK;
	}
	else {
		return ERROR_WRONG_PRM;
	}
}
