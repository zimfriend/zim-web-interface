<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Activation extends MY_Controller
{
	//FIXME rewrite totally this controller and pass core function to helper

	public function index()
	{
		$network_ok = false;
		$this->load->library('parser');

		if (!(@file_get_contents("https://sso.zeepro.com/login.ashx") === FALSE))
		{
			$network_ok = true;
		}
		if ($network_ok)
		{
			$body_page = $this->parser->parse('template/activation/index', array(), TRUE);
			$this->lang->load('activation/activation', $this->config->item('language'));
			$template_data = array(
					'lang'					=> $this->config->item('language_abbr'),
					'headers'				=> '<title>' . "zim - Activation" . '</title>',
					'contents'				=> $body_page,
					'title'					=> t('title'),
					'errors'				=> "",
					'password'				=> t('password'),
					'sign_in'				=> t('sign_in'),
					'sign_up'				=> t('sign_up'),
					'back'					=> t('back'),
					'privacy_policy_link'	=> t('privacy_policy_link'),
					'create_account'		=> t('create_account'),
					'show_password'			=> t('show_password'),
					'returnUrl'				=> isset($_GET['returnUrl']) ? ("?returnUrl=".$_GET['returnUrl']) : ""
			);
		}
		else
		{
			$body_page = $this->parser->parse('template/activation/network_error', array(), TRUE);
			$this->lang->load('activation/network_error', $this->config->item('language'));
			$template_data = array(
					'lang'			=> $this->config->item('language_abbr'),
					'headers'		=> '<title>' . t('ZeePro Personal Printer 21 - Home') . '</title>',
					'contents'		=> $body_page,
					'back'			=> t('back'),
					'try_again'		=> t('try_again'),
					'try_again_hint'=> t('try_again_hint'),
					'network_err_msg'=> t('network_err_msg')
			);
		}
		$this->parser->parse('basetemplate', $template_data);
	}
	
	public function activation_form()
	{
		$this->load->library('parser');
		$this->lang->load('activation/activation_confirm', $this->config->item('language'));
		$file = 'template/activation/activation_form';
		if ($this->input->server('REQUEST_METHOD') == 'POST')
		{
			$this->load->library('form_validation');
			$this->form_validation->set_rules('printer_name', 'Printer name', 'required');
			if ($this->form_validation->run())
			{
				$this->load->helper('zimapi');
				extract($_POST);
				$url = 'https://sso.zeepro.com/addprinter.ashx';
				$data = array('email' => $this->session->userdata('email'), 'password' => $this->session->userdata('password'), 'printersn' => ZimAPI_getSerial(), 'printername' => $printer_name);
				$options = array('http' => array('header'  => "Content-type: application/x-www-form-urlencoded\r\n",
						'method'  => 'POST',
						'content' => http_build_query($data)));
				$context  = stream_context_create($options);
				@file_get_contents($url, false, $context);
				$result = substr($http_response_header[0], 9, 3);
				if ($result == 200)
				{
					ZimAPI_setPrinterSSOName($printer_name);
					$file = 'template/activation/activation_confirm';
					/*if (isset($_GET['returnUrl']))
						$this->output->set_header("Location:/" . $_GET['returnUrl']);
					else
						$this->output->set_header("Location:/");*/
				}
				else
					$this->output->set_header("Location:/activation/activation_form");
			}
		}
		$body_page = $this->parser->parse($file, array(), TRUE);
		// parse all page
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>Zim - Activation</title>',
				'contents'		=> $body_page,
				'name_printer'	=> t('name_printer'),
				'back'			=> t('back'),
				'congrats'		=> t('congrats'),
				'confirmation_message'	=> t('hint'),
				'returnUrl'		=> isset($_GET['returnUrl']) ? "/" . $_GET['returnUrl'] : "/"
		);
		$this->parser->parse('basetemplate', $template_data);
	}
	
	public function wizard_confirm($mode = NULL) {
		$body_page = NULL;
		$hint_txt = NULL;
		$hint_title = NULL;
		$hostname = NULL;
		$template_data = array();
		
		$this->load->library('parser');
		$this->load->helper(array('corestatus', 'zimapi'));
		$this->lang->load('activation/activation_wizard', $this->config->item('language'));
		
		if (!CoreStatus_finishActivation())
		{
			$this->load->helper('printerlog');
			PrinterLog_LogError('can not finish need activation mode', __FILE__, __LINE__);
		}
		if (ERROR_OK != ZimAPI_getHostname($hostname)) {
			$this->load->helper('printerlog');
			PrinterLog_logError('can not get hostname', __FILE__, __LINE__);
		}
		
		switch ($mode)
		{
			case NULL:
				$hint_txt = t('wizard_success_hint', array($hostname, $hostname, $hostname));
				$hint_title = t('hint_title_success');

				// send email
				$email = $this->session->userdata('email');
				@file_get_contents('https://sso.zeepro.com/sendtipsmail.ashx?email='.$email);
				break;
				
			case 'fail':
				$hint_txt = t('wizard_fail_hint', array($hostname, $hostname));
				$hint_title = t('hint_title');
				break;

			case 'skip':
				$this->output->set_header("Location: /menu_home");
				return;
			default:
				if ($mode != 'skip') {
					$this->load->helper('printerlog');
					PrinterLog_LogError('unknown mode for wizard, mode: ' . $mode, __FILE__, __LINE__);
				}
				$hint_txt = t('wizard_skip_hint', array($hostname, $hostname));
				break;
		}
		
		$template_data = array(
				'hint_title'	=> $hint_title,
				'hint_txt'		=> $hint_txt,
				'button_ok'		=> t('button_ok'),
		);
		$body_page = $this->parser->parse('template/activation/activation_wizard', $template_data, TRUE);
		
		// parse all page
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>' . t('ZeePro Personal Printer 21 - Activation') . '</title>',
				'contents'		=> $body_page,
		);
		$this->parser->parse('basetemplate', $template_data);
		
		return;
	}
}