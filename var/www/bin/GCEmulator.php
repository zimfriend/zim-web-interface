<?php
// Version: 1.0

define('RC_ERROR_OK',			0);
define('RC_ERROR_NO_PRM',		1);
define('RC_ERROR_INVALID_TEMP',	2);
define('RC_ERROR_NO_FILENAME',	3);
define('RC_ERROR_NO_FILE',		4);
define('RC_ERROR_INVALID_FILE',	5);

define('PRINTER_FN_CHARGE',			'_charge.gcode');
define('PRINTER_FN_RETRACT',		'_retract.gcode');
define('PRINTER_FN_PRINTPRIME_L',	'_print_prime_left.gcode');
define('PRINTER_FN_PRINTPRIME_R',	'_print_prime_right.gcode');

define('CEILING_COOL',	25);

$temppath = '';
$filepath = '';
$array_line = array();
$extruder_current = 'T0';
$temper_left = -1;
$temper_right = -1;
$temper_bed = -1;

function do_nothing() {
	return TRUE;
}

// treat input data
if (empty($_GET) && !empty($argv)) {
	$temppath = $argv[1];
	$filepath = $argv[2];
}
else if (!empty($_GET)) {
	$temppath = $_GET['p'];
	$filepath = $_GET['v'];
}
else {
	print "no prm";
	exit(RC_ERROR_NO_PRM);
}

// check temp path
if (!is_dir($temppath)) {
	print "invalid temp";
	exit(RC_ERROR_INVALID_TEMP);
}

// check filename
if (empty($filepath)) {
	print "no path";
	exit(RC_ERROR_NO_FILENAME);
}
else if (!file_exists($filepath)) {
	print "no file";
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
		$pos_m104 = -1;
		$pos_temp = -1;
		
		$line = trim($line, " \t\n\r\0\x0B");
		
		// do not count comment and empty line
		$pos_comment = strpos($line, ';');
		if (empty($line) || $pos_comment === 0) {
			continue;
		}
		
		// control current extruder
		foreach (array('T0', 'T1') as $extruder) {
			$pos_extruder = strpos($line, $extruder);
			
			if ($pos_extruder !== FALSE && $pos_extruder == 0) {
				// do not count key word in comment
				if ($pos_comment !== FALSE && $pos_comment < $pos_extruder) {
					continue;
				}
				$extruder_current = $extruder;
			}
		}
		
		// change of m109
		$pos_m109 = strpos($line, 'M109');
		if ($pos_m109 !== FALSE) {
			// do not count key word in comment
			if ($pos_comment !== FALSE && $pos_comment < $pos_m109) {
				do_nothing();
			}
			else {
				// get temperature
				$string_temp = '';
				
				// count M109 and TX in only one line
				if (strpos($line, 'T0') !== FALSE) {
					$extruder_set = 'T0';
				}
				else if (strpos($line, 'T1') !== FALSE) {
					$extruder_set = 'T1';
				} else {
					$extruder_set = $extruder_current;
				}
				
				//start output
				print ";modification of cubie start\r\n";
				$line[$pos_m109 + 3] = '4';
				print $line . "\r\n";
				if ($extruder_set == 'T0') {
					print "M1300 ;check temper\r\n";
				}
				else { // if ($extruder_set == 'T1')
					print "M1301 ;check temper\r\n";
				}
				
				if ($extruder_current != $extruder_set) {
					print $extruder_set . "\r\n";
				}
				
				// charge filament change
				if (!file_exists($temppath . PRINTER_FN_CHARGE)) {
					exit(RC_ERROR_INVALID_TEMP);
				}
				$string_temp = file_get_contents($temppath . PRINTER_FN_CHARGE);
				print $string_temp . "\r\n";
				
				// printing prime change
				if ($extruder_set == 'T0') {
					$string_temp = $temppath . PRINTER_FN_PRINTPRIME_R;
				}
				else { // if ($extruder_set == 'T1')
					$string_temp = $temppath . PRINTER_FN_PRINTPRIME_L;
				}
				if (!file_exists($string_temp)) {
					exit(RC_ERROR_INVALID_TEMP);
				}
				$string_temp = file_get_contents($string_temp);
				print $string_temp . "\r\n";
				
				if ($extruder_current != $extruder_set) {
					print $extruder_current . "\r\n";
				}
				print ";modification of cubie end\r\n";
				continue;
			}
		}
		
		// change of m104
		$pos_m104 = strpos($line, 'M104');
		if ($pos_m104 !== FALSE) {
			// do not count key word in comment
			if ($pos_comment !== FALSE && $pos_comment < $pos_m104) {
				do_nothing();
			}
			else {
				// get temperature
				$string_temp = '';
				$offset = 0;
				
				$pos_temp = strpos($line, 'S', 0);
				while ($pos_temp < $pos_m104) {
					++$offset;
					$pos_temp = strpos($line, 'S', $offset);
				}
				$string_temp = substr($line, $pos_temp + 1);
				
				// do not change heating m104
				if ((int)$string_temp > CEILING_COOL) {
					print $line . "\r\n";
					continue;
				}
				
				// count M104 and TX in only one line
				if (strpos($line, 'T0') !== FALSE) {
					$extruder_set = 'T0';
				}
				else if (strpos($line, 'T1') !== FALSE) {
					$extruder_set = 'T1';
				}
				else {
					$extruder_set = $extruder_current;
				}
				
				// start output
				print ";modification of cubie start\r\n";
				if ($extruder_current != $extruder_set) {
					print $extruder_set . "\r\n";
				}
				
				// retract filament change
				if (!file_exists($temppath . PRINTER_FN_RETRACT)) {
					exit(RC_ERROR_INVALID_TEMP);
				}
				$string_temp = file_get_contents($temppath . PRINTER_FN_RETRACT);
				print $string_temp . "\r\n";
				
				if ($extruder_current != $extruder_set) {
					print $extruder_current . "\r\n";
				}
				
				print $line . "\r\n";
				print ";modification of cubie end\r\n";
				continue;
			}
		}
		
		print $line . "\r\n";
	}	
}

exit(RC_ERROR_OK);
