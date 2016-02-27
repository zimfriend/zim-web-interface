<?php
if (! defined ( 'BASEPATH' ))
	exit ( 'No direct script access allowed' );

class Printerstate extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'printerstate',
				'url',
				'json'
		) );
	}
	
	private function _display_changecartridge_base($template_name, $template_data) {
		$this->load->library('parser');
		
		$this->parser->parse($template_name, $template_data);
		
		return;
	}
	
	private function _display_changecartridge_wait_unload_filament($abb_cartridge, $id_model, $wait_unload) {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_UNLOAD_F,
				'unload_button'	=> t('Unload the filament'),
				'prime_button'	=> t('prime_button'),
				'abb_cartridge'	=> $abb_cartridge,
				'id_model'		=> $id_model,
// 				'enable_unload'	=> ($wait_unload == TRUE) ? 'false' : 'true',
		);
		$template_name = 'printerstate/changecartridge_ajax/wait_unload_filament';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _display_changecartridge_in_unload_filament($abb_cartridge) {
		$in_heating = TRUE;
		$value_temper = 0; //TODO find a proper way to deal with error
		// check if we in heating process or not
		if (!file_exists(PRINTERSTATE_FILE_UNLOAD_HEAT)) {
			$in_heating = FALSE;
		}
		else {
			$ret_val = PrinterState_getTemperature($value_temper, 'e', $abb_cartridge);
			if ($ret_val != ERROR_OK) {
				$value_temper = 0;
			}
		}
		
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_REMOVE_C,
				'unload_info'	=> t('Wait for unloading...'),
				'hint_temper'	=> t('hint_unload_temper'),
				'value_temper'	=> $value_temper,
				'in_heating'	=> $in_heating ? 'true' : 'false',
				'cancel_button'	=> t('cancel_button'),
		);
		$template_name = 'printerstate/changecartridge_ajax/in_unload_filament';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		return;
	}
	
	private function _display_changecartridge_remove_cartridge($low_hint = FALSE) {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> ($low_hint) ? PRINTERSTATE_CHANGECART_REINST_C : PRINTERSTATE_CHANGECART_INSERT_C,
				'low_hint'		=> ($low_hint) ? t('Not enough filament') : '',
				'action_hint'	=> t('Remove the cartridge above'),
		);
		$template_name = 'printerstate/changecartridge_ajax/remove_cartridge';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		return;
	}
	
	private function _display_changecartridge_insert_cartridge() {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_LOAD_F,
				'action_hint'	=> t('Insert the cartridge above'),
		);
		$template_name = 'printerstate/changecartridge_ajax/insert_cartridge';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		return;
	}
	
	private function _display_changecartridge_write_cartridge($abb_cartridge, $next_phase = PRINTERSTATE_CHANGECART_WAIT_F_C, $need_filament = 0) {
		$cr = 0;
		$cartridge_data = array();
		$length_value = 0;
		$temper_value = 0;
		$material_value = 0;
		$option_selected = 'selected="selected"';
		
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$cr = PrinterState_getCartridgeAsArray($cartridge_data, $abb_cartridge);
		if ($cr != ERROR_OK) {
			$length_value = 10;
			$temper_value = 200;
		}
		else {
			$length_value = floor(($cartridge_data[PRINTERSTATE_TITLE_INITIAL] - $cartridge_data[PRINTERSTATE_TITLE_USED]) / 1000);
// 			if ($length_value < 10) {
// 				$length_value = 10;
// 			}
			$temper_value = $cartridge_data[PRINTERSTATE_TITLE_EXT_TEMPER];
// 			if ($temper_value < 160) {
// 				$temper_value = 160;
// 			}
		}
		
		$template_data = array (
				'next_phase'		=> $next_phase,
				'color_label'		=> t('color_label'),
				'temper_label'		=> t('temper_label'),
				'length_label'		=> t('length_label'),
				'write_button'		=> t('write_button'),
				'material_label'	=> t('material_label'),
				'material_array'	=> array(
						array('name' => 'PLA', 'value' => PRINTERSTATE_VALUE_MATERIAL_PLA, 'on' => NULL),
						array('name' => 'ABS', 'value' => PRINTERSTATE_VALUE_MATERIAL_ABS, 'on' => NULL),
						array('name' => 'PVA', 'value' => PRINTERSTATE_VALUE_MATERIAL_PVA, 'on' => NULL),
				),
				'length_min'		=> ceil($need_filament / 1000) + 2,
				'length_value'		=> $length_value,
				'temper_value'		=> $temper_value,
				'rfid_color'		=> $cartridge_data[PRINTERSTATE_TITLE_COLOR],
		);
		
		switch ($cartridge_data[PRINTERSTATE_TITLE_MATERIAL]) {
			case PRINTERSTATE_DESP_MATERIAL_PLA:
				$material_value = PRINTERSTATE_VALUE_MATERIAL_PLA;
				break;
				
			case PRINTERSTATE_DESP_MATERIAL_ABS:
				$material_value = PRINTERSTATE_VALUE_MATERIAL_ABS;
				break;
				
			case PRINTERSTATE_DESP_MATERIAL_PVA:
				$material_value = PRINTERSTATE_VALUE_MATERIAL_PVA;
				break;
				
			default:
				$material_value = PRINTERSTATE_VALUE_MATERIAL_PLA;
				break;
		}
		$template_data['material_array'][$material_value]['on'] = $option_selected;
		
		$this->_display_changecartridge_base('printerstate/changecartridge_ajax/cartridge', $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _display_changecartridge_wait_load_filament($change_able = FALSE, $id_model = NULL, $abb_cartridge = NULL) {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		if ($change_able == TRUE) {
			$template_data = array (
					'next_phase'	=> PRINTERSTATE_CHANGECART_WAIT_F_C,
					'load_button'	=> t('Load the filament'),
					'change_button'	=> t('Change the cartridge'),
// 					'wait_info'		=> t('Waiting for getting information...'),
//					'abb_cartridge'	=> $abb_cartridge,
//					'id_model'		=> $id_model,
			);
			$template_name = 'printerstate/changecartridge_ajax/wait_load_change_filament';
		}
		else {
			$template_data = array (
					'next_phase'	=> PRINTERSTATE_CHANGECART_WAIT_F,
					'load_button'	=> t('Load the filament'),
					'hint'			=> t('Your cartridge is loaded'),
			);
			$template_name = 'printerstate/changecartridge_ajax/wait_load_filament';
			
		}
		$this->_display_changecartridge_base($template_name, $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _display_changecartridge_cartridge_detail($abb_cartridge, $id_model) {
		$cr = 0;
		$template_data = array();
		$cartridge_data = array();
		
		$this->load->helper(array('printlist', 'printerstate'));
		$this->load->library('parser');
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		
		$color_model = PRINTERSTATE_VALUE_DEFAULT_COLOR;
		$color_cart = PRINTERSTATE_VALUE_DEFAULT_COLOR;
		$model_title = t('Color of model');
		$cartridge_title = t('Color of cartridge');
		
		if ($id_model) {
			$model_data = array();
			
			$cr = ModelList__getDetailAsArray($id_model, $model_data, TRUE);
			if (($cr != ERROR_OK) || is_null($model_data)) {
				$this->load->helper('printerlog');
				PrinterLog_logMessage('can not get model info', __FILE__, __LINE__);
				$id_model = NULL;
				$model_title = t('No model');
			}
			else {
				$color_model = ($abb_cartridge == 'r')
				? $model_data[PRINTLIST_TITLE_COLOR_F1] : $model_data[PRINTLIST_TITLE_COLOR_F2];
			}
		}
		else {
			$model_title = t('No model');
		}
		
		$cr = PrinterState_getCartridgeAsArray($cartridge_data, $abb_cartridge);
		if (($cr != ERROR_OK) && is_null($cartridge_data)) {
			$this->load->helper('printerlog');
			PrinterLog_logError('can not get cartridge info', __FILE__, __LINE__);
			$cartridge_title = t('Error');
		}
		else {
			$color_cart = $cartridge_data[PRINTERSTATE_TITLE_COLOR];
		}
		
		$template_data = array (
				'cart_title'	=> $cartridge_title,
				'model_title'	=> $model_title,
				'model_color'	=> $color_model,
				'cart_color'	=> $color_cart,
		);
		
		$this->parser->parse('printerstate/changecartridge_ajax/detail_cartridge', $template_data);
		
		return;
	}
	
	private function _display_changecartridge_in_load_filament() {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_NEED_P,
				'load_info'		=> t('Waiting for loading...'),
				'cancel_button'	=> t('cancel_button'),
				'cancel_info'	=> t('cancel_info'),
		);
		$template_name = 'printerstate/changecartridge_ajax/in_load_filament';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		return;
	}
	
	private function _display_changecartridge_need_prime($abb_cartridge, $id_model) {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$yes_url = '/printdetail/printprime?v=' . $abb_cartridge;
		$no_url = '';
		
// 		$this->load->helper('printlist');
// 		if ($abb_cartridge == 'r') {
// 			$yes_url .= ModelList_codeModelHash(PRINTLIST_MODEL_PRIME_R);
// 		}
// 		else {
// 			$yes_url .= ModelList_codeModelHash(PRINTLIST_MODEL_PRIME_L);
// 		}
		if ($id_model) {
			$yes_url .= '&cb=' . $id_model;
			if ($id_model == 'slice') {
				$no_url = '/sliceupload/slice?callback';
			}
			else {
				$no_url = '/printmodel/detail?id=' . $id_model;
			}
		}
		else {
			$no_url = '/manage';
		}
		
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_FINISH,
				'question'		=> t('Prime?'),
				'yes_button'	=> t('Yes'),
				'no_button'		=> t('No'),
				'yes_url'		=> $yes_url,
				'no_url'		=> $no_url,
		);
		$template_name = 'printerstate/changecartridge_ajax/need_prime';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _display_changecartridge_error_status($status_current) {
		$template_data = NULL;
		$message = NULL;
		
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		switch ($status_current) {
			case CORESTATUS_VALUE_UNLOAD_FILA_R:
			case CORESTATUS_VALUE_LOAD_FILA_R:
				$message = t('PLEASE GO TO CHANGE RIGHT CARTRIDGE');
				break;

			case CORESTATUS_VALUE_UNLOAD_FILA_L:
			case CORESTATUS_VALUE_LOAD_FILA_L:
				$message = t('PLEASE GO TO CHANGE LEFT CARTRIDGE');
				break;
				
			default:
				$message = t('IN BUSY: ') . $status_current;
				break;
		}
		$template_data = array (
				'message'	=> $message,
		);
		$template_name = 'printerstate/changecartridge_ajax/error_status';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _display_changecartridge_error_loading() {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_UNLOAD_F,
				'unload_button'	=> t('Unload the filament'),
		);
		$template_name = 'printerstate/changecartridge_ajax/error_loading';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _display_changecartridge_error_unloading() {
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		$template_data = array (
				'next_phase'	=> PRINTERSTATE_CHANGECART_UNLOAD_F,
				'unload_error'	=> t('unload_error'),
				'home_button'	=> t('home'),
		);
		$template_name = 'printerstate/changecartridge_ajax/error_unloading';
		$this->_display_changecartridge_base($template_name, $template_data);
		
		$this->output->set_status_header(202); // disable checking
		
		return;
	}
	
	private function _deal_with_unloading_wait_time($abb_cartridge, $array_status) {
		$offset_check = PRINTERSTATE_VALUE_OFFSET_TO_CHECK_UNLOAD;
		
		if (array_key_exists(CORESTATUS_TITLE_FILA_MAT, $array_status)
				&& $array_status[CORESTATUS_TITLE_FILA_MAT] == PRINTERSTATE_DESP_MATERIAL_PVA) {
			$offset_check = PRINTERSTATE_VALUE_OFFSET_TO_CHECK_UNLOAD_PVA;
		}
		
		if (file_exists(PRINTERSTATE_FILE_UNLOAD_HEAT)) {
			$time_start = @file_get_contents(PRINTERSTATE_FILE_UNLOAD_HEAT);
// 			PrinterLog_logDebug('start time: ' . $time_start, __FILE__, __LINE__);
			if (is_null($time_start)) {
				PrinterLog_logError('check unload heat status file error', __FILE__, __LINE__);
				return FALSE;
			}
			else if (time() - $time_start <= PRINTERSTATE_VALUE_TIMEOUT_UNLOAD_HEAT) {
				// block the status if in timeout, and refresh the start time for the following state
				CoreStatus_setInUnloading($abb_cartridge);
// 				PrinterLog_logDebug('rewrite status', __FILE__, __LINE__);
				return FALSE;
			}
			else {
				// always in heating when we passed timeout, we unlock the mobile site
				PrinterLog_logError('always in heating process when we unload filament', __FILE__, __LINE__);
				@unlink(PRINTERSTATE_FILE_UNLOAD_HEAT);
				$ret_val = CoreStatus_setInIdle();
				if ($ret_val == TRUE) {
					$status_current = CORESTATUS_VALUE_IDLE;
					return TRUE;
				}
				$CI = &get_instance();
				$CI->load->helper('printerlog');
				PrinterLog_logError('can not set status into idle', __FILE__, __LINE__);
				return FALSE;
			}
		} else
		
		// wait the time for arduino before checking filament when unloading filament
		// we return TRUE only when finishing action or passing max wait time (Arduino is avaliable for command)
		if (CoreStatus_checkInWaitTime($offset_check)) {
			return FALSE;
		}
		
		return TRUE;
	}
	
	public function index() {
		$template_data = array();
		$ret_val = 0;
		$status_upgrade = FALSE;
		$status_tromboning = FALSE;
		$status_statistic = FALSE;
		$status_ssh = FALSE;
		$option_selected = 'selected="selected"';
		
		// get function status
		$this->load->helper(array('zimapi', 'printerlog'));
		$ret_val = ZimAPI_getUpgradeMode($status_upgrade);
		if ($ret_val != TRUE) {
			$status_upgrade = 'off';
		}
		$ret_val = ZimAPI_getSSH($status_ssh);
		if ($ret_val != TRUE) {
			$status_ssh = FALSE;
		}
		$status_tromboning = ZimAPI_getTromboning();
		$status_statistic = ZimAPI_getStatistic();
		
// 		$this->changecartridge();
		$this->load->library('parser');
		$this->lang->load('printerstate/index', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'reset_network'			=> t('reset_network'),
				'printer_info'			=> t('printer_info'),
				'back'					=> t('back'),
				'set_hostname'			=> t('set_hostname'),
				'set_preset'			=> t('set_preset'),
				'upgrade_on'			=> ($status_upgrade != 'off') ? $option_selected : NULL,
				'tromboning_on'			=> ($status_tromboning == TRUE) ? $option_selected : NULL,
				'statistic_on'			=> ($status_statistic == TRUE) ? $option_selected : NULL,
				'remote_control_on'		=> ($status_ssh == TRUE) ? $option_selected : NULL,
				'upgrade'				=> t('upgrade'),
				'tromboning'			=> t('tromboning'),
				'remote_control'		=> t('remote_control'),
// 				'statistic'				=> t('statistic'),
				'function_on'			=> t('function_on'),
				'function_off'			=> t('function_off'),
				'nozzles_adjustments'	=> t('nozzles_adjustments'),
				'support'				=> t('support'),
				'bicolor'				=> ($this->config->item('nb_extruder') >= 2) ? 'true' : 'false', 
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstate_index_pagetitle'),
				$this->parser->parse('printerstate/index', $template_data, TRUE));
		
		return;
	}
	
	public function printerinfo() {
		$template_data = array();
		$temp_info = array();
		$array_info = NULL; //array()
// 		$sso_name = NULL;
		$ssh_mode = NULL;
		$ssh_link = NULL;
// 		$ip_addr6 = NULL;
		
		$this->load->helper(array('printerstate', 'zimapi'));
		$this->load->library('parser');
		$this->lang->load('printerstate/printerinfo', $this->config->item('language'));
		$this->lang->load('printerstate/index', $this->config->item('language'));
// 		ZimAPI_getPrinterSSOName($sso_name);
		
		$temp_info = PrinterState_getInfoAsArray();
// 		if (is_array($temp_info[ZIMAPI_TITLE_IPV6])) {
// 			foreach ($temp_info[ZIMAPI_TITLE_IPV6] as $ip_scope => $ip_addr) {
// 				if (!is_null($ip_addr6)) {
// 					$ip_addr6 .= ', ';
// 				}
// 				$ip_addr6 .= $ip_addr . ' (' . t($ip_scope) . ')';
// 			}
// 		}
// 		else {
// 			$ip_addr6 = $temp_info[ZIMAPI_TITLE_IPV6];
// 		}
		if (ZimAPI_getSSH($ssh_mode, $ssh_link) && $ssh_mode == TRUE) {
			$ssh_mode = t('function_on') . ' (' . $ssh_link . ')';
		}
		else {
			$ssh_mode = t('function_off');
		}
// 		$hostname = "no hostname";
// 		ZimAPI_getHostname($hostname);
		$array_info = array(
				array(
						'title'	=> t('version_title'),
						'value'	=> $this->parser->parse('printerstate/printerinfo_grid_b',
								array(
										'id'		=> 'button_release',
										'value'		=> $temp_info[PRINTERSTATE_TITLE_VERSION],
										'link'		=> '/printerstate/upgradenote',
										'button'	=> '{button_release}',
								), TRUE),
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
				),
// 				array(
// 						'title' => t('ip_address_v6'),
// 						'value'	=> $ip_addr6,
// 				),
				array(
						'title'	=> t('remote_control'),
						'value'	=> $ssh_mode,
				),
				array(
						'title' => t('sso_name'),
						'value'	=> $this->parser->parse('printerstate/printerinfo_grid_b',
								array(
										'id'		=> 'button_sso',
										'value'		=> array_key_exists(PRINTERSTATE_TITLE_SSO_NAME, $temp_info)
												? $temp_info[PRINTERSTATE_TITLE_SSO_NAME] : "",
										'link'		=> '/activation/?returnUrl=printerstate/printerinfo',
										'button'	=> '{button_sso}',
								), TRUE),
				),
// 				array(
// 						'title'	=> t('hostname'),
// 						'value'	=> "<div style='text-align:center'>" . $temp_info[PRINTERSTATE_TITLE_HOSTNAME]
// 								. "<a data-ajax=false data-role='button' href='/printerstate/sethostname'>{button_fqdn}</a></div>"
// 				),
		);
		
		// parse the main body
		$template_data = array(
				'array_info'		=> $array_info,
				'back'				=> t('back'),
				'home'				=> t('Home'),
				'button_sso'		=> (array_key_exists(PRINTERSTATE_TITLE_SSO_NAME, $temp_info)
						? t('button_rename_sso') : t('button_active_sso')),
				'button_fqdn'		=> t('button_rename_sso'),
				'button_release'	=> t('button_release'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstate_printerinfo_pagetitle'),
				$this->parser->parse('printerstate/printerinfo', $template_data, TRUE));
		
		return;
	}
	
	public function upgradenote() {
		$template_data = array();
		$note_html = NULL;
		$array_upgrade = array();
		$normal_mode = ($this->input->get('reboot') === FALSE) ? TRUE : FALSE;
		$ui_mode = ($this->input->get('ui') === FALSE) ? FALSE : TRUE;
		
		$this->load->helper(array('zimapi', 'printerlog'));
		$this->load->library('parser');
		$this->lang->load('printerstate/upgradenote', $this->config->item('language'));
		
// 		ZimAPI_getUpgradeNote($note_html);
		if (ZimAPI_getUpgradeNoteArray($array_upgrade, $normal_mode)) {
			// convert API array to codeigniter display array
// 			$api_array = array(
// 					'upgrade vesrion number'	=> array(
// 							'part title'			=> array(
// 									'note line in this part',
// 									'another note line',
// 							),
// 							'another part title'	=> array(
// 									'note line in another part',
// 							),
// 					),
// 					// possibility to add another version info here
// 			);
// 			$template_data = array(
// 				'version'	=> 'upgrade version number',
// 				'part_ele'	=> array(
// 						array(
// 								'part_title'	=> 'upgrade part title here',
// 								'note_ele'		=> array(
// 										array('note_line' => 'note line in this part'),
// 										array('note_line' => 'another note line in this part'),
// 								),
// 						),
// 						// another part here
// 				),
// 			);
			//TODO move html code into view if marketing group prefer ui mode in collapsibleset
			if ($ui_mode == TRUE) {
				$note_html .= '<div id="upgradenote_collapsibleset" data-role="collapsibleset">';
			}
			foreach ($array_upgrade as $version => $parts) {
				$tmp_array = array();
				
				foreach ($parts as $title => $notes) {
					$tmp_note = array();
					
					foreach ($notes as $note) {
						$tmp_note[] = array('note_line' => $note);
					}
					
					$tmp_array[] = array(
							'part_title'	=> $title,
							'note_ele' 		=> $tmp_note,
					);
				}
				
				$template_data = array(
						'version'	=> $version,
						'part_ele'	=> $tmp_array,
				);
				
				if ($ui_mode == TRUE) {
					$note_html .= $this->parser->parse('printerstate/upgradenote_xml', $template_data, TRUE);
				}
				else {
					$note_html .= $this->parser->parse('printerstate/upgradenote_txt', $template_data, TRUE);
				}
			}
			if ($ui_mode == TRUE) {
				$note_html .= '</div>';
			}
		}
		else {
			// add internet fetch rollback if local file not found
			if (!ZimAPI_getUpgradeNote($note_html)) {
				$note_html = 'N/A';
			}
		}
		
		// stats info
		PrinterLog_statsWebClick(PRINTERLOG_STATS_LABEL_UPGRADE);
		
		$template_data = array(
				'back'				=> t('back'),
				'note_title'		=> ($normal_mode == TRUE) ? t('releasenote_title') : t('whatsnew_title'),
				'note_hint'			=> t('note_hint'),
				'note_body'			=> $note_html,
				'reboot_button'		=> t('releasenote_reboot'),
				'ui_button'			=> t('ui_button'),
				'reboot_display'	=> ($normal_mode == TRUE) ? 'false' : 'true',
				'ui_display'		=> ($ui_mode == FALSE) ? 'true' : 'false',
				'ui_link'			=> '/printerstate/upgradenote?ui' . (($normal_mode == TRUE) ? NULL : '&reboot'),
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('printerstate_upgradenote_pagetitle'),
				$this->parser->parse('printerstate/upgradenote', $template_data, TRUE));
		
		return;
	}
	
	public function changecartridge() {
		$template_data = array();
		
		$abb_cartridge = $this->input->get('v');
		$need_filament = $this->input->get('f');
		$id_model = $this->input->get('id');
		
		if (!$abb_cartridge && !$need_filament && !in_array($abb_cartridge, array('l', 'r'))) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$this->output->set_header('Location: ' . $_SERVER['HTTP_REFERER']);
			}
			else {
				$this->output->set_header('Location: /');
			}
			return;
		}
		
		$this->load->library('parser');
		$this->lang->load('printerstate/changecartridge', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'title'			=> ($abb_cartridge == 'l') ? t('Left cartridge change') : t('Right cartridge change'),
				'wait_info'		=> t('Waiting for getting information...'),
				'home'			=> t('Home'),
				'first_status'	=> PRINTERSTATE_CHANGECART_UNLOAD_F,
				'insert_status'	=> PRINTERSTATE_CHANGECART_INSERT_C,
				'back'			=> t('back'),
				'abb_cartridge'	=> $abb_cartridge,
				'need_filament'	=> $need_filament,
				'id_model'		=> $id_model,
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Change cartridge'),
				$this->parser->parse('printerstate/changecartridge', $template_data, TRUE));
		
		return;
	}
	
	public function changecartridge_ajax() {
		$template_data = array();
		$body_page = NULL;
		$ret_val = 0;
		
		$abb_cartridge = $this->input->post('abb_cartridge');
		$need_filament = $this->input->post('need_filament');
		$id_model = $this->input->post('mid');
		$next_phase = $this->input->post('next_phase');
		$code_miss_cartridge = ($abb_cartridge == 'r') ? ERROR_MISS_RIGT_CART : ERROR_MISS_LEFT_CART;
		$code_low_filament = ($abb_cartridge == 'r') ? ERROR_LOW_RIGT_FILA : ERROR_LOW_LEFT_FILA;
		$code_miss_filament = ($abb_cartridge == 'r') ? ERROR_MISS_RIGT_FILA : ERROR_MISS_LEFT_FILA;
		$low_hint = FALSE;
		$change_able = TRUE;
		
		// treat input data
		if (!$abb_cartridge && !in_array($abb_cartridge, array('l', 'r'))) {
			if (isset($_SERVER['HTTP_REFERER'])) {
				$this->output->set_header('Location: ' . $_SERVER['HTTP_REFERER']);
			}
			else {
				$this->output->set_header('Location: /');
			}
			return;
		}
		if ($need_filament) {
			$need_filament = (int)$need_filament;
		}
		else {
			$need_filament = 0;
		}
		
		$this->load->helper(array('corestatus'));
		
		// detect status
		switch ($next_phase) {
			case PRINTERSTATE_CHANGECART_UNLOAD_F:
				// we call the page: wait unload filament, need checking status (first status page)
				$status_current = '';
				$array_status = array();
				
				// block any sending command to arduino when in unloading wait time
				if (CoreStatus_checkInIdle($status_current, $array_status) == FALSE) {
					if (in_array($status_current, array(CORESTATUS_VALUE_UNLOAD_FILA_L, CORESTATUS_VALUE_UNLOAD_FILA_R))) {
						if (!$this->_deal_with_unloading_wait_time($abb_cartridge, $array_status)) {
							$this->_display_changecartridge_in_unload_filament($abb_cartridge);
							break;
						}
					}
					else if (in_array($status_current, array(CORESTATUS_VALUE_LOAD_FILA_L, CORESTATUS_VALUE_LOAD_FILA_R))) {
						$this->_display_changecartridge_in_load_filament();
						break;
					}
				}
				
				if (PrinterState_getFilamentStatus($abb_cartridge)) {
					// have filament
					$status_correct = ($abb_cartridge == 'r') ? CORESTATUS_VALUE_UNLOAD_FILA_R : CORESTATUS_VALUE_UNLOAD_FILA_L;
					$status_changed = ($abb_cartridge == 'r') ? CORESTATUS_VALUE_LOAD_FILA_R : CORESTATUS_VALUE_LOAD_FILA_L;
					
					if (CoreStatus_checkInIdle($status_current, $array_status)) {
						// in idle
						$ret_val = PrinterState_getTemperature($temp_data, 'e', $abb_cartridge);
						if ($ret_val != ERROR_OK) {
							$this->load->helper('printerlog');
							PrinterLog_logError('can not get temperature: ' . $abb_cartridge, __FILE__, __LINE__);
							$this->output->set_status_header(202); // disable checking
						}
						else {
							$this->_display_changecartridge_wait_unload_filament($abb_cartridge, $id_model, 
									($temp_data > PRINTERSTATE_VALUE_MAXTEMPER_BEFORE_UNLOAD));
						}
					}
					else if ($status_current == $status_correct) {
						// in busy (normally only unloading is possible)
						$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_UNLOAD;
						
						if (array_key_exists(CORESTATUS_TITLE_FILA_MAT, $array_status)
								&& $array_status[CORESTATUS_TITLE_FILA_MAT] == PRINTERSTATE_DESP_MATERIAL_PVA) {
							$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_UNLOAD_PVA;
						}
						
						if (!CoreStatus_checkInWaitTime($timeout_check)) {
							//TODO test me
							// already passed the timeout of changement
							// change status into idle
							$ret_val = CoreStatus_setInIdle();
							if ($ret_val == FALSE) {
								$this->load->helper('printerlog');
								PrinterLog_logError('can not set idle after unloading filament', __FILE__, __LINE__);
								$this->output->set_status_header(202); // disable checking
							}
							$this->_display_changecartridge_error_unloading();
							break;
						}
						$this->_display_changecartridge_in_unload_filament($abb_cartridge);
					}
					else if ($status_current == $status_changed) {
						// in busy (but in idle, status is changed in real)
						$ret_val = CoreStatus_setInIdle();
						if ($ret_val == FALSE) {
							$this->load->helper('printerlog');
							PrinterLog_logError('can not set idle after unloading filament', __FILE__, __LINE__);
							$this->output->set_status_header(202); // disable checking
						}
					}
					else {
						// in other busy status
						$this->load->helper('printerlog');
						PrinterLog_logError('error status when changing filament', __FILE__, __LINE__);
						$this->_display_changecartridge_error_status($status_current);
// 						$this->output->set_status_header(202); // disable checking
					}
				}
				else {
					// no filament
					$status_correct = ($abb_cartridge == 'r') ? CORESTATUS_VALUE_LOAD_FILA_R : CORESTATUS_VALUE_LOAD_FILA_L;
					$status_changed = ($abb_cartridge == 'r') ? CORESTATUS_VALUE_UNLOAD_FILA_R : CORESTATUS_VALUE_UNLOAD_FILA_L;
					
					if (CoreStatus_checkInIdle($status_current, $array_status)) {
						$ret_val = PrinterState_checkFilament($abb_cartridge, $need_filament);
						if ($ret_val == $code_miss_filament) {
							// have cartridge, enough filament
							$this->_display_changecartridge_wait_load_filament(TRUE, $id_model, $abb_cartridge);
						}
						else if ($ret_val == $code_low_filament) {
							// have cartridge, low filament
							$this->_display_changecartridge_remove_cartridge(TRUE);
						}
						else if ($ret_val == $code_miss_cartridge) {
							// no cartridge
							// raise the platform for first loading ajax page
							if (ERROR_OK != PrinterState_raisePlatform()) {
								$this->output->set_header('Location: /');
								return;
							}
							
							$this->_display_changecartridge_insert_cartridge();
						}
						else {
							// error status
							$this->load->helper('printerlog');
							PrinterLog_logError('error checkfilament return status when changing filament (in starting)', __FILE__, __LINE__);
							$this->_display_changecartridge_remove_cartridge();
						}
					}
					else if ($status_current == $status_correct) {
						$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_LOAD;
						
						if (array_key_exists(CORESTATUS_TITLE_FILA_MAT, $array_status)
								&& $array_status[CORESTATUS_TITLE_FILA_MAT] == PRINTERSTATE_DESP_MATERIAL_PVA) {
							$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_LOAD_PVA;
						}
						
						// in busy (normally only loading is possible)
						if (!CoreStatus_checkInWaitTime($timeout_check)) {
							// already passed the timeout of changement
							// change status into idle
							$ret_val = CoreStatus_setInIdle();
							if ($ret_val == FALSE) {
								$this->load->helper('printerlog');
								PrinterLog_logError('can not set idle after loading filament', __FILE__, __LINE__);
								$this->output->set_status_header(202); // disable checking
							}
							$this->_display_changecartridge_error_loading();
							break;
						}
						$this->_display_changecartridge_in_load_filament();
					}
					else if ($status_current == $status_changed) {
						// in busy (but in idle, status is changed in real)
						$ret_val = CoreStatus_setInIdle();
						if ($ret_val == FALSE) {
							$this->load->helper('printerlog');
							PrinterLog_logError('can not set idle after loading filament', __FILE__, __LINE__);
							$this->output->set_status_header(202); // disable checking
						}
					}
					else {
						// in other busy status
						$this->load->helper('printerlog');
						PrinterLog_logError('error status when changing filament', __FILE__, __LINE__);
						$this->_display_changecartridge_error_status($status_current);
// 						$this->output->set_status_header(202); // disable checking
					}
					
				}
				break;
				
			case PRINTERSTATE_CHANGECART_REMOVE_C:
				// we call the page: in unload filament
// 				$status_current = NULL;
				$array_status = array();
				
// 				CoreStatus_checkInIdle($status_current, $array_status);
				CoreStatus_getStatusArray($array_status);
				
				if (!$this->_deal_with_unloading_wait_time($abb_cartridge, $array_status)) {
					$this->_display_changecartridge_in_unload_filament($abb_cartridge);
					break;
				}
				
				if (PrinterState_getFilamentStatus($abb_cartridge)) {
					// have filament
					$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_UNLOAD;
					
					if (array_key_exists(CORESTATUS_TITLE_FILA_MAT, $array_status)
							&& $array_status[CORESTATUS_TITLE_FILA_MAT] == PRINTERSTATE_DESP_MATERIAL_PVA) {
						$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_UNLOAD_PVA;
					}
					
					if (!CoreStatus_checkInWaitTime($timeout_check)) {
						// already passed the timeout of changement
						// change status into idle
						$ret_val = CoreStatus_setInIdle();
						if ($ret_val == FALSE) {
							$this->load->helper('printerlog');
							PrinterLog_logError('can not set idle after unloading filament', __FILE__, __LINE__);
							$this->output->set_status_header(202); // disable checking
						}
						$this->_display_changecartridge_error_unloading();
						break;
					}
					
					$this->_display_changecartridge_in_unload_filament($abb_cartridge);
				}
				else {
					// no filament
					$ret_val = CoreStatus_setInIdle();
					if ($ret_val == FALSE) {
						$this->load->helper('printerlog');
						PrinterLog_logError('can not set idle after unloading filament', __FILE__, __LINE__);
						$this->output->set_status_header(202); // disable checking
					}
					$this->_display_changecartridge_remove_cartridge();
				}
				break;

			case PRINTERSTATE_CHANGECART_REINST_C:
				// we use switch breakdown to continue the treatement
				$low_hint = TRUE;
				
			case PRINTERSTATE_CHANGECART_INSERT_C:
				// we call the page: remove / reinsert cartridge
				$temp_data = NULL;
				
				$ret_val = PrinterState_checkFilament($abb_cartridge, $need_filament, $temp_data, FALSE);
				if ($ret_val == $code_miss_cartridge) {
					// no cartridge
					$this->_display_changecartridge_insert_cartridge();
				}
				else if ($ret_val == $code_low_filament) {
					// have cartridge, low filament
					$this->_display_changecartridge_remove_cartridge(TRUE);
				}
				else if ($ret_val == $code_miss_filament) {
					// have cartridge, no filemant
					$this->_display_changecartridge_remove_cartridge($low_hint);
				}
				else {
					// error status
					$this->load->helper('printerlog');
					PrinterLog_logError('error checkfilament return status when changing filament (in removing)', __FILE__, __LINE__);
					$this->_display_changecartridge_remove_cartridge();
				}
				break;
				
			case PRINTERSTATE_CHANGECART_LOAD_F:
				// we call the page: insert cartridge
				$temp_data = NULL;
				
				$ret_val = PrinterState_checkFilament($abb_cartridge, $need_filament, $temp_data, FALSE);
				if ($ret_val == $code_miss_filament) {
					//TODO added a new temporary page here, need to remove when not needed
// 					$this->_display_changecartridge_wait_load_filament(FALSE);
// 					if ($temp_data[PRINTERSTATE_TITLE_CARTRIDGE] == PRINTERSTATE_DESP_CARTRIDGE_REFILL) {
// 						$this->_display_changecartridge_write_cartridge($abb_cartridge, PRINTERSTATE_CHANGECART_WAIT_F, $need_filament);
// 					}
// 					else {
// 						$this->_display_changecartridge_wait_load_filament(FALSE);
// 					}
					$this->_display_changecartridge_write_cartridge($abb_cartridge, PRINTERSTATE_CHANGECART_WAIT_F, $need_filament);
					//TODO a new filament quantity verification system
					
					// turn off RFID power after changing
					$ret_val = PrinterState_setRFIDPower(FALSE);
					if ($ret_val != ERROR_OK) {
						$this->load->helper('printerlog');
						PrinterLog_logError('error in turning off RFID power', __FILE__, __LINE__);
					}
				}
				else if ($ret_val == $code_low_filament) {
					$this->_display_changecartridge_remove_cartridge(TRUE);
				}
				else if ($ret_val == $code_miss_cartridge) {
					// no cartridge
					$this->_display_changecartridge_insert_cartridge();
				}
				else {
					// error status
					$this->load->helper('printerlog');
					PrinterLog_logError('error checkfilament return status when changing filament (in inserting)', __FILE__, __LINE__);
					$this->_display_changecartridge_remove_cartridge();
				}
				break;
				
			case PRINTERSTATE_CHANGECART_WAIT_F:
				// we use switch breakdown to continue the treatement
				$change_able = FALSE;
				
			case PRINTERSTATE_CHANGECART_WAIT_F_C:
				// we call the page: wait load filament / change cartridge
				if (CoreStatus_checkInIdle()) {
					// in idle
					$this->_display_changecartridge_wait_load_filament($change_able, $id_model, $abb_cartridge);
				}
				else {
					// in busy (normally only loading is possible)
					$this->_display_changecartridge_in_load_filament();
				}
				break;
				
			case PRINTERSTATE_CHANGECART_NEED_P:
				// we call the page: in load filament
// 				$status_current = NULL;
				$array_status = array();
				$offset_check = PRINTERSTATE_VALUE_OFFSET_TO_CHECK_LOAD;
				
// 				CoreStatus_checkInIdle($status_current, $array_status);
				CoreStatus_getStatusArray($array_status);
				if (array_key_exists(CORESTATUS_TITLE_FILA_MAT, $array_status)
						&& $array_status[CORESTATUS_TITLE_FILA_MAT] == PRINTERSTATE_DESP_MATERIAL_PVA) {
					$offset_check = PRINTERSTATE_VALUE_OFFSET_TO_CHECK_LOAD_PVA;
				}
				
				// wait the time for arduino before checking filament when loading filament
				if (CoreStatus_checkInWaitTime($offset_check)) {
					$this->_display_changecartridge_in_load_filament();
					break;
				}
				
				if (PrinterState_getFilamentStatus($abb_cartridge)) {
					// have filament
					$ret_val = CoreStatus_setInIdle();
					if ($ret_val == FALSE) {
						$this->load->helper('printerlog');
						PrinterLog_logError('can not set idle after loading filament', __FILE__, __LINE__);
						$this->output->set_status_header(202); // disable checking
					}
					$this->_display_changecartridge_need_prime($abb_cartridge, $id_model);
				}
				else {
					// no filament
					$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_LOAD;
					
					if (array_key_exists(CORESTATUS_TITLE_FILA_MAT, $array_status)
							&& $array_status[CORESTATUS_TITLE_FILA_MAT] == PRINTERSTATE_DESP_MATERIAL_PVA) {
						$timeout_check = PRINTERSTATE_VALUE_TIMEOUT_TO_CHECK_LOAD_PVA;
					}
					
					if (!CoreStatus_checkInWaitTime($timeout_check)) {
						// already passed the timeout of changement
						CoreStatus_setInIdle(); //TODO need test and error control here
						$this->_display_changecartridge_error_loading();
						break;
					}
					$this->_display_changecartridge_in_load_filament();
				}
				break;
				
			default:
				break;
		}
		
		$this->output->set_content_type('text/plain; charset=UTF-8');
		
		return;
	}
	
	public function changecartridge_action($mode = '') {
		$abb_cartridge = $this->input->get('v');
		
		if (!$abb_cartridge && !in_array($abb_cartridge, array('l', 'r'))) {
			$this->output->set_status_header(403); // invalid request
			return;
		}
		
		if ($mode == 'unload_r') {
			$mode = 'unload';
		}
		else if ($mode == 'load_r') {
			$mode = 'load';
		}
		else if ($mode != 'cancel_unload') {
			//block request when not in idle
			$this->load->helper('corestatus');
			if (CoreStatus_checkInIdle() == FALSE) {
				$this->output->set_status_header(403); // bad request
				return;
			}
		}
		
		switch ($mode) {
			case 'unload':
				$ret_val = PrinterState_unloadFilament($abb_cartridge);
				if ($ret_val != ERROR_OK) {
					$this->output->set_status_header($ret_val, MyERRMSG($ret_val));
				}
				break;
				
			case 'cancel_unload':
				$ret_val = 0;
				@unlink(PRINTERSTATE_FILE_UNLOAD_HEAT);
				$ret_val = CoreStatus_setInIdle();
				if ($ret_val == FALSE) {
					$this->load->helper('printerlog');
					PrinterLog_logError('can not set idle after cancelling unloading', __FILE__, __LINE__);
					$this->output->set_status_header(ERROR_INTERNAL);
				}
				
				break;
				
			case 'load':
				$ret_val = PrinterState_loadFilament($abb_cartridge);
				if ($ret_val != ERROR_OK) {
					$this->output->set_status_header($ret_val);
				}
				break;
				
			case 'detail':
				$id_model = $this->input->get('id');
				$this->_display_changecartridge_cartridge_detail($abb_cartridge, $id_model);
				break;
				
			case 'write':
				$ret_val = 0;
				$array_data = array();
				$array_old = array();
				$color = $this->input->get('c');
				$temper = (int) $this->input->get('t');
				$material = (int) $this->input->get('m');
				$length = (int) $this->input->get('l') * 1000;
				$abb_cartridge = $this->input->get('v');
				
				// get cartridge type from old RFID
				$ret_val = PrinterState_getCartridgeAsArray($array_old, $abb_cartridge, FALSE);
				if ($ret_val != ERROR_OK) {
					$this->output->set_status_header(403);
					
					$this->load->helper('printerlog');
					PrinterLog_logMessage('read rfid error: ' . $ret_val, __FILE__, __LINE__);
					break;
				}
				// change color from name to hex code
				$this->load->helper('printlist');
				$ret_val = ModelList__changeColorName($color);
				if ($ret_val == ERROR_WRONG_PRM) {
					$this->output->set_status_header(404);
					
					$this->load->helper('printerlog');
					PrinterLog_logMessage('unknown color name: ' . $color, __FILE__, __LINE__);
					break;
				}
				$color = str_replace('#', '', $color);
				// write RFID card
				$array_data = array(
						PRINTERSTATE_TITLE_COLOR		=> $color,
						PRINTERSTATE_TITLE_EXT_TEMPER	=> $temper,
						PRINTERSTATE_TITLE_INITIAL		=> $length,
						PRINTERSTATE_TITLE_MATERIAL		=> $material,
						PRINTERSTATE_TITLE_CARTRIDGE	=> $array_old[PRINTERSTATE_TITLE_CARTRIDGE],
				);
				$ret_val = PrinterState_setCartridgeAsArray($abb_cartridge, $array_data);
				if ($ret_val != ERROR_OK) {
					$this->output->set_status_header(403);
					
					$this->load->helper('printerlog');
					PrinterLog_logMessage('write rfid error: ' . $ret_val, __FILE__, __LINE__);
					break;
				}
				
				break;
				
			default:
				$this->output->set_status_header(403); // unknown request
				break;
		}
		
		return;
	}
	
	public function changecartridge_temper() {
		$ret_val = 0;
		$temp_data = 0;
		$abb_cartridge = $this->input->get('v');
		
		if (!$abb_cartridge && !in_array($abb_cartridge, array('l', 'r'))) {
			$this->output->set_status_header(403); // invalid request
			return;
		}
		
		$ret_val = PrinterState_getTemperature($temp_data, 'e', $abb_cartridge);
		if ($ret_val == ERROR_OK && $temp_data >= PRINTERSTATE_VALUE_MAXTEMPER_BEFORE_UNLOAD) {
			$ret_val = 202; // change status header to stop signal
		}
		$this->output->set_status_header($ret_val);
		
		return;
	}
	
	public function resetnetwork() {
		$template_data = array();
		$error = '';
		
		$this->load->library('parser');
		$this->lang->load('printerstate/resetnetwork', $this->config->item('language'));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->load->helper('zimapi');
			
			if (ZimAPI_resetNetwork() == ERROR_OK) {
// 				$error = t('wait a moment');
// 				$this->output->set_header("Location:/connection");
				
				// parse the main body
				$template_data = array(
						'hint'		=> t('Connect to the new printer\'s network, then press OK'),
						'ok_button'	=> t('OK'),
				);
				
				// parse all page
				$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Reset connection'),
						$this->parser->parse('printerstate/resetnetwork_finish', $template_data, TRUE));
				
				return;
			}
			else {
				$error = t('Reset error');
			}
		}
		
		// parse the main body
		$template_data = array(
 				'reset_hint'	=> t('reset_hint'),
				'reset_button'	=> t('Reset the printer\'s network'),
				'error'			=> $error,
				'back'			=> t('back'),
				'home'			=> t('Home')
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('ZeePro Personal Printer 21 - Reset connection'),
				$this->parser->parse('printerstate/resetnetwork', $template_data, TRUE));
		
		return;
	}
	
	public function sethostname() {
		$template_data = array();
		$error = '';
		$ret_val = 0;
		$hostname = NULL;
		$restart = NULL;
		
		$this->load->library('parser');
		$this->lang->load('printerstate/sethostname', $this->config->item('language'));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$hostname = $this->input->post('hostname');
			$restart = $this->input->post('restart');
			$returnUrl = $this->input->post('returnUrl');
			
			if ((int) $restart == 0) {
				$restart = FALSE;
			}
			else {
				$restart = TRUE;
			}
			
			if ($hostname) {
				$this->load->helper('zimapi');

				$code = ZimAPI_setHostname($hostname, $restart);
				if (!CoreStatus_finishHostname())
				{
					$this->load->helper('printerlog');
					PrinterLog_logError('can not remove need hostname status', __FILE__, __LINE__);
				}
				if ($code == ERROR_OK)
				{
					$hint_message = NULL;
					$network_info = array();
					$err = ZimAPI_getNetworkInfoAsArray($network_info);
					if ($err != ERROR_OK)
					{
						$this->load->helper('printerlog');
						PrinterLog_logError('can not retrieve connection info array', __FILE__, __LINE__);
						$error = t("network_array_error");
					}
					else
					{
						if ($network_info[ZIMAPI_TITLE_TOPOLOGY] == ZIMAPI_VALUE_P2P)//peertopeer
						{
							$hint_message = t('p2p', $network_info[ZIMAPI_TITLE_SSID]);
						}
						else if ($restart == TRUE)
						{
							$hint_message = t('finish_hint', array($hostname, $hostname, $hostname, $hostname));
						}
						else
						{
							$hint_message = t('finish_hint_norestart', array($hostname, $hostname));
						}
						//parse the main body
						$template_data = array(
								'hint'			=> $hint_message,
// 								'home_button'	=> t('home_button'),
						);
						
						// parse all page
						$this->_parseBaseTemplate(t('page_title'),
								$this->parser->parse('printerstate/sethostname_finish', $template_data, TRUE));
						
						return;
					}
				}
				else if ($code == ERROR_WRONG_PRM)
				{
					$error = t('bad_char');
				}
				else
				{
					$error = t('set_error');
				}
			}
			else
			{
				$error = t('no_input');
			}
		}
		
		if ($restart === NULL) {
			if (FALSE === $this->input->get('norestart'))
			{
				$restart = TRUE;
			}
			else
			{
				$restart = FALSE;
			}
		}
		
		$ret_val = ZimAPI_getHostname($hostname);
		if ($ret_val != ERROR_OK) {
			$hostname = 'zim';
		}
		
		// parse the main body
		$template_data = array(
				'hint'			=> t('set_hint'),
				'set_button'	=> t('set_button'),
				'error'			=> $error,
				'back'			=> t('back'),
				'home_button'	=> t('home_button'),
				'hostname'		=> $hostname,
				'info_text'		=> t('info_text'),
				'length_error'	=> t('length_error'),
				'restart'		=> ($restart) ? 1 : 0,
		);
		
		// parse all page
		$this->_parseBaseTemplate(t('page_title'),
				$this->parser->parse('printerstate/sethostname', $template_data, TRUE));
		
		return;
	}
	
	public function nozzles_adjustment()
	{
		$this->load->library('parser');
		$this->lang->load('printerstate/nozzles', $this->config->item('language'));
		$template_data = array(
				'print_calibration'	=> t('print_calibration'),
				'trim_offset'		=> t('trim_offset'),
				'back'				=> t('back'),
				'home'				=> t('home'),
		);
		
		$this->_parseBaseTemplate(t('nozzle_offset_pagetitle'),
				$this->parser->parse('printerstate/nozzles_adjustment', $template_data, TRUE));
		
		return;
	}
	
	public function offset_setting() {
		$template_data = array();
		$error = '';
		
		$this->load->library('parser');
		$this->lang->load('printerstate/nozzles', $this->config->item('language'));
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$this->load->helper('printerstate');
			$top_offset = $this->input->post('top_offset') * PRINTERSTATE_VALUE_FACTOR_NOZZLE_OFFSET;
			$bot_offset = $this->input->post('bot_offset') * PRINTERSTATE_VALUE_FACTOR_NOZZLE_OFFSET;
			$right_offset = $this->input->post('right_offset') * PRINTERSTATE_VALUE_FACTOR_NOZZLE_OFFSET;
			$left_offset = $this->input->post('left_offset') * PRINTERSTATE_VALUE_FACTOR_NOZZLE_OFFSET;
			
			if ($top_offset * $bot_offset != 0 || $right_offset * $left_offset != 0) {
				$error .= t('invalid_input') . '<br/>';
			}
			else {
				$array_data = array();
				
				if ($top_offset != 0) {
					$array_data['Y'] = -$top_offset;
				}
				else if ($bot_offset != 0) {
					$array_data['Y'] = $bot_offset;
				}
				if ($right_offset != 0) {
					$array_data['X'] = -$right_offset;
				}
				else if ($left_offset != 0) {
					$array_data['X'] = $left_offset;
				}
				
				if (count($array_data)) {
					$cr = 0;
					
					foreach (array('X', 'Y') as $axis) {
						if (array_key_exists($axis, $array_data)) {
							$value = 0;
							$cr = PrinterState_getOffset($axis, $value);
							if ($cr != ERROR_OK) {
								$error .= t('marlin_error') . '<br/>';
								$this->load->helper('printerlog');
								PrinterLog_logError('get ancient offset error, axis: ' . $axis
										. ', cr: ' . $cr, __FILE__, __LINE__);
								break;
							}
							$array_data[$axis] += $value; // add new offset on the ancient offset
						}
					}
					if (count($error))
					{
						// we have no error for getting ancient offset, goto setting offset
						$cr = PrinterState_setOffset($array_data);
						if ($cr == ERROR_WRONG_PRM) {
							$error .= t('value_zone_error') . '<br/>';
							$this->load->helper('printerlog');
							PrinterLog_logError('pass value zone for offset', __FILE__, __LINE__);
						}
						else if ($cr != ERROR_OK) {
							$error .= t('marlin_error') . '<br/>';
							$this->load->helper('printerlog');
							PrinterLog_logError('set offset error, axis: ' . $axis
									. ', cr: ' . $cr, __FILE__, __LINE__);
						}
						else {
							$this->output->set_header('Location: /printerstate/nozzles_adjustment');
							
							return;
						}
					}
				}
			}
		}
		
		$template_data = array(
				'nozzles_title'	=> t('nozzles_title'),
				'nozzles_intro'	=> t('nozzles_intro'),
				'collapsible_1'	=> t('collapsible_1'),
				'collapsible_2'	=> t('collapsible_2'),
				'back'			=> t('back'),
				'home'			=> t('home'),
				'error'			=> $error,
		);
		
		$this->_parseBaseTemplate(t('nozzle_offset_pagetitle'),
				$this->parser->parse('printerstate/offset_setting', $template_data, TRUE));
		
		return;
	}
	
	public function checkupgrade() {
		$ret_val = FALSE;
		$this->load->helper('zimapi');
		
		$ret_val = !(ZimAPI_getVersion(TRUE) == ZimAPI_getVersion(FALSE));
		
		if ($ret_val == TRUE) {
			$this->output->set_status_header(202); // send 202 code to indicate upgrade
		}
		else {
			$this->output->set_status_header(200);
		}
		
		$this->load->library('parser');
		$this->output->set_content_type('txt_u');
		$this->parser->parse('plaintxt', array('display' => ZimAPI_getVersion(TRUE))); //optional
		
		return;
	}
	
	public function stats_support() {
		$this->load->helper('printerlog');
		$this->load->library('parser');
		
		// stats info
		PrinterLog_statsWebClick(PRINTERLOG_STATS_LABEL_SUPPORT);
		$this->parser->parse('plaintxt', array('display' => 'ok'));
		
		return;
	}
}
