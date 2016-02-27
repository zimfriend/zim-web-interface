<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

$CI = &get_instance();
$CI->load->helper(array('detectos', 'errorcode'));

if (!defined('ZIMAPI_CMD_LIST_SSID')) {
	define('ZIMAPI_CMD_LIST_SSID',		'sudo /usr/sbin/zeepro-list-ssid');
	define('ZIMAPI_CMD_CONFIG_NET',		'sudo /usr/sbin/zeepro-netconf ');
	define('ZIMAPI_CMD_SWIFI',			ZIMAPI_CMD_CONFIG_NET . 'sWifi');
	define('ZIMAPI_CMD_CWIFI',			ZIMAPI_CMD_CONFIG_NET . 'cWifi');
	define('ZIMAPI_CMD_PETH',			ZIMAPI_CMD_CONFIG_NET . 'pEth');
	define('ZIMAPI_CMD_CETH',			ZIMAPI_CMD_CONFIG_NET . 'cEth ');
	define('ZIMAPI_CMD_RESET_NETWORK',	ZIMAPI_CMD_CONFIG_NET . 'default');
	define('ZIMAPI_CMD_RESTART_WEB',	'sudo /etc/init.d/zeepro-network delayed-restart >> /var/log/network.log 2>&1 &');
	define('ZIMAPI_CMD_GATEWAY',		'route -n | awk \'$2 != "0.0.0.0" { print $2 }\' | sed -n 3p');
	define('ZIMAPI_CMD_DNS',			'grep nameserver /etc/resolv.conf | awk \'{print $2}\'');
// 	define('ZIMAPI_CMD_SERIAL',			'ifconfig -a | grep eth0 | awk \'{print $5}\' | head -n 1');
	define('ZIMAPI_CMD_SERIAL',			'[ -f /fab/macaddr.txt ] && cat /fab/macaddr.txt || ifconfig -a | grep eth0 | awk \'{print $5}\' | head -n 1');
	define('ZIMAPI_CMD_VERSION',		'zfw_printenv version`zfw_printenv last_good`');
	define('ZIMAPI_CMD_VERSION_REBOOT',	'zfw_printenv version`zfw_printenv update` || zfw_printenv version`zfw_printenv last_good`');
	define('ZIMAPI_CMD_PROFILE_LINK',	'cat /fab/profile.txt || cat /config/local/upgrade_profile');
	define('ZIMAPI_CMD_SETHOSTNAME',	ZIMAPI_CMD_CONFIG_NET . '-n ');
	define('ZIMAPI_CMD_GETHOSTNAME',	'cat /etc/hostname');
	define('ZIMAPI_CMD_USB_CONNECT',	'[ `cat /sys/class/gpio/gpio3_pg9/value` -eq 0 ]');
	define('ZIMAPI_CMD_CONFIG_SSH',		'/etc/init.d/remote_ssh ');
	define('ZIMAPI_CMD_SSH_ON',			ZIMAPI_CMD_CONFIG_SSH . 'start');
	define('ZIMAPI_CMD_SSH_OFF',		ZIMAPI_CMD_CONFIG_SSH . 'stop');
	define('ZIMAPI_CMD_SSH_STATUS',		ZIMAPI_CMD_CONFIG_SSH . 'status');
	
	define('ZIMAPI_GLOBAL_KEY_SERIAL',	'serial');
	
	define('ZIMAPI_TITLE_TOPOLOGY',	'topology');
	define('ZIMAPI_TITLE_MEDIUM',	'medium');
	define('ZIMAPI_TITLE_SSID',		'ssid');
	define('ZIMAPI_TITLE_IP',		'ip');
	define('ZIMAPI_TITLE_IPV6',		'ipv6');
	define('ZIMAPI_TITLE_GATEWAY',	'gateway');
	define('ZIMAPI_TITLE_DNS',		'dns');
	define('ZIMAPI_TITLE_MAC',		'mac');
	define('ZIMAPI_TITLE_MASK',		'mask');
	define('ZIMAPI_TITLE_MODE',		'mode');
	define('ZIMAPI_TITLE_CUSTOM',	'ipv4');
	define('ZIMAPI_TITLE_CUS_IP',	'user_assigned_address');
	define('ZIMAPI_TITLE_CUS_GW',	'user_assigned_gateway');
	define('ZIMAPI_TITLE_CUS_MK',	'user_assigned_mask');
	define('ZIMAPI_TITLE_PASSWD',	'password');
	define('ZIMAPI_TITLE_VERSION',	'Version');
	
	define('ZIMAPI_VALUE_ETH',		'eth');
	define('ZIMAPI_VALUE_WIFI',		'wifi');
	define('ZIMAPI_VALUE_NETWORK',	'network');
	define('ZIMAPI_VALUE_P2P',		'p2p');
	define('ZIMAPI_MODE_CETH',		'cEth');
	
	if (DectectOS_checkWindows()) {
		define('ZIMAPI_FILEPATH_TIMELAPSE',	$CI->config->item('temp') . 'timelapse.mp4');
		define('ZIMAPI_FILEPATH_TL_TMPIMG',	$CI->config->item('temp') . 'img0001.jpg');
		define('ZIMAPI_FILEPATH_CAPTURE',	$CI->config->item('bin') . 'capture.jpg');
		define('ZIMAPI_FILEPATH_ENDPRINT',	$CI->config->item('bin') . 'timelapse_end_print.sh');
		define('ZIMAPI_FILEPATH_ENDCANCEL',	$CI->config->item('bin') . 'timelapse_end_cancel.sh');
		define('ZIMAPI_FILEPATH_POSTHEAT',	$CI->config->item('bin') . 'timelapse_post_heat.sh');
		define('ZIMAPI_FILEPATH_PREFINISH',	$CI->config->item('bin') . 'timelapse_pre_finish.sh');
		define('ZIMAPI_FILEPATH_UPGRADE',	$CI->config->item('conf') . 'profile.json');
		define('ZIMAPI_FILEPATH_VIDEO_TS',	$CI->config->item('temp') . 'zim0.ts');
		define('ZIMAPI_FILEPATH_UPGDNOTE',	$CI->config->item('nandconf') . 'release_note.xml');
		define('ZIMAPI_FILEPATH_UPGDNOTES',	'./data/release_notes.xml');
	}
	else {
		define('ZIMAPI_FILEPATH_TIMELAPSE',	'/var/www/tmp/timelapse.mp4');
		define('ZIMAPI_FILEPATH_TL_TMPIMG',	'/var/www/tmp/timelapse/img0001.jpg');
		define('ZIMAPI_FILEPATH_CAPTURE',	'/var/www/tmp/image.jpg');
		define('ZIMAPI_FILEPATH_ENDPRINT',	'/var/www/bin/timelapse_end_print.sh');
		define('ZIMAPI_FILEPATH_ENDCANCEL',	'/var/www/bin/timelapse_end_cancel.sh');
		define('ZIMAPI_FILEPATH_POSTHEAT',	'/var/www/bin/timelapse_post_heat.sh');
		define('ZIMAPI_FILEPATH_PREFINISH',	'/var/www/bin/timelapse_pre_finish.sh');
		define('ZIMAPI_FILEPATH_UPGRADE',	'/config/conf/profile.json');
		define('ZIMAPI_FILEPATH_VIDEO_TS',	'/var/www/tmp/zim0.ts');
		define('ZIMAPI_FILEPATH_UPGDNOTE',	'/config/release_note.xml');
		define('ZIMAPI_FILEPATH_UPGDNOTES',	'/var/www/data/release_notes.xml');
	}
	
	define('ZIMAPI_FILENAME_CAMERA',	'Camera.json');
	define('ZIMAPI_FILENAME_SOFTWARE',	'Software.json');
	define('ZIMAPI_FILENAME_TIMELAPSE',	'timelapse.mp4');
	define('ZIMAPI_PRM_CAMERA_PRINTSTART',
			' -v quiet -r 15 -s 640x480 -f video4linux2 -i /dev/video0 -vf "crop=640:360:0:60" -minrate 512k -maxrate 512k -bufsize 2512k -map 0 -force_key_frames "expr:gte(t,n_forced*2)" -c:v libx264 -r 15 -threads 2 -crf 35 -profile:v baseline -b:v 512k -pix_fmt yuv420p -flags -global_header -f hls -hls_time 5 -hls_wrap 20 -hls_list_size 10 /var/www/tmp/zim.m3u8');
	define('ZIMAPI_PRM_CAMERA_PRINTSTART_TIMELAPSE',
			' -v quiet -r 15 -s 640x480 -f video4linux2 -i /dev/video0 -vf "crop=640:360:0:60" -minrate 512k -maxrate 512k -bufsize 2512k -map 0 -force_key_frames "expr:gte(t,n_forced*2)" -c:v libx264 -r 15 -threads 2 -crf 35 -profile:v baseline -b:v 512k -pix_fmt yuv420p -flags -global_header -f hls -hls_time 5 -hls_wrap 20 -hls_list_size 10 /var/www/tmp/zim.m3u8 -f image2 -vf fps=fps={fps} -qscale:v 2 /var/www/tmp/timelapse/img%04d.jpg');
	define('ZIMAPI_PRM_CAMERA_STOP',	' stop ');
	define('ZIMAPI_PRM_END_TIMELAPSE',	' clean_tl ');
	define('ZIMAPI_PRM_CAMERA_CAPTURE',
			' -v quiet -f video4linux2 -i /dev/video0 -y -vframes 1 /var/www/tmp/image.jpg');
// 	define('ZIMAPI_TITLE_MODE',			'mode');
	define('ZIMAPI_TITLE_COMMAND',		'command');
	define('ZIMAPI_VALUE_MODE_OFF',		'off');
	define('ZIMAPI_VALUE_MODE_HLS',		'hls');
	define('ZIMAPI_VALUE_MODE_HLS_IMG',	'hls+image');
	define('ZIMAPI_TITLE_PRESET',		'preset');
	define('ZIMAPI_PRM_UTIL_REBOOT',	' reboot');
	
	define('ZIMAPI_TITLE_PRESET_ID',		'id');
	define('ZIMAPI_TITLE_PRESET_NAME',		'name');
	define('ZIMAPI_TITLE_PRESET_INFILL',	'fill_density');
	define('ZIMAPI_TITLE_PRESET_SKIRT',		'skirts');
	define('ZIMAPI_TITLE_PRESET_RAFT',		'raft_layers');
	define('ZIMAPI_TITLE_PRESET_SUPPORT',	'support_material');
	
	define('ZIMAPI_TITLE_RELEASENOTE_VERSION',		'version');
	define('ZIMAPI_TITLE_RELEASENOTE_PART',			'part');
	define('ZIMAPI_TITLE_RELEASENOTE_PART_TITLE',	'title');
	define('ZIMAPI_TITLE_RELEASENOTE_PART_NOTE',	'note');
	define('ZIMAPI_TITLE_RELEASENOTE_UPGRADE',		'upgrade');
	define('ZIMAPI_TITLE_RELEASENOTE_ATTRIB_LANG',	'lang');
	
	define('ZIMAPI_VALUE_DEFAULT_RHO',			800);
	define('ZIMAPI_VALUE_DEFAULT_DELTA',		45);
	define('ZIMAPI_VALUE_DEFAULT_THETA',		30);
	define('ZIMAPI_VALUE_DEFAULT_LENGTH',		8000);
	define('ZIMAPI_VALUE_DEFAULT_SPEED',		0.78);
	define('ZIMAPI_VALUE_DEFAULT_TL_LENGTH',	30);
	define('ZIMAPI_VALUE_DEFAULT_TL_OFFSET',	5); // 5 secondes
	define('ZIMAPI_VALUE_MANDRILL_KEY',			'fvdIarvGVCRpDHmV41swgA');
	define('ZIMAPI_VALUE_TL_FROM_EMAIL',		'zim-motion@zeepro.com');
	define('ZIMAPI_VALUE_TL_FROM_NAME',			'Zim');
	define('ZIMAPI_VALUE_TL_SUBACCOUNT',		'zim-motion');
	define('ZIMAPI_VALUE_TL_MIMETYPE',			'video/mp4');
	define('ZIMAPI_VALUE_TL_IP_POOL',			'Main Pool');
	define('ZIMAPI_VALUE_TL_VIDEO_NAME',		'zimmotion.mp4');
	define('ZIMAPI_VALUE_TL_MANDRILL_API',		'https://mandrillapp.com/api/1.0/messages/send.json');
	define('ZIMAPI_VALUE_RELEASENOTE_URL',		'http://zimsupport.zeepro.com/support/solutions/articles/5000050231-release-notes');
	define('ZIMAPI_VALUE_PRESETS_CS_NAME',		'cs_presets.tar.bz2');
	
	define('ZIMAPI_PRM_CAPTURE',	'picture');
	define('ZIMAPI_PRM_VIDEO_MODE',	'video');
	define('ZIMAPI_PRM_PRESET',		'slicerpreset');
	define('ZIMAPI_PRM_PASSWD',		'password');
	define('ZIMAPI_PRM_SSO_NAME',	'name');
	define('ZIMAPI_PRM_UPGRADE',	'upgrade');
	define('ZIMAPI_PRM_PROXY',		'tromboning');
	define('ZIMAPI_PRM_SSH',		'remotecontrol');
	define('ZIMAPI_PRM_STATS',		'stats');
	
	define('ZIMAPI_FILE_PRESET_JSON',	'preset.json');
	define('ZIMAPI_FILE_PRESET_INI',	'config.ini');
	define('ZIMAPI_FILE_SSO_NAME',		'SSOActivation.txt');
}

