<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Test_presets extends MY_Controller {
	
	public function index() {
		$filepath = NULL;
		
		$this->load->helper(array('zimapi', 'download'));
		
		$filepath = ZimAPI_packUpPresets();
		if ($filepath && file_exists($filepath)) {
			force_download(ZIMAPI_VALUE_PRESETS_CS_NAME, @file_get_contents($filepath));
		}
		else {
			$this->_exitWithError500('export presets error');
		}
		
		return;
	}

}
