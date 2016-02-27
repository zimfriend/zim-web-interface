<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Test_version extends CI_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'printerstate',
				'url',
				'json'
		) );
	}
	
	private function _bindUpgradeURL($profile_link) {
		$profile_name = NULL;
		
		$profile_link = trim($profile_link);
		switch ($profile_link) {
			case 'http://repo.zeepro.com/upgrade/prod-b.profile':
			case 'http://repo.zeepro.com/upgrade/profile/prod-b.profile':
				$profile_name = 'prod-b';
				break;
				
			case 'http://repo.zeepro.com/upgrade/prod-a.profile':
			case 'http://repo.zeepro.com/upgrade/profile/prod-a.profile':
				$profile_name = 'prod-a';
				break;
				
			case 'http://repo.zeepro.com/upgrade/preprod.profile':
			case 'http://repo.zeepro.com/upgrade/profile/preprod.profile':
				$profile_name = 'preprod';
				break;
				
			case 'http://repo.zeepro.com/upgrade/profile/beta.profile':
			case 'http://repo.zeepro.com/upgrade/profile/preprod_test.profile':
				$profile_name = 'beta';
				break;
				
			case 'http://repo.zeepro.com/upgrade/profile/dev.profile':
			case 'http://repo.zeepro.com/upgrade/profile/preprod_dev.profile':
			case 'http://repo.zeepro.com/upgrade/profile/preprod_test_dev.profile':
				$profile_name = 'dev';
				break;
				
			default:
				return 'unknown: ' . $profile_link;
				break;
		}
		
		return $profile_name;
	}
	
	public function ssh() {
		$output = array();
		$ret_val = 0;
		if (!file_exists('/tmp/remoteSSH')) {
			exec('/etc/init.d/remote_ssh start');
		}
		exec('/etc/init.d/remote_ssh status', $output, $ret_val);
		
		var_dump(array(
				'ret_code'	=> $ret_val,
				'output'	=> $output,
		));
		
		return;
	}
	
	public function branch() {
		$url = $this->input->post('url');
		if ($url) {
			if (FALSE !== @file_get_contents($url) || $this->input->post('force')) {
				$cr = 0;
				
				$this->load->helper('errorcode');
				if ($this->input->post('permanent')) {
					$ret_val = 0;
					$output = array();
					
					exec('sudo ' . $this->config->item('siteutil') . ' upgrade_url "' . $url . '"', $output, $ret_val);
					if ($ret_val != ERROR_NORMAL_RC_OK) {
						$cr = ERROR_WRONG_PRM;
					}
					else {
						$cr = ERROR_OK;
					}
				}
				else {
					$this->load->helper('zimapi');
					$cr = ZimAPI_setUpgradeMode('change', $url);
				}
				
				$this->load->library('parser');
				$this->output->set_status_header($cr, MyERRMSG($cr));
				$this->parser->parse('plaintxt', array('display' => MyERRMSG($cr)));
			}
		}
		else {
			//TODO change load view into parser?
			$this->load->helper('form');
			$this->load->view('template/branch_switch');
		}
		
		return;
	}
	
	public function test_port() {
		$cr = 500;
		$port = (int) $this->input->get('v');
		
		if ($port <= 0) {
			$cr = 403;
		}
		else if (FALSE === @file_get_contents("http://portquiz.net:" . $port)) {
			$cr = 404;
		}
		else {
			$cr = 200;
		}
		
		$this->load->library('parser');
		$this->output->set_status_header($cr);
		$this->parser->parse('plaintxt', array('display' => 'test'));
		
		return;
	}
	
	public function index() {
		$template_data = array();
		$body_page = NULL;
		$temp_info = array();
		$array_info = array();
		$sso_name = NULL;
		$upgrade_mode = NULL;
		$profile_link = NULL;
		
		$this->load->helper(array('printerstate', 'zimapi'));
		$this->load->library('parser');
		$this->lang->load('printerstate/printerinfo', $this->config->item('language'));
		$this->lang->load('test_version', $this->config->item('language'));
		ZimAPI_getPrinterSSOName($sso_name);
		ZimAPI_getUpgradeMode($upgrade_mode, $profile_link);
		$profile_link = $this->_bindUpgradeURL($profile_link);
		
		$temp_info = PrinterState_getInfoAsArray();
		// config variable is set in MY_controller, so we need to correct number of extruder by ourselves
		$temp_info[PRINTERSTATE_TITLE_NB_EXTRUD] = PrinterState_getNbExtruder();
		$array_info = array(
				array(
						'title'	=> t('profile_title'),
						'value'	=> $upgrade_mode . ' [ ' . $profile_link . ' ]',
				),
				array(
						'title'	=> t('version_title'),
						'value'	=> $temp_info[PRINTERSTATE_TITLE_VERSION],
				),
				array(
						'title'	=> t('next_version_title'),
						'value'	=> $temp_info[PRINTERSTATE_TITLE_VERSION_N],
				),
				array(
						'title'	=> t('type_title'),
						'value'	=> $temp_info[PRINTERSTATE_TITLE_TYPE],
				),
				array(
						'title'	=> t('serial_title'),
						'value'	=> $temp_info[PRINTERSTATE_TITLE_SERIAL],
				),
				array(
						'title'	=> t('extruder_title'),
						'value'	=> $temp_info[PRINTERSTATE_TITLE_NB_EXTRUD],
				),
				array(
						'title'	=> t('marlin_title'),
						'value'	=> $temp_info[PRINTERSTATE_TITLE_VER_MARLIN],
				),
				array(
						'title' => t('ip_address'),
						'value'	=> $temp_info[ZIMAPI_TITLE_IP],
				)
		);
		
		// parse the main body
		$template_data = array(
				'array_info'		=> $array_info,
				'port_test_title'	=> t('port_test_title'),
				'port_test_ok'		=> t('port_test_ok'),
				'port_test_ko'		=> t('port_test_ko'),
				'port_test_r80'		=> t('port_test_printer', array(80)),
				'port_test_r443'	=> t('port_test_printer', array(443)),
				'port_test_r4443'	=> t('port_test_printer', array(4443)),
				'port_test_l80'		=> t('port_test_client', array(80)),
				'port_test_l443'	=> t('port_test_client', array(443)),
		);
		
		$body_page = $this->parser->parse('test_version', $template_data, TRUE);
		
		// parse all page
		$template_data = array(
				'lang'			=> $this->config->item('language_abbr'),
				'headers'		=> '<title>' . t('printerstate_printerinfo_pagetitle') . '</title>',
				'contents'		=> $body_page,
		);
		
		$this->parser->parse('basetemplate', $template_data);
		
		return;
	}
}