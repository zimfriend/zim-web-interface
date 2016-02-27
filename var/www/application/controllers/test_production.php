<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Test_production extends CI_Controller {
	
	public function index() {
		$this->load->helper('corestatus');
		
		CoreStatus_prodTmpConnection();
		$this->output->set_header('Location: /');
		
		return;
	}
}