function ZimAPI_initialFile() {
	global $CFG;
	$setting_fullpath = $CFG->config['conf'] . ZIMAPI_FILENAME_SOFTWARE;
	
	if (file_exists($setting_fullpath)) {
		return TRUE;
	}
	else {
		// prepare data array
		$cr = 0;
		$array_preset = ZimAPI_getPresetListAsArray();
		
		$data_json = array(
				ZIMAPI_TITLE_VERSION	=> '1.0',
				ZIMAPI_TITLE_PASSWD		=> md5(''),
				ZIMAPI_TITLE_PRESET		=> NULL,
		);
		
		// write json file
		$fp = fopen($setting_fullpath, 'w');
		if ($fp) {
			fwrite($fp, json_encode($data_json));
			fclose($fp);
			chmod($setting_fullpath, 0777);
		}
		else {
			return FALSE;
		}
		
		$cr = ZimAPI_setPreset($array_preset[0][ZIMAPI_TITLE_PRESET_ID]);
		if ($cr != ERROR_OK) {
			unlink($setting_fullpath);
			return FALSE;
		}
	}
	
	return TRUE;
}

function ZimAPI_getNetworkInfoAsArray(&$array_data) {
	$output = NULL;
	$ret_val = 0;
	
	// detect OS type, if windows, just do simulation
	if (DectectOS_checkWindows()) {
		$ret_val = ERROR_NORMAL_RC_OK;
// 		$output = array(
// 				'MODE: pEth',
// 				'IP Config:',
// 				'addr:192.168.1.99  Bcast:192.168.1.255  Mask:255.255.255.0',
// 				'addr6: fe80::f60e:11ff:fe80:1a/64 Scope:Link',
// 				'MAC: 0c:82:68:21:69:57',
// 		);
// 		$output = array(
// 				'MODE: cEth',
// 				'IP Config:',
// 				'addr:192.168.1.99  Bcast:192.168.1.255  Mask:255.255.255.0',
// 				'addr6: fe80::f60e:11ff:fe80:1a/64 Scope:Link',
// 				'MAC: 0c:82:68:21:69:57',
// 		);
// 		$output = array(
// 				'MODE: sWifi',
// 				'SSID: zim_peng',
// 				'PASSWORD:',
// 				'IP Config:',
// 				'addr:10.0.0.1  Bcast:10.255.255.255  Mask:255.0.0.0',
// 				'addr6: fe80::f60e:11ff:fe80:1a/64 Scope:Link',
// 				'MAC: 0c:82:68:21:69:57',
// 		);
		$output = array(
				'MODE: cWifi',
				'ACCESS POINT: ssid="freebox_zeepro"',
				'IP Config:',
				'addr:192.168.1.41  Bcast:192.168.1.255  Mask:255.255.255.0',
// 				'addr6: fe80::f60e:11ff:fe80:1a/64 Scope:Link',
				'MAC: 0c:82:68:21:69:57',
		);
// 		$output = array(
// 				'MODE: cWifi',
// 				'ACCESS POINT: ssid="TG1672G02"',
// 				'IP Config:',
// 				'addr:192.168.0.11  Bcast:192.168.0.255  Mask:255.255.255.0',
// 				'addr6: 2605:6000:6d84:c000:2e1:40ff:fe19:e7/64 Scope:Global',
// 				'addr6: fe80::2e1:40ff:fe19:e7/64 Scope:Link',
// 				'MAC: 00:e1:40:19:00:e7',
// 		);
	}
	else {
		exec(ZIMAPI_CMD_CONFIG_NET, $output, $ret_val);
	}
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		return ERROR_INTERNAL;
	}
	else {
		$flag_ok = 0; // 0000
		/*  LSB => MSB:
			1 => ZIMAPI_TITLE_MODE & ZIMAPI_TITLE_MEDIUM & ZIMAPI_TITLE_TOPOLOGY,
			2 => ZIMAPI_TITLE_SSID (optional, no value in mode *Eth),
			4 => ZIMAPI_TITLE_IP & ZIMAPI_TITLE_MASK,
			8 => ZIMAPI_TITLE_MAC
			we treat ip v6 as a parameter totally optional
			so we should get 15 (1111) for *Wifi mode, and 13 (1101) for *Eth mode
		 */
		foreach ($output as $line) {
			// get medium and potology
			if (strpos($line, 'MODE') === 0) {
				$array_data[ZIMAPI_TITLE_MODE] = substr($line, 6);
				if (strpos($line, 'Eth') !== FALSE) {
					$array_data[ZIMAPI_TITLE_MEDIUM] = ZIMAPI_VALUE_ETH;
					$array_data[ZIMAPI_TITLE_TOPOLOGY] = ZIMAPI_VALUE_NETWORK;
				}
				else if (strpos($line, 'Wifi') !== FALSE) {
					$array_data[ZIMAPI_TITLE_MEDIUM] = ZIMAPI_VALUE_WIFI;
					if (strpos($line, 'cWifi') !== FALSE) {
						$array_data[ZIMAPI_TITLE_TOPOLOGY] = ZIMAPI_VALUE_NETWORK;
					}
					else {
						$array_data[ZIMAPI_TITLE_TOPOLOGY] = ZIMAPI_VALUE_P2P;
					}
				}
				else {
					return ERROR_INTERNAL;
				}
				$flag_ok |= 1;
			}
			// get ssid (mode cWifi)
			// $array_data[ZIMAPI_TITLE_TOPOLOGY] == ZIMAPI_VALUE_NETWORK
			// && $array_data[ZIMAPI_TITLE_MEDIUM] != ZIMAPI_VALUE_ETH
			else if (strpos($line, 'ACCESS POINT') === 0) {
				$array_data[ZIMAPI_TITLE_SSID] = substr($line, strpos($line, 'ssid=') + 6, -1);
				$flag_ok |= 2;
			}
			// get ssid (mode sWifi)
			else if (strpos($line, 'SSID') === 0) {
				$array_data[ZIMAPI_TITLE_SSID] = substr($line, 6);
				$flag_ok |= 2;
			}
			// get ip v6
			else if (strpos($line, 'addr6') === 0) {
				$array_temp = explode(' ', str_replace('  ', ' ', $line));
				if (count($array_temp) > 2) {
					$array_data[ZIMAPI_TITLE_IPV6][trim($array_temp[2])] = trim($array_temp[1]);
				}
			}
			// get ip v4
			else if (strpos($line, 'addr') === 0) { // && strpos($line, 'addr6') === FALSE
				$array_temp = explode(' ', str_replace('  ', ' ', $line));
				if (count($array_temp) > 2) {
					$array_data[ZIMAPI_TITLE_IP] = substr($array_temp[0], 5);
					$array_data[ZIMAPI_TITLE_MASK] = substr($array_temp[2], 5);
					$flag_ok |= 4;
				}
			}
			// get mac
			else if (strpos($line, 'MAC') === 0) {
				$array_data[ZIMAPI_TITLE_MAC] = substr($line, 5);
				$flag_ok |= 8;
			}
		}
		
		if (($array_data[ZIMAPI_TITLE_MEDIUM] == ZIMAPI_VALUE_WIFI && $flag_ok != 15)
				|| ($array_data[ZIMAPI_TITLE_MEDIUM] == ZIMAPI_VALUE_ETH && $flag_ok != 13)) {
			return ERROR_INTERNAL;
		}
	}
	
	// get gateway if not P2P
	if ($array_data[ZIMAPI_TITLE_TOPOLOGY] == ZIMAPI_VALUE_NETWORK) {
		$output = array();
		if (DectectOS_checkWindows()) {
			$ret_val = ERROR_NORMAL_RC_OK;
			$output = array('192.168.1.254');
		}
		else {
			exec(ZIMAPI_CMD_GATEWAY, $output, $ret_val);
		}
		if ($ret_val != ERROR_NORMAL_RC_OK) {
			return ERROR_INTERNAL;
		}
		if (count($output)) {
			$array_data[ZIMAPI_TITLE_GATEWAY] = $output[0];
		}
		else {
			//TODO check here if it's better to return internal error here or not
			$array_data[ZIMAPI_TITLE_GATEWAY] = NULL;
		}
	}
	
	// get DNS
	$output = array();
	if (DectectOS_checkWindows()) {
		$ret_val = ERROR_NORMAL_RC_OK;
		$output = array('8.8.8.8');
	}
	else {
		exec(ZIMAPI_CMD_DNS, $output, $ret_val);
	}
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		return ERROR_INTERNAL;
	}
	if (count($output)) {
		$array_data[ZIMAPI_TITLE_DNS] = $output[0];
	}
	else {
		$array_data[ZIMAPI_TITLE_DNS] = NULL;
	}
	
	return ERROR_OK;
}

function ZimAPI_getNetworkIP(&$json_data) {
	$return_array = array();
	$array_data = array();
	$ret_val = ZimAPI_getNetworkInfoAsArray($array_data);
	
	if ($ret_val == ERROR_OK) {
		$return_array = array(
				ZIMAPI_TITLE_IP			=> $array_data[ZIMAPI_TITLE_IP],
				ZIMAPI_TITLE_GATEWAY	=> $array_data[ZIMAPI_TITLE_GATEWAY],
				ZIMAPI_TITLE_DNS		=> $array_data[ZIMAPI_TITLE_DNS],
				ZIMAPI_TITLE_MAC		=> $array_data[ZIMAPI_TITLE_MAC],
		);
		$json_data = json_encode($return_array);
	}
	
	return $ret_val;
}

function ZimAPI_getNetwork(&$json_data) {
	$return_array = array();
	$array_data = array();
	$ret_val = ZimAPI_getNetworkInfoAsArray($array_data);
	
	if ($ret_val == ERROR_OK) {
		$return_array = array(
					ZIMAPI_TITLE_TOPOLOGY	=> $array_data[ZIMAPI_TITLE_TOPOLOGY],
					ZIMAPI_TITLE_MEDIUM		=> $array_data[ZIMAPI_TITLE_MEDIUM],
		);
		
		if ($array_data[ZIMAPI_TITLE_MEDIUM] == ZIMAPI_VALUE_WIFI) {
			$return_array[ZIMAPI_TITLE_SSID] = $array_data[ZIMAPI_TITLE_SSID];
		}
		else if ($array_data[ZIMAPI_TITLE_MODE] == ZIMAPI_MODE_CETH) {
			$return_array[ZIMAPI_TITLE_CUSTOM] = array(
					ZIMAPI_TITLE_CUS_IP	=> $array_data[ZIMAPI_TITLE_IP],
					ZIMAPI_TITLE_CUS_GW	=> $array_data[ZIMAPI_TITLE_GATEWAY],
					ZIMAPI_TITLE_CUS_MK	=> $array_data[ZIMAPI_TITLE_MASK],
			);
		}
		
		$json_data = json_encode($return_array);
	}
	
	return $ret_val;
}

function ZimAPI_listSSID() {
	return json_encode(ZimAPI_listSSIDAsArray());
}

function ZimAPI_listSSIDAsArray() {
	try {
		// detect OS type, if windows, just do simulation
		if (DectectOS_checkWindows()) {
			$list_ssid = "\"freebox_zeepro\"\n\"livebox_zeepro\"\n\"bbox_zeepro\"\n\"freebox_zeepro\"\n\"\"\n";
		}
		else {
			$list_ssid = shell_exec(ZIMAPI_CMD_LIST_SSID);
		}
		$ssid = explode("\n", str_replace('"', '', $list_ssid));
		$ssid = array_unique(array_filter($ssid)); // remove empty and duplicate name
	} catch (Exception $e) {
		$ssid = array();
	}
	return $ssid;
}

