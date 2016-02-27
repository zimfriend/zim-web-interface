<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Connectb2b extends MY_Controller {
	private function _treat3dslash($name_model, $token_model) {
		//TODO think if we pass defined variable to constant and pass basic function to helper
		$key_3dslash = '179c2398cf1ab499e7f68070856f3de7ceac10764e89de05f8aaa0f047eb3b14';
		$url_3dslash = 'https://www.3dslash.net/i.php?src=zim&token=';
		$save_path = $this->config->item('temp');
		$ret_val = 0;
		$slicer_return = array();
		$slicer_ok = TRUE;
		$fp = NULL;
			
		if (!preg_match("/^([a-z0-9])+$/i", $token_model)) {
			$this->_exitWithError500('3dslash token format invalid');
			return;
		}
		
		$this->load->helper(array('printerstoring', 'errorcode', 'slicer', 'printerlog'));
		
		$url_3dslash .= hash('sha256', $token_model . '|' . $key_3dslash);
		$save_path .= PrinterStoring__generateFilename($name_model) . '.stl';
		
		// verify slicer online
		$slicer_ok = Slicer_checkOnline(TRUE);
		
// 		copy($url_3dslash, $save_path);
		$fp = fopen($url_3dslash, 'r');
		if (!$fp) {
			$this->_exitWithError500('3dslash remote file initialize failed');
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
		
		$ret_val = Slicer_addModel(array($save_path), PRINTERLOG_STATS_LABEL_3DSLASH, TRUE, $slicer_return);
		if ($ret_val != ERROR_OK) {
			$this->_exitWithError500('3dslash model import failed');
		}
		else {
			$this->output->set_header('Location: /sliceupload/slice');
		}
		
		return;
	}
	
	//TODO manage this function with all busy statuses
	public function with3dslash() {
		$redirect_cookie = $this->input->cookie('redirectData');
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$name_post = $this->input->post('name');
			$token_post = $this->input->post('token');
			
			$this->_treat3dslash($name_post, $token_post);
		}
		else if ($redirect_cookie !== FALSE) {
			$array_cookie = json_decode($redirect_cookie, TRUE);
			
			if (is_array($array_cookie) && isset($array_cookie['name']) && isset($array_cookie['token'])) {
				$this->_treat3dslash($array_cookie['name'], $array_cookie['token']);
			}
			else {
				$this->_exitWithError500('3dslash remote redirection data invalid');
			}
		}
		else {
			$this->load->helper('form');
			$this->load->view('template/3dslash');
		}
		
		return;
	}
}
