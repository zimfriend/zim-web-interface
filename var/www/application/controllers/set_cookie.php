<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Set_cookie extends CI_Controller {
	public function index() {
		$this->load->helper('printerlog');
		
		if ($this->input->server('REQUEST_METHOD') == 'POST') {
			$token_post = $this->input->post('token');
			$token_json = json_decode($token_post, TRUE);
			
			PrinterLog_logDebug('remote token: ' . $token_post);
			if (is_array($token_json) && isset($token_json['token'])) {
				// new token system
				$this->input->set_cookie('auth', $token_json['token'], 0);
				$this->input->set_cookie('token_system', 'new', 1800); // 30 mins
				
				if (isset($token_json['redirect']) && is_array($token_json['redirect'])
						&& isset($token_json['redirect']['url'])) {
					$redirect_url = $token_json['redirect']['url'] . '?from=remote';
					
					// treat get parameter
					if (isset($token_json['redirect']['prm']) && is_array($token_json['redirect']['prm'])) {
						foreach ($token_json['redirect']['prm'] as $prm_key => $prm_val) {
							$redirect_url .= '&' . $prm_key . '=' . $prm_val;
						}
					}
					// treat cookie parameter
					if (isset($token_json['redirect']['cookie']) && is_array($token_json['redirect']['cookie'])) {
						$array_cookie = array();
						
						foreach ($token_json['redirect']['cookie'] as $cookie_key => $cookie_value) {
							$array_cookie[$cookie_key] = $cookie_value;
						}
						$this->input->set_cookie('redirectData', json_encode($array_cookie), 60); // 1 min
					}
					
					// filter outside redirection
					if ($redirect_url[0] != '/') {
						$redirect_url = '/' . $redirect_url;
					}
					
					$this->output->set_header('Location: ' . $redirect_url);
				}
			}
			else {
				// old token system
				$this->input->set_cookie('auth', $token_post, 0);
				$this->input->set_cookie('token_system', 'old', 1800); // 30 mins
				
				$this->output->set_header('Location: /');
			}
		}
		else {
			PrinterLog_logError("SetCookie: method != POST");
			echo '<script>alert("denied")</script>';
		}
	}
}