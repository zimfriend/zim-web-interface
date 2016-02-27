<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

function json_read($source, $normal = FALSE) {

	//$normal: read normal json file if it is TRUE
    $retry = 0;

    do {
        $json = @file_get_contents($source);
        if ($json === false) {
            // Can't access file...
            if (file_exists($source)) {
                // ... cause it seems to be locked by the software layer
                if ($retry > 5) {
                    return array("error" => array("context" => "error",
                            "id" => "Internal error #5 (file locked ; if this message persists, thank you to contact our maintenance service)",
                            "cargo" => $source));
                }
                $retry = $retry + 1;
                usleep(500000);
                continue;
            } else {
                // ... cause it was deleted in the meanwhile
                return array("error" => array("context" => "error",
                        "id" => "Internal error #4 (file not found ; if this message persists, thank you to contact our maintenance service)",
                        "cargo" => $source));
            }
        }
    } while (false);

    // Json decoding
    $arr = json_decode($json, true);

//     if ($arr === null or !array_key_exists("Version", $arr)) {
    if ($arr === null or (!array_key_exists("Version", $arr) && ($normal == FALSE))) {
        // Json decoding error
        return array("error" => array("context" => "error",
                "id" => "Internal error #6 (invalid json ; if this message persists, thank you to contact our maintenance service)",
                "cargo" => $source));
    } else {
        return array("error" => null,
            "json" => $arr);
    }
}

// function json_unicode_decode($str){
// 	return preg_replace("/\\\u([0-9A-F]{4})/ie", "iconv('utf-16', 'utf-8', json__hex2str(\"$1\"))", $str);
// }

// function json__hex2str($hex) {
// 	$r = '';
// 	for ($i = 0; $i < strlen($hex) - 1; $i += 2)
// 		$r .= chr(hexdec($hex[$i] . $hex[$i + 1]));
// 	return $r;
// }

function json_encode_unicode($struct) {
	if (defined('JSON_UNESCAPED_UNICODE')) { // for PHP 5.4+
		return json_encode($struct, JSON_UNESCAPED_UNICODE);
	}
	return preg_replace("/\\\\u([a-f0-9]{4})/e", "iconv('UCS-4LE','UTF-8',pack('V', hexdec('U$1')))", json_encode($struct));
}
