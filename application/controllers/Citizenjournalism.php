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
    }  //Function getAnimatedOverlayTimestamp_post ends here
}

?>
