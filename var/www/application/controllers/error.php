<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Error extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->helper(array('form', 'url', 'json'));
	}

	public function index()
	{
		$this->load->library('parser');
		$this->lang->load('error', $this->config->item('language'));
		
		$data = array(
			'title'	=>	t('title'),
			'error'	=>	t('error'),
			'home'	=>	t('home')
		);
		
		$body_page = $this->parser->parse('error.php', $data, TRUE);
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>' . t('ZeePro Personal Printer 21 - Home') . '</title>',
				'contents'		=> $body_page,
		);
		$this->output->set_status_header(503, 'Error - Zeepro');
		$this->parser->parse('basetemplate', $template_data);
	}

}