<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Exceptions extends CI_Exceptions {

    public function __construct() {
        parent::__construct();
    }

    function show_404($page = '', $log_error = TRUE) {

        // By default we log this, but allow a dev to skip it
        if ($log_error) {
            log_message('error', '404 Page Not Found --> ' . $page);
        }

        $this->config = & get_config();
        $base_url = $this->config['base_url'];

        $_SESSION['error_message'] = 'Error message';
        header("location: " . $base_url);
        exit;
    }

}