<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Gcode extends MY_Controller {
	function __construct() {
		parent::__construct();
	}
	
	private function _analyse_gcode($mode = 'display', $gcode_id = NULL) {
		$back_url = '/sliceupload/slice?callback';
		$request_url = '/sliceupload/gcode_ajax';
		$template_data = array();
		
		$this->load->library('parser');
		$this->lang->load('gcode', $this->config->item('language'));
		
		if (!is_null($gcode_id)) {
			$back_url = '/printerstoring/gcodedetail?id=' . $gcode_id;
			$request_url = '/printerstoring/gcode_ajax?id=' . $gcode_id;
		}
		
		$template_data = array(
				'home'					=> t('home'),
				'back'					=> t('back'),
				'js_render'				=> ($mode == 'display') ? 'false' : 'true',
				'back_print_url'		=> $back_url,
				'gcode_request_url'		=> $request_url,
				'tab_render'			=> t('tab_render'),
				'tab_gcode'				=> t('tab_gcode'),
// 				'layer_start'			=> t('layer_start'),
// 				'layer_end'				=> t('layer_end'),
// 				'layer_prefix'			=> t('layer_prefix'),
				'speed_label'			=> t('speed_label'),
				'speedDisplay'			=> t('speedDisplay'),
				'exPerMM'				=> t('exPerMM'),
				'volPerSec'				=> t('volPerSec'),
				'showMoves'				=> t('showMoves'),
				'showRetracts'			=> t('showRetracts'),
				'moveModel'				=> t('moveModel'),
				'differentiateColors'	=> t('differentiateColors'),
				'thickExtrusion'		=> t('thickExtrusion'),
				'alpha'					=> t('alpha'),
				'showNextLayer'			=> t('showNextLayer'),
				'back_print_button'		=> t('back_print_button'),
				'layer_number'			=> t('layer_number'),
				'layer_flow'			=> t('layer_flow'),
				'option_others'			=> t('option_others'),
		);
		
		$this->_parseBaseTemplate(t('gcode_pagetitle'),
				$this->parser->parse('gcode.php', $template_data, TRUE));
		
		return;
	}
	
	public function slice($mode = 'display') {
		$this->_analyse_gcode($mode);
		
		return;
	}
	
	public function library($mode = 'display') {
		$gcode_id = $this->input->get('id');
		
		$this->_analyse_gcode($mode, $gcode_id);
		
		return;
	}
}
