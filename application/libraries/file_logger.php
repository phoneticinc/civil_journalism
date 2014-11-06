<?php
require_once("logger.php");
class File_logger implements Logger
{
	private $strFilePath;
	private $nReqId;
	public function __construct($nReqId=NULL, $strFileName = NULL)
	{
		$ci = get_instance(); 
		$ci->load->config('config');
		$log = $ci->config->item('LOG_PATH');
		if(NULL != $strFileName)
		{
			$this->strFilePath = $log.$strFileName.'_'.date("Y-m-d", time()).".txt";
		}
		else
		{
			$this->strFilePath = $log."daylog-".date("Y-m-d", time()).".txt";
		}
		$this->nReqId = $nReqId;
	}
	/*
	public function __destruct()
	{
		unset($this->strFilePath);
		unset($this->nReqId);
	}
	There will be 2 or more static singleton objects: 1 or more file loggers and 1 or more Db instance objs.
	Once the request is catered. The PHP runtime destroys these objects by invoking their destructors. 
	There is no specifc order for calling destructors. Or they may be invoked in the reverse order of construction. 
	In both cases, it is possible that File logger's destructor is called before Db instance destructor. 
	In such scenario, since Db instance destructor uses File logger for logging the db connection closure info, 
	an error occurs; because logging is attempted without an alive logger object. 
	This problem got solved by removing the explicit destructor to File Logger.
	Since File Logger has no explicit destructor, I think Runtime is not calling a destructor on it. 
	There won't be any problem due the lack of destructor to File logger because, member variables of File logger 
	are only native variables, so they will just get unset.
	*/
    public function logMessage($strMsg, $strLevel = "ERROR")
	{
		$arrTraceHashes = debug_backtrace();
		$strCallerFile = $arrTraceHashes[0]["file"];
		$nCallerLine = $arrTraceHashes[0]["line"]; 
		$handleFile = NULL;
		if(file_exists($this->strFilePath))
		{
			$handleFile = fopen($this->strFilePath, "a+");
		}
		else
		{
			$handleFile = fopen($this->strFilePath, "a+");
			system("chmod 777 $this->strFilePath");					
		}
		if($handleFile)
		{
			if(fwrite($handleFile, strtoupper($strLevel)." -- ".date("Y-m-d H:i:s")." -- ".$this->nReqId." --> $strCallerFile:$nCallerLine $strMsg \r\n"))
			{					
				return TRUE;
			}
			else
			{
				return FALSE;
			}
			fclose($handleFile);
		}
		else
		{
			return FALSE;
		}
	}
}
?>
