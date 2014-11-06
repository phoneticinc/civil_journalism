<?php
class App_exception extends Exception
{
	private $strAppMessage;
	/*
	 * $strAppErrorMsg is message in App words
	 * 
	 * $strSystemErrorMsg is message in System words
	 * 
	 * $strSystemErrorCode is error code that system may return. It is optional.
	 * 
	 * */
	public function __construct($strAppErrorMsg = "", $strSystemErrorMsg = "", $strSystemErrorCode=0)
	{
		parent::__construct($strSystemErrorMsg, $strSystemErrorCode);
		$this->strAppMessage = $strAppErrorMsg;
	}
	public function __destruct()
	{
		unset($this->strAppMessage);
	}
	/*
	 * Use this method to get the message in App words
	 * */
	public function getAppMessage()
	{
		return $this->strAppMessage;
	}
	/*
	 * Use PHP's Exception class's getMessage(), for message in system words
	 * */ 
	/*
	 * Use PHP's Exception class's getCode(), for error code given by system
	 * */ 
}
?>