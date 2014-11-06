<?php
require_once("file_logger_factory.php");
require_once("db_factory.php");
class Weather_service_wrapper {
	public function getAnimatedOverlayTimestamp($name)
	{
		try
		{
			$getFlightplanDetailsQuery = "SELECT * FROM temp.sua_info s";
			$arrFlightplanDetailsResult = Db_factory::getInstance()->query($getFlightplanDetailsQuery);
		}
		catch(App_exception $gdcException)
		{
			$logError = $gdcException->getMessage();
			File_logger_factory::getInstance("ipad_services_log")->logMessage("Unable to get sua details ", "error");
			return array('error' => 'Unable to fetch Route Weather. Please try again.');
		}
		
		return array('name' => $name);
    }  //Function getAnimatedOverlayTimestamp ends here
	
}

?>
