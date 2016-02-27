<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

// run the following code before run this function (optional)
// $this->lang->load('timedisplay', $this->config->item('language'));
$CI = &get_instance();
$CI->lang->load('timedisplay', $CI->config->item('language'));

function TimeDisplay__convertsecond($second, $prefix, $unknown = 'N/A') {
	$display = '';
	
	if ($second >= (60*60)) {
		$display = $prefix . t('%dh %dm %ds',
				array($second / (60*60), //h
						($second / 60) % 60, //m
						$second % 60, //s
				)
		);
	}
	else if ($second >= 60) {
		$display = $prefix . t('%dm %ds',
				array($second / 60, //m
						$second % 60, //s
				)
		);
	}
	else if ($second > 0) {
		$display = $prefix . t('%ds', array($second));
	}
	else {
		$display = $prefix . $unknown;
	}
	
	return $display;
}
