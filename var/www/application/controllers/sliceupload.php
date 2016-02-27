<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Sliceupload extends MY_Controller {
	function __construct() {
		parent::__construct ();
		$this->load->helper( array(
				'errorcode',
		) );
	}
	
	public function index() {
		$this->output->set_header('Location: /sliceupload/upload');
		return;
	}
	
	public function upload() {
		$template_data = array();
		$error = NULL;
		$response = 0;
		$button_goto_slice = NULL;
		$bicolor = ($this->config->item('nb_extruder') >= 2);
		
		$this->load->library('parser');
		$this->load->helper('slicer');
		$this->lang->load('sliceupload/upload', $this->config->item('language'));
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$array_model = array();
			$upload_config = array (
					'upload_path'	=> $this->config->item('temp'),
					'allowed_types'	=> '*',
					'overwrite'		=> FALSE,
					'remove_spaces'	=> TRUE,
// 					'encrypt_name'	=> TRUE,
			);
			$this->load->library('upload', $upload_config);
			
			if ($this->upload->do_upload('file'))
			{
				$model = $this->upload->data();
				$model_ext = strtolower($model['file_ext']);
				// we just let xml pass to check amf.xml by slicer itself
				$array_check = $bicolor ? array('.stl', '.amf', '.obj', '.xml') : array('.stl');
				
				if (!is_null($model_ext) && !in_array($model_ext, $array_check)) {
					// we can treat extension error differently
					$error = t('fail_message_ext');
				}
				else {
					$array_model[] = $model['file_name'];
				}
			}
			else if ($this->upload->do_upload('file_c1')) {
				$first_combine = TRUE;
				$model = $this->upload->data();
				$model_ext = strtolower($model['file_ext']);
				
				if (!is_null($model_ext) && $model_ext != '.stl') {
					// we can treat extension error differently
					$error = t('fail_message_ext');
				}
				else {
					$array_model[] = $model['file_name'];
					
					foreach (array('file_c2') as $file_key) {
						if ($this->upload->do_upload($file_key)) {
							$first_combine = FALSE;
							$model = $this->upload->data();
							$model_ext = strtolower($model['file_ext']);
							
							if (!is_null($model_ext) && $model_ext != '.stl') {
								// we can treat extension error differently
								$error = t('fail_message_ext');
							}
							else {
								$array_model[] = $model['file_name'];
							}
						}
						else if ($first_combine == TRUE) {
							$error = t('fail_message');
							break;
						}
					}
				}
			}
			else {
				// treat error - missing gcode file
				$error = t('fail_message');
			}
			
			if (is_null($error) && count($array_model)) {
				// load a wait page for adding model into slicer
				$template_data = array(
						'wait_message'	=> t('wait_message'),
						'return_button'	=> t('return_button'),
						'model_name'	=> json_encode($array_model),
						'fail_message'	=> t('fail_message'),
						'fin_message'	=> t('fin_message'),
						'key_smax'		=> SLICER_TITLE_MAXSCALE,
				);
				
				$this->_parseBaseTemplate(t('sliceupload_upload_pagetitle'),
						$this->parser->parse('sliceupload/upload_wait', $template_data, TRUE));
				
				return;
			}
		}
		
		if (0 == strlen(@file_get_contents($this->config->item('temp') . SLICER_FILE_HTTP_PORT))
				&& FALSE == $this->config->item('simulator')) {
			$this->output->set_header('Location: /sliceupload/restart?inboot=1');
			
			return;
		}
		else if (!Slicer_checkOnline(FALSE)) {
			$this->output->set_header('Location: /sliceupload/restart');
			
			return;
		}
		
		// cleanup old upload temporary files
		Slicer_cleanUploadFolder($this->config->item('temp'));
		
		if (ERROR_OK == Slicer_listModel($response) && $response != "[]"
				&& ERROR_OK == Slicer_checkPlatformModel()) {
			$template_data = array(
					'text'	=> t('button_goto_slice'),
					'link'	=> '/sliceupload/slice',
					'id'	=> 'goto_slice_button',
			);
			$button_goto_slice = $this->parser->parse('sliceupload/a_button', $template_data, TRUE);
			
			if (Slicer_checkSlicedModel()) {
				$template_data = array(
						'text'	=> t('button_goto_result'),
						'link'	=> '/sliceupload/slice?callback',
						'id'	=> 'goto_result_button',
				);
				$button_goto_slice .= $this->parser->parse('sliceupload/a_button', $template_data, TRUE);
			}
		}
		
		// parse the main body
		$template_data = array(
				'back'				=> t('back'),
				'select_hint'		=> t('select_hint'),
				'select_hint_multi'	=> t('select_hint_multi'),
				'header_single' 	=> t('header_single'),
				'header_multi'		=> t('header_multi'),
				'upload_button'		=> t('upload_button'),
				'goto_slice'		=> $button_goto_slice,
				'error'				=> $error,
				'bicolor'			=> $bicolor ? 'true' : 'false',
		);
		
		$this->_parseBaseTemplate(t('sliceupload_upload_pagetitle'),
				$this->parser->parse('sliceupload/upload', $template_data, TRUE));
		
		return;
	}
	
	function slice() {
		$ret_val = 0;
		$status_current = NULL;
		$list_display = array();
		$template_data = array();
		$current_stage = 'wait_slice';
		$current_scale = 100;
		$current_xrot = 0;
		$current_yrot = 0;
		$current_zrot = 0;
		$current_scale_max = 100;
		$current_xsize = 0;
		$current_ysize = 0;
		$current_zsize = 0;
		$multi_part = FALSE;
		
		// redirect the client when in slicing
		$this->load->helper('corestatus');
		$ret_val = CoreStatus_checkInIdle($status_current);
		// check status in slicing
		if ($ret_val == FALSE || $status_current == CORESTATUS_VALUE_SLICE) {
			$this->output->set_header('Location: /sliceupload/slicestatus');
			return;
		}
		
		$this->load->helper(array('zimapi', 'printerstate', 'slicer'));
		
		if ($this->input->get('callback') !== FALSE) {
			$current_stage = 'wait_print';
		}
		else { // need preset list only in wait slice mode
			$list_preset = ZimAPI_getPresetListAsArray();
			
			foreach ($list_preset as $preset) {
				//TODO add rollback function to get correct values here
				
				$list_display[] = array(
						'name'		=> $preset[ZIMAPI_TITLE_PRESET_NAME],
						'id'		=> $preset[ZIMAPI_TITLE_PRESET_ID],
						'infill'	=> isset($preset[ZIMAPI_TITLE_PRESET_INFILL]) ? $preset[ZIMAPI_TITLE_PRESET_INFILL] : '30%',
						'skirt'		=> isset($preset[ZIMAPI_TITLE_PRESET_SKIRT]) ? $preset[ZIMAPI_TITLE_PRESET_SKIRT] : 0,
						'raft'		=> isset($preset[ZIMAPI_TITLE_PRESET_RAFT]) ? $preset[ZIMAPI_TITLE_PRESET_RAFT] : 0,
						'support'	=> isset($preset[ZIMAPI_TITLE_PRESET_SUPPORT]) ? $preset[ZIMAPI_TITLE_PRESET_SUPPORT] : 1,
				);
			}
			usort($list_display, 'ZimAPI_usortComparePreset');
		}
		
		try {
			$tmp_string = NULL;
			$tmp_array = NULL;
			
// 			$this->load->helper('slicer');
			$ret_val = Slicer_listModel($tmp_string);
			$tmp_array = json_decode($tmp_string, TRUE);
			
			if ($ret_val == ERROR_OK && count($tmp_array)) {
				$current_scale = $tmp_array[0][SLICER_PRM_SCALE];
				$current_xrot = $tmp_array[0][SLICER_PRM_XROT];
				$current_yrot = $tmp_array[0][SLICER_PRM_YROT];
				$current_zrot = $tmp_array[0][SLICER_PRM_ZROT];
				$current_xsize = number_format($tmp_array[0][SLICER_TITLE_XSIZE], 1);
				$current_ysize = number_format($tmp_array[0][SLICER_TITLE_YSIZE], 1);
				$current_zsize = number_format($tmp_array[0][SLICER_TITLE_ZSIZE], 1);
				$current_scale_max = floor($tmp_array[0][SLICER_TITLE_MAXSCALE]);
				
				if (count($tmp_array[0][SLICER_TITLE_COLOR]) > 1) {
					$multi_part = TRUE;
				}
			}
			
		} catch (Exeception $e) {
			$this->load->helper('printerlog');
			PrinterLog_logError('synchronize slicer model info error', __FILE__, __LINE__);
		}
		
		$this->load->library('parser');
		$this->lang->load('sliceupload/slice', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
 				'home'					=> t('home'),
				'back'					=> t('back'),
				'slice_button'			=> t('slice_button'),
				'goto_preset'			=> t('goto_preset'),
				'value_rho'				=> ZIMAPI_VALUE_DEFAULT_RHO,
				'value_delta'			=> ZIMAPI_VALUE_DEFAULT_DELTA,
				'value_theta'			=> ZIMAPI_VALUE_DEFAULT_THETA,
				'preset_list'			=> $list_display,
				'current_stage'			=> $current_stage,
				'goto_hint'				=> t('goto_hint'),
				'wait_preview'			=> t('wait_preview'),
				'wait_slice'			=> t('wait_slice'),
				'wait_in_slice'			=> t('wait_in_slice'),
				'near_button'			=> t('near_button'),
				'far_button'			=> t('far_button'),
				'color_default'			=> PRINTERSTATE_VALUE_DEFAULT_COLOR,
				'preview_fail'			=> t('preview_fail'),
				'setmodel_fail'			=> t('setmodel_fail'),
				'scale_rotate_title'	=> t('scale_rotate_title'),
				'scale_title'			=> t('scale_title'),
				'rotate_title'			=> t('rotate_title'),
				'select_hint'			=> t('select_hint'),
				'rotate_x_title'		=> t('rotate_x_title'),
				'rotate_y_title'		=> t('rotate_y_title'),
				'rotate_z_title'		=> t('rotate_z_title'),
				'set_model_button'		=> t('set_model_button'),
				'reset_model_button'	=> t('reset_model_button'),
				'preset_title'			=> t('preset_title'),
				'slice_risk_confirm'	=> t('slice_risk_confirm'),
				'model_key_smax'		=> SLICER_TITLE_MAXSCALE,
				'model_key_xsize'		=> SLICER_TITLE_XSIZE,
				'model_key_ysize'		=> SLICER_TITLE_YSIZE,
				'model_key_zsize'		=> SLICER_TITLE_ZSIZE,
				'model_scale'			=> $current_scale,
				'model_xrot'			=> $current_xrot,
				'model_yrot'			=> $current_yrot,
				'model_zrot'			=> $current_zrot,
				'model_xsize'			=> $current_xsize,
				'model_ysize'			=> $current_ysize,
				'model_zsize'			=> $current_zsize,
				'model_smax'			=> $current_scale_max,
				'multi_part'			=> $multi_part ? 'true' : 'false',
		);
		
		$this->_parseBaseTemplate(t('sliceupload_slice_pagetitle'),
				$this->parser->parse('sliceupload/slice', $template_data, TRUE),
				'<!-- client rendering part -->
				<script type="text/javascript" src="/assets/rendering/ivmatrix3d.js"></script>
				<script type="text/javascript" src="/assets/rendering/ivwindow3d.js"></script>
				<script type="text/javascript" src="/assets/rendering/ivspace3d.js"></script>
				<script type="text/javascript" src="/assets/rendering/ivmtl3d.js"></script>
				<script type="text/javascript" src="/assets/rendering/ivmesh3d.js"></script>
				<script type="text/javascript" src="/assets/rendering/ivnode3d.js"></script>
				<script type="text/javascript" src="/assets/rendering/ivextra.js"></script>
				<script type="text/javascript" src="/assets/rendering/stl.js"></script>
				<script type="text/javascript" src="/assets/rendering/zpviewer.js"></script>');
		
		return;
	}
	
	function slicestatus() {
		$template_data = array();
		$ret_val = 0;
		$status_current = NULL;
		
		$this->load->helper('corestatus');
		$ret_val = CoreStatus_checkInIdle($status_current);
		// check status in slicing
		if ($ret_val != FALSE || $status_current != CORESTATUS_VALUE_SLICE) {
			$this->output->set_header('Location: /sliceupload/slice');
			return;
		}
		
		$this->load->library('parser');
		$this->lang->load('sliceupload/slice', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'wait_in_slice'			=> t('wait_in_slice'),
				'slice_percent_prefix'	=> t('slice_percent_prefix'),
				'slice_percent_suffix'	=> t('slice_percent_suffix'),
				'cancel_button'			=> t('cancel_button'),
				'wait_cancel'			=> t('wait_cancel'),
				'return_button'			=> t('return_home_button'),
				'slice_failmsg'			=> t('slice_failmsg'),
		);
		
		$this->_parseBaseTemplate(t('sliceupload_slice_pagetitle'),
				$this->parser->parse('sliceupload/slicestatus', $template_data, TRUE));
		
		return;
	}
	
	function restart() {
		$template_data = array();
		$in_boot = $this->input->get('inboot');
		
		$this->load->library('parser');
		$this->lang->load('sliceupload/upload', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'home'			=> t('home'),
				'check_in_boot'	=> $in_boot ? 'true' : 'false',
				'wait_msg'		=> $in_boot ? t('wait_in_boot') : t('wait_in_restart'),
		);
		
		$this->_parseBaseTemplate(t('sliceupload_slice_pagetitle'),
				$this->parser->parse('sliceupload/restart', $template_data, TRUE));
		
		return;
	}
	
	function reducesize() {
		$template_data = array();
		$id = 0;
		$xsize = 0;
		$ysize = 0;
		$zsize = 0;
		$scalemax = NULL;
		
		if (FALSE !== $this->input->get('error')) {
			$this->load->library('parser');
			$this->lang->load('sliceupload/upload', $this->config->item('language'));
			
			$template_data = array(
					'home'				=> t('home'),
					'back'				=> t('back'),
					'fail_message'		=> t('fail_message_tooBig'),
					'return_button'		=> t('return_button'),
			);
			
			$this->_parseBaseTemplate(t('sliceupload_slice_pagetitle'),
					$this->parser->parse('sliceupload/reducesize_fail', $template_data, TRUE));
			
			return;
		}
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$scale_set = (int) $this->input->post('sizepercentage');
			
			$id = (int) $this->input->post('id');
			$xsize = (float) $this->input->post('x');
			$ysize = (float) $this->input->post('y');
			$zsize = (float) $this->input->post('z');
			$scalemax = $this->input->post('ms');
		}
		else {
			$id = (int) $this->input->get('id');
			$xsize = (float) $this->input->get('x');
			$ysize = (float) $this->input->get('y');
			$zsize = (float) $this->input->get('z');
			$scalemax = $this->input->get('ms');
		}
		
		if ($scalemax === FALSE) {
			$this->output->set_header('Location: /sliceupload/upload');
			return;
		}
		
		// simple check of passing value
		if ($scalemax === FALSE || $xsize * $ysize * $zsize == 0) {
			$this->output->set_header('Location: /sliceupload/upload');
			return;
		}
		else {
			$scalemax = floor((float) $scalemax);
			if ($scalemax < 1) {
				$this->output->set_header('Location: /sliceupload/reducesize?error');
				return;
			}
		}
		
		$this->load->library('parser');
		$this->lang->load('sliceupload/upload', $this->config->item('language'));
		
		// parse the main body
		$template_data = array(
				'home'				=> t('home'),
				'back'				=> t('back'),
				'cancel_button'		=> t('cancel_button'),
				'max_percent'		=> $scalemax,
				'xsize'				=> $xsize,
				'ysize'				=> $ysize,
				'zsize'				=> $zsize,
				'id'				=> $id,
				'reducesize_title'	=> t('reducesize_title'),
				'reducesize_text'	=> t('reducesize_text'),
				'reducesize_scale'	=> t('reducesize_scale'),
				'reduced_size'		=> t('reduced_size'),
				'resize_button'		=> t('resize_button'),
		);
		
		$this->_parseBaseTemplate(t('sliceupload_slice_pagetitle'),
				$this->parser->parse('sliceupload/reducesize', $template_data, TRUE));
		//'<link rel="stylesheet" href="/assets/jquery-mobile-fluid960.min.css">'
		
		return;
	}
	
	function add_model_ajax() {
		$cr = ERROR_OK;
		$display = NULL;
		
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$filename = $this->input->post('file');
			if ($filename) {
				$array_model = json_decode($filename, TRUE);
				$number_model = count($array_model);
				if ($number_model) {
					$tmp_i = 0;
					
					for ($tmp_i = 0; $tmp_i < $number_model; $tmp_i++) {
						$array_model[$tmp_i] = $this->config->item('temp') . $array_model[$tmp_i];
						if (!file_exists($array_model[$tmp_i])) {
							$cr = ERROR_WRONG_PRM;
							break;
						}
					}
					
					if ($cr == ERROR_OK) {
						$array_return = array();
						
						$this->load->helper('slicer');
// 						$cr = Slicer_addModel($array_model);
						$cr = Slicer_addModel($array_model, TRUE, FALSE, $array_return);
						if ($cr == ERROR_OK) {
							try {
								if ($array_return[SLICER_TITLE_MAXSCALE] < 100) {
									$display = json_encode($array_return);
									$this->output->set_status_header(202);
									$this->output->set_content_type('txt_u');
									$this->load->library('parser');
									$this->parser->parse('plaintxt', array('display' => $display)); //optional
									
									return;
								}
							} catch (Exception $e) {
								$cr = ERROR_INTERNAL;
							}
						}
					}
				}
			}
			else {
				$cr = ERROR_MISS_PRM;
			}
		}
		else {
			$cr = ERROR_WRONG_PRM;
		}
		
		$display = $cr . " " . t(MyERRMSG($cr));
		$this->output->set_status_header($cr, $display);
		$this->output->set_content_type('txt_u');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		if ($cr != ERROR_OK) {
			$this->load->helper('printerlog');
			PrinterLog_logError('add model in slicer error, ' . $cr, __FILE__, __LINE__);
		}
		
		return;
	}
	
	function slice_model_ajax() {
		$cr = 0;
		$array_cartridge = array();
		$display = NULL;
		$id_preset = $this->input->get('id');
		$density = $this->input->get('density');
		$skirt = $this->input->get('skirt');
		$raft = $this->input->get('raft');
		$support = $this->input->get('support');
		$array_setting = array();
		$custom_change = FALSE;
		
		$this->load->helper('slicer');
		
		// set and load preset into slicer
		if ($id_preset) {
			if ($id_preset == 'previous') {
				$cr = ZimAPI_getPreset($id_preset);
			}
			else {
				$cr = ZimAPI_setPreset($id_preset);
			}
		}
		else {
			$cr = ERROR_MISS_PRM;
		}
		
		if ($cr == ERROR_OK) {
			$cr = Slicer_reloadPreset();
		}
		
		// load 4 extra parameters
		//TODO finish me (syntax in comment need be changed to function)
// 		if ($density !== FALSE) {
// 			if (FALSE == strpos('%', $density)) {
// 				$density = (float)$density;
// 				if ($density <= 0 || $density >= 1) {
// 					$cr = ERROR_MISS_PRM;
// 					break;
// 				}
// 			}
// 			$array_setting['fill_density'] = $density;
// 		}
// 		if ($skirt !== FALSE) {
// 			$array_setting['skirts'] = ((int)$skirt == 1) ? 1 : 0;
// 		}
// 		if ($raft !== FALSE) {
// 			$array_setting['raft_layers'] = ((int)$raft == 1) ? 1 : 0;
// 		}
// 		if ($support !== FALSE) {
// 			$array_setting['support_material'] = ((int)$support == 1) ? 1 : 0;
// 		}
// 		if (count($array_setting) == 0) {
// 			$cr = ERROR_MISS_PRM;
// 		}
// 		else if ($cr == ERROR_OK) {
// 			$cr = Slicer_changeParameter($array_setting);
// 		}
		
		// check platform and filament present (do not check filament quantity)
		if ($cr == ERROR_OK) {
			$cr = Slicer_checkPlatformColor($array_cartridge, $custom_change);
		}
		
		if ($cr == ERROR_OK) {
			$cr = Slicer_changeTemperByCartridge($array_cartridge);
		}
		
		// start slice command after checking filament
		if ($cr == ERROR_OK) {
			// we prefer to slice remotely except modified AMF
			if ($this->config->item('simulator')) {
				$custom_change = TRUE; // force local slicing for simulator
			}
			$cr = Slicer_slice(!$custom_change);
		}
		
		$display = $cr . " " . t(MyERRMSG($cr));
		$this->output->set_status_header($cr, $display);
		$this->output->set_content_type('txt_u');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		return;
	}
	
	function slice_status_ajax() {
		$ret_val = 0;
		$cr = 0;
		$array_data = array();
		$status_current = NULL;
		$display = NULL;
		
		$this->load->helper(array('printerstate', 'slicer'));
		$this->load->library('parser');
		
		$ret_val = CoreStatus_checkInIdle($status_current);
		if ($ret_val == TRUE) {
			$cr = 403;
			$this->output->set_status_header($cr);
			
			return;
		}
		$ret_val = PrinterState_checkBusyStatus($status_current, $array_data);
		if ($ret_val == TRUE && $status_current == CORESTATUS_VALUE_IDLE) {
			if (isset($array_data[PRINTERSTATE_TITLE_LASTERROR])) {
				$cr = $array_data[PRINTERSTATE_TITLE_LASTERROR];
			}
			else {
				$cr = ERROR_OK;
			}
		}
		else if ($ret_val == FALSE && $status_current == CORESTATUS_VALUE_SLICE) {
			if (!isset($array_data[PRINTERSTATE_TITLE_PERCENT])) {
				$this->load->helper('printerlog');
				PrinterLog_logError('can not find percentage in slicing', __FILE__, __LINE__);
				$cr = ERROR_INTERNAL;
			}
			else {
				$this->lang->load('sliceupload/slice_status_ajax', $this->config->item('language'));
				$array_display = array(
						'percent'	=> $array_data[PRINTERSTATE_TITLE_PERCENT],
						'message'	=> isset($array_data[PRINTERSTATE_TITLE_DETAILMSG])
								? t($array_data[PRINTERSTATE_TITLE_DETAILMSG]) : NULL
				);
				
				$cr = ERROR_OK;
				$this->output->set_status_header($cr);
				$this->output->set_content_type('jsonu');
				$this->parser->parse('plaintxt', array('display' => json_encode($array_display)));
				
				return;
			}
		}
		else {
			$this->load->helper('printerlog');
			PrinterLog_logError('unknown status in slicing', __FILE__, __LINE__);
			$cr = ERROR_INTERNAL;
			CoreStatus_setInIdle();
		}
		
		if (!in_array($cr, array(
				ERROR_OK, ERROR_INTERNAL,
				ERROR_LOW_RIGT_FILA, ERROR_LOW_LEFT_FILA,
				ERROR_MISS_RIGT_FILA, ERROR_MISS_LEFT_FILA,
				ERROR_MISS_RIGT_CART, ERROR_MISS_LEFT_CART,
		))) {
			$this->load->helper('printerlog');
			PrinterLog_logError('unknown return after slicing: ' . $cr, __FILE__, __LINE__);
			$cr = ERROR_INTERNAL;
		}
		
		if ($cr == ERROR_INTERNAL) {
			$this->output->set_status_header($cr);
		}
		else {
			$this->output->set_status_header(202);
		}
		$display = $cr . " " . t(MyERRMSG($cr));
		$this->output->set_content_type('txt_u');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		return;
	}
	
	function slice_result_ajax() {
		$template_data = array();
		$cr = ERROR_OK;
		$array_data = array();
		$check_filament = array();
		$change_filament = array();
		$data_json = array();
		$error = NULL;
		$bicolor_model = FALSE;
		$exchange_select_snd = FALSE;
		$bicolor_printer = ($this->config->item('nb_extruder') >= 2);
		$enable_print = 'false';
		$enable_exchange = 'disabled="disabled"'; // select disable
		$option_selected = 'selected="selected"';
		
		$this->load->helper(array('printerstate', 'slicer'));
		$this->load->library('parser');
		$this->lang->load('sliceupload/slice_status_ajax', $this->config->item('language'));
		
		$cr = PrinterState_getSlicedJson($data_json);
		
		if ($cr != ERROR_OK) {
			$display = $cr . " " . t(MyERRMSG($cr));
			$this->output->set_status_header($cr);
			$this->output->set_content_type('txt_u');
			$this->parser->parse('plaintxt', array('display' => $display)); //optional
			
			return;
		}
		else {
			$material = NULL;
			
			$check_filament = array('l' => t('filament_ok'), 'r' => t('filament_ok'));
			$change_filament = array('l' => t('change_filament'), 'r' => t('change_filament'));
			
			foreach (array('r', 'l') as $abb_filament) {
				$data_cartridge = array();
				$data_slice = array();
				$tmp_ret = 0;
				$volume_need = 0;
				
				if (isset($data_json[$abb_filament])) {
					$data_slice = $data_json[$abb_filament];
					if (isset($data_slice[PRINTERSTATE_TITLE_NEED_L])) {
						$volume_need = $data_slice[PRINTERSTATE_TITLE_NEED_L];
					}
				}
				else {
					$check_filament[$abb_filament] = t('filament_not_need');
				}
				
				// check mono extruder case (normally, it's not necessary)
				if ($bicolor_printer == FALSE && $abb_filament == 'l') {
					$tmp_ret = ERROR_MISS_LEFT_CART;
				}
				else {
					$tmp_ret = PrinterState_checkFilament($abb_filament, $volume_need, $data_cartridge);
				}
				
				if (in_array($tmp_ret, array(
						ERROR_OK, ERROR_MISS_LEFT_FILA, ERROR_MISS_RIGT_FILA,
						ERROR_LOW_LEFT_FILA, ERROR_LOW_RIGT_FILA,
				))) {
					$array_data[$abb_filament] = array(
							PRINTERSTATE_TITLE_COLOR		=> $data_cartridge[PRINTERSTATE_TITLE_COLOR],
							PRINTERSTATE_TITLE_EXT_TEMPER	=> $data_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER],
							PRINTERSTATE_TITLE_EXT_TEMP_1	=> $data_cartridge[PRINTERSTATE_TITLE_EXT_TEMP_1],
					);
					
					// set default temperature if pla
					if ($data_cartridge[PRINTERSTATE_TITLE_MATERIAL] == PRINTERSTATE_DESP_MATERIAL_PLA) {
						$array_data[$abb_filament][PRINTERSTATE_TITLE_EXT_TEMPER] = PRINTERSTATE_VALUE_FILAMENT_PLA_PRINT_TEMPER;
					}
				}
				else {
					$array_data[$abb_filament] = array(
							PRINTERSTATE_TITLE_COLOR		=> PRINTERSTATE_VALUE_DEFAULT_COLOR,
							PRINTERSTATE_TITLE_EXT_TEMPER	=> SLICER_VALUE_DEFAULT_TEMPER,
							PRINTERSTATE_TITLE_EXT_TEMP_1	=> SLICER_VALUE_DEFAULT_FIRST_TEMPER,
					);
				}
				$array_data[$abb_filament][PRINTERSTATE_TITLE_NEED_L] = $volume_need;
				
				// treat error
				switch ($tmp_ret) {
					case ERROR_OK:
						// do nothing if no error
						break;
						
					case ERROR_LOW_RIGT_FILA:
						
						$check_filament['r'] = t('filament_not_enough');
						break;
							
					case ERROR_MISS_RIGT_FILA:
						$check_filament['r'] = t('filament_unloaded');
						$change_filament['r'] = t('load_filament');
						break;
							
					case ERROR_MISS_RIGT_CART:
						$check_filament['r'] = t('filament_empty');
						$change_filament['r'] = t('load_filament');
						break;
							
					case ERROR_LOW_LEFT_FILA:
						$check_filament['l'] = t('filament_not_enough');
						break;
							
					case ERROR_MISS_LEFT_FILA:
						$check_filament['l'] = t('filament_unloaded');
						$change_filament['l'] = t('load_filament');
						break;
							
					case ERROR_MISS_LEFT_CART:
						$check_filament['l'] = t('filament_empty');
						$change_filament['l'] = t('load_filament');
						break;
							
					default:
						$this->load->helper('printerlog');
						PrinterLog_logError('unexpected return when generating slicing result: ' . $tmp_ret, __FILE__, __LINE__);
						
						// assign error message if necessary
						$check_filament[$abb_filament] = t('filament_error');
						break;
				}
				// assign $cr only when status is ok (acts like a flag of error)
				if ($cr == ERROR_OK && $volume_need > 0) {
					$cr = $tmp_ret;
				}
				
				// check material difference for all used cartridges
				if (!in_array($tmp_ret, array(
						ERROR_INTERNAL, ERROR_MISS_LEFT_CART, ERROR_MISS_RIGT_CART,
				)) && $volume_need > 0) {
					if ($material == NULL) {
						$material = $data_cartridge[PRINTERSTATE_TITLE_MATERIAL];
					}
					else if ($material != $data_cartridge[PRINTERSTATE_TITLE_MATERIAL]) {
						$error .= t('cartridge_material_diff_msg') . '<br>';
					}
					
// 					if ($volume_need > 0) { // act as count($data_slice), but with more verification
// 						if ($data_slice[PRINTERSTATE_TITLE_EXT_TEMPER] != $data_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER]) {
// 							$error .= t('temper_diff_msg',
// 									array(
// 											$data_slice[PRINTERSTATE_TITLE_EXT_TEMPER],
// 											$data_cartridge[PRINTERSTATE_TITLE_EXT_TEMPER],
// 									)
// 							) . '<br>';
// 						}
// 						if ($data_slice[PRINTERSTATE_TITLE_EXT_TEMP_1] != $data_cartridge[PRINTERSTATE_TITLE_EXT_TEMP_1]) {
// 							$error .= t('first_temper_diff_msg',
// 									array(
// 											$data_slice[PRINTERSTATE_TITLE_EXT_TEMP_1],
// 											$data_cartridge[PRINTERSTATE_TITLE_EXT_TEMP_1],
// 									)
// 							) . '<br>';
// 						}
// 					}
				}
			}
			if (!is_null($error)) {
				$error .= t('suggest_reslice');
			}
			
			// check enable print
			if ($cr == ERROR_OK) {
				$enable_print = 'true';
			}
			
			// check bicolor model
			if ($array_data['r'][PRINTERSTATE_TITLE_NEED_L] > 0 && $array_data['l'][PRINTERSTATE_TITLE_NEED_L] > 0) {
				$bicolor_model = TRUE;
			}
			else if ($array_data['r'][PRINTERSTATE_TITLE_NEED_L] > 0) {
				$exchange_select_snd = TRUE;
			}
			
			// check exchange possiblity
			if (ERROR_OK == PrinterState_checkFilaments(array(
					'l'	=> $array_data['r'][PRINTERSTATE_TITLE_NEED_L],
					'r'	=> $array_data['l'][PRINTERSTATE_TITLE_NEED_L],
			))) {
				$enable_exchange = NULL; // enable exchange if verification is passed
			}
			// display not enough even if filament is unused (in mono-color model)
			else if ($bicolor_model == FALSE) {
				if ($array_data['l'][PRINTERSTATE_TITLE_NEED_L] == 0) {
					$check_filament['l'] = t('filament_not_enough_for_switch');
				}
				else { // ($array_data['r'][PRINTERSTATE_TITLE_NEED_L] == 0)
					$check_filament['r'] = t('filament_not_enough_for_switch');
				}
			}
		}
		
		$template_data = array(
				'cartridge_c_l'		=> $array_data['l'][PRINTERSTATE_TITLE_COLOR],
				'cartridge_c_r'		=> $array_data['r'][PRINTERSTATE_TITLE_COLOR],
				'state_f_l'			=> $check_filament['l'],
				'state_f_r'			=> $check_filament['r'],
				'need_filament_l'	=> $array_data['l'][PRINTERSTATE_TITLE_NEED_L],
				'need_filament_r'	=> $array_data['r'][PRINTERSTATE_TITLE_NEED_L],
				'temper_l'			=> $array_data['l'][PRINTERSTATE_TITLE_EXT_TEMPER],
				'temper_r'			=> $array_data['r'][PRINTERSTATE_TITLE_EXT_TEMPER],
				'print_button'		=> t('print_button'),
				'left_temperature'	=> t('left_temperature'),
				'right_temperature'	=> t('right_temperature'),
				'chg_temperature'	=> t('chg_temperature'),
				'change_left'		=> $change_filament['l'],
				'change_right'		=> $change_filament['r'],
				'error_msg'			=> $error,
				'reslice_button'	=> t('reslice_button'),
				'exchange_extruder'	=> t('exchange_extruder'),
				'exchange_o1_val'	=> $exchange_select_snd ? 1 : 0,
				'exchange_o2_val'	=> $exchange_select_snd ? 0 : 1,
				'exchange_o2_sel'	=> $exchange_select_snd ? $option_selected : NULL,
				'exchange_o1'		=> t('exchange_left'),
				'exchange_o2'		=> t('exchange_right'),
				'advanced'			=> t('advanced'),
				'gcode_link'		=> t('gcode_link'),
				'2drender_link'		=> t('2drender_link'),
				'filament_not_need'	=> t('filament_not_need'),
				'filament_ok'		=> t('filament_ok'),
				'result_title' 		=> t('result_title'),
				'temper_max'		=> PRINTERSTATE_TEMPER_CHANGE_MAX,
				'temper_min'		=> PRINTERSTATE_TEMPER_CHANGE_MIN,
				'temper_delta'		=> PRINTERSTATE_TEMPER_CHANGE_VAL,
				'enable_print'		=> $enable_print,
				'enable_exchange'	=> $enable_exchange,
				'enable_reslice'	=> $error ? 'true' : 'false',
				'bicolor_printer'	=> $bicolor_printer ? 'true' : 'false',
				'bicolor_model'		=> $bicolor_model ? 'true' : 'false',
				'extrud_multiply'	=> t('extrud_multiply'),
				'left_extrud_mult'	=> t('left_extrud_mult'),
				'right_extrud_mult'	=> t('right_extrud_mult'),
				'extrud_r'			=> PRINTERSTATE_EXT_MULTIPLY_DEFAULT,
				'extrud_l'			=> PRINTERSTATE_EXT_MULTIPLY_DEFAULT,
				'extrud_min'		=> PRINTERSTATE_EXT_MULTIPLY_MIN,
				'extrud_max'		=> PRINTERSTATE_EXT_MULTIPLY_MAX,
		);
		$this->parser->parse('sliceupload/slice_result_ajax', $template_data);
		
		$this->output->set_status_header(202);
		
		return;
	}
	
	function preview_ajax() {
		$cr = 0;
		$path_image = NULL;
		$display = NULL;
		$rho = $this->input->get('rho');
		$theta = $this->input->get('theta');
		$delta = $this->input->get('delta');
		$color1 = $this->input->get('color_right');
		$color2 = $this->input->get('color_left');
		
		// check color input
		if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color1)) {
			$color1 = NULL;
		}
		if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color2)) {
			$color2 = NULL;
		}
		
		if ($rho === FALSE || $theta === FALSE || $delta === FALSE) {
			$cr = ERROR_MISS_PRM;
		}
		else if ((int)$rho < 0) {
			$cr = ERROR_WRONG_PRM;
		}
		else {
			$file_info = array();
			$file_cartridge = NULL;
			
			$this->load->helper('slicer');
			$cr = Slicer_rendering((int)$rho, (int)$theta, (int)$delta, $path_image, $color1, $color2);
			
			if ($cr == ERROR_OK) {
				//TODO add the possibility of making path everywhere, but not only in /var/www/tmp/
				$this->load->helper('file');
				$file_info = get_file_info(realpath($path_image), array('name'));
				$display = '/tmp/' . $file_info['name'] . '?' . time();
			}
		}
		
		if ($cr != ERROR_OK) {
			$display = $cr . " " . t(MyERRMSG($cr));
		}
		else if (!file_exists(realpath($path_image))) {
			// in the case: $cr == ERROR_OK 
			$cr = 202;
			$display = 'preview image unavailable';
		}
		$this->output->set_status_header($cr, ($cr != ERROR_OK) ? $display : 'ok');
		$this->output->set_content_type('txt_u');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display));
		
		return;
	}
	
	function preview_change_ajax() {
		$cr = 0;
		$display = NULL;
		
		$this->load->helper('slicer');
		$array_data = array(
				SLICER_PRM_ID		=> $this->input->get('id'),
				SLICER_PRM_XPOS		=> $this->input->get('xpos'),
				SLICER_PRM_YPOS		=> $this->input->get('ypos'),
				SLICER_PRM_ZPOS		=> $this->input->get('zpos'),
				SLICER_PRM_XROT		=> $this->input->get('xrot'),
				SLICER_PRM_YROT		=> $this->input->get('yrot'),
				SLICER_PRM_ZROT		=> $this->input->get('zrot'),
				SLICER_PRM_SCALE	=> $this->input->get('s'),
				SLICER_PRM_COLOR	=> $this->input->get('c'),
		);
		
		$cr = Slicer_setModel($array_data, $display);
		
		if ($cr != ERROR_OK) {
			$display = $cr . " " . t(MyERRMSG($cr));
			$this->output->set_status_header($cr, $display);
		}
		else {
			$this->output->set_status_header($cr);
		}
// 		http_response_code($cr);
		$this->output->set_content_type('txt_u');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		return;
	}
	
	function preview_reset_ajax() {
		$cr = 0;
		$display = NULL;
		$mid = $this->input->get('id');
		
		$this->load->helper('slicer');
		
		$cr = Slicer_resetModel($mid, $display);
		
		if ($cr != ERROR_OK) {
			$display = $cr . " " . t(MyERRMSG($cr));
			$this->output->set_status_header($cr, $display);
		}
		else {
			$this->output->set_status_header($cr);
		}
// 		http_response_code($cr);
		$this->output->set_content_type('txt_u');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		return;
	}
	
	function restart_ajax() {
		$ret_val = 0;
		$display = NULL;
		$action = $this->input->get('action');
		
		$this->load->helper('slicer');
		if (Slicer_checkOnline(FALSE)) {
			$this->output->set_status_header(202, 'Opened');
			
			return;
		}
		else if ($action) {
			$this->load->helper('printerlog');
			PrinterLog_logDebug('restarting slicer', __FILE__, __LINE__);
			
			Slicer_restart();
		}
		
		$display = 200 . " " . t(MyERRMSG(200));
		$this->output->set_status_header(200, $display);
		
		return;
	}
	
	function preset_prm_ajax() {
		$ret_val = 0;
		$display = NULL;
		$array_setting = array();
		$id = $this->input->get('id');
		
		$this->load->helper('zimapi');
		$this->load->library('parser');
		$ret_val = ZimAPI_getPresetSettingAsArray($id, $array_setting);
		
		if ($ret_val == ERROR_OK) {
			if (array_key_exists(ZIMAPI_TITLE_PRESET_INFILL, $array_setting)
					&& array_key_exists(ZIMAPI_TITLE_PRESET_SKIRT, $array_setting)
					&& array_key_exists(ZIMAPI_TITLE_PRESET_RAFT, $array_setting)
					&& array_key_exists(ZIMAPI_TITLE_PRESET_SUPPORT, $array_setting)) {
				$array_display = array();
				
				$array_display[ZIMAPI_TITLE_PRESET_INFILL]	= $array_setting[ZIMAPI_TITLE_PRESET_INFILL];
				$array_display[ZIMAPI_TITLE_PRESET_SKIRT]	= $array_setting[ZIMAPI_TITLE_PRESET_SKIRT];
				$array_display[ZIMAPI_TITLE_PRESET_RAFT]	= $array_setting[ZIMAPI_TITLE_PRESET_RAFT];
				$array_display[ZIMAPI_TITLE_PRESET_SUPPORT]	= $array_setting[ZIMAPI_TITLE_PRESET_SUPPORT];
				$display = json_encode($array_display);
				
				$this->output->set_status_header($ret_val, $display);
				$this->output->set_content_type('application/json; charset=UTF-8');
				$this->parser->parse('plaintxt', array('display' => $display));
				
				return;
			}
			else {
				$ret_val = ERROR_INTERNAL;
			}
		}
		
		$display = $ret_val . " " . t(MyERRMSG($ret_val));
		$this->output->set_status_header($ret_val, $display);
		$this->output->set_content_type('text/plain; charset=UTF-8');
		$this->parser->parse('plaintxt', array('display' => $display));
		
		return;
	}
	
	function gcode_ajax() {
		$cr = 0;
		$display = NULL;
		$status_array = array();
		
		$this->load->helper(array('printerstate', 'slicer'));
		
		$status_array = PrinterState_checkStatusAsArray();
		if ($status_array[PRINTERSTATE_TITLE_STATUS] == CORESTATUS_VALUE_SLICED) {
			$gcode_path = $this->config->item('temp') . SLICER_FILE_MODEL;
			
			if (file_exists($gcode_path)) {
// 				$this->load->helper('file');
// 				$this->output->set_content_type(get_mime_by_extension($gcode_path))->set_output(@file_get_contents($gcode_path));
// 				$this->load->helper('download');
// 				force_download('slice.gcode', @file_get_contents($gcode_path));
				$this->_sendFileContent($gcode_path, 'slice.gcode');
				
				return;
			}
			else {
				$cr = ERROR_INTERNAL;
			}
		}
		else {
			$cr = ERROR_EMPTY_PLATFORM;
		}
		
		$display = $cr . " " . t(MyERRMSG($cr));
		$this->output->set_status_header($cr, $display);
		// 		http_response_code($cr);
		$this->output->set_content_type('text/plain; charset=UTF-8');
		$this->load->library('parser');
		$this->parser->parse('plaintxt', array('display' => $display)); //optional
		
		return;
	}
}
