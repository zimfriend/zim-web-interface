<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');


class Test_video extends CI_Controller {
	
	public function index() {
		$this->load->helper('zimapi');
		ZimAPI_cameraOn(ZIMAPI_PRM_CAMERA_PRINTSTART);
		
		$this->load->library('parser');
		$this->parser->parse('test_video', array('video_url' => $this->config->item('video_url')));
		
		return;
	}

}
