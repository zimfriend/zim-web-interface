<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Menu_home extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'url',
				'json' 
		) );
	}
	
	public function index() {
		$template_data = array();
		$need_update = FALSE;
	
		$this->load->library('parser');
		$this->lang->load('menu_home', $this->config->item('language'));
		$this->load->helper('zimapi');
		
		if (!ZimAPI_cameraOff()) {
			$this->load->helper('printerlog');
			PrinterLog_logError('can not turn off camera', __FILE__, __LINE__);
		}
		$need_update = !(ZimAPI_getVersion(TRUE) == ZimAPI_getVersion(FALSE));
		//parse the main body
		$template_data = array(
// 				'title'				=> t('Home'),
				'update_available'	=> $need_update ? t('update_available') : "",
				'update_hint'		=> t('update_available'),
				'my_library'		=> t('my_library'),
				'my_zim_shop'		=> t('my_zim_shop'),
				'menu_printlist'	=> t('Quick print'),
				'menu_printerstate'	=> t('Configuration'),
				'manage'			=> t('manage'),
				'upload'			=> t('upload'),
				'about'				=> t('about'),
				'library_visible'	=> ($this->config->item('use_sdcard') == TRUE) ? 'block' : 'none',
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Home'),
				$this->parser->parse('menu_home', $template_data, TRUE));
		
		return;
	}
	
	public function stats_my_shop() {
		$this->load->helper('printerlog');
		$this->load->library('parser');
		
		// stats info
		PrinterLog_statsWebClick(PRINTERLOG_STATS_LABEL_SHOP);
		$this->parser->parse('plaintxt', array('display' => 'ok'));
		
		return;
	}
}

