<?php
require_once("file_logger.php");
class File_logger_factory
{
	private static $arrLoggerObjs;
	private static $nReqId;
	public static function &getInstance($strFileName = NULL)
	{
		if(!isset(File_logger_factory::$nReqId))
		{
			File_logger_factory::$nReqId = md5(microtime(TRUE).".".mt_rand());
		}
		if(is_array(File_logger_factory::$arrLoggerObjs))
		{
			if(!array_key_exists($strFileName, File_logger_factory::$arrLoggerObjs))
			{
				File_logger_factory::$arrLoggerObjs[$strFileName] =  new File_logger(File_logger_factory::$nReqId, $strFileName);
			}
		}
		else
		{
			File_logger_factory::$arrLoggerObjs[$strFileName] =  new File_logger(File_logger_factory::$nReqId, $strFileName);
		}
		return File_logger_factory::$arrLoggerObjs[$strFileName];
	}
}
?>