function ZimAPI_setsWifi($nameWifi, $passWifi = '') {
	$CI = &get_instance();
	$CI->load->helper(array('corestatus'));
	
	if (!DectectOS_checkWindows()) {
		try {
			$command = '';
			$output = NULL;
			$ret_val = 0;
			
			//treat some special characters
			if (!ctype_print($nameWifi) || ($passWifi && !ctype_print($passWifi))) {
				return ERROR_WRONG_PRM;
			}
			$nameWifi = ZimAPI__filterCharacter($nameWifi); //str_replace('"', '\"', $nameWifi);
			
			if (strlen($passWifi) == 0) {
				$command = ZIMAPI_CMD_SWIFI . ' ' . $nameWifi;
			}
			else {
				// check password length
				if (strlen($passWifi) < 8 || strlen($passWifi) > 64) {
					return ERROR_WRONG_PRM;
				}
				
				$passWifi = ZimAPI__filterCharacter($passWifi); //str_replace('"', '\"', $passWifi);
				
				// use WPA crypt as default
				$command = ZIMAPI_CMD_SWIFI . ' ' . $nameWifi . ' wpa ' . $passWifi;
			}
			exec($command, $output, $ret_val);
			if ($ret_val != ERROR_NORMAL_RC_OK) {
				return ERROR_INTERNAL;
			}
			else {
// 				$retry_once = TRUE;
				
// 				do {
// 					exec(ZIMAPI_CMD_RESTART_WEB, $output, $ret_val);
// 				} while (($ret_val != ERROR_NORMAL_RC_OK)
// 						&& ($retry_once == TRUE) && ($retry_once = FALSE));
				// we can not get return value because of '&'
			//	ZimAPI_restartNetwork();
			}
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
	}
	
	$ret_val = CoreStatus_finishConnection(array('type'=>'sWifi', "name"=>$nameWifi, "passwd"=>$passWifi));
	if ($ret_val == FALSE) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('finish connection in sWifi error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ZimAPI_setcWifi($nameWifi, $passWifi = '') {
	$CI = &get_instance();
	$CI->load->helper(array('corestatus', 'printerlog'));
	
	if (!DectectOS_checkWindows()) {
		try {
			$command = '';
			$output = NULL;
			$ret_val = 0;
			
			//treat some special characters
			if (!ctype_print($nameWifi) || ($passWifi && !ctype_print($passWifi))) {
				return ERROR_WRONG_PRM;
			}
			$nameWifi = ZimAPI__filterCharacter($nameWifi); //str_replace('"', '\"', $nameWifi);
			
			if (strlen($passWifi) == 0) {
				$command = ZIMAPI_CMD_CWIFI . ' ' . $nameWifi;
			}
			else {
				// check password length
				if (strlen($passWifi) < 8 || strlen($passWifi) > 64) {
					return ERROR_WRONG_PRM;
				}
				
				$passWifi = ZimAPI__filterCharacter($passWifi); //str_replace('"', '\"', $passWifi);
				
				// use WPA crypt as default
				$command = ZIMAPI_CMD_CWIFI . ' ' . $nameWifi . ' ' . $passWifi;
			}
			exec($command, $output, $ret_val);
			if ($ret_val != ERROR_NORMAL_RC_OK) {
				return ERROR_INTERNAL;
			}
			else {
// 				$retry_once = TRUE;
				
// 				do {
// 					exec(ZIMAPI_CMD_RESTART_WEB, $output, $ret_val);
// 				} while (($ret_val != ERROR_NORMAL_RC_OK)
// 						&& ($retry_once == TRUE) && ($retry_once = FALSE));
				// we can not get return value because of '&'
			//	ZimAPI_restartNetwork();
			}
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
	}
	
	$ret_val = CoreStatus_finishConnection(array('type'=>'cWifi', "name"=>$nameWifi, "passwd"=>$passWifi));
	if ($ret_val == FALSE) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('finish connection in cWifi error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ZimAPI_setcEth($ip = '', $mask = '', $gateWay = '') {
	$CI = &get_instance();
	$CI->load->helper(array('corestatus', 'printerlog'));
	
	if (!DectectOS_checkWindows()) {
		$command = '';
		$output = NULL;
		$ret_val = 0;
		
		if (strlen($ip . $mask . $gateWay) == 0) {
			$command = ZIMAPI_CMD_PETH;
		}
		else if (filter_var($ip, FILTER_VALIDATE_IP)
				&& filter_var($mask, FILTER_VALIDATE_IP)
				&& filter_var($gateWay, FILTER_VALIDATE_IP)) {
			//TODO check mask work with gateway
			$command = ZIMAPI_CMD_CETH . $ip . ' ' . $mask . ' ' . $gateWay;
		}
		else {
			return ERROR_WRONG_PRM;
		}
		
		try {
			exec($command, $output, $ret_val);
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
			
		if ($ret_val != ERROR_NORMAL_RC_OK) {
			return ERROR_INTERNAL;
		}
		else {
		//	ZimAPI_restartNetwork();
		}
	}
	
	$ret_val = CoreStatus_finishConnection(array('type'=>'Eth'));
	if ($ret_val == FALSE) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('finish connection in Eth error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ZimAPI_setpEth() {	
	return ZimAPI_setcEth();
}

function ZimAPI_setHostname($hostname, $restart = TRUE) {
	// check characters
	if (strlen($hostname) > 9 || strlen($hostname) == 0) {
		return ERROR_MISS_PRM;
	}
	else if (preg_match('/^[A-Za-z0-9][A-Za-z0-9\-]{0,7}[A-Za-z0-9]$|^[A-Za-z0-9]$/', $hostname)) {
		$ret_val = 0;
		$output = array();
		$command = ZIMAPI_CMD_SETHOSTNAME . $hostname;
		
		// do nothing for windows
		if (DectectOS_checkWindows()) {
			return ERROR_OK;
		}
		
		try {
			exec($command, $output, $ret_val);
			
			if ($ret_val != ERROR_NORMAL_RC_OK) {
				return ERROR_INTERNAL;
			}
			else {
				if ($restart == TRUE) {
					ZimAPI_restartNetwork();
				}
				
				return ERROR_OK;
			}
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
	}
	else {
		return ERROR_WRONG_PRM;
	}
	
	return ERROR_INTERNAL; // never reach here
}

function ZimAPI_getHostname(&$hostname) {
	$output = array();
	$ret_val = 0;
	$hostname = "zim";
	
	// do nothing for windows
	if (DectectOS_checkWindows()) {
		return ERROR_OK;
	}
	
	try {
		exec(ZIMAPI_CMD_GETHOSTNAME, $output, $ret_val);
			
		if ($ret_val != ERROR_NORMAL_RC_OK) {
			return ERROR_INTERNAL;
		}
		else {
			if (count($output) <= 0) {
				return ERROR_INTERNAL;
			}
			$hostname = $output[0];
			
			return ERROR_OK;
		}
	} catch (Exception $e) {
		return ERROR_INTERNAL;
	}
	
	return ERROR_INTERNAL; // never reach here
}

function ZimAPI_setNetwork($string_json) {
	$array_config = json_decode($string_json);
	
	if ($array_config) {
		if (isset($array_config[ZIMAPI_TITLE_TOPOLOGY]) && isset($array_config[ZIMAPI_TITLE_MEDIUM])) {
			if ($array_config[ZIMAPI_TITLE_MEDIUM] == ZIMAPI_VALUE_WIFI) {
				if (!isset($array_config[ZIMAPI_TITLE_SSID]) || !isset($array_config[ZIMAPI_TITLE_PASSWD])) {
					return ERROR_MISS_PRM;
				}
				
				$ssid = $array_config[ZIMAPI_TITLE_SSID];
				$pwd = $array_config[ZIMAPI_TITLE_PASSWD];
				
				if ($array_config[ZIMAPI_TITLE_TOPOLOGY] == ZIMAPI_VALUE_NETWORK) {
					return ZimAPI_setcWifi($ssid, $pwd);
				}
				else if ($array_config[ZIMAPI_TITLE_TOPOLOGY] == ZIMAPI_VALUE_P2P) {
					return ZimAPI_setsWifi($ssid, $pwd);
				}
				else {
					return ERROR_WRONG_PRM;
				}
			}
			else if ($array_config[ZIMAPI_TITLE_MEDIUM] == ZIMAPI_VALUE_ETH
					&& $array_config[ZIMAPI_TITLE_TOPOLOGY] == ZIMAPI_VALUE_NETWORK) {
				if (isset($array_config[ZIMAPI_TITLE_CUS_IP])
						|| isset($array_config[ZIMAPI_TITLE_CUS_GW])
						|| isset($array_config[ZIMAPI_TITLE_CUS_MK])) {
					if (!isset($array_config[ZIMAPI_TITLE_CUS_IP])
							|| !isset($array_config[ZIMAPI_TITLE_CUS_GW])
							|| !isset($array_config[ZIMAPI_TITLE_CUS_MK])) {
						return ERROR_MISS_PRM;
					}
					$ip = $array_config[ZIMAPI_TITLE_CUS_IP];
					$gateway = $array_config[ZIMAPI_TITLE_CUS_GW];
					$mask = $array_config = $array_config[ZIMAPI_TITLE_CUS_MK];
					
					return ZimAPI_setcEth($ip, $mask, $gateway);
				}
				else {
					return ZimAPI_setpEth();
				}
			}
			else {
				return ERROR_WRONG_PRM;
			}
		}
		else {
			return ERROR_MISS_PRM;
		}
	}
	else {
		return ERROR_WRONG_PRM;
	}
}

function ZimAPI_restartNetwork() {
	exec(ZIMAPI_CMD_RESTART_WEB);
}

function ZimAPI_resetNetwork() {
	$CI = &get_instance();
	$CI->load->helper(array('corestatus'));
	
	if (!DectectOS_checkWindows()) {
		try {
			$command = '';
			$output = NULL;
			$ret_val = 0;
			
			ZimAPI_setHostname("zim");
			
			exec(ZIMAPI_CMD_RESET_NETWORK, $output, $ret_val);
			if ($ret_val != ERROR_NORMAL_RC_OK) {
				return ERROR_INTERNAL;
			}
			else {
// 				$retry_once = TRUE;
				
// 				do {
// 					exec(ZIMAPI_CMD_RESTART_WEB, $output, $ret_val);
// 				} while (($ret_val != ERROR_NORMAL_RC_OK)
// 						&& ($retry_once == TRUE) && ($retry_once = FALSE));
				// we can not get return value because of '&'
				ZimAPI_restartNetwork();
			}
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
	}
	
	$ret_val = CoreStatus_wantConnection();
	if ($ret_val == FALSE) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('want connection in reset error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ZimAPI_checkCamera(&$info_camera = '') {
	global $CFG;
	$camera_file = $CFG->config['temp'] . ZIMAPI_FILENAME_CAMERA;
	$tmp_array = array();
	
	$CI = &get_instance();
	$CI->load->helper('json');
	
	if (!file_exists($camera_file)) {
		$info_camera = ZIMAPI_VALUE_MODE_OFF;
		return TRUE;
	}
	
	// read json file
	try {
		$tmp_array = json_read($camera_file, TRUE);
		if ($tmp_array['error']) {
			throw new Exception('read json error');
		}
	} catch (Exception $e) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read camera status file error', __FILE__, __LINE__);
		return FALSE;
	}
	$info_camera = $tmp_array['json'][ZIMAPI_TITLE_MODE];
	
	return TRUE;
}

function ZimAPI_checkCameraPassword($password) {
	$tmp_array = array();
	$CI = &get_instance();
	$json_fullpath = $CI->config->item('conf') . ZIMAPI_FILENAME_SOFTWARE;
	
	$CI->load->helper('json');
	try {
		$tmp_array = json_read($json_fullpath);
		if ($tmp_array['error']) {
			throw new Exception('read json error');
		}
	} catch (Exception $e) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read software json error', __FILE__, __LINE__);
		return FALSE;
	}
	
	$md5_input = md5($password);
	$md5_system = $tmp_array['json'][ZIMAPI_TITLE_PASSWD];
	if ($md5_input != $md5_system) {
		$CI->load->helper('printerlog');
		PrinterLog_logMessage('input password is wrong', __FILE__, __LINE__);
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_setCameraPassword($password = '') {
	$tmp_array = array();
	$data_json = array();
	$CI = &get_instance();
	$json_fullpath = $CI->config->item('conf') . ZIMAPI_FILENAME_SOFTWARE;
	
	$CI->load->helper('json');
	try {
		$tmp_array = json_read($json_fullpath);
		if ($tmp_array['error']) {
			throw new Exception('read json error');
		}
	} catch (Exception $e) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read software json error', __FILE__, __LINE__);
		return FALSE;
	}
	
	$data_json = $tmp_array['json'];
	$data_json[ZIMAPI_TITLE_PASSWD] = md5($password);
	
	// write json file
	$fp = fopen($json_fullpath, 'w');
	if ($fp) {
		fwrite($fp, json_encode($data_json));
		fclose($fp);
	}
	else {
		$CI->load->helper('printerlog');
		PrinterLog_logError('write camera password error', __FILE__, __LINE__);
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_cameraCapture(&$path_capture) {
	$output = NULL;
	$ret_val = 0;
	$info_camera = '';
	$CI = &get_instance();
	
	if (!ZimAPI_checkCamera($info_camera)) {
		return FALSE;
	}
	if ($info_camera != ZIMAPI_VALUE_MODE_OFF) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('capture can not run when camera is on', __FILE__, __LINE__);
		return FALSE;
	}
	
	$command = $CI->config->item('capture') . ZIMAPI_PRM_CAMERA_CAPTURE;
	
	exec($command, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('capture camera command error', __FILE__, __LINE__);
		return FALSE;
	}
	
	$path_capture = ZIMAPI_FILEPATH_CAPTURE;
	
	return TRUE;
}

function ZimAPI_checkCameraInBlock() {
	$data_json = array();
	$CI = &get_instance();
	
	$CI->load->helper(array('corestatus', 'printerstate'));
	
	// check we are in printing, and not in heating
	$data_json = PrinterState_checkStatusAsArray(FALSE);
	if (is_array($data_json) && $data_json[PRINTERSTATE_TITLE_STATUS] == CORESTATUS_VALUE_PRINT
			&& isset($data_json[PRINTERSTATE_TITLE_EXTEND_PRM][PRINTERSTATE_TITLE_EXT_OPER])
			&& $data_json[PRINTERSTATE_TITLE_EXTEND_PRM][PRINTERSTATE_TITLE_EXT_OPER] != PRINTERSTATE_VALUE_PRINT_OPERATION_HEAT) {
// 		$status_current = NULL;
		
		// check we are in printing of normal model which has timelapse
		//TODO union two verification of timelapse into 1 (preparePrint function in printer helper and this function)
// 		CoreStatus_checkInIdle($status_current, $data_json);
		CoreStatus_getStatusArray($data_json);
		if (isset($data_json[CORESTATUS_TITLE_PRINTMODEL]) && in_array($data_json[CORESTATUS_TITLE_PRINTMODEL], array(
						CORESTATUS_VALUE_MID_PRIME_R, CORESTATUS_VALUE_MID_PRIME_L, CORESTATUS_VALUE_MID_CALIBRATION))) {
			return FALSE;
		}
		else {
			$CI->load->helper('printerlog');
			PrinterLog_logMessage('ignore all camera requests in printing non-heating phase');
		}
		
		return TRUE;
	}
	
	return FALSE;
}

// function ZimAPI_cameraOn($parameter, $timelapse_length = 0) {
function ZimAPI_cameraOn($parameter) {
	$output = NULL;
	$ret_val = 0;
	$mode_current = '';
	$data_json = array();
	$fp = 0;
	$CI = &get_instance();
	
	$status_file = $CI->config->item('temp') . ZIMAPI_FILENAME_CAMERA;
	$mode_request = ZimAPI__getModebyParameter($parameter);
	
// 	// complete command parameter if in timelapse mode
// 	if ($parameter == ZIMAPI_PRM_CAMERA_PRINTSTART_TIMELAPSE) {
// 		// assign a default value
// 		if ($timelapse_length == 0) {
// 			$timelapse_length = ZIMAPI_VALUE_DEFAULT_LENGTH;
// 		}
		
// 		// 30s timelapse at 10 fps, so 300 / print time with 0.5mm/s average speed
// 		$parameter = str_replace('{fps}',
// 					(ZIMAPI_VALUE_DEFAULT_TL_LENGTH * 10 / ($timelapse_length / ZIMAPI_VALUE_DEFAULT_SPEED + ZIMAPI_VALUE_DEFAULT_TL_OFFSET)),
// 					ZIMAPI_PRM_CAMERA_PRINTSTART_TIMELAPSE);
// 	}
	$command = $CI->config->item('camera') . $parameter;
	
	// check camera is in block status or not
	if (ZimAPI_checkCameraInBlock()) {
		return TRUE;
	}
	
	$ret_val = ZimAPI_checkCamera($mode_current);
	if ($ret_val == FALSE) {
		return $ret_val;
	}
	else if ($mode_current != ZIMAPI_VALUE_MODE_OFF) {
		$CI->load->helper('printerlog');
		
		if ($mode_request != $mode_current) {
			PrinterLog_logError('camera already open with another mode, ' . $mode_current, __FILE__, __LINE__);
			return FALSE;
		}
		PrinterLog_logMessage('camera already open', __FILE__, __LINE__);
		return TRUE;
	}
	
	exec($command, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('camera start command error', __FILE__, __LINE__);
		return FALSE;
	}
	$data_json = array(
			ZIMAPI_TITLE_MODE		=> $mode_request,
			ZIMAPI_TITLE_COMMAND	=> $parameter,
	);
	
	// write json file
	$fp = fopen($CI->config->item('temp') . ZIMAPI_FILENAME_CAMERA, 'w');
	if ($fp) {
		fwrite($fp, json_encode($data_json));
		fclose($fp);
	}
	else {
		$CI->load->helper('printerlog');
		PrinterLog_logError('write camera status error', __FILE__, __LINE__);
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_cameraOff() {
	$CI = &get_instance();
	$output = NULL;
	$ret_val = 0;
	$command = $CI->config->item('camera') . ZIMAPI_PRM_CAMERA_STOP;
	$data_json = array();
	$fp = 0;
	$mode_current = '';
	
	if (!ZimAPI_checkCamera($mode_current)) {
		return FALSE;
	}
	else if ($mode_current == ZIMAPI_VALUE_MODE_OFF) {
		return TRUE;
	}
	else if (ZimAPI_checkCameraInBlock()) {
		return TRUE;
	}
	
	exec($command, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('camera stop command error', __FILE__, __LINE__);
		return FALSE;
	}
	
	$data_json = array(
			ZIMAPI_TITLE_MODE		=> ZIMAPI_VALUE_MODE_OFF,
			ZIMAPI_TITLE_COMMAND	=> NULL,
	);
	
	// write json file
	$fp = fopen($CI->config->item('temp') . ZIMAPI_FILENAME_CAMERA, 'w');
	if ($fp) {
		fwrite($fp, json_encode($data_json));
		fclose($fp);
	}
	else {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('write camera status error', __FILE__, __LINE__);
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_cleanTimelapseTempFile() {
	global $CFG;
	$output = NULL;
	$ret_val = 0;
	$command = $CFG->config['camera'] . ZIMAPI_PRM_END_TIMELAPSE;
	
	exec($command, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logMessage('camera clean timelapse command error', __FILE__, __LINE__);
// 		return FALSE; // we ignore this error
	}
	
	return TRUE;
}

function ZimAPI_checkTimelapse(&$done = FALSE) {
	$ret_val = file_exists(ZIMAPI_FILEPATH_TIMELAPSE);
	
	if ($ret_val == TRUE && !file_exists(ZIMAPI_FILEPATH_TL_TMPIMG)) {
		$done = TRUE;
	}
	else {
		$done = FALSE;
	}
	
	return $ret_val;
}

function ZimAPI_removeTimelapse() {
	$ret_val = unlink(ZIMAPI_FILEPATH_TIMELAPSE);
	
	if ($ret_val == FALSE) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('remove timelapse video file error', __FILE__, __LINE__);
	}
	
	return $ret_val;
}

function ZimAPI_sendMandrillEmail($array_senddata) {
	$send_context = NULL;
	$result = NULL;
	$json_data = array();
	$send_status = FALSE;
	
// 	$array_senddata = json_encode($array_senddata);
	$send_context = stream_context_create(array(
			'http' => array(
					'header'		=> "Content-type: application/x-www-form-urlencoded",
// 					'header'		=> "Content-type: application/json\r\nConnection: close\r\nContent-length: " . strlen($array_senddata) . "\r\n",
					'method'		=> 'POST',
					'content'		=> http_build_query($array_senddata),
// 					'content'		=> $array_senddata,
					'ignore_errors'	=> TRUE,
			),
	));
	
	$result = @file_get_contents(ZIMAPI_VALUE_TL_MANDRILL_API, FALSE, $send_context);
	
	// check response
	if ($result === FALSE || is_null($http_response_header)) {
		return ERROR_NO_PRINT; // act as no internet access
	}
	
	$json_data = json_decode($result, TRUE);
	foreach($json_data as $json_element) {
		if (is_array($json_element)) {
			if (array_key_exists('status', $json_element)
					&& in_array($json_element['status'], array('sent', 'queued'))) {
				$send_status = TRUE;
			}
			else {
				$send_status = FALSE;
				break;
			}
		}
		//TODO check if this case exists or not
		else if (array_key_exists('status', $json_data) && $json_data['status'] != 'error') {
			$send_status = TRUE;
			break;
		}
		else {
			break;
		}
	}
	
	if ($send_status == FALSE) {
		$matches = array();
		$CI = &get_instance();
		
		$CI->load->helper('printerlog');
		PrinterLog_logError('decode json data return error, return: ' . $result, __FILE__, __LINE__);
		
		if (count($http_response_header)) {
			preg_match('#HTTP/\d+\.\d+ (\d+)#', $http_response_header[0], $matches);
			PrinterLog_logDebug('send email mandrill status code: ' . $matches[1], __FILE__, __LINE__);
		}
		
		return ERROR_INTERNAL;
	}
	else {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logDebug('send email json: ' . $result, __FILE__, __LINE__);
// 		PrinterLog_logDebug('send to mandrill json: ' . json_encode($array_senddata));
	}
	
	return ERROR_OK;
}

function ZimAPI_sendTimelapse($emails, $model_name = NULL) {
	$CI = &get_instance();
	$array_senddata = array();
	$array_to = array();
	
	// check email validation
	foreach($emails as $email) {
		$email = trim($email);
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			return ERROR_WRONG_PRM;
		}
		else {
			$array_to[] = array('email' => $email);
		}
	}
	
	if (count($array_to) == 0) {
		return ERROR_MISS_PRM;
	}
	
	//stats info
	PrinterLog_statsShareEmail(count($array_to));
	
	$CI->lang->load('sendtimelapse', $CI->config->item('language'));
	
	// prepare mandrill json array
	$array_senddata = array(
			'key'		=> ZIMAPI_VALUE_MANDRILL_KEY,
			'message'	=> array(
					'from_email'			=> ZIMAPI_VALUE_TL_FROM_EMAIL,
					'from_name'				=> ZIMAPI_VALUE_TL_FROM_NAME,
					'subaccount'			=> ZIMAPI_VALUE_TL_SUBACCOUNT,
					'html'					=> t('timelapse_email_html'),
					'subject'				=> t('timelapse_email_subject') . ' ' . $model_name,
					'to'					=> $array_to,
					'important'				=> FALSE,
					'track_opens'			=> NULL,
					'track_clicks'			=> NULL,
					'auto_text'				=> NULL,
					'auto_html'				=> NULL,
					'inline_css'			=> NULL,
					'url_strip_qs'			=> NULL,
					'preserve_recipients'	=> NULL,
					'view_content_link'		=> NULL,
					'tracking_domain'		=> NULL,
					'signing_domain'		=> NULL,
					'return_path_domain'	=> NULL,
					'merge'					=> FALSE,
					'attachments'			=> array(array(
							'type'		=> ZIMAPI_VALUE_TL_MIMETYPE,
							'name'		=> ZIMAPI_VALUE_TL_VIDEO_NAME,
							'content'	=> base64_encode(file_get_contents(ZIMAPI_FILEPATH_TIMELAPSE)),
					)), //TODO pass filepath into send api function for performance
			),
			'async'		=> FALSE,
			'ip_pool'	=> ZIMAPI_VALUE_TL_IP_POOL,
	);
	
	return ZimAPI_sendMandrillEmail($array_senddata);
}

function ZimAPI_packUpPresets() {
	$output = array();
	$ret_val = 0;
	$CI = &get_instance();
	$command = 'tar cjf ' . $CI->config->item('temp') . ZIMAPI_VALUE_PRESETS_CS_NAME . ' ' . $CI->config->item('presetlist');
	
	exec($command, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		return NULL;
	}
	
	return $CI->config->item('temp') . ZIMAPI_VALUE_PRESETS_CS_NAME;
}

function ZimAPI_getPresetList($set_localization = TRUE) {
	$array_data = ZimAPI_getPresetListAsArray($set_localization);
	$CI = &get_instance();
	
	$CI->load->helper('json');
	
	return json_encode_unicode($array_data);
}

function ZimAPI_getPresetListAsArray($set_localization = TRUE, $user_only = FALSE) {
	$json_data = array();
	$array_path = array();
	$tmp_array = NULL;
	
	$CI = &get_instance();
	$CI->load->helper(array('file', 'directory', 'json'));
	
	if ($user_only) {
		$array_path = array($CI->config->item('presetlist'));
	}
	else {
		$array_path = array(
				$CI->config->item('systempreset'),
				$CI->config->item('presetlist'),
		);
	}
	foreach ($array_path as $presetlist_basepath) {
		$preset_array = directory_map($presetlist_basepath, 1);
		
		foreach ($preset_array as $preset_id) {
			$preset_path = $presetlist_basepath . $preset_id . '/';
			
			try {
				$tmp_array = json_read($preset_path . ZIMAPI_FILE_PRESET_JSON, TRUE);
				if ($tmp_array['error']) {
					throw new Exception('read json error');
				}
			} catch (Exception $e) {
				// log internal error
				$CI = &get_instance();
				$CI->load->helper('printerlog');
				PrinterLog_logError('catch exception when getting preset json ' . $preset_id, __FILE__, __LINE__);
				continue; // just jump through the wrong data file
			}
			
			// localization preset name
			if ($set_localization) {
				ZimAPI__setPresetLocalization($tmp_array['json']);
			}
			
			// check json info with 4 parameters
			if (!array_key_exists(ZIMAPI_TITLE_PRESET_INFILL, $tmp_array['json'])
					|| !array_key_exists(ZIMAPI_TITLE_PRESET_SKIRT, $tmp_array['json'])
					|| !array_key_exists(ZIMAPI_TITLE_PRESET_RAFT, $tmp_array['json'])
					|| !array_key_exists(ZIMAPI_TITLE_PRESET_SUPPORT, $tmp_array['json'])) {
				$array_settings = array();
				$ret_val = ZimAPI_getPresetSettingAsArray($preset_id, $array_settings);
				
				// assign json data
				if ($ret_val != ERROR_OK) {
					continue;
				}
				else if (ERROR_OK != ZimAPI_setPresetSetting($preset_id, $array_settings, array(), $tmp_array['json'][ZIMAPI_TITLE_PRESET_NAME], TRUE)) {
					// we let setPresetSetting function to correct and write json file, and ignore preset in error
					continue;
				}
				else {
					$tmp_array['json'][ZIMAPI_TITLE_PRESET_INFILL] = $array_settings[ZIMAPI_TITLE_PRESET_INFILL];
					$tmp_array['json'][ZIMAPI_TITLE_PRESET_SKIRT] = $array_settings[ZIMAPI_TITLE_PRESET_SKIRT];
					$tmp_array['json'][ZIMAPI_TITLE_PRESET_RAFT] = $array_settings[ZIMAPI_TITLE_PRESET_RAFT];
					$tmp_array['json'][ZIMAPI_TITLE_PRESET_SUPPORT] = $array_settings[ZIMAPI_TITLE_PRESET_SUPPORT];
				}
			}
			
			$json_data[] = $tmp_array['json']; //asign final data
		}
	}
	
	return $json_data;
}

function ZimAPI_getPreset(&$id_preset) {
	$tmp_array = array();
	$CI = &get_instance();
	$json_fullpath = $CI->config->item('conf') . ZIMAPI_FILENAME_SOFTWARE;
	
	$CI->load->helper('json');
	try {
		$tmp_array = json_read($json_fullpath);
		if ($tmp_array['error']) {
			throw new Exception('read json error');
		}
	} catch (Exception $e) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read software json error', __FILE__, __LINE__);
		return FALSE;
	}
	
	if (isset($tmp_array['json'][ZIMAPI_TITLE_PRESET])) {
		$id_preset = $tmp_array['json'][ZIMAPI_TITLE_PRESET];
		return TRUE;
	}
	else {
		$CI->load->helper('printerlog');
		PrinterLog_logError('no preset id setting error', __FILE__, __LINE__);
		return FALSE;
	}
}

function ZimAPI_setPreset($id_preset) {
	$tmp_array = array();
	$data_json = array();
// 	$cr = 0;
	$config_fullpath = '';
	$preset_basepath = '';
	$CI = &get_instance();
	$json_fullpath = $CI->config->item('conf') . ZIMAPI_FILENAME_SOFTWARE;
	
	if (!ZimAPI_checkPreset($id_preset, $preset_basepath)) {
		return ERROR_WRONG_PRM;
	}
	
	$CI->load->helper('json');
	try {
		$tmp_array = json_read($json_fullpath);
		if ($tmp_array['error']) {
			throw new Exception('read json error');
		}
	} catch (Exception $e) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('read software json error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	$data_json = $tmp_array['json'];
	$data_json[ZIMAPI_TITLE_PRESET] = $id_preset;
	
	$config_fullpath = $preset_basepath . $id_preset . '/' . ZIMAPI_FILE_PRESET_INI;
	if (file_exists($config_fullpath)) {
		$ret_val = copy($config_fullpath, $CI->config->item('conf') . ZIMAPI_FILE_PRESET_INI);
		if ($ret_val != TRUE) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('copy preset file error', __FILE__, __LINE__);
			return ERROR_INTERNAL;
		}
	}
	else {
		$CI->load->helper('printerlog');
		PrinterLog_logError('not find preset file: ' . ZIMAPI_FILE_PRESET_INI, __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
// 	$CI->load->helper('slicer');
// 	$cr = Slicer_reloadPreset();
// 	if ($cr != ERROR_OK) {
// 		return $cr;
// 	}
	
	// write json file
	$fp = fopen($json_fullpath, 'w');
	if ($fp) {
		fwrite($fp, json_encode($data_json));
		fclose($fp);
	}
	else {
		$CI->load->helper('printerlog');
		PrinterLog_logError('write preset id error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ZimAPI_deletePreset($id_preset) {
	$cr = 0;
	$ret_val = 0;
	$preset_basepath = NULL;
	$system_preset = NULL;
	
	$CI = &get_instance();
	$CI->load->helper('file');
	
	if ($id_preset) {
		$ret_val = ZimAPI_checkPreset($id_preset, $preset_basepath, $system_preset);
		if ($ret_val == TRUE) {
			if ($system_preset == TRUE) {
				$cr = ERROR_WRONG_PRM;
				$CI->load->helper('printerlog');
				PrinterLog_logMessage('try to delete system preset', __FILE__, __LINE__);
			}
			else {
				$preset_path = $preset_basepath . $id_preset;
				if (file_exists($preset_path)) {
					delete_files($preset_path, TRUE); //there are no folders inside normally, but we delete all
					rmdir($preset_path);
					$cr = ERROR_OK;
				}
				else {
					$cr = ERROR_INTERNAL;
					$CI->load->helper('printerlog');
					PrinterLog_logError('can not find preset filepath', __FILE__, __LINE__);
				}
			}
		}
		else {
			$cr = ERROR_WRONG_PRM;
			$CI->load->helper('printerlog');
			PrinterLog_logError('can not find preset by id', __FILE__, __LINE__);
		}
	}
	else {
		$cr = ERROR_MISS_PRM;
		$CI->load->helper('printerlog');
		PrinterLog_logError('miss preset id', __FILE__, __LINE__);
	}
	
	return $cr;
}

function ZimAPI_getSerial() {
	global $PRINTER;
	$address_mac = NULL;
	
	if (!is_array($PRINTER)) {
		$PRINTER = array();
	}
	elseif (isset($PRINTER[ZIMAPI_GLOBAL_KEY_SERIAL])) {
		return $PRINTER[ZIMAPI_GLOBAL_KEY_SERIAL];
	}
	
	$CI = &get_instance();
	$CI->load->helper('detectos');
	
	if ($CI->config->item('simulator') && DectectOS_checkWindows()) {
		$address_mac = '00:00:00:00:00:00';
	}
	else {
		try {
			$address_mac = trim(shell_exec(ZIMAPI_CMD_SERIAL));
		} catch (Exception $e) {
			$address_mac = 'ff:ff:ff:ff:ff:ff';
		}
	}
	$address_mac = strtoupper(str_replace(':', '', $address_mac));
	$PRINTER[ZIMAPI_GLOBAL_KEY_SERIAL] = $address_mac;
	
	return $address_mac;
}

function ZimAPI_getVersion($next_boot = FALSE) {
	$version = NULL;
	
	$CI = &get_instance();
	$CI->load->helper('detectos');
	
	if (DectectOS_checkWindows()) {
		$version = trim(@file_get_contents($CI->config->item('version_file')));
	}
	else if ($next_boot == TRUE) {
		$version = trim(shell_exec(ZIMAPI_CMD_VERSION_REBOOT));
	}
	else {
		$version = trim(shell_exec(ZIMAPI_CMD_VERSION));
	}
	
// 	//FIX/ME remove me as soon as possible
// 	if ($version == 'dev_release_1.2') { // error version after flash SD or recovery
// 		$version = trim(@file_get_contents($CI->config->item('version_file')));
// 	}
	
	return $version;
}

function ZimAPI_getType() {
	global $CFG;
	
	return trim(@file_get_contents($CFG->config['type_file']), " \t\n\r\0\x0B\"");
}

function ZimAPI_getPrinterSSOName(&$value) {
	$CI = &get_instance();
	$filename = $CI->config->item('conf') . ZIMAPI_FILE_SSO_NAME;
	
	$value = NULL;
	if (file_exists($filename)) {
		try {
			$value = @file_get_contents($filename);
		}
		catch (Exception $e) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('read printer sso name error', __FILE__, __LINE__);
			
			return ERROR_INTERNAL;
		}
	}
	
	return ERROR_OK;
}

function ZimAPI_setPrinterSSOName($value, $set_hostname = TRUE) {
	$CI = &get_instance();
	$filename = $CI->config->item('conf') . ZIMAPI_FILE_SSO_NAME;
	
	if ($set_hostname == TRUE) {
		$cr = ZimAPI_setHostname($value, FALSE);
		if ($cr != ERROR_OK) {
			return $cr;
		}
	}
	else {
		//TODO check if it is necessary to add verification here
		if (strlen($value) > 9 || strlen($value) == 0
				|| !preg_match('/^[A-Za-z0-9]+$/', $hostname)) {
			return ERROR_WRONG_PRM;
		}
	}
	
	if ($value == NULL) {
		unlink($filename);
	}
	else {
		try {
			$fp = fopen($filename, 'w');
			if ($fp) {
				fwrite($fp, $value);
				fclose($fp);
			}
			else {
				$CI->load->helper('printerlog');
				PrinterLog_logError('open sso name file error', __FILE__, __LINE__);
				
				return ERROR_INTERNAL;
			}
		} catch (Exception $e) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('write printer sso name error', __FILE__, __LINE__);
			
			return ERROR_INTERNAL;
		}
	}
	
	return ERROR_OK;
}

function ZimAPI_checkUSB() {
	$ret_val = 0;
	$output = array();
	$CI = &get_instance();
	
	if ($CI->config->item('simulator')) {
		return FALSE;
	}
	
	exec(ZIMAPI_CMD_USB_CONNECT, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		return TRUE;
	}
	else {
		return FALSE;
	}
	
	return TRUE; // never reach here
}

function ZimAPI_getUpgradeMode(&$mode, &$profile = NULL) {
	$tmp_array = array();
	$CI = &get_instance();
	
	$CI->load->helper('json');
	if (file_exists(ZIMAPI_FILEPATH_UPGRADE)) {
		try {
			$tmp_array = json_read(ZIMAPI_FILEPATH_UPGRADE, TRUE);
			if ($tmp_array['error']) {
				throw new Exception('read json error');
			}
		} catch (Exception $e) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('read upgrade json error', __FILE__, __LINE__);
			return FALSE;
		}
		$mode = $tmp_array['json']['mode'];
		if (array_key_exists('profile', $tmp_array['json'])) {
			$profile = $tmp_array['json']['profile'];
		}
	}
	else {
		$mode = 'on';
	}
	
	if (is_null($profile)) {
		$profile = shell_exec(ZIMAPI_CMD_PROFILE_LINK);
	}
	
	return TRUE;
}

function ZimAPI_setUpgradeMode($mode, $profile = NULL) {
	$json_data = array();
	$CI = &get_instance();
	
	$mode = strtolower($mode);
	if ($mode == 'change' && is_null($profile)) {
		// do not accept empty profile in change mode
		return ERROR_WRONG_PRM;
	}
	
	switch ($mode) {
		case 'change':
			$json_data['profile'] = $profile;
			
		case 'off':
		case 'on':
			$json_data['mode'] = $mode;
			break;
			
		default:
			$CI->load->helper('printerlog');
			PrinterLog_logError('unknown mode in upgrade profile json', __FILE__, __LINE__);
			return ERROR_WRONG_PRM;
			break;
	}
	
	//write model json info
	try {
		$fp = fopen(ZIMAPI_FILEPATH_UPGRADE, 'w');
		if ($fp) {
			fwrite($fp, json_encode_unicode($json_data));
			fclose($fp);
		}
		else {
			throw new Exception('write json error');
		}
	} catch (Exception $e) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('write upgrade json file error', __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
	
	return ERROR_OK;
}

function ZimAPI_getStatistic() {
	$CI = &get_instance();
	$CI->load->helper('printerlog');
	
	return PrinterLog_getStats();
}

function ZimAPI_setStatistic($mode) {
	$CI = &get_instance();
	$CI->load->helper('printerlog');
	
	if (PrinterLog_setStats($mode)) {
		return ERROR_OK;
	}
	
	return ERROR_INTERNAL;
}

// deprecated function
function ZimAPI_getUpgradeNote(&$note_html = '') {
	$count = 0;
	$html = NULL;
	$article_children = NULL;
	
	try {
		@include_once BASEPATH . '/../assets/simple_html_dom.php'; // use include instead of required to avoid fatal error
		
		if (!function_exists('file_get_html')) {
			throw new Exception('include html parser failed');
		}
		
		$note_html = ''; // initialization of text
		$html = file_get_html(ZIMAPI_VALUE_RELEASENOTE_URL);
		$article_children = $html->find('article', 0)->children();
		
		foreach($article_children as $ele_child) {
			if (count($ele_child->find('u'))) {
				++$count;
				if ($count > 1) {
					break;
				}
			}
			else {
				if (count($ele_child->find('hr'))) {
					break;
				}
				$note_html .= $ele_child . "\n";
			}
		}
		
		$html->clear();
	} catch (Exception $e) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('get upgrade release note error', __FILE__, __LINE__);
		
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_getUpgradeNoteArray(&$array_upgrade, $display_all = FALSE) {
	// we put a possibility to parse all notes here
	$file_note = ($display_all == TRUE) ? ZIMAPI_FILEPATH_UPGDNOTES : ZIMAPI_FILEPATH_UPGDNOTE;
	
	$array_upgrade = array(); // initialization of array
	
	try {
		if (file_exists($file_note)) {
			$xml = simplexml_load_file($file_note);
			ZimAPI__parseUpgradeXML($xml, $array_upgrade);
		}
		else {
			throw new Exception('file not found');
		}
	} catch (Exception $e) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('read upgrade release note error, file: ' . $file_note . ', message: ' . $e->getMessage(), __FILE__, __LINE__);
		
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_getTromboning() {
	global $CFG;
	$state_file = $CFG->config['conf'] . CORESTATUS_FILENAME_REMOTEOFF;
	
	if (file_exists($state_file)) {
		return FALSE;
	}
	else {
		return TRUE;
	}
	
	return TRUE;
}

function ZimAPI_setTromboning($mode) {
	global $CFG;
	$state_file = $CFG->config['conf'] . CORESTATUS_FILENAME_REMOTEOFF;
	
	$mode = strtolower($mode);
	
	switch ($mode) {
		case 'off':
			try {
				$fp = fopen($state_file, 'w');
				if ($fp) {
					fwrite($fp, 'off');
					fclose($fp);
				}
				else {
					throw new Exception('write file error');
				}
			} catch (Exception $e) {
				$CI = &get_instance();
				$CI->load->helper('printerlog');
				PrinterLog_logError('write tromboning file error', __FILE__, __LINE__);
				return ERROR_INTERNAL;
			}
			break;
			
		case 'on':
			@unlink($state_file);
			break;
			
		default:
			return ERROR_WRONG_PRM;
			break;
	}
	
	return ERROR_OK;
}

function ZimAPI_getSSH(&$mode, &$url = NULL) {
	$output = array();
	$ret_val = 0;
	
	exec(ZIMAPI_CMD_SSH_STATUS, $output, $ret_val);
	if ($ret_val != ERROR_NORMAL_RC_OK) {
		$CI = &get_instance();
		$CI->load->helper('printerlog');
		PrinterLog_logError('remote ssh status command error', __FILE__, __LINE__);
		$mode = FALSE;
		
		return FALSE;
	}
	else {
		if (count($output)) {
			$temp_string = $output[0];
			if (FALSE !== strpos($temp_string, 'ON')) {
				$temp_url = NULL;
				
				$mode = TRUE;
				$temp_string = strstr($temp_string, '[');
				$url = str_replace(array('[', ']'), '', $temp_string);
			}
			else {
				$mode = FALSE;
			}
		}
	}
	
	return TRUE;
}

function ZimAPI_setSSH($mode) {
	$output = array();
	$ret_val = 0;
	
	$mode = strtolower($mode);
	
	switch ($mode) {
		case 'off':
			exec(ZIMAPI_CMD_SSH_OFF);
			break;
			
		case 'on':
			$mode_current = NULL;
			if (ZimAPI_getSSH($mode_current)) {
				if ($mode_current == FALSE) {
					exec(ZIMAPI_CMD_SSH_ON);
				}
				else {
					$CI = &get_instance();
					$CI->load->helper('printerlog');
					PrinterLog_logMessage('remote ssh already opened', __FILE__, __LINE__);
				}
			}
			else {
				return ERROR_INTERNAL;
			}
			break;
			
		default:
			return ERROR_WRONG_PRM;
			break;
	}
	
	return ERROR_OK;
}

function ZimAPI_reboot() {
	$output = array();
	$ret_val = 0;
	$CI = &get_instance();
	$command = 'sudo ' . $CI->config->item('siteutil') . ZIMAPI_PRM_UTIL_REBOOT;
	
	exec($command, $output, $ret_val);
	
	if ($ret_val == ERROR_NORMAL_RC_OK) {
		return ERROR_OK;
	}
	
	return ERROR_INTERNAL;
}

function ZimAPI_shutdown() {
	// we leave a possibility to shutdown in ZimAPI helper, but I recommand you to load PrinterState directly
	$CI = &get_instance();
	$CI->load->helper('printerstate');
	
	return PrinterState_powerOff();
}

//TODO move preset functions into another helper
function ZimAPI_getPresetInfoAsArray($preset_id, &$array_info, &$system_preset = NULL, $set_localization = TRUE) {
	$presetlist_basepath = NULL;
	$tmp_array = NULL;
	$system_preset = FALSE;
	
	$CI = &get_instance();
	$CI->load->helper(array('file', 'json'));
	
	if (!ZimAPI_checkPreset($preset_id, $presetlist_basepath, $system_preset)) {
		return ERROR_WRONG_PRM;
	}
// 	$presetlist_basepath = $CI->config->item('presetlist');
	
	try {
		$preset_path = $presetlist_basepath . $preset_id . '/';
		$tmp_array = json_read($preset_path . ZIMAPI_FILE_PRESET_JSON, TRUE);
		if ($tmp_array['error']) {
			throw new Exception('read json error');
		}
	} catch (Exception $e) {
		// log internal error
		$CI->load->helper('printerlog');
		PrinterLog_logError('catch exception when getting preset json ' . $preset_id, __FILE__, __LINE__);
		return ERROR_INTERNAL;
	}
			
	// localization preset name
	if ($set_localization) {
		ZimAPI__setPresetLocalization($tmp_array['json']);
	}
	
	$array_info = $tmp_array['json']; //asign final data
	
	return ERROR_OK;
}

function ZimAPI_getPresetSettingAsArray($id_preset, &$array_setting) {
	$preset_basepath = '';
	$error_parameter = array();
	
	// check if preset exists
	if (!ZimAPI_checkPreset($id_preset, $preset_basepath)) {
		return ERROR_WRONG_PRM;
	}
	$array_setting = @parse_ini_file($preset_basepath . $id_preset . '/' . ZIMAPI_FILE_PRESET_INI);
	
	if ($array_setting == FALSE) {
		return ERROR_INTERNAL; // read ini file error
	}
	if (!ZimAPI_checkPresetSetting($array_setting, $error_parameter, FALSE)) {
		return ERROR_INTERNAL; // internal settings file error
	}
	
	return ERROR_OK;
}

function ZimAPI_codePresetHash($raw_name) {
	$CI = &get_instance();
	$CI->load->helper(array('detectos'));

	if (DectectOS_checkWindows()) {
		return md5(utf8_encode($raw_name));
	}
	else {
		return md5($raw_name);
	}
}

function ZimAPI_setPresetSetting($id_preset, $array_input, &$error_parameter, $name_preset = NULL, $overwrite = FALSE) {
	// $name_preset is NULL when creating preset from an old id
	$ret_val = 0;
	$array_setting = array();
	$preset_path = NULL;
	$system_preset = FALSE;
// 	$rewrite_json = FALSE;
	$CI = &get_instance();
	
	if (!is_array($array_input)) {
		return ERROR_INTERNAL;
	}
	
	// check if we have same name, and define preset path
	if ($name_preset != NULL) {
		$old_path = NULL;
		
		$ret_val = ZimAPI_checkPreset(ZimAPI_codePresetHash($name_preset), $old_path, $system_preset);
		if ($ret_val == TRUE) {
			$CI->load->helper('printerlog');
			
			if ($system_preset == TRUE) {
				PrinterLog_logMessage('system can not modify default preset');
				return ERROR_WRONG_PRM;
			}
			else if ($overwrite == FALSE) {
				PrinterLog_logMessage('system has already the same preset name: ' . $name_preset);
				return ERROR_FULL_PRTLST; // just use another error code
			}
			else {
				PrinterLog_logMessage('system detects a same preset name, and will overwrite it: ' . $name_preset);
			}
		}
		
		$preset_path = $CI->config->item('presetlist') . ZimAPI_codePresetHash($name_preset) . '/';
	}
	else {
		$ret_val = ZimAPI_checkPreset($id_preset, $preset_path, $system_preset);
		if ($ret_val == FALSE) {
			$CI->load->helper('printerlog');
			PrinterLog_logError('system can not find preset: ' . $id_preset);
			return ERROR_WRONG_PRM; // just use another error code
		}
		
		if ($system_preset == TRUE) {
			$CI->load->helper('printerlog');
			PrinterLog_logMessage('system can not modify default preset');
			return ERROR_WRONG_PRM;
		}
		
		$preset_path .= '/' . $id_preset . '/';
	}
	
	$ret_val = ZimAPI_checkPresetSetting($array_input, $error_parameter);
	if ($ret_val != TRUE) {
		$CI->load->helper('printerlog');
		PrinterLog_logError('user input preset setting has wrong parameter');
		return ERROR_WRONG_PRM;
	}
	
	$ret_val = ZimAPI_getPresetSettingAsArray($id_preset, $array_setting);
	if ($ret_val != ERROR_OK) {
		return $ret_val;
	}
	
	if (!ZimAPI_fixPresetSetting($array_setting)) {
		return ERROR_INTERNAL;
	}
	
	// check if 4 parameters are changed in same preset name saving (preset json is forced generated in different preset name saving)
	if ($name_preset == NULL) {
		foreach (array(ZIMAPI_TITLE_PRESET_INFILL, ZIMAPI_TITLE_PRESET_SKIRT,
				ZIMAPI_TITLE_PRESET_RAFT, ZIMAPI_TITLE_PRESET_SUPPORT) as $key) {
			if ($array_setting[$key] != $array_input[$key]) {
				$json_data = array();
				
				if (ERROR_OK == ZimAPI_getPresetInfoAsArray($id_preset, $json_data)) {
					$name_preset = $json_data[ZIMAPI_TITLE_PRESET_NAME]; // assign preset name as the case in same name overwrite case
				}
				else {
					$CI->load->helper('printerlog');
					PrinterLog_logError('read preset info error in reassignment of 4 parameters', __FILE__, __LINE__);
					
					return ERROR_INTERNAL;
				}
// 				$rewrite_json = TRUE;
			}
		}
	}
	
	// assign new setting
	foreach ($array_input as $key => $value) {
		$array_setting[$key] = $value;
	}
	
	// save preset
	if (!file_exists($preset_path)) {
		mkdir($preset_path);
	}
	if ($name_preset != NULL) { // || $rewrite_json == TRUE
		$json_data = array(
				ZIMAPI_TITLE_PRESET_ID		=> ZimAPI_codePresetHash($name_preset),
				ZIMAPI_TITLE_PRESET_NAME	=> $name_preset,
				ZIMAPI_TITLE_PRESET_INFILL	=> $array_setting[ZIMAPI_TITLE_PRESET_INFILL],
				ZIMAPI_TITLE_PRESET_SKIRT	=> (int) $array_setting[ZIMAPI_TITLE_PRESET_SKIRT],
				ZIMAPI_TITLE_PRESET_RAFT	=> (int) $array_setting[ZIMAPI_TITLE_PRESET_RAFT],
				ZIMAPI_TITLE_PRESET_SUPPORT	=> (int) $array_setting[ZIMAPI_TITLE_PRESET_SUPPORT],
		);
		
		$CI->load->helper('json');
		//write model json info
		try {
			$fp = fopen($preset_path . ZIMAPI_FILE_PRESET_JSON, 'w');
			if ($fp) {
				fwrite($fp, json_encode_unicode($json_data));
				fclose($fp);
			}
			else {
				return ERROR_INTERNAL;
			}
		} catch (Exception $e) {
			return ERROR_INTERNAL;
		}
	}
	//write config ini file
	try {
		$fp = fopen($preset_path . ZIMAPI_FILE_PRESET_INI, 'w');
		if ($fp) {
			foreach ($array_setting as $key => $value) {
				fwrite($fp, $key . " = " . $value . "\r\n");
			}
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

function ZimAPI_fixPresetSetting(&$array_setting) {
	$array_bedsize = array();
	$CI = &get_instance();
	
	// auto fix bed size and print center by hardconf json file
	$CI->load->helper('printerstate');
	if (ERROR_OK == PrinterState_getPrintSize($array_bedsize)
			&& array_key_exists(PRINTERSTATE_TITLE_PRINT_XMAX, $array_bedsize)
			&& array_key_exists(PRINTERSTATE_TITLE_PRINT_YMAX, $array_bedsize)) {
		$array_setting['bed_size'] = $array_bedsize[PRINTERSTATE_TITLE_PRINT_XMAX]
				. ',' . $array_bedsize[PRINTERSTATE_TITLE_PRINT_YMAX];
		$array_setting['print_center'] = $array_bedsize[PRINTERSTATE_TITLE_PRINT_XMAX] / 2
				. ',' . $array_bedsize[PRINTERSTATE_TITLE_PRINT_YMAX] / 2;
	}
	else {
		return FALSE;
	}
	
	return TRUE;
}

function ZimAPI_checkPreset($id_preset, &$preset_basepath = NULL, &$system_preset = FALSE) {
	$CI = &get_instance();
	$CI->load->helper('directory');
	
	$system_preset = FALSE;
	
	foreach (array(
					$CI->config->item('systempreset'), $CI->config->item('presetlist')
			) as $presetlist_basepath) {
		$preset_array = directory_map($presetlist_basepath, 1);
		
		foreach ($preset_array as $check_id) {
			if ($check_id == $id_preset) {
				$preset_basepath = $presetlist_basepath;
				if ($CI->config->item('systempreset') == $preset_basepath) {
					$system_preset = TRUE;
				}
				return TRUE;
				break; // never reach here
			}
		}
	}
	
	return FALSE;
}

function ZimAPI_checkPresetSetting(&$array_setting, &$errors = array(), $input = TRUE) {
	// check no any extra settings for user input preset setting only
	if ($input == TRUE) {
		$array_check = array(
				'layer_height',
				'first_layer_height',
				'perimeters',
				'spiral_vase',
				'top_solid_layers',
				'bottom_solid_layers',
				'extra_perimeters',
				'avoid_crossing_perimeters',
				// old for 1.0
// 				'start_perimeters_at_concave_points',
// 				'start_perimeters_at_non_overhang',
				// end of old for 1.0
				'thin_walls',
				'overhangs',
// 				'randomize_start', // old for 1.0
				'seam_position', // new for 1.1.7
				'external_perimeters_first',
				'fill_density',
				'fill_pattern',
				'solid_fill_pattern',
				'infill_every_layers',
				'infill_only_where_needed',
				'solid_infill_every_layers',
				'fill_angle',
				'solid_infill_below_area',
				'only_retract_when_crossing_perimeters',
				'infill_first',
				'perimeter_speed',
				'small_perimeter_speed',
				'external_perimeter_speed',
				'infill_speed',
				'solid_infill_speed',
				'top_solid_infill_speed',
				'support_material_speed',
				'bridge_speed',
				'gap_fill_speed',
				'support_material_interface_speed', // new for 1.1.7
				'travel_speed',
				'first_layer_speed',
				'skirts',
				'skirt_distance',
				'skirt_height',
				'min_skirt_length',
				'brim_width',
				'support_material',
				'support_material_threshold',
				'support_material_enforce_layers',
				'raft_layers',
				'support_material_pattern',
				'support_material_spacing',
				'support_material_angle',
				'support_material_interface_layers',
				'support_material_interface_spacing',
				'dont_support_bridges', // new for 1.1.7
				'perimeter_extruder',
				'infill_extruder',
				'support_material_extruder',
				'support_material_interface_extruder',
				'ooze_prevention',
				'standby_temperature_delta',
				// new for 1.1.7
				'interface_shells',
				'fan_always_on',
				'cooling',
				'min_fan_speed',
				'max_fan_speed',
				'bridge_fan_speed',
				'disable_fan_first_layers',
				'fan_below_layer_time',
				'slowdown_below_layer_time',
				'min_print_speed',
				// end of new for 1.1.7
				'extrusion_width',
				'first_layer_extrusion_width',
				'perimeter_extrusion_width',
				'infill_extrusion_width',
				'solid_infill_extrusion_width',
				'top_infill_extrusion_width',
				'support_material_extrusion_width',
				'bridge_flow_ratio',
				'resolution',
		);
		foreach ($array_setting as $key => $value) {
			if (!in_array($key, $array_check)) {
				$errors['input'] = $key;
				return FALSE;
				break; // never reach here
			}
		}
	}
	
	// check no any losing settings
	//TODO add value checking
	// layers and perimeters
	if (!array_key_exists('layer_height', $array_setting)) {
		$array_setting['layer_height'] = 0.4;
	}
	if (!array_key_exists('first_layer_height', $array_setting)) {
		$array_setting['first_layer_height'] = 0.35;
	}
	if (!array_key_exists('perimeters', $array_setting)) {
		$array_setting['perimeters'] = 3;
	}
	if (!array_key_exists('spiral_vase', $array_setting)) {
		$array_setting['spiral_vase'] = 0;
	}
	if (!array_key_exists('top_solid_layers', $array_setting)) {
		$array_setting['top_solid_layers'] = 3;
	}
	if (!array_key_exists('bottom_solid_layers', $array_setting)) {
		$array_setting['bottom_solid_layers'] = 3;
	}
	if (!array_key_exists('extra_perimeters', $array_setting)) {
		$array_setting['extra_perimeters'] = 1;
	}
	if (!array_key_exists('avoid_crossing_perimeters', $array_setting)) {
		$array_setting['avoid_crossing_perimeters'] = 0;
	}
	// old for 1.0
// 	if (!array_key_exists('start_perimeters_at_concave_points', $array_setting)) {
// 		$array_setting['start_perimeters_at_concave_points'] = 0;
// 	}
// 	if (!array_key_exists('start_perimeters_at_non_overhang', $array_setting)) {
// 		$array_setting['start_perimeters_at_non_overhang'] = 0;
// 	}
	// end of old for 1.0
	if (!array_key_exists('thin_walls', $array_setting)) {
		$array_setting['thin_walls'] = 1;
	}
	if (!array_key_exists('overhangs', $array_setting)) {
		$array_setting['overhangs'] = 1;
	}
// 	if (!array_key_exists('randomize_start', $array_setting)) { // old for 1.0
// 		$array_setting['randomize_start'] = 0;
// 	}
	if (!array_key_exists('seam_position', $array_setting)) { // new for 1.1.7
		$array_setting['seam_position'] = 'random';
	}
	if (!array_key_exists('external_perimeters_first', $array_setting)) {
		$array_setting['external_perimeters_first'] = 0;
	}
	// infill
	if (!array_key_exists('fill_density', $array_setting)) {
		$array_setting['fill_density'] = 0.4;
	}
	if (!array_key_exists('fill_pattern', $array_setting)) {
		$array_setting['fill_pattern'] = 'honeycomb';
	}
	if (!array_key_exists('solid_fill_pattern', $array_setting)) {
		$array_setting['solid_fill_pattern'] = 'rectilinear';
	}
	if (!array_key_exists('infill_every_layers', $array_setting)) {
		$array_setting['infill_every_layers'] = 1;
	}
	if (!array_key_exists('infill_only_where_needed', $array_setting)) {
		$array_setting['infill_only_where_needed'] = 0;
	}
	if (!array_key_exists('solid_infill_every_layers', $array_setting)) {
		$array_setting['solid_infill_every_layers'] = 0;
	}
	if (!array_key_exists('fill_angle', $array_setting)) {
		$array_setting['fill_angle'] = 45;
	}
	if (!array_key_exists('solid_infill_below_area', $array_setting)) {
		$array_setting['solid_infill_below_area'] = 70;
	}
	if (!array_key_exists('only_retract_when_crossing_perimeters', $array_setting)) {
		$array_setting['only_retract_when_crossing_perimeters'] = 1;
	}
	if (!array_key_exists('infill_first', $array_setting)) {
		$array_setting['infill_first'] = 0;
	}
	// speed
	if (!array_key_exists('perimeter_speed', $array_setting)) {
		$array_setting['perimeter_speed'] = 30;
	}
	if (!array_key_exists('small_perimeter_speed', $array_setting)) {
		$array_setting['small_perimeter_speed'] = 30;
	}
	else
	{
		$tmp = $array_setting['small_perimeter_speed'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 20 || substr($tmp, 0, $pos) > 100))
				|| ($pos === FALSE && ($tmp < 10 || $tmp > 200)))
		{
			$errors['small_perimeter_speed'] = '[20,100] OR [10%,200%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('external_perimeter_speed', $array_setting)) {
		$array_setting['external_perimeter_speed'] = '70%';
	}
	else
	{
		$tmp = $array_setting['external_perimeter_speed'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 20 || substr($tmp, 0, $pos) > 100))
				|| ($pos === FALSE && ($tmp < 10 || $tmp > 200)))
		{
			$errors['external_perimeter_speed'] = '[20,100] OR [10%,200%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('infill_speed', $array_setting)) {
		$array_setting['infill_speed'] = 60;
	}
	if (!array_key_exists('solid_infill_speed', $array_setting)) {
		$array_setting['solid_infill_speed'] = 60;
	}
	else
	{
		$tmp = $array_setting['solid_infill_speed'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 20 || substr($tmp, 0, $pos) > 100))
				|| ($pos === FALSE && ($tmp < 10 || $tmp > 200)))
		{
			$errors['solid_infill_speed'] = '[20,100] OR [10%,200%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('top_solid_infill_speed', $array_setting)) {
		$array_setting['top_solid_infill_speed'] = 50;
	}
	else
	{
		$tmp = $array_setting['top_solid_infill_speed'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 20 || substr($tmp, 0, $pos) > 100))
				|| ($pos === FALSE && ($tmp < 10 || $tmp > 200)))
		{
			$errors['top_solid_infill_speed'] = '[20,100] OR [10%,200%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('support_material_speed', $array_setting)) {
		$array_setting['support_material_speed'] = 60;
	}
	if (!array_key_exists('bridge_speed', $array_setting)) {
		$array_setting['bridge_speed'] = 60;
	}
	if (!array_key_exists('gap_fill_speed', $array_setting)) {
		$array_setting['gap_fill_speed'] = 20;
	}
	if (!array_key_exists('travel_speed', $array_setting)) {
		$array_setting['travel_speed'] = 130;
	}
	if (!array_key_exists('first_layer_speed', $array_setting)) { // new for 1.1.7
		$array_setting['first_layer_speed'] = '30%';
	}
	else
	{
		$tmp = $array_setting['first_layer_speed'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 20 || substr($tmp, 0, $pos) > 100))
				|| ($pos === FALSE && ($tmp < 10 || $tmp > 200)))
		{
			$errors['first_layer_speed'] = '[20,100] OR [10%,200%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('support_material_interface_speed', $array_setting)) {
		$array_setting['support_material_interface_speed'] = '100%';
	}
	else
	{
		$tmp = $array_setting['support_material_interface_speed'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 20 || substr($tmp, 0, $pos) > 100))
				|| ($pos === FALSE && ($tmp < 10 || $tmp > 200)))
		{
			$errors['support_material_interface_speed'] = '[20,100] OR [10%,200%]';
// 			return FALSE;
		}
	}
	// skirt and brim
	if (!array_key_exists('skirts', $array_setting)) {
		$array_setting['skirts'] = 1;
	}
	if (!array_key_exists('skirt_distance', $array_setting)) {
		$array_setting['skirt_distance'] = 6;
	}
	if (!array_key_exists('skirt_height', $array_setting)) {
		$array_setting['skirt_height'] = 1;
	}
	if (!array_key_exists('min_skirt_length', $array_setting)) {
		$array_setting['min_skirt_length'] = 0;
	}
	if (!array_key_exists('brim_width', $array_setting)) {
		$array_setting['brim_width'] = 0;
	}
	// support material
	if (!array_key_exists('support_material', $array_setting)) {
		$array_setting['support_material'] = 0;
	}
	if (!array_key_exists('material_threshold', $array_setting)) {
		$array_setting['material_threshold'] = 0;
	}
	if (!array_key_exists('support_material_enforce_layers', $array_setting)) {
		$array_setting['support_material_enforce_layers'] = 0;
	}
	if (!array_key_exists('raft_layers', $array_setting)) {
		$array_setting['raft_layers'] = 0;
	}
	if (!array_key_exists('support_material_pattern', $array_setting)) {
		$array_setting['support_material_pattern'] = 'honeycomb';
	}
	if (!array_key_exists('support_material_spacing', $array_setting)) {
		$array_setting['support_material_spacing'] = 2.5;
	}
	if (!array_key_exists('support_material_angle', $array_setting)) {
		$array_setting['support_material_angle'] = 0;
	}
	if (!array_key_exists('support_material_interface_layers', $array_setting)) {
		$array_setting['support_material_interface_layers'] = 3;
	}
	if (!array_key_exists('support_material_interface_spacing', $array_setting)) {
		$array_setting['support_material_interface_spacing'] = 0;
	}
	if (!array_key_exists('dont_support_bridges', $array_setting)) { // new for 1.1.7
		$array_setting['dont_support_bridges'] = 1;
	}
	// multiple extruders
	if (!array_key_exists('perimeter_extruder', $array_setting)) {
		$array_setting['perimeter_extruder'] = 1;
	}
	if (!array_key_exists('infill_extruder', $array_setting)) {
		$array_setting['infill_extruder'] = 1;
	}
	if (!array_key_exists('support_material_extruder', $array_setting)) {
		$array_setting['support_material_extruder'] = 1;
	}
	if (!array_key_exists('support_material_interface_extruder', $array_setting)) {
		$array_setting['support_material_interface_extruder'] = 1;
	}
	if (!array_key_exists('ooze_prevention', $array_setting)) {
		$array_setting['ooze_prevention'] = 0;
	}
	if (!array_key_exists('standby_temperature_delta', $array_setting)) {
		$array_setting['standby_temperature_delta'] = -5;
	}
	if (!array_key_exists('interface_shells', $array_setting)) { // new for 1.1.7
		$array_setting['interface_shells'] = 0;
	}
	// cooling fan (all new for 1.1.7)
	if (!array_key_exists('fan_always_on', $array_setting)) {
		$array_setting['fan_always_on'] = 0;
	}
	if (!array_key_exists('cooling', $array_setting)) {
		$array_setting['cooling'] = 1;
	}
	if (!array_key_exists('min_fan_speed', $array_setting)) {
		$array_setting['min_fan_speed'] = 35;
	}
	if (!array_key_exists('max_fan_speed', $array_setting)) {
		$array_setting['max_fan_speed'] = 100;
	}
	if (!array_key_exists('bridge_fan_speed', $array_setting)) {
		$array_setting['bridge_fan_speed'] = 100;
	}
	if (!array_key_exists('disable_fan_first_layers', $array_setting)) {
		$array_setting['disable_fan_first_layers'] = 1;
	}
	if (!array_key_exists('fan_below_layer_time', $array_setting)) {
		$array_setting['fan_below_layer_time'] = 60;
	}
	if (!array_key_exists('slowdown_below_layer_time', $array_setting)) {
		$array_setting['slowdown_below_layer_time'] = 30;
	}
	if (!array_key_exists('min_print_speed', $array_setting)) {
		$array_setting['min_print_speed'] = 10;
	}
	// advanced
	if (!array_key_exists('extrusion_width', $array_setting)) {
		$array_setting['extrusion_width'] = 0;
	}
	else
	{
		$tmp = $array_setting['extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 50 || substr($tmp, 0, $pos) > 300))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['extrusion_width'] = '[0.25,0.5] OR [50%,300%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('first_layer_extrusion_width', $array_setting)) {
		$array_setting['first_layer_extrusion_width'] = '200%';
	}
	else
	{
		$tmp = $array_setting['first_layer_extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 50 || substr($tmp, 0, $pos) > 300))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['first_layer_extrusion_width'] = '[0.25,0.5] OR [50%,300%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('perimeter_extrusion_width', $array_setting)) {
		$array_setting['perimeter_extrusion_width'] = 0;
	}
	else
	{
		$tmp = $array_setting['perimeter_extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 25 || substr($tmp, 0, $pos) > 150))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['perimeter_extrusion_width'] = '[0.25,0.5] OR [50%,150%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('infill_extrusion_width', $array_setting)) {
		$array_setting['infill_extrusion_width'] = 0;
	}
	else
	{
		$tmp = $array_setting['infill_extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 50 || substr($tmp, 0, $pos) > 150))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['infill_extrusion_width'] = '[0.25,0.5] OR [50%,150%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('solid_infill_extrusion_width', $array_setting)) {
		$array_setting['solid_infill_extrusion_width'] = 0;
	}
	else
	{
		$tmp = $array_setting['solid_infill_extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 50 || substr($tmp, 0, $pos) > 300))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['solid_infill_extrusion_width'] = '[0.25,0.5] OR [50%,300%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('top_infill_extrusion_width', $array_setting)) {
		$array_setting['top_infill_extrusion_width'] = 0;
	}
	else
	{
		$tmp = $array_setting['top_infill_extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 25 || substr($tmp, 0, $pos) > 150))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['top_infill_extrusion_width'] = '[0.25,0.5] OR [50%,150%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('support_material_extrusion_width', $array_setting)) {
		$array_setting['support_material_extrusion_width'] = 0;
	}
	else
	{
		$tmp = $array_setting['support_material_extrusion_width'];
		$pos = strpos($tmp, "%");
		if (($pos !== FALSE && (substr($tmp, 0, $pos) < 50 || substr($tmp, 0, $pos) > 150))
				|| ($pos === FALSE && ($tmp != 0 && ($tmp < 0.25 || $tmp > 0.5))))
		{
			$errors['support_material_extrusion_width'] = '[0.25,0.5] OR [50%,150%]';
// 			return FALSE;
		}
	}
	if (!array_key_exists('bridge_flow_ratio', $array_setting)) {
		$array_setting['bridge_flow_ratio'] = 1;
	}
	if (!array_key_exists('resolution', $array_setting)) {
		$array_setting['resolution'] = 0;
	}
	
	// return false if error
	if (count($errors)) {
		return FALSE;
	}
	
	return TRUE;
}

//internal function
function ZimAPI__getModebyParameter($parameter) {
	switch ($parameter) {
		case ZIMAPI_PRM_CAMERA_PRINTSTART:
			return ZIMAPI_VALUE_MODE_HLS;
			break;
			
		case ZIMAPI_PRM_CAMERA_PRINTSTART_TIMELAPSE:
			return ZIMAPI_VALUE_MODE_HLS_IMG;
			break;
			
		default:
			return 'on'; //TODO edit here
			break;
	}
	
	return ZIMAPI_VALUE_MODE_OFF; // never reach here
}

function ZimAPI__filterCharacter($raw) {
	$filtered = "'" . str_replace("'", "'\"'\"'", $raw) . "'";
	
	return $filtered;
}

function ZimAPI__parseUpgradeXML(&$xml, &$array_upgrade) {
	$key_version = ZIMAPI_TITLE_RELEASENOTE_VERSION;
	$key_part = ZIMAPI_TITLE_RELEASENOTE_PART;
	$key_part_title = ZIMAPI_TITLE_RELEASENOTE_PART_TITLE;
	$key_part_note = ZIMAPI_TITLE_RELEASENOTE_PART_NOTE;
	$key_upgrade = ZIMAPI_TITLE_RELEASENOTE_UPGRADE;
	$attrib_lang = ZIMAPI_TITLE_RELEASENOTE_ATTRIB_LANG;
	$array_xml = array();
	$CI = &get_instance();
	
	switch(count($xml->$key_version)) {
		case 0: // new release note system (1.4.1+)
			if (count($xml->$key_upgrade) == 0) {
				throw new Exception('upgrade version and node not found');
			}
			
			foreach($xml->$key_upgrade as $upgd_xml) {
				$array_xml[] = $upgd_xml;
			}
			break;
			
		case 1: // release note system 1.4
			$array_xml[] = $xml;
			break;
			
		default:
			throw new Exception('unknown format with several versions in root');
	}
	
	foreach ($array_xml as $upgd_xml) {
		if (count($upgd_xml->$key_version) != 1) {
			throw new Exception('upgrade node contains several versions');
		}
		
		$upgd_version = (string) $upgd_xml->$key_version;
		
		if (count($upgd_xml->$key_part)) {
			$tmp_array = array();
			$retry = 0;
			
			while ($retry < 2) { // this loop just do twice at maximum
				foreach($upgd_xml->$key_part as $part) {
					if (isset($part[$attrib_lang]) && (
							($retry == 0 && $part[$attrib_lang] != $CI->config->item('language_abbr'))
							|| ($retry > 0 && $part[$attrib_lang] != 'en'))) {
						// if xml element has language attribute, we select user language, but force to english version if no result;
						// if xml element has no language attribute, we let it pass the check anyway (that means neutral message)
						continue;
					}
					
					if (count($part->$key_part_title) != 1) {
						throw new Exception('part with no title or several titles detected');
					}
					if (count($part->$key_part_note)) {
						foreach($part->$key_part_note as $note) {
							$tmp_array[(string) $part->$key_part_title][] = (string) $note;
						}
					}
					else {
						throw new Exception('part with no notes detected');
					}
				}
				
				if (count($tmp_array)) {
					break;
				}
				else {
					++$retry;
				}
			}
			$array_upgrade[$upgd_version] = $tmp_array;
		}
		else {
			throw new Exception('upgrade with no parts detected');
		}
	}
	
	return;
}

function ZimAPI__setPresetLocalization(&$array_json) {
	$CI = &get_instance();
	$lang_current = $CI->config->item('language_abbr');
	
	if (!is_array($array_json[ZIMAPI_TITLE_PRESET_NAME])) {
		return; // return directly if not array (old system or user preset)
	}
	
	if (isset($array_json[ZIMAPI_TITLE_PRESET_NAME][$lang_current])) {
		$array_json[ZIMAPI_TITLE_PRESET_NAME] = $array_json[ZIMAPI_TITLE_PRESET_NAME][$lang_current];
	}
	else {
		$array_json[ZIMAPI_TITLE_PRESET_NAME] = $array_json[ZIMAPI_TITLE_PRESET_NAME]['en'];
	}
	
	return;
}

function ZimAPI_usortComparePreset($a, $b) {
	$CI = &get_instance();
	$lang_current = $CI->config->item('language_abbr');
	
	if (!is_array($a[ZIMAPI_TITLE_PRESET_NAME]) || !is_array($b[ZIMAPI_TITLE_PRESET_NAME])) {
		return strcasecmp($a[ZIMAPI_TITLE_PRESET_NAME], $b[ZIMAPI_TITLE_PRESET_NAME]);
	}
	
	if (isset($array_json[ZIMAPI_TITLE_PRESET_NAME][$lang_current])) {
		return strcasecmp($a[ZIMAPI_TITLE_PRESET_NAME][$lang_current], $b[ZIMAPI_TITLE_PRESET_NAME][$lang_current]);
	}
	else {
		return strcasecmp($a[ZIMAPI_TITLE_PRESET_NAME]['en'], $b[ZIMAPI_TITLE_PRESET_NAME]['en']);
	}
	
	return 0;
}
