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
	public function addMedia($data)
	{
		try
		{
			$insertSuperAdminQuery = "REPLACE INTO citizenjournalism.cj_user_profile(UserId, EmailId, Password, Users, Authorized_permesions) VALUES(?, ?, ?, ?, ?)";
			//$insertReporterQuery = "REPLACE INTO `citizenjournalism`.CJ_User_Login_Info (UserId, Password) VALUES(?, ?)";
			$arrSuperAdminDetailsResult = Db_factory::getInstance()->query($insertSuperAdminQuery, array($data['uname'],  $data['email'], $data['password'], $data['users'], json_encode($data['auth_permisions'])));
			//$arrReporterDetailsResult = Db_factory::getInstance()->query($insertReporterQuery, array($data['uname'], $data['password']));
			if($arrSuperAdminDetailsResult != -1)
			{
				return array('status' => 1);
			}
			else{
				return array('status' => 0, 'msg'=> 'Please Check The Data');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to Insert Super Admin details: ".$logError, "error");
			return array('error' => 'Unable to INSERT Super Admin Details:'.$logError);
		}
	}
	public function deleteMedia($uname)
	{
		try
		{
			$deleteSuperAdminQuery = "DELETE FROM citizenjournalism.cj_user_profile WHERE UserId = ?";
			$deleteReporterQuery = "DELETE FROM `citizenjournalism`.CJ_User_Login_Info WHERE UserId = ?";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($deleteReporterQuery, array($uname));
			$arrSuperAdminDetailsResult = Db_factory::getInstance()->query($deleteSuperAdminQuery, array($uname));
			if($arrSuperAdminDetailsResult != -1 && $arrReporterDetailsResult != -1)
			{
				return array('status' => 1);
			}
			else{
				return array('status' => 0, 'msg'=> 'Please Check The Data');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to DELETE Super Admin details: ".$logError, "error");
			return array('error' => 'Unable to DELETE Super Admin Details:'.$logError);
		}
	}
	public function updateMedia($uname)
	{
		try
		{
			$updateSuperAdminQuery = "SELECT * FROM citizenjournalism.cj_user_profile WHERE UserId = ?";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($updateSuperAdminQuery, array($uname), "ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				return $arrReporterDetailsResult;
			}
			else{
				return array('status' => 0, 'msg'=> 'Details Not Found in Database');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to update Super Admin details: ".$logError, "error");
			return array('error' => 'Unable to Update Super Admin Details:'.$logError);
		}
	}
	public function viewMedia()
	{
		try
		{
			$viewSuperAdminQuery = "SELECT * FROM citizenjournalism.cj_user_profile";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($viewSuperAdminQuery, NULL, "ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				return $arrReporterDetailsResult;
			}
			else{
				return array('status' => 0, 'msg'=> 'No Records Found in Database');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to retrive Super Admin details: ".$logError, "error");
			return array('error' => 'Unable to retrive Super Admin Details:'.$logError);
		}
	}
	public function authenticateReporter($uid,$pwd)
	{
		try
		{
			$getReporterQuery = "SELECT * FROM `citizenjournalism`.cj_user_profile where UserId=?";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($getReporterQuery, array($uid),"ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				for($i=0;$i<count($arrReporterDetailsResult);$i++){
					if($arrReporterDetailsResult[$i]['UserId'] == $uid && $arrReporterDetailsResult[$i]['Password'] == $pwd){
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
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to get Reporter details ", "error");
			return array('error' => 'Unable to Reporter Details. Please try again.');
		}
	}
	public function userPermesions($uid)
	{
		try
		{
			$userPermesionsQuery = "SELECT * FROM `citizenjournalism`.cj_user_profile where UserId=?";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($userPermesionsQuery, array($uid),"ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				return $arrReporterDetailsResult[0]['Authorized_permesions'];
			}
			else{
				return false;
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to get Reporter details ", "error");
			return array('error' => 'Unable to Reporter Details. Please try again.');
		}
	}
	public function userCount($uid)
	{
		try
		{
			$userPermesionsQuery = "SELECT * FROM `citizenjournalism`.cj_user_profile where UserId=?";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($userPermesionsQuery, array($uid),"ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				$users = $arrReporterDetailsResult[0]['Users'];
				$userCountQuery = "SELECT count(*) as count FROM `citizenjournalism`.CJ_User_Login_Info  WHERE UserId=?";
				$userCountDetailsResult = Db_factory::getInstance()->query($userCountQuery, array($uid),"ASSOC");
				$count = $userCountDetailsResult[0]['count'];
				if($count <= $users)
				{
					return true;
				}
				else{
					return false;
				}
			}
			else{
				return array('status' => 0, 'msg'=> 'Details Not Found in Database');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to get Reporter details ", "error");
			return array('error' => 'Unable to Reporter Details. Please try again.');
		}
	}
	public function addReporter($uid, $uname, $pwd, $device)
	{
		$permesions = json_decode($this->userPermesions($uid),true);
		$permesion_flag = false;
		if($permesions){
			foreach($permesions as $val)
			{
				if(strcasecmp($val, 'add') == 0){
					$permesion_flag = true;
				}
			}
		}
		$count = $this->userCount($uid);
		if($permesion_flag && $count){
			try
			{
				$insertReporterQuery = "INSERT INTO citizenjournalism.CJ_User_Login_Info (UserId, UserName, Password, DeviceName) VALUES(?, ?, ?, ?)";
				$arrReporterDetailsResult = Db_factory::getInstance()->query($insertReporterQuery, array($uid, $uname, $pwd, $device));
				if($arrReporterDetailsResult != -1)
				{
					return array('status' => 1);
				}
				else{
					return array('status' => 0, 'msg'=> 'Please Check The Data');
				}
			}
			catch(App_exception $e)
			{
				$logError = $e->getMessage();
				File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to Insert Reporter  details: ".$logError, "error");
				return array('error' => 'Unable to INSERT Reporter  Details:'.$logError);
			}
		}
		else{
			return array('status' => 0, 'msg'=> 'Please check User Permesions or Number of users ');
		}
	}
	public function deleteReporter($uid, $uname)
	{
		$permesions = json_decode($this->userPermesions($uid),true);
		$permesion_flag = false;
		if($permesions){
			foreach($permesions as $val)
			{
				if(strcasecmp($val, 'delete') == 0){
					$permesion_flag = true;
				}
			}
		}
		if($permesion_flag){
			try
			{
				$deleteReporterQuery = "DELETE FROM `citizenjournalism`.CJ_User_Login_Info WHERE UserName = ?";
				$arrReporterDetailsResult = Db_factory::getInstance()->query($deleteReporterQuery, array($uname));
				if($arrReporterDetailsResult != -1)
				{
					return array('status' => 1);
				}
				else{
					return array('status' => 0, 'msg'=> 'Please Check The Data');
				}
			}
			catch(App_exception $e)
			{
				$logError = $e->getMessage();
				File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to DELETE Reporter details: ".$logError, "error");
				return array('error' => 'Unable to DELETE Reporter Details:'.$logError);
			}
		}
		else{
			return array('status' => 0, 'msg'=> 'Please check User Permesions');
		}
	}
	public function resetReporterPassword($uid ,$uname, $pwd)
	{
		$permesions = json_decode($this->userPermesions($uid),true);
		$permesion_flag = false;
		if($permesions){
			foreach($permesions as $val)
			{
				if(strcasecmp($val, 'modify') == 0){
					$permesion_flag = true;
				}
			}
		}
		if($permesion_flag){
			try
			{
				$deleteReporterQuery = "UPDATE `citizenjournalism`.CJ_User_Login_Info SET Password= ? WHERE UserName = ?";
				$arrReporterDetailsResult = Db_factory::getInstance()->query($deleteReporterQuery, array($pwd, $uname));
				if($arrReporterDetailsResult != -1)
				{
					return array('status' => 1);
				}
				else{
					return array('status' => 0, 'msg'=> 'Please Check The Data');
				}
			}
			catch(App_exception $e)
			{
				$logError = $e->getMessage();
				File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to UPDATE Reporter details: ".$logError, "error");
				return array('error' => 'Unable to UPDATE Reporter Details:'.$logError);
			}
		}
		else{
			return array('status' => 0, 'msg'=> 'Please check User Permesions');
		}
		
	}
	public function resetReporterDevice($uid ,$uname, $device)
	{
		$permesions = json_decode($this->userPermesions($uid),true);
		$permesion_flag = false;
		if($permesions){
			foreach($permesions as $val)
			{
				if(strcasecmp($val, 'modify') == 0){
					$permesion_flag = true;
				}
			}
		}
		if($permesion_flag){
			try
			{
				$deleteReporterQuery = "UPDATE `citizenjournalism`.CJ_User_Login_Info SET DeviceName= ? WHERE UserName = ?";
				$arrReporterDetailsResult = Db_factory::getInstance()->query($deleteReporterQuery, array($device, $uname));
				if($arrReporterDetailsResult != -1)
				{
					return array('status' => 1);
				}
				else{
					return array('status' => 0, 'msg'=> 'Please Check The Data');
				}
			}
			catch(App_exception $e)
			{
				$logError = $e->getMessage();
				File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to UPDATE Reporter details: ".$logError, "error");
				return array('error' => 'Unable to UPDATE Reporter Details:'.$logError);
			}
		}
		else{
			return array('status' => 0, 'msg'=> 'Please check User Permesions');
		}
	}
	public function getReporterDevice($uname)
	{
		try
		{
			$viewSuperAdminQuery = "SELECT DeviceName FROM citizenjournalism.CJ_User_Login_Info WHERE UserName = ?";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($viewSuperAdminQuery, array($uname), "ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				return $arrReporterDetailsResult;
			}
			else{
				return array('status' => 0, 'msg'=> 'No Records Found in Database');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to retrive Super Admin details: ".$logError, "error");
			return array('error' => 'Unable to retrive Super Admin Details:'.$logError);
		}
	}
	public function viewReporter()
	{
		try
		{
			$viewSuperAdminQuery = "SELECT * FROM citizenjournalism.CJ_User_Login_Info";
			$arrReporterDetailsResult = Db_factory::getInstance()->query($viewSuperAdminQuery, NULL, "ASSOC");
			if(count($arrReporterDetailsResult) != 0){
				return $arrReporterDetailsResult;
			}
			else{
				return array('status' => 0, 'msg'=> 'No Records Found in Database');
			}
		}
		catch(App_exception $e)
		{
			$logError = $e->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to retrive Super Admin details: ".$logError, "error");
			return array('error' => 'Unable to retrive Super Admin Details:'.$logError);
		}
	}
	
}

?>
