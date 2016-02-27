<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Test_log extends CI_Controller {

	public function index() {
		$display = '';
		
		$this->load->helper('corestatus');
		if (!CoreStatus_initialFile()) {
			$this->load->helper('printerlog');
			PrinterLog_logError('status files initialisation error when MY_Controller started', __FILE__, __LINE__);
			
			// let request failed
			$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');
			header($protocol . ' 500');
			header('Content-type: text/plain; charset=UTF-8');
			echo 'file initialisation error';
			exit;
		}
		
// 		$this->output->set_content_type('text/plain; charset=UTF-8');
		$this->output->set_content_type('txt_u');
		$display .= 'Log level: ' . $this->config->item('log_level') . "\n";
		if (file_exists($this->config->item('log_file'))) {
			$array_log = @file($this->config->item('log_file'));
			foreach ($array_log as $line) {
				$display .= $line;
			}
		}
		else {
			$display .= "no log file\n";
		}
		
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display));
// 		$this->output->set_header('Location: /test_log/debug');
		
		return;
	}
	
	public function test_curl() {
		$this->load->helper('printerlog');
		PrinterLog_logDebug('category: ' . $this->input->post('category') . ', action: ' . $this->input->post('action')
				. ', printersn: ' . $this->input->post('printersn') . ', label: ' . $this->input->post('label')
				. ', value: ' . $this->input->post('value'));
		
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => 'ok'));
		return;
	}
	
	public function clear() {
		if (file_exists($this->config->item('log_file'))) {
			unlink($this->config->item('log_file'));
// 			echo "clear log file\n";
		}
// 		$this->index();
		$this->output->set_header('Location: /test_log');
		
		return;
	}
	
	public function debug($type = NULL) {
		if ($type == 'clear') {
			if (file_exists($this->config->item('log_file'))) {
				unlink($this->config->item('log_file'));
			}
			
			$this->output->set_header('Location: /test_log/debug');
			return;
		}
		
		$this->file('debug');
		
		return;
	}
	
	public function arduino($type = NULL) {
		if ($type == 'clear') {
			if (file_exists($this->config->item('log_arduino'))) {
				unlink($this->config->item('log_arduino'));
			}
			
			$this->output->set_header('Location: /test_log/arduino');
			return;
		}
		
		$this->file('arduino');
		
		return;
	}
	
	public function printlog() {
		$this->file('printlog');
		
		return;
	}
	
	public function slicelog() {
		$this->file('slicelog');
		
		return;
	}
	
	public function statslog() {
		$this->file('statslog');
		
		return;
	}
	
	public function remoteslice() {
		$this->file('remoteslice');
		
		return;
	}
	
	private function file($type = 'debug') {
		$path_file = '';
		
		switch ($type) {
			case 'debug':
				$path_file = $this->config->item('log_file');
				break;
				
			case 'arduino':
				$path_file = $this->config->item('log_arduino');
				break;
				
			case 'printlog':
				$this->load->helper('printerstate');
				$path_file = PRINTERSTATE_FILE_PRINTLOG;
				break;
				
			case 'slicelog':
				$this->load->helper('slicer');
				$path_file = SLICER_FILE_SLICELOG;
				break;
				
			case 'statslog':
				$this->load->helper('printerlog');
				$path_file = PRINTERLOG_STATS_FILEPATH_LOG;
				break;
				
			case 'remoteslice':
				$this->load->helper('slicer');
				$path_file = SLICER_FILE_REMOTE_LOG;
				break;
				
			default:
				break;
		}
		
		if (!$path_file) {
			$this->output->set_content_type('txt_u');
			echo 'error';
		}
		else if (!file_exists($path_file)) {
			$this->output->set_content_type('txt_u');
			echo 'no file';
		}
		else {
			$this->load->helper('file');
			$this->output->set_content_type(get_mime_by_extension($path_file))->set_output(@file_get_contents($path_file));
		}
		
		return;
	}

}
