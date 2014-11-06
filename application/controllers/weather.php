<?php
require_once(APPPATH.'libraries/REST_Controller.php'); 
include_once(APPPATH.'libraries/Weather_service_wrapper.php');

class weather extends REST_Controller {
	public function getTest_post()
	{
		$name = $this->post('name');
		$wxWrap = new Weather_service_wrapper();
		$result = $wxWrap->getAnimatedOverlayTimestamp($name);
        echo $this->response($result, 200);
    }  //Function getAnimatedOverlayTimestamp_post ends here
}

?>
