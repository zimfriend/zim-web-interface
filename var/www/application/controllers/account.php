<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Account extends MY_Controller
{
	//FIXME rewrite totally this controller and pass core function to helper
	private function _assign_wizard($email, $password)
	{
		$context = NULL;
		$printer_name = NULL;
		$cr = 0;
		$option = array();
		
		$this->load->helper('zimapi');
		if (ERROR_OK != ZimAPI_getHostname($printer_name)) {
			$printer_name = 'zim';
		}
		$data = array('email' => $email, 'password' => $password, 'printersn' => ZimAPI_getSerial(), 'printername' => $printer_name);
		
		$options = array('http' => array('header'  => "Content-type: application/x-www-form-urlencoded\r\n",
				'method'  => 'POST',
				'content' => http_build_query($data)));
		$context = stream_context_create($options);
		@file_get_contents('https://sso.zeepro.com/addprinter.ashx', false, $context);
		$result = substr($http_response_header[0], 9, 3);
		if ($result == 200) {
			ZimAPI_setPrinterSSOName($printer_name);
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function signin()
	{
		$this->load->library('parser');
		$data = array();
		$errors = "";
		$file = 'template/activation/index.php';
		$this->lang->load('activation/activation_form', $this->config->item('language'));
		
		if ($this->input->server('REQUEST_METHOD') == 'POST')
		{
			extract($_POST);
			$url = 'https://sso.zeepro.com/login.ashx';
			$data = array('email' => $email, 'password' => $password);
			$options = array('http' => array('header'  => "Content-type: application/x-www-form-urlencoded\r\n",
       										'method'  => 'POST',
       										'content' => http_build_query($data)));
			$context  = stream_context_create($options);
			try
			{
				@file_get_contents($url, false, $context);
			}
			catch (Exception $e)
			{
				//TODO:error handling
				$this->output->set_header('Location: /activation');
			}
			$result = substr($http_response_header[0], 9, 3);
			if ($result == 202)
			{
				// if in wizard mode, we fix the printer name
				$this->load->helper('corestatus');
				if (CoreStatus_checkInConnection())
				{
					$cr = $this->_assign_wizard($email, $password);
					if ($cr == TRUE)
					{
						//Save creds in session
						$custom_data = array('email' => $email, 'password' => $password);
						$this->session->set_userdata($custom_data);
						$this->output->set_header('Location: /activation/wizard_confirm');
					}
					else
						$this->output->set_header('Location: /activation/wizard_confirm/fail');
					return;
				}	
				$file = 'template/activation/activation_form';
				$custom_data = array('email' => $email, 'password' => $password);
				$this->session->set_userdata($custom_data);
				$data = array('email' =>$email, 'password' => $password, 'returnUrl' => isset($_GET['returnUrl']) ? ('?returnUrl='.$_GET['returnUrl']) : '');
			}
			else //error, for example bad password
				//$this->output->set_header('Location: /activation');
			{
				$this->lang->load('activation/activation', $this->config->item('language'));
				if ($result == 432)
					$errors = t('missing_param');
				if ($result == 433)
					$errors = t('bad_login');
				else if ($result == 434)
					$errors = t('bad_pass');
				else if ($result == 439)
					$errors = t('unknown_email');
				$data = array('sign_in' => t('sign_in'), 'title' => t('title'), 'show_password' => t('show_password'), 'password' => t('password'), 'returnUrl' => isset($_GET['returnUrl']) ? ('?returnUrl='.$_GET['returnUrl']) : '');
			}
		}
		else {
			// simply protect when not in post method
			$this->output->set_header('Location: /activation');
			return;
		}
		
		$body_page = $this->parser->parse($file, $data, TRUE);
		
		// parse all page
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>' . t('ZeePro Personal Printer 21 - Home') . '</title>',
				'contents'		=> $body_page,
				'back'			=> t('back'),
				'give_name'		=> t('give_name'),
				'errors'		=> $errors,
				'activate'		=> t('activate'),
				'format'		=> t('hostname_format'),
				'name_printer'	=> t('name_printer')
		);
		$this->parser->parse('basetemplate', $template_data);
		return;
	}
	
	public function signup_confirmation()
	{
		$file = 'template/account/signup_confirmation';
		$error = "";
		$this->load->library('parser');
		$this->load->helper('url');
		$this->lang->load('signup_confirmation', $this->config->item('language'));
		$this->lang->load('activation/activation_form', $this->config->item('language'));
		
		// try to keep flashdata, but it seems not working
		$this->session->keep_flashdata('email');
		$this->session->keep_flashdata('password');
		
		if ($this->input->server('REQUEST_METHOD') == 'POST')
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('code', 'Code', 'required');
			if ($this->form_validation->run())
			{
				extract($_POST);
				$url = 'https://sso.zeepro.com/confirmaccount.ashx';
				$data = array('email' => $this->session->userdata('email'), 'code' => $code);
			
				$options = array('http' => array('header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)));
				$context  = stream_context_create($options);
				@file_get_contents($url, false, $context);
				$result = substr($http_response_header[0], 9, 3);
				if ($result == 200) // perhaps we will have problem with 437
				{
// 					redirect('/');
					// if in wizard mode, we fix the printer name
					$this->load->helper('corestatus');
					if (CoreStatus_checkInConnection()) {
						$cr = $this->_assign_wizard($this->session->userdata('email'), $this->session->userdata('password'));
						if ($cr == TRUE) {
							$this->output->set_header('Location: /activation/wizard_confirm');
						}
						else {
							$this->output->set_header('Location: /activation/wizard_confirm/fail');
						}
						
						return;
					}
					
					$file = 'template/activation/activation_form';
					$data = array('email' => $this->session->userdata('email'), 'password' => $this->session->userdata('password'), 'returnUrl' => '');
				}
				$error = t('code_err');
			}
			else
				$error = t('no_code');
		}
		$data = array(
				'error'			=> $error,
				'back'			=> t('back'),
				'give_name'		=> t('give_name'),
				'activate'		=> t('activate'),
				'name_printer'	=> t('name_printer'),
				'code_title'	=> t('code_title')
		);
		$body_page = $this->parser->parse($file, $data, TRUE);
		
		// parse all page
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>Zim</title>',
				'contents'		=> $body_page
		);
		$this->parser->parse('basetemplate', $template_data);
		return;
	}
	
	public function first_signup()
	{
//		var_dump($hostname);
//		$ip = gethostbyname($hostname);
//		var_dump($ip);
//		var_dump($_SERVER);
//		die();
//		$this->output->set_header('Location: ' . $ip . '/account/signup');
		$location = 'http://' . $_SERVER["SERVER_ADDR"] . '/account/signup';
// 		var_dump($location);
// 		var_dump($_SERVER);
		$this->output->set_header('Location: ' . $location);
		return;
	}
	
	public function signup()
	{
		$body_page = NULL;
		$template_data = array();
		$data = array();
		$data['error'] = "";

		$this->load->library('parser');
		$this->lang->load('signup', $this->config->item('language'));
		$this->load->helper(array('url','corestatus'));
		
		// check network
		if (@file_get_contents("https://sso.zeepro.com/login.ashx") === FALSE) {
			if (CoreStatus_checkInConnection())
			{
				$this->output->set_header('Location: /activation/wizard_confirm/fail');
			}
			else {
				$body_page = $this->parser->parse('template/activation/network_error', array(), TRUE);
				$this->lang->load('activation/network_error', $this->config->item('language'));
				$template_data = array(
						'lang'			=> $this->config->item('language_abbr'),
						'headers'		=> '<title>' . t('ZeePro Personal Printer 21 - Home') . '</title>',
						'contents'		=> $body_page,
						'back'			=> t('back'),
						'network_err_msg'=> t('network_err_msg')
				);
				$this->parser->parse('basetemplate', $template_data);
			}
			
			return;
		}
		
		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			$this->load->library('form_validation');
			$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
			$this->form_validation->set_rules('password', 'Password', 'required|matches[confirm]');
			$this->form_validation->set_rules('confirm', 'Password confirmation', 'required');
				
			if ($this->form_validation->run()) {
				extract($_POST);
				$data = array(
						'email'		=> $email,
						'password'	=> $password,
						'optin'		=> ((int)$this->input->post('optin') == 1) ? 'on' : 'off',
				);
				
				$options = array(
						'http' => array(
								'header'	=> "Content-type: application/x-www-form-urlencoded\r\n",
								'method'	=> 'POST',
								'content'	=> http_build_query($data)
						)
				);
				$context = stream_context_create($options);
				@file_get_contents('https://sso.zeepro.com/createaccount.ashx', false, $context);
				$result = substr($http_response_header[0], 9, 3);
				if ($result == 200)
				{
					$this->session->set_userdata($data);
					redirect('/account/signup_confirmation');
				}
				else if ($result == 437)
					$data['error'] = t('account_exists') . '<br />';
				else if ($result == 432)
					$data['error'] = t('missing_parameter') . '<br />';
			}
			else if ($_POST['password'] != $_POST['confirm'])
				$data['error'] = t("wrong_pass") . '<br />';
			else
				$data['error'] = t('missing_parameter') . '<br />';;
		}
		
		// add skip button if in wizard
		if (CoreStatus_checkInConnection())
		{
			$data['back_or_already'] = t('already');
// 			$data['has_skip'] = "block"; 			
			$data['btn_url'] ='/account/signin';
		}
		else
		{
			$data['back_or_already'] = t('back');
// 			$data['has_skip'] = "none";
			$data['btn_url'] ='/activation/';
		}
		$data['has_skip'] = "none";
		$data['confcode_hint'] = t('confcode_hint');
		$data['signup_title'] = t('signup_title');
		$data['signup_text'] = t('signup_text');
		$data['skip_title'] = t('button_skip');
		$data['confirm_skip_text'] = t('confirm_skip_text');
		$data['still_skip'] = t('still_skip');
		$data['back'] = t('back');
		$data['show_password'] = t('show_password');
		$data['privacy_policy_link'] = t('privacy_policy_link');
		$data['optin_title'] = t('optin_title');
		$body_page = $this->parser->parse('template/account/signup', $data, TRUE);
		
		// parse all page
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>' . t('ZeePro Personal Printer 21 - Home') . '</title>',
				'contents'		=> $body_page,
		);
		$this->parser->parse('basetemplate', $template_data);
		
		return;
	}
}