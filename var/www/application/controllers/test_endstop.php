<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Test_endstop extends MY_Controller {
	public function index() {
		$endstop = array();
		$template_data = array();
		$body_page = NULL;
		$error = 'none';
		
		$this->load->library('parser');
		//$this->lang->load('test_endstop', $this->config->item('language'));
		$this->load->helper('printerstate');
		
		if (PrinterState_getEndstopList($endstop) != ERROR_OK) {
			$error = 'block';
		}
		
		$template_data = array(
				'error'		=> $error,
				'yback'		=> $endstop['ymax'] ? 'Pressed' : "Not pressed",
				'yfront'	=> $endstop['ymin'] ? 'Pressed' : "Not pressed",
				'xleft'		=> $endstop['xmin'] ? 'Pressed' : "Not pressed",
				'xright'	=> $endstop['xmax'] ? 'Pressed' : "Not pressed",
				'ztop'		=> $endstop['zmin'] ? 'Pressed' : "Not pressed",
				'zbottom'	=> $endstop['zmax'] ? 'Pressed' : "Not pressed",
				'leftcart'	=> $endstop['E1'] ? 'Filament' : "No filament",
				'rightcart'	=> $endstop['E0'] ? 'Filament' : "No filament",
				'monocart'	=> $endstop['E0'] ? 'Filament' : "No filament",
				'home'		=> t('Home'),
				'bicolor'	=> ($this->config->item('nb_extruder') >= 2) ? 'true' : 'false',
		);
		$body_page = $this->parser->parse('test_endstop', $template_data, TRUE);
		
		$template_data = array(
				'lang'		=> 'en',
				'headers'	=> '<title>Zim</title>',
				'contents'	=> $body_page,
		);
		$this->parser->parse('basetemplate', $template_data);
		return;
	}

	public function endstop_ajax()
	{
		$this->load->helper('printerstate');
		$endstop = array();
		
		if (PrinterState_getEndstopList($endstop) != ERROR_OK)
		{
			$this->output->set_status_header(503);
			return;
		}
		
		$display = json_encode($endstop);
		echo $display;
		return;
	}
}