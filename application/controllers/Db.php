<?php

class Db extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        if (!$this->input->is_cli_request())
            die('Not Authorized');

    }

    public function index()
    {
        $this->load->library('migration');

        if (!$this->migration->current()) {
            echo($this->migration->error_string());
        }

    }

}

;
