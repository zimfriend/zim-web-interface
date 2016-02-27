<?php
// Version: 1.0

define('RC_ERROR_OK',			0);
define('RC_ERROR_NO_PRM',		1);
define('RC_ERROR_INVALID_PRM',	2);
define('RC_ERROR_NO_FILENAME',	3);
define('RC_ERROR_NO_FILE',		4);
define('RC_ERROR_INVALID_FILE',	5);

$parameter = '';
$filepath = '';
$array_line = array();
$nb_extruder = 1;
$extruder_current = 'T0';
$temper_left = -1;
$temper_right = -1;
$temper_bed = -1;
$array_output = array();

function do_nothing() {
	return TRUE;
}

// treat input data
if (empty($_GET) && !empty($argv)) {
	$parameter = $argv[1];
	$filepath = $argv[2];
}
else if (!empty($_GET)) {
	$parameter = $_GET['p'];
	$filepath = $_GET['v'];
}
else {
	exit(RC_ERROR_NO_PRM);
}

// check parameter
if ($parameter != '-t') {
	exit(RC_ERROR_INVALID_PRM);
}

// check filename
if (empty($filepath)) {
	exit(RC_ERROR_NO_FILENAME);
}
else if (!file_exists($filepath)) {
	exit(RC_ERROR_NO_FILE);
}
else {
	// main function
	// read file into array
	$array_line = file($filepath);
	
	foreach($array_line as $line) {
		$pos_extruder = -1;
		$pos_comment = -1;
		$pos_m109 = -1;
		$pos_m190 = -1;
		$pos_temp = -1;
		
		$line = trim($line, " \t\n\r\0\x0B");
		
		// do not count comment and empty line
		$pos_comment = strpos($line, ';');
		if (empty($line) || $pos_comment === 0) {
			continue;
		}
		
		// count number of extruders
		foreach (array('T0', 'T1') as $extruder) {
			$pos_extruder = strpos($line, $extruder);
			
			if ($pos_extruder !== FALSE) {
				// do not count key word in comment
				if ($pos_comment !== FALSE && $pos_comment < $pos_extruder) {
					continue;
				}
				if ($extruder == 'T1') {
					$nb_extruder = ($nb_extruder < 2) ? 2 : $nb_extruder;
				}
				$extruder_current = $extruder;
			}
		}
		
		// count value of extruder's temperature
		$pos_m109 = strpos($line, 'M109');
		if ($pos_m109 !== FALSE) {
			// do not count key word in comment
			if ($pos_comment !== FALSE && $pos_comment < $pos_m109) {
				do_nothing();
			}
			else {
				// get temperature
				$string_temp = '';
				$offset = 0;
				$pos_temp = strpos($line, 'S', 0);
				while ($pos_temp < $pos_m109) {
					++$offset;
					$pos_temp = strpos($line, 'S', $offset);
				}
				$string_temp = substr($line, $pos_temp + 1);
				
				// count M109 and TX in only one line
				if (strpos($line, 'T0') !== FALSE) {
					if ($temper_right == -1) {
						$temper_right = (int)$string_temp;
					}
				}
				else if (strpos($line, 'T1') !== FALSE) {
					if ($temper_left == -1) {
						$temper_left = (int)$string_temp;
					}
				}
				else {
					if ($extruder_current == 'T0') {
						if ($temper_right == -1) {
							$temper_right = (int)$string_temp;
						}
					}
					else if ($extruder_current == 'T1') {
						if ($temper_left == -1) {
							$temper_left = (int)$string_temp;
						}
					}
				}
			}
		}
		
		// count value of bed's temperature
		$pos_m190 = strpos($line, 'M190');
		if ($pos_m190 !== FALSE) {
			// do not count key word in comment
			if ($pos_comment !== FALSE && $pos_comment < $pos_m190) {
				do_nothing();
			}
			else {
				// get temperature
				$string_temp = '';
				$offset = 0;
				$pos_temp = strpos($line, 'S', 0);
				while ($pos_temp < $pos_m190) {
					++$offset;
					$pos_temp = strpos($line, 'S', $offset);
				}
				$string_temp = substr($line, $pos_temp + 1);
				
				if ($temper_bed == -1) {
					$temper_bed = (int)$string_temp;
				}
			}
		}
		
		// do not treat the rest if we get all info
		if ($temper_left != -1 && $temper_right != -1 && $temper_bed != -1) {
			break;
		}
	}
	
	$array_output['N'] = $nb_extruder;
	foreach(array('T0'=>$temper_right,'T1'=>$temper_left,'B'=>$temper_bed) as $key => $value) {
		if ($value == -1) {
			$array_output[$key] = 0;
		}
		else {
			$array_output[$key] = $value;
		}
	}
	
	print json_encode($array_output);
	
	exit(RC_ERROR_OK);
}
