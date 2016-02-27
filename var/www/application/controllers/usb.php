<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Usb extends MY_Controller {
	
	function __construct() {
		parent::__construct();
		$this->load->helper(array('url'));
	}
	
	public function index()
	{
		$this->load->library('parser');
		$this->lang->load('usb', $this->config->item('language'));
		
		$data = array(
			'hint'	=>	t('hint'),
		);
		
		$this->_parseBaseTemplate(t('usb_pagetitle'),
				$this->parser->parse('usb', $data, TRUE));
		
		return;
	}
}