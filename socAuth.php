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

}