<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Test_cartridge extends CI_Controller {

	public function index() {
// 		$this->output->set_header('Location: /');
		
		$abb_cartridge = $this->input->get('p');
		$value_cartridge = $this->input->get('v');
		$path_cartridge = $this->config->item('bin') . 'arcontrol/';
		$path_database = $path_cartridge . '_cartridge/';

		if ($this->config->item('simulator') == FALSE) {
			$display = 'just for simulator';
		}
		
		if ($abb_cartridge && $value_cartridge) {
			$ret_val = 0;
			
			$filename_cartridge = ($abb_cartridge == 'r') ?  'right.json' : 'left.json';
			
			if (!file_exists($path_database . $value_cartridge)) {
				echo 'no selected cartridge';
			}
			else {
				$ret_val = copy($path_database . $value_cartridge, $path_cartridge . $filename_cartridge);
				if ($ret_val == FALSE) {
					echo 'selected cartridge error';
				}
				else {
					echo 'ok';
				}
			}
		}
		else {
			$cartridge_data_l = array();
			$cartridge_data_r = array();
			$data_view = array();
			$enable_l = FALSE;
			$enable_r = FALSE;
			
			$this->load->helper(array('printerstate', 'directory', 'form'));
			
			$enable_l = !PrinterState_getFilamentStatus('l');
			$enable_r = !PrinterState_getFilamentStatus('r');
			
			$temp_array = directory_map($path_database, 1);
			foreach ($temp_array as $cartridge_name) {
				if ($enable_l) {
					$cartridge_data_l[$cartridge_name] = $cartridge_name;
				}
				if ($enable_r) {
					$cartridge_data_r[$cartridge_name] = $cartridge_name;
				}
			}
			
			if ($enable_l == TRUE && file_exists($path_cartridge . '_left.json')) {
				$enable_l = FALSE;
			}
			if ($enable_r == TRUE && file_exists($path_cartridge . '_right.json')) {
				$enable_r = FALSE;
			}
			
			$data_view = array(
					'cartridge_data_l'	=> $cartridge_data_l,
					'cartridge_data_r'	=> $cartridge_data_r,
					'disable_l'			=> ($enable_l) ? '' : 'disabled',
					'disable_r'			=> ($enable_r) ? '' : 'disabled',
			);
			
			$this->load->view('template/test_cartridge', $data_view);
		}
		
		return;
	}
	
	public function remove($abb_cartridge = '') {
		$ret_val = 0;
		$display = NULL;
		$parameter = '';
		$output = array();
		$arcontrol_fullpath = $this->config->item('arcontrol_c');
		
		$this->load->helper('printerstate');
		
		switch (strtolower($abb_cartridge)) {
			case 'l':
				$parameter = ' -rmctl';
				break;
				
			case 'r':
				$parameter = ' -rmctr';
				break;
				
			default:
				$display = 'wrong cartridge';
				break;
		}
		if ($this->config->item('simulator') == FALSE) {
			$display = 'just for simulator';
		}
		
		if (is_null($display)) {
			$ret_val = PrinterState_getFilamentStatus(strtolower($abb_cartridge));
			if ($ret_val == FALSE) {
				exec($arcontrol_fullpath . $parameter, $output, $ret_val);
				if ($ret_val == ERROR_NORMAL_RC_OK) {
					$display = 'ok';
				}
				else {
					$display = 'internal command error';
				}
			}
			else {
				$display = 'filament status error';
			}
		}
		
		$this->output->set_content_type('txt_u');
// 		echo $display;
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display));
		
		return;
	}
	
	public function insert($abb_cartridge = '') {
		$ret_val = 0;
		$display = NULL;
		$parameter = '';
		$output = array();
		$arcontrol_fullpath = $this->config->item('arcontrol_c');
		
		$this->load->helper('printerstate');
		
		switch (strtolower($abb_cartridge)) {
			case 'l':
				$parameter = ' -isctl';
				break;
				
			case 'r':
				$parameter = ' -isctr';
				break;
				
			default:
				$display = 'wrong cartridge';
				break;
		}
		if ($this->config->item('simulator') == FALSE) {
			$display = 'just for simulator';
		}
		
		if (is_null($display)) {
			$ret_val = PrinterState_getFilamentStatus(strtolower($abb_cartridge));
			if ($ret_val == FALSE) {
				exec($arcontrol_fullpath . $parameter, $output, $ret_val);
				if ($ret_val == ERROR_NORMAL_RC_OK) {
					$display = 'ok';
				}
				else {
					$display = 'internal command error';
				}
			}
			else {
				$display = 'filament status error';
			}
		}
		
		$this->output->set_content_type('txt_u');
// 		echo $display;
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display));
		
		return;
	}

}
