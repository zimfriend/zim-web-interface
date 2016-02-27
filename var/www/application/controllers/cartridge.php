<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Cartridge extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'printerstate',
				'json'
		) );
	}
	
	public function index() {
		$this->readnwrite('r');
		return;
	}
	
	public function right() {
		$this->readnwrite('r');
		return;
	}
	
	public function left() {
		$this->readnwrite('l');
		return;
	}
	
	public function readnwrite($side = 'r') {
		$cr = 0;
		$cartridge_code = NULL;
		$err_code = NULL;
		$error = NULL;
		$cartridge_data = array();
		$initial_length_value = 0;
		$used_length_value = 0;
		$temper_value = 0;
		$temper_first = 0;
		$material_value = 0;
		$cartridge_value = 0;
		$color_value = '#ffffff';
		$option_selected = 'selected="selected"';
		$packdate_val = 'N/A';
		
		// right as default
		if (in_array($side, array('l', 'left')))
			$side = 'l';
		else
			$side = 'r';
		
		$this->load->library('parser');
		$this->lang->load('cartridge', $this->config->item('language'));
		
		if (PrinterState_getCartridgeCode($cartridge_code, $side) != ERROR_OK) {
			$cartridge_code = t('error retrieving cartridge code');
		}
		
		$cr = PrinterState_getCartridgeAsArray($cartridge_data, $side, TRUE, $err_code);
		if ($cr != ERROR_OK) {
			$initial_length_value = 20;
			$used_length_value = 10;
			$temper_value = 200;
			$temper_first = 210;
			
			if ($cr == ERROR_INTERNAL) {
				switch ($err_code) {
					case PRINTERSTATE_CARTRIDGE_ERR_MAGIC:
						$error = t('magic_number_error');
						break;
						
					case PRINTERSTATE_CARTRIDGE_ERR_CRC:
						$error = t('crc_error');
						break;
						
					case PRINTERSTATE_CARTRIDGE_ERR_CART:
						$error = t('cartridge_type_error');
						break;
						
					case PRINTERSTATE_CARTRIDGE_ERR_FILA:
						$error = t('filament_type_error');
						break;
						
					default:
						$error = t('internal_error');
						break;
				}
			}
			else if (in_array($cr, array(ERROR_MISS_LEFT_CART, ERROR_MISS_RIGT_CART))) {
				$error = 'No cartridge';
			}
		}
		else {
			$initial_length_value = $cartridge_data[PRINTERSTATE_TITLE_INITIAL] / 1000;
			$used_length_value = $cartridge_data[PRINTERSTATE_TITLE_USED] / 1000;
// 			if ($length_value < 10) {
// 				$length_value = 10;
// 			}
			$temper_value = $cartridge_data[PRINTERSTATE_TITLE_EXT_TEMPER];
			$temper_first = $cartridge_data[PRINTERSTATE_TITLE_EXT_TEMP_1];
			$color_value = $cartridge_data[PRINTERSTATE_TITLE_COLOR];
			if ($temper_value < 165) {
				$temper_value = 165;
			}
			$packdate_val = $cartridge_data[PRINTERSTATE_TITLE_SETUP_DATE];
		}
		
		$template_data = array (
				'material_array'	=> array(
						array('name' => t('PLA'), 'value' => PRINTERSTATE_VALUE_MATERIAL_PLA, 'on' => NULL),
						array('name' => t('ABS'), 'value' => PRINTERSTATE_VALUE_MATERIAL_ABS, 'on' => NULL),
						array('name' => t('PVA'), 'value' => PRINTERSTATE_VALUE_MATERIAL_PVA, 'on' => NULL),
				),
				'cartridge_array'	=> array(
						array('name' => t('Normal'), 'value' => PRINTERSTATE_VALUE_CARTRIDGE_NORMAL, 'on' => NULL),
						array('name' => t('Refill'), 'value' => PRINTERSTATE_VALUE_CARTRIDGE_REFILL, 'on' => NULL),
				),
				'cartridge_code'		=> $cartridge_code,
				'initial_length_value'	=> $initial_length_value,
				'used_length_value'		=> $used_length_value,
				'temper_value'			=> $temper_value,
				'temper_f_value'		=> $temper_first,
				'rfid_color'			=> $color_value,
				'abb_cartridge'			=> $side,
				'side_cartridge'		=> ($side == 'l') ? t('left') : t('right'),
				'error'					=> $error,
				'color'					=> t('color'),
				'cartridge_type'		=> t('cartridge_type'),
				'material_type'			=> t('material_type'),
				'temperature'			=> t('temperature'),
				'temperature_first'		=> t('temperature_first'),
				'initial_length'		=> t('initial_length'),
				'used_length'			=> t('used_length'),
				'initial_length_mm'		=> t('initial_length_mm'),
				'used_length_mm'		=> t('used_length_mm'),
				'cartridge_code_hint'	=> t('cartridge_code_hint'),
				'magic_number'			=> t('magic_number'),
				'red_label'				=> t('red_label'),
				'green_label'			=> t('green_label'),
				'blue_label'			=> t('blue_label'),
				'pack_label'			=> t('pack_label'),
				'checksum_label'		=> t('checksum_label'),
				'write_button'			=> t('write_button'),
				'pack_date_val'			=> $packdate_val,
				'info_not_changed'		=> t('information_not_changed'),
				'writing_successed'		=> t('writing_successed'),
				'error_writing'			=> t('error_writing'),
		);
	
		if ($cr == ERROR_OK) {
			switch ($cartridge_data[PRINTERSTATE_TITLE_MATERIAL])
			{
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
			switch ($cartridge_data[PRINTERSTATE_TITLE_CARTRIDGE]) {
				case PRINTERSTATE_DESP_CARTRIDGE_NORMAL:
					$cartridge_value = PRINTERSTATE_VALUE_CARTRIDGE_NORMAL;
					break;
						
				case PRINTERSTATE_DESP_CARTRIDGE_REFILL:
					$cartridge_value = PRINTERSTATE_VALUE_CARTRIDGE_REFILL;
					break;
						
				default:
					$cartridge_value = PRINTERSTATE_VALUE_CARTRIDGE_NORMAL;
					break;
			}
			$template_data['cartridge_array'][$cartridge_value]['on'] = $option_selected;
		}
		
		// parse all page
		$this->_parseBaseTemplate(t('readnwrite_pagetitle'),
				$this->parser->parse('cartridge', $template_data, TRUE));
		
		return;
	}
	
	public function readnwrite_ajax()
	{
		$ret_val = 0;
		$array_data = array();
		$array_old = array();
		$color = $this->input->get('c');
		$temper = (int) $this->input->get('t');
		$temper_f = (int) $this->input->get('tf');
		$material = (int) $this->input->get('m');
		$length = (int) $this->input->get('l') * 1000;
		$length_use = (int) $this->input->get('lu') * 1000;
		$abb_cartridge = $this->input->get('v');
		$type = (int) $this->input->get('ct');
		
		// change color from name to hex code
		$this->load->helper('printlist');
		$ret_val = ModelList__changeColorName($color);
		if ($ret_val == ERROR_WRONG_PRM) {
			$this->output->set_status_header(404);
			
			$this->load->helper('printerlog');
			PrinterLog_logMessage('unknown color name: ' . $color, __FILE__, __LINE__);
			return;
		}
		$color = str_replace('#', '', $color);
		// write RFID card
		$array_data = array(
				PRINTERSTATE_TITLE_COLOR		=> $color,
				PRINTERSTATE_TITLE_EXT_TEMPER	=> $temper,
				PRINTERSTATE_TITLE_EXT_TEMP_1	=> $temper_f,
				PRINTERSTATE_TITLE_INITIAL		=> $length,
				PRINTERSTATE_TITLE_USED			=> $length_use,
				PRINTERSTATE_TITLE_MATERIAL		=> $material,
				PRINTERSTATE_TITLE_CARTRIDGE	=> $type,
		);
		$ret_val = PrinterState_setCartridgeAsArray($abb_cartridge, $array_data);
		if ($ret_val != ERROR_OK) {
			$this->output->set_status_header(403);
			
			$this->load->helper('printerlog');
			PrinterLog_logMessage('write rfid error: ' . $ret_val, __FILE__, __LINE__);
		}
		
		return;
	}
}