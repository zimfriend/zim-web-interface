<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

if (!defined('PRONTERFACE_EMULATOR_LOG')) {
	define('PRONTERFACE_EMULATOR_LOG', '_emulator.log');
}

class Extrusion_control extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'url',
				'errorcode'
		) );
	}
	
	public function index() {
		global $CFG;
		$template_data = array();
		$body_page = NULL;
		
		$this->load->library('parser');
		
		// parse the main body
		$template_data = array(
				'bicolor'	=> ($this->config->item('nb_extruder') >= 2) ? 'true' : 'false',
		);
		$body_page = $this->parser->parse('extrusion_control', $template_data, TRUE);

		// parse all page
		$template_data = array(
				'lang'			=> 'en',
				'headers'		=> '<title>Extrusion control</title>',
				'contents'		=> $body_page,
		);

		$this->parser->parse('basetemplate', $template_data);

		return;
	}
	
	// stop printing (but no access when in printing due to my_controller, why we put here?)
	// just for stop print in idle mode (asynchronized case)
	public function stop() {
		$this->load->helper('printerstate');
		PrinterState_stopPrinting();
		$this->output->set_header('Location: /extrusion_control');
		
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
	
	public function temper_ajax() {
		$array_temper = array();
		
		$this->load->helper(array('printerstate', 'errorcode'));
		$array_temper = PrinterState_getExtruderTemperaturesAsArray();
		if (count($array_temper)) {
			if (isset($array_temper[PRINTERSTATE_LEFT_EXTRUD])) {
				if ($this->config->item('nb_extruder') >= 2) {
					$array_temper['left'] = $array_temper[PRINTERSTATE_LEFT_EXTRUD];
				}
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
