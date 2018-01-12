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

    public function signIn($socialAuthorization){
        switch ($socialAuthorization){
            case 'fb':
                if(isset($_GET['code'])) {
                    $tokenLink = 'https://graph.facebook.com/v2.9/oauth/access_token?client_id='.FB_ID.'&redirect_uri='.FB_URL.'&client_secret='.FB_CODE.'&code='.$_GET['code'];
                    $this->setTokenData($tokenLink);
                    $tokenFB = $this->getTokenData();

                    if ($tokenFB) {
                        $userLink = 'https://graph.facebook.com/v2.9/me?client_id=' . FB_ID . '&redirect_uri=' . FB_URL . '&client_secret=' . FB_CODE . '&code=' . $_GET['code'] . '&access_token=' . $tokenFB['access_token'] . '&fields=id,name,email';
                        $this->setUserData($userLink);

                        $userFB = $this->getUserData();

                        if ($userFB) {
                            $this->setSessionData($userFB);
                            $sessionData = $this->getSessionData();
                            $this->session->set_userdata($sessionData);

                            if ($this->loginModel->checkUserIDinDB($sessionData['id']))
                                $this->newUserAutorization($sessionData);
                        }
                        else
                            redirect(base_url() . 'login');
                    }
                    else
                        redirect(base_url() . 'login');
                }
                break;

            case 'vc':
                if(isset($_GET['code'])) {
                    $tokenLink = 'https://oauth.vk.com/access_token?client_id='.VC_ID.'&redirect_uri='.VC_URL.'&client_secret='.VC_SECRET.'&code='.$_GET['code'];
                    $this->setTokenData($tokenLink);
                    $tokenVC = $this->getTokenData();

                    if ($tokenVC) {
                        $userLink = 'https://api.vk.com/method/users.get?user_id='.$tokenVC['user_id'].'&access_token='.$tokenVC['access_token'].'&fields=uid,first_name,last_name,photo_big';
                        $this->setUserData($userLink);

                        $userVC = $this->getUserData();

                        if ($userVC) {
                            $this->setSessionData($userVC);
                            $sessionData = $this->getSessionData();
                            $this->session->set_userdata($sessionData);

                            if ($this->loginModel->checkUserIDinDB($sessionData['id']))
                                $this->newUserAutorization($sessionData);
                        }
                        else
                            redirect(base_url() . 'login');
                    }
                    else
                        redirect(base_url() . 'login');
                }
                break;

            default:
                redirect(base_url() . 'login');
        }
        redirect(base_url() . 'login');
    }

    public function logout(){
        $this->session->unset_userdata();
        redirect(base_url());
    }

}
