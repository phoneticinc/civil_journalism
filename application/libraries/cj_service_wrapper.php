<?php
require_once("file_logger_factory.php");
require_once("db_factory.php");
class Cj_service_wrapper {
	public function authenticateSuperAdmin($uid, $pwd)
	{
		try
		{
			$getSuperAdminQuery = "SELECT * FROM citizenjournalism.super_admin_login_Info where UserId=?";
			$arrSuperAdminDetailsResult = Db_factory::getInstance()->query($getSuperAdminQuery, array($uid),"ASSOC");
			if(count($arrSuperAdminDetailsResult) != 0){
				for($i=0;$i<count($arrSuperAdminDetailsResult);$i++){
					if($arrSuperAdminDetailsResult[$i]['UserId'] == $uid && $arrSuperAdminDetailsResult[$i]['Password'] == $pwd){
						return array('status' => 1);
					}
					else
					{
						return array('status' => 0, 'msg'=> 'Authentication Fail');
					}
				}
			}
			else{
				return array('status' => 0, 'msg'=> 'Details Not Found in Database');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to get Super Admin details ", "error");
			return array('error' => 'Unable to fetch Super Admin Details. Please try again.');
		}
	} 
	
}

?>
