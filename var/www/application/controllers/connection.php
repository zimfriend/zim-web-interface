<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Connection extends MY_Controller {

	private function _generate_framePage($body_page) {
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Connection configuration'), $body_page);
		
		return;
	}
	
	public function ip_check($ip) {
		if (filter_var ( $ip, FILTER_VALIDATE_IP )) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function mask_check($mask) {
		if (! $m = ip2long ( $mask ))
			return false;
		
		$m = ~ $m;
		return $m && ~ $m && ! ($m & ($m + 1));
	}
	
	public function gateway_check($ip) {
	// @todo The gateway should be within the mask
		if (filter_var ( $ip, FILTER_VALIDATE_IP )) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	public function index() {
		$this->output->set_header("Location:/connection/wifissid/wizard");
		
		return;
	}
	
	public function advanced() {
		$template_data = array();
		$body_page = NULL;
		
		$this->load->library('parser');
		
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/index', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'title'			=> t('Connection configuration'),
				'hint'			=> t('Welcome...'),
				'wifissid'		=> htmlspecialchars(t('Option 1')),
				'wifip2p'		=> htmlspecialchars(t('Option 3')),
				'wired'			=> htmlspecialchars(t('Option 2')),
				'set_hostname'	=> t('set_hostname'),
		);
		
		$body_page = $this->parser->parse('connection/index', $template_data, TRUE);
	
		// parse all page
		$this->_generate_framePage($body_page);
		
		return;
	}
	
	public function wifissid($mode = NULL) {
		$template_data = array();
		$list_ssid = array();
		$body_page = NULL;
		
		$this->load->helper(array(
				'form',
				'url',
				'zimapi'
		));
		
		$this->load->library(array('form_validation', 'parser'));
		
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/wifissid', $this->config->item('language'));

		if ($this->form_validation->run() == FALSE) {
			foreach(ZimAPI_listSSIDAsArray() as $ssid) {
				$list_ssid[] = array(
						'name'	=> htmlspecialchars($ssid),
						'link'	=> htmlspecialchars(rawurlencode($ssid)),
				);
			}
			
			// parse the main body
			$template_data = array(
					'title'			=> t('WiFi network connected to the Internet'),
					'back'			=> t('Back'),
					'list_ssid'		=> $list_ssid,
					'wizard'		=> 'mode=' . (($mode == 'wizard') ? 'wizard' : 'normal'),
					'no_visable'	=> htmlspecialchars(t("Not visible...")),
			);
			
			$body_page = $this->parser->parse('connection/wifissid', $template_data, TRUE);
			
			// parse all page
			$this->_generate_framePage($body_page);
		} else {
// 			header("Location:/connection/wifipswd");
			$this->output->set_header('Location: /connection/wifipswd');
		}
		
		return;
	}
	
	public function wifinotvisiblessid() {
		$template_data = array();
		$body_page = NULL;
		$mode = $this->input->get('mode');
		
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/wifinotvisiblessid', $this->config->item('language'));
		
		$this->load->helper(array(
				'form',
				'url'
		));
		
		$this->load->library(array('form_validation', 'parser'));
		
		$this->form_validation->set_rules('ssid', 'SSID', 'required');
		$this->form_validation->set_message('required', t('required ssid'));
		
		if ($this->form_validation->run() == FALSE) {
			// parse the main body
			$template_data = array(
					'title'		=> t('WiFi network connected to the Internet'),
					'back'		=> t('Back'),
					'error'		=> form_error('ssid'),
					'submit'	=> htmlspecialchars(t("OK")),
			);
			
			$body_page = $this->parser->parse('connection/wifinotvisiblessid', $template_data, TRUE);
			
			// parse all page
			$this->_generate_framePage($body_page);
		} else {
			$this->output->set_header('Location: /connection/wifipswd?ssid=' . rawurlencode($this->input->post('ssid'))
					. '&mode=' . (($mode == 'wizard') ? 'wizard' : 'normal'));
		}
		
		return;
	}
	
	public function wifipswd()
	{
		$template_data = array();
		$body_page = NULL;
		$valid = TRUE;

		$this->load->library(array('parser', 'form_validation'));
		$this->load->helper(array('zimapi', 'corestatus'));
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/wifipswd', $this->config->item('language'));
			
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$this->form_validation->set_rules('password_confirm', 'Password confirmation', 'matches[password]');
			$valid = $this->form_validation->run();
			$ssid = $this->input->post('ssid');
			$mode = $this->input->post('mode');
			
			if ($valid != FALSE)
			{
				$passwd = $this->input->post('password');
					
				$ret_val = ZimAPI_setcWifi($ssid, $passwd);
				if ($ret_val != ERROR_OK)
				{
					// 				$error = t('invalid data');
					$this->output->set_header("Location:/connection/wifissid" . ($mode == 'wizard') ? '/wizard' : NULL);
					return;
				}
				else
				{
					//$this->confirmation();
					if ($mode == 'wizard')
					{
						// 					$this->confirmation_wizard();
						// 					$this->in_progress();
						if (!CoreStatus_wantActivation())
						{
							$this->load->helper('printerlog');
							PrinterLog_logError('can not set need activation status', __FILE__, __LINE__);
						}
						ZimAPI_restartNetwork();
						$this->output->set_header("Location:/connection/in_progress/" . rawurlencode($ssid));
							
						return;
					}
					else {
						if (!CoreStatus_wantHostname())
						{
							$this->load->helper('printerlog');
							PrinterLog_logError('can not set need hostname status', __FILE__, __LINE__);
						}
						$this->output->set_header("Location:/printerstate/sethostname");
						return;
					}
				}
			}
		}
		else
		{
			$ssid = $this->input->get('ssid');
			$mode = $this->input->get('mode');
		}
		
		$template_data = array(
				'title'				=> htmlspecialchars(t("network", $ssid)),
				'ssid'				=> $ssid,
				'label'				=> htmlspecialchars(t("network password")),
				'back'				=> t('Back'),
				'submit'			=> htmlspecialchars(t("OK")),
				'mode'				=> ($mode == 'wizard') ? 'wizard' : 'normal',
				'confirm_password'	=> t('confirm_password'),
				'show_password'		=> t('show_password'),
				'err_msg'			=> $valid == FALSE ? t('err_msg') : ""
		);
		$body_page = $this->parser->parse('connection/wifipswd', $template_data, TRUE);
			
		// parse all page
		$this->_generate_framePage($body_page);
		
		return;
	}
	
	public function wired() {
		$template_data = array();
		$body_page = NULL;
		
		$this->load->helper(array('form', 'url'));
		
		$this->load->library(array('form_validation', 'parser'));
				
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/wired', $this->config->item('language'));
		
		$this->template->set ( 'lang', $this->config->item('language_abbr') );
		$this->template->set ( 'header', "<title>" . t ( 'ZeePro Personal Printer 21 - Connection configuration' ) . "</title>" );
		
// 		if ($this->form_validation->run() == FALSE) {
			$template_data = array(
					'title'		=> t('Wired network connection'),
					'back'		=> t('Back'),
					'text1'		=> t("Text"),
					'auto_btn'	=> htmlspecialchars(t("OK")),
					'text2'		=> t("Text2"),
					'adv_btn'	=> htmlspecialchars(t("Advanced")),
			);
			$body_page = $this->parser->parse('connection/wired', $template_data, TRUE);
				
			// parse all page
			$this->_generate_framePage($body_page);
// 		}
// 		else {
// 			$this->confirmation();
// 		}
		
		return;
	}
	
	public function wiredauto() {
		$cr = 0;
		
		$this->load->helper(array('zimapi'));
		
		$cr = ZimAPI_setpEth();
		if ($cr != ERROR_OK) {
			$this->output->set_header("Location:/connection/advanced");
		}
		else
		{
// 			$this->confirmation();
			$this->output->set_header("Location:/printerstate/sethostname");
			if (!CoreStatus_wantHostname()) {
				$this->load->helper('printerlog');
				PrinterLog_logError('can not set need hostname status', __FILE__, __LINE__);
			}
		}
		
		return;
	}
	
	public function wiredadvanced() {
// 		global $CFG;
		$template_data = array();
		$body_page = NULL;
		
		$this->load->helper(array('form', 'url'));
		
		$this->load->library('form_validation');
				
		$this->lang->load('connection/master', $this->config->item ('language'));
		$this->lang->load('connection/wiredadvanced', $this->config->item('language'));
		
		$this->template->set ( 'lang', $this->config->item('language_abbr') );
		$this->template->set ( 'header', "<title>" . t ( 'ZeePro Personal Printer 21 - Connection configuration' ) . "</title>" );
		
 		$this->form_validation->set_rules('ip', 'ip error', 'callback_ip_check');
		$this->form_validation->set_message('ip_check', t("ip error"));
 		$this->form_validation->set_rules('mask', 'mask error', 'callback_mask_check');
		$this->form_validation->set_message('mask_check', t("mask error"));
 		$this->form_validation->set_rules('gateway', 'gateway error', 'callback_gateway_check');
		$this->form_validation->set_message('gateway_check', t("gateway error"));
 		$this->form_validation->set_rules('dns', 'dns error', 'callback_ip_check');
 		$this->form_validation->set_message('ip_check', t("dns error"));
 		$this->form_validation->set_error_delimiters('<i>', '</i>');
 		 			
		if ($this->form_validation->run () == FALSE) {
			$this->load->library('parser');
// 			$this->template->load ( 'connectionmaster', 'connectionwiredadvanced', $data );
			$template_data = array(
					'title'			=> t('Advanced wired network connection'),
					'back'			=> t('Back'),
					'ip_label'		=> htmlspecialchars(t("ip")),
					'ip_value'		=> set_value('ip'),
					'ip_hint'		=> htmlspecialchars(t("ip ex")),
					'ip_error'		=> form_error('ip'),
					'mask_label'	=> htmlspecialchars(t("mask")),
					'mask_value'	=> set_value('mask'),
					'mask_hint'		=> htmlspecialchars(t("mask ex")),
					'mask_error'	=> form_error('mask'),
					'gateway_label'	=> htmlspecialchars(t("gateway")),
					'gateway_value'	=> set_value('gateway'),
					'gateway_hint'	=> htmlspecialchars(t("gateway ex")),
					'gateway_error'	=> form_error('gateway'),
					'dns_label'		=> htmlspecialchars(t("dns")),
					'dns_value'		=> set_value('dns'),
					'dns_hint'		=> htmlspecialchars(t("dns ex")),
					'dns_error'		=> form_error('dns'),
					'submit'		=> htmlspecialchars(t("OK")),
			);
			$body_page = $this->parser->parse('connection/wiredadvanced', $template_data, TRUE);
				
			// parse all page
			$this->_generate_framePage($body_page);
		} else {
			$cr = 0;
			$ip = $this->input->post('ip');
			$mask = $this->input->post('mask');
			$gateWay = $this->input->post('gateway');
			
			$cr = ZimAPI_setcEth($ip, $mask, $gateWay);
			if ($cr != ERROR_OK) {
				$this->output->set_header("Location:/connection/advanced");
			}
			else
			{
				//$this->confirmation();
				$this->output->set_header("Location:/printerstate/sethostname");
				if (!CoreStatus_wantHostname()) {
					$this->load->helper('printerlog');
					PrinterLog_logError('can not set need hostname status', __FILE__, __LINE__);
				}
			}
		}
		
		return;
	}
	
	public function wifip2p() {
		$ret_val = 0;
		$error = '';
		$template_data = array();
		$body_page = NULL;
		
		$this->load->helper(array('zimapi'));
		$this->lang->load('connection/wifip2p', $this->config->item('language'));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			
			$this->load->library('form_validation');
			
			$this->form_validation->set_rules('ssid', 'SSID', 'required');
			
			if ($this->form_validation->run() == FALSE) {
				// Here is where you do stuff when the submitted form is invalid.
				$error = t('invalid SSID');
			}
			else {
				$ssid = $this->input->post('ssid');
				$passwd = $this->input->post('pwd');
				
				if (!ctype_print($ssid) || ($passwd && !ctype_print($passwd))) {
					$error = t('invalid data (special character)');
				}
				else {
					$ret_val = ZimAPI_setsWifi($ssid, $passwd);
					if ($ret_val != ERROR_OK) {
						$error = t('invalid data');
					}
					else {
// 						$this->output->set_header("Location:/connection/confirmation");
// 						$this->confirmation();
						$this->output->set_header("Location:/printerstate/sethostname");
						if (!CoreStatus_wantHostname()) {
							$this->load->helper('printerlog');
							PrinterLog_logError('can not set need hostname status', __FILE__, __LINE__);
						}
						return; // end generating if successed
					}
				}
			}
		}
		
		// generate form to submit when not in post method
		$this->load->library('parser');
		
		// parse the main body
		$template_data = array(
				'title'			=> t('Personalize the printer\'s network'),
				'ssid_title'	=> t('Write your network\'s name'),
				'pwd_title'		=> t('Write your password'),
				'error'			=> $error,
				'ok'			=> t('OK'),
				'show_password'	=> t('show_password'),
				'back'			=> t('back'),
		);
		
		$body_page = $this->parser->parse('connection/wifip2p', $template_data, TRUE);
				
		// parse all page
		$this->_generate_framePage($body_page);
		
		return;
	}
	
	public function confirmation() {
		$template_data = array();
		$body_page = NULL;
		$hostname = NULL;
		
		$this->load->library('parser');
		$this->load->helper('zimapi');
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/confirmation', $this->config->item('language'));
		
		if (ERROR_OK != ZimAPI_getHostname($hostname)) {
			$this->load->helper('printerlog');
			PrinterLog_logError('can not get hostname', __FILE__, __LINE__);
		}
		
		// parse the main body
		$template_data = array(
				'thank_you'	=> t('thank you'),
				'confirm'	=> t("confirmation text", array($hostname, $hostname, $hostname, $hostname)),
		);
		
		$body_page = $this->parser->parse('connection/confirmation', $template_data, TRUE);
				
		// parse all page
		$this->_generate_framePage($body_page);
		return;
	}
	
	public function android_oldversions()
	{
		$template_data = array();
		$body_page = NULL;
		
		$this->load->library('parser');
		$this->lang->load('connection/deprecated_android', $this->config->item('language'));
		
		$template_data = array(
				'inf_kit_kat' => t('inf_kit_kat'),
		);
		
		$body_page = $this->parser->parse('connection/android_oldversions', $template_data, TRUE);
		$this->_generate_framePage($body_page);
		
		return;
	}
	
	public function in_progress($ssid = NULL)
	{
		$template_data = array();
		$body_page = NULL;
		$hostname = NULL;
		
		$this->load->library('parser'); 
		$this->load->helper('zimapi');
		$this->lang->load('connection/master', $this->config->item('language'));
		$this->lang->load('connection/in_progress', $this->config->item('language'));
		
		if (ERROR_OK != ZimAPI_getHostname($hostname)) {
			$this->load->helper('printerlog');
			PrinterLog_logError('cannot get hostname', __FILE__, __LINE__);
		}
		
		$template_data = array(
				'hostname'			=> $hostname,
				'printersn'			=> ZimAPI_getSerial(),
				'config_printer'	=> t('config_printer'),
				'connect_error_msg'	=> t('connect_error_msg'),
				'popup'				=> t('popup', $ssid ? urldecode($ssid) : "XXX")
		);
		
		$body_page = $this->parser->parse('connection/in_progress', $template_data, TRUE);
		$this->_generate_framePage($body_page);
		
		return;
	}
}
//TODO test me