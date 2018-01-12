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

    private function setTokenData($tokenLink){
        $this->socialToken = $tokenLink;
    }

    private function getTokenData(){
        return json_decode(file_get_contents($this->socialToken), true);
    }

    private function setUserData($userLink){
        $this->userData = $userLink;
    }

    private function getUserData(){
        return json_decode(file_get_contents($this->userData), true);
    }

    private function setSessionData(array $userData){
        if(count($userData) > 1){
            #Facebook user data
            if (!isset($userData['email']))
                $userFB['email'] = 'noMail';
            $this->sessionData = [
                'user'      => $userData['name'],
                'id'        => $userData['id'],
                'email'     => $userData['email']
            ];
        }
        else{
            #VC user data
            foreach ($userData as $firstIndex) {
                foreach ($firstIndex as $dataVC) {
                    $this->sessionData = [
                        'user'     => $dataVC['first_name'].' '.$dataVC['last_name'],
                        'id'       => $dataVC['uid'],
                        'email'    => 'noMail'
                    ];
                }
            }
        }
    }

    private function getSessionData(){
        return $this->sessionData;
    }

    private function newUserAutorization(array $sessionData){
        $newUserData['user_name']   = $sessionData['user'];
        $newUserData['user_id']     = $sessionData['id'];
        $newUserData['user_mail']  = $sessionData['email'];
        $newUserData['user_ip']     = $this->input->ip_address();

        $this->loginModel->addNewUserInDB($newUserData);
    }

}