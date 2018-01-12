<?php
defined('BASEPATH') OR exit('No direct script access allowed');

Class LoginSocial Extends CI_Controller{
    private $socialToken;
    private $userData;
    private $sessionData;

	function __construct(){
		parent::__construct();

		$this->load->model('loginModel');
		$this->load->model('sitedataModel');
		$this->load->library('accountLibrary');
	}

    public function index(){
        $siteData['checkLogin'] = FALSE;

        $siteData['userSession'] = $this->session->userdata('user');

        if(empty($siteData['userSession']))
            $this->accountLibrary->userAccount($siteData);
        else {
            $siteData['userSessionInfo'] = $this->session->userdata();
            $siteData['checkLogin'] = TRUE;
            $siteData['siteData'] = $this->sitedataModel->userSelect($this->session->userdata('id'));

            $this->accountLibrary->userAccount($siteData);
        }
    }

}