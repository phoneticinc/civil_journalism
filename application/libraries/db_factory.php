<?php
require_once("file_logger_factory.php");
require_once("mysqli_instance.php");
require_once("app_exception.php");
class Db_factory
{
	private static $arrInstanceObjs;
	public static function &getInstance($strDb="DEFAULT", $flagSecure=FALSE, $strType="MYSQLI")
	{
		switch($strType)
		{
			case "MYSQLI":
			{
				if(is_array(Db_factory::$arrInstanceObjs))
				{
					if(!array_key_exists("MYSQLI", Db_factory::$arrInstanceObjs) || !array_key_exists($strDb, Db_factory::$arrInstanceObjs["MYSQLI"]))
					{
						Db_factory::$arrInstanceObjs["MYSQLI"][$strDb] = new Mysqli_instance($strDb, $flagSecure);
					}
				}
				else
				{
					Db_factory::$arrInstanceObjs["MYSQLI"][$strDb] = new Mysqli_instance($strDb, $flagSecure);
				}
				//File_logger_factory::getInstance()->logMessage("MYSQLI $strDb instance (".Db_factory::$arrInstanceObjs["MYSQLI"][$strDb]->getInstanceId().") given", "INFO"); 
				return Db_factory::$arrInstanceObjs["MYSQLI"][$strDb];
				break;
			}
			default:
			{
				File_logger_factory::getInstance()->logMessage("Undefined DB instance type requested [$strType][$strDb]", "ERROR");				
				throw new App_exception($strType." is not defined", "Undefined DB instance type requested");
				break;
			}
		}
	}
	
	/*
	*@purpose: Prepares an insert query.
	*@name: buildInsertQuery
	*@params: $tableName, $arrData
	*@return: insert query.
	*/
	public static function buildInsertQuery($tableName, $arrData)
	{	
		//Prepare query
		$sql = "INSERT INTO ".$tableName." SET ";
		foreach ($arrData as $key => $value)
		{
			$sql .= " $key = ?,";
		}
		$sql = rtrim($sql, ',');
		
		return $sql;
	}
}
?>