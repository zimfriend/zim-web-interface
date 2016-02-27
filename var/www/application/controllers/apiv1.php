<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Apiv1 extends MY_Controller {
	private function _stlupload_fail($msg = NULL) {
		$this->load->helper('printerlog');
		PrinterLog_logDebug($msg);
		
		$this->output->set_header('Location: /');
		
		return;
	}
	
	private function _stlupload_main($model_name, $model_url) {
		$save_path = $this->config->item('temp');
		
		$this->load->helper(array('printerstoring', 'errorcode', 'slicer'));
		
		$model_url = filter_var($model_url, FILTER_SANITIZE_URL);
		$save_path .= PrinterStoring__generateFilename($model_name) . '.stl';
		
		// verify slicer online
		$slicer_ok = Slicer_checkOnline(TRUE);
		
		$fp = fopen($model_url, 'r');
		if (!$fp) {
			$this->_stlupload_fail('remote model url open failed');
		}
		file_put_contents($save_path, $fp);
		fclose($fp);
		
		if ($slicer_ok == FALSE) {
			// wait slicer to get online if in restarting (only 10s)
			for ($i=0; $i < 10; ++$i) {
				if (Slicer_checkOnline(FALSE)) {
					break;
				}
				else {
					sleep(1);
				}
			}
		}
		
		$ret_val = Slicer_addModel(array($save_path), 'APIV1', TRUE, $slicer_return);
		if ($ret_val != ERROR_OK) {
			$this->_stlupload_fail('remote model import failed');
		}
		else {
			$this->output->set_header('Location: /sliceupload/slice');
		}
		
		return;
	}
	
	public function stlupload() {
		$redirect_cookie = $this->input->cookie('redirectData');
		
		$this->load->helper('printerlog');
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$name_post = $this->input->post('name');
			$url_post = $this->input->post('url');
				
			$this->_stlupload_main($name_post, $url_post);
		}
		else if ($redirect_cookie !== FALSE) {
			$array_cookie = json_decode($redirect_cookie, TRUE);
			
			if (is_array($array_cookie) && isset($array_cookie['name']) && isset($array_cookie['url'])) {
				$this->_stlupload_main($array_cookie['name'], $array_cookie['url']);
			}
			else {
				$this->_exitWithError500('remote model redirection data invalid');
			}
		}
		else {
			$this->output->set_header('Location: /');
			PrinterLog_logDebug('remote model import entry API failed');
		}
		
		return;
	}
}