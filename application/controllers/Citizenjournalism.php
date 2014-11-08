<?php
require_once(APPPATH.'libraries/REST_Controller.php'); 
include_once(APPPATH.'libraries/cj_service_wrapper.php');

class Citizenjournalism extends REST_Controller {
	public function authenticateSuperAdmin_post()
	{
		$uid = $this->post('user_id');
		$pwd = $this->post('password');
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->authenticateSuperAdmin($uid, $pwd);
        echo $this->response($result, 200);
    }  
	public function addMedia_post()
	{
		/*$inputs = array(
			'uname' => 124,
			'email' => 'test@info.com',
			'password' => 'test',
			'users'  => 15,
			'auth_permisions' => array( 'add')
		);*/
		$arrData = array();
		foreach($this->post(NULL, true) as $key=>$val){
			$arrData[$key] = trim($val);
		}
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->addMedia($arrData);
        echo $this->response($result, 200);
	}
	public function deleteMedia_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->deleteMedia($this->post('uname'));
        echo $this->response($result, 200);
	}
	public function updateMedia_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->updateMedia($this->post('uname'));
        echo $this->response($result, 200);
	}
	public function viewMedia_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->viewMedia();
        echo $this->response($result, 200);
	}
	public function authenticateReporter_post()
	{
		$uid = $this->post('user_id');
		$pwd = $this->post('password');
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->authenticateReporter($uid, $pwd);
        echo $this->response($result, 200);
    }  
	public function addReporter_post()
	{
		$uid = $this->post('user_id');
		$uname = $this->post('uname');
		$device = $this->post('device');
		$pwd = $this->post('password');
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->addReporter($uid, $uname, $pwd, $device);
        echo $this->response($result, 200);
	}
	public function deleteReporter_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->deleteReporter($this->post('user_id'), $this->post('uname'));
        echo $this->response($result, 200);
	}
	public function resetReporterPassword_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->resetReporterPassword($this->post('user_id'), $this->post('uname'), $this->post('password'));
        echo $this->response($result, 200);
	}
	public function resetReporterDevice_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->resetReporterDevice($this->post('user_id'), $this->post('uname'), $this->post('device'));
        echo $this->response($result, 200);
	}
	public function getReporterDevice_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->getReporterDevice($this->post('uname'));
        echo $this->response($result, 200);
	}
	public function viewReporter_post()
	{
		$wxWrap = new Cj_service_wrapper();
		$result = $wxWrap->viewReporter();
        echo $this->response($result, 200);
	}
	
}

?>
