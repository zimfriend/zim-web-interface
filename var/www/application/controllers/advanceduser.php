<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Advanceduser extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'url',
				'errorcode'
		) );
	}
	
	private function _display_controlIndex($err_msg = '') {
		$template_data = array();
		
		$this->load->helper('zimapi');
		$this->load->library('parser');
		
		$template_data = array(
				'serial'	=> ZimAPI_getSerial(),
				'err_msg'	=> $err_msg,
				'bicolor'	=> ($this->config->item('nb_extruder') >= 2) ? 'true' : 'false',
		);
		
		$this->_parseBaseTemplate('Advanced user', $this->parser->parse('advanceduser/index', $template_data, TRUE));
		
		return;
	}
	
	public function index() {
		global $CFG;

		$this->load->library('parser');
		
		if (file_exists($CFG->config['conf'] . '/G-code.json')) {
			$this->_display_controlIndex();
		} else {
			if ($_SERVER['REQUEST_METHOD'] == 'POST')
			{
				$this->load->helper('zimapi');
				
				$temp_serial = ZimAPI_getSerial();
				
				if (strtoupper($this->input->post('serial')) != strtoupper($temp_serial)) {
					$template_data = array('err_msg' => 'Incorrect serial number');
					
					$this->_parseBaseTemplate('Advanced user', $this->parser->parse('advanceduser/register', $template_data, TRUE));
				} else {
					
					$url = 'https://stat.service.zeepro.com/log.ashx';
					$data = array('printersn' => $temp_serial, 'version' => '1', 'category' => 'gcode', 'action' => 'register');
					$options = array('http' => array('header'  => "Content-type: application/x-www-form-urlencoded\r\n",
							'method'  => 'POST',
							'content' => http_build_query($data)));
					$context  = stream_context_create($options);
					@file_get_contents($url, false, $context);
					$result = substr($http_response_header[0], 9, 3);
					if ($result != 200) {
						$template_data = array('err_msg' => 'Can\'t connect to the Internet');
						
						$this->_parseBaseTemplate('Advanced user', $this->parser->parse('advanceduser/register', $template_data, TRUE));
					} else {
						$fp = fopen($CFG->config['conf'] . '/G-code.json', 'w');
						if ($fp) {
							fwrite($fp, json_encode(array('register' => date("c"))));
							fclose($fp);
							
							$this->_display_controlIndex();
						} else {
							$template_data = array('err_msg' => 'Internal error');
							
							$this->_parseBaseTemplate('Advanced user', $this->parser->parse('advanceduser/register', $template_data, TRUE));
						}
					}
				}
			} else {
				$template_data = array('err_msg' => '');

				$this->_parseBaseTemplate('Advanced user', $this->parser->parse('advanceduser/register', $template_data, TRUE));
			}
		}
		return;
	}
	
	public function stop() {
		$this->load->helper('printerstate');
		PrinterState_stopPrinting();
		$this->output->set_header('Location: /advanceduser');

		return;
	}
	
	public function move() {
		$axis = $this->input->get('axis');
		$value = $this->input->get('value');
		$speed = $this->input->get('speed');
		
		if ($axis === FALSE || $value === FALSE || $speed === FALSE
				|| ((float)$value == 0) || ((int)$speed == 0)) {
			$this->output->set_status_header(403);
			return;
		}
		else {
			$cr = 0;
			
			$this->load->helper(array('printerstate', 'errorcode'));
			
			$axis = strtoupper($axis);
			$cr = PrinterState_relativePositioning(TRUE);
			if ($cr == ERROR_OK) {
				$cr = PrinterState_move($axis, (float)$value, (int)$speed);
			}
			if ($cr == ERROR_OK) {
				$cr = PrinterState_relativePositioning(FALSE);
			}
			if ($cr == ERROR_OK) {
				$this->output->set_status_header(200);
				return;
			}
		}
		
		$this->output->set_status_header(403);
		return;
	}
	
	public function extrude($extruder = NULL, $value = NULL, $speed = NULL) {
		if (is_null($extruder) || is_null($value) || is_null($speed)
				|| ((int)$value == 0) || ((int)$speed == 0)) {
			$this->output->set_status_header(403);
			return;
		}
		else {
			$cr = 0;
			
			$this->load->helper(array('printerstate', 'errorcode'));
			
			$cr = PrinterState_setExtruder($extruder);
			if ($cr == ERROR_OK) {
				$cr = PrinterState_move('E', (int)$value, (int)$speed);
			}
			if ($cr == ERROR_OK) {
				$this->output->set_status_header(200);
				return;
			}
		}
		
		$this->output->set_status_header(403);
		return;
	}
	
	public function home($axis = 'ALL') {
		$cr = 0;
		
		$this->load->helper(array('printerstate', 'errorcode'));
		$axis = strtoupper($axis);
		$cr = PrinterState_homing($axis);
		if ($cr == ERROR_OK) {
			$this->output->set_status_header(200);
			return;
		}
		
		$this->output->set_status_header(403);
		return;
	}
	
	public function level($point = NULL) {
		$cr = 0;
		$array_cmd = array();
		
		if (is_null($point)) {
			$this->output->set_status_header(403);
			return;
		}
		
		$this->load->helper(array('printerstate', 'errorcode'));
		$cr = PrinterState_relativePositioning(FALSE);
		if ($cr != ERROR_OK) {
			$point = 'error';
		}
		switch ($point) {
			case 'center':
				$array_cmd = array(
						'X'	=> 75,
						'Y'	=> 75,
				);
				break;
				
			case 'xmin_ymin':
				$array_cmd = array(
						'X'	=> 0,
						'Y'	=> 0,
				);
				break;
				
			case 'xmin_ymax':
				$array_cmd = array(
						'X'	=> 0,
						'Y'	=> 150,
				);
				break;
				
			case 'xmax_ymax':
				$array_cmd = array(
						'X'	=> 150,
						'Y'	=> 150,
				);
				break;
				
			case 'xmax_ymin':
				$array_cmd = array(
						'X'	=> 150,
						'Y'	=> 0,
				);
				break;
				
			default:
				$this->output->set_status_header(403);
				return;
				break; // never reach here
		}
		
		foreach ($array_cmd as $axis => $value) {
			$cr = PrinterState_move($axis, $value, 2000);
			if ($cr != ERROR_OK) {
				$this->output->set_status_header(403);
				return;
			}
		}
		
		$this->output->set_status_header(200);
		return;
	}
	
	public function heat($extruder = NULL, $temper = NULL) {
		$cr = 0;
		
		if (is_null($extruder) || is_null($temper)) {
			$this->output->set_status_header(403);
			return;
		}
		
		$this->load->helper(array('printerstate', 'errorcode'));
		$cr = PrinterState_setExtruder($extruder);
		if ($cr == ERROR_OK) {
			$cr = PrinterState_setTemperature($temper);
		}
		if ($cr == ERROR_OK) {
			$this->output->set_status_header(200);
			return;
		}
		
		$this->output->set_status_header(403);
		return;
	}
	
	public function gcodefile() {
		$cr = 0;
		$gcode = NULL;
		$mode = '';
		$rewrite = TRUE;
		
		$this->load->library('parser');
		$this->load->helper('printerstate');
		
		$mode = $this->input->post('mode');
		if ($mode == 'verbatim') {
			$rewrite = FALSE;
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$upload_config = array (
					'upload_path'	=> $this->config->item('temp'),
					'allowed_types'	=> '*',
// 					'allowed_types'	=> 'gcode',
					'overwrite'		=> TRUE,
					'remove_spaces'	=> TRUE,
					'encrypt_name'	=> TRUE,
			);
			$this->load->library('upload', $upload_config);
			
			if ($this->upload->do_upload('f')) {
				$gcode = $this->upload->data();
				
				$cr = PrinterState_runGcodeFile($gcode['full_path'], $rewrite);
				if ($cr == TRUE) {
					$this->_parseBaseTemplate('Advanced user', $this->parser->parse('advanceduser/confirm', array(), TRUE));
				}
				else {
					$this->_display_controlIndex('Internal error');
				}
			} else {
				$this->_display_controlIndex('Missing file');
			}
		}
		else {
			$this->output->set_header('Location: /');
		}
		
		return;
	}
	
	public function temper_ajax() {
		$array_temper = 0;
		
		$this->load->helper(array('printerstate', 'errorcode'));
		$array_temper = PrinterState_getExtruderTemperaturesAsArray();
		if (count($array_temper)) {
			if (isset($array_temper[PRINTERSTATE_LEFT_EXTRUD])) {
				$array_temper['left'] = $array_temper[PRINTERSTATE_LEFT_EXTRUD];
				unset($array_temper[PRINTERSTATE_LEFT_EXTRUD]);
			}
			if (isset($array_temper[PRINTERSTATE_RIGHT_EXTRUD])) {
				$array_temper['right'] = $array_temper[PRINTERSTATE_RIGHT_EXTRUD];
				unset($array_temper[PRINTERSTATE_RIGHT_EXTRUD]);
			}
			
			$this->output->set_status_header(200);
			print json_encode($array_temper);
			return;
		}
		
		$this->output->set_status_header(403);
		return;
	}
	
	public function rfid_ajax() {
		$array_rfid = array();
		$array_cmd = array();
		
		$this->load->helper(array('printerstate', 'errorcode'));
		if ($this->config->item('nb_extruder') >= 2) {
			$array_cmd['left'] = 'l';
		}
		$array_cmd['right'] = 'r';
		foreach($array_cmd as $key => $value) {
			$tmp_rfid = NULL;
			$cr = PrinterState_getCartridgeCode($tmp_rfid, $value);
			if ($cr != ERROR_OK) {
				$this->output->set_status_header(403);
				return;
			}
			else if (is_null($tmp_rfid)) {
				$tmp_rfid = 'EMPTY';
			}
			$array_rfid[$key] = $tmp_rfid;
		}
				
		$this->output->set_status_header(200);
		print json_encode($array_rfid);
		return;
	}
}
