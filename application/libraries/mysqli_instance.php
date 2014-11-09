<?php
require_once("file_logger_factory.php");
require_once("db_instance.php");
class Mysqli_instance implements Db_instance
{
	private $objMysqli;
	private $strRandomId;
	private $dbQueryTime;
	private $strHost;
	private $strUser;
	private $strPwd; 
	private $flagSecure;
	private function connectDb($strDb=NULL, $flagSecure=FALSE)
	{
		switch($strDb)
		{
			case "DEV":
			{
				$this->strHost=DEV_HOSTNAME;
				$this->strUser=DEV_USERNAME;
				$this->strPwd=DEV_PASSWORD;
				break;									
			}
			default:
			{
				$ci = get_instance(); 
				$ci->load->config('config');
				$this->strHost=$ci->config->item('DEFAULT_HOSTNAME');
				$this->strUser=$ci->config->item('DEFAULT_USERNAME');
				$this->strPwd=$ci->config->item('DEFAULT_PASSWORD');
				echo 	$this->strHost." ".$this->strUser." ".$this->strPwd;
				break;
			}
		}
		$this->strRandomId = md5(microtime(true)."MYSQLI".$this->strHost.$this->strUser);
		$this->objMysqli = mysqli_init();
		if($flagSecure)
		{
			mysqli_options($this->objMysqli, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, TRUE);
			if($this->objMysqli->ssl_set(NULL, NULL, SSL_CERT_LOC, NULL, NULL))
			{
				$this->flagSecure=TRUE;
				File_logger_factory::getInstance()->logMessage("Successfully set SSL certificate while making MYSQLI connection ".$this->strUser."@".$this->strHost." [".$this->strRandomId."]", "error");
			}
			else
			{
				$this->flagSecure=FALSE;
				File_logger_factory::getInstance()->logMessage("Failed to set SSL certificate while making MYSQLI connection ".$this->strUser."@".$this->strHost." [".$this->strRandomId."]", "error");
			}
		}
		if($this->objMysqli->real_connect($this->strHost, $this->strUser, $this->strPwd))
		{
			File_logger_factory::getInstance()->logMessage("Opened persistent MYSQLI".($this->flagSecure?" SSL ":" ")."connection ".$this->strUser."@".$this->strHost." [".$this->strRandomId."]", "info");					
		}
		else
		{
			File_logger_factory::getInstance()->logMessage("Failed to open persistent MYSQLI".($this->flagSecure?" SSL ":" ")."connection ".$this->strUser."@".$this->strHost." [".$this->strRandomId."]", "error");
		}
	}
	private function disconnectDb()
	{
		$this->objMysqli->close();
		File_logger_factory::getInstance()->logMessage("Closed MYSQLI".($this->flagSecure?" SSL ":" ")."connection ".$this->strUser."@".$this->strHost." [".$this->strRandomId."]", "info");
	}
	public function __construct($strDb=NULL, $flagSecure=FALSE)
	{
		$this->connectDb($strDb, $flagSecure);
	}
	public function __destruct()
	{
		$this->disconnectDb();
		unset($this->objMysqli);
		unset($this->strRandomId);
		unset($this->dbQueryTime);
		unset($this->strHost);
		unset($this->strUser);
		unset($this->strPwd);
		unset($this->flagSecure);
	}
	public function getInstanceId()
	{
		return $this->strRandomId;
	}
	public function sanitize($data)
	{
		if($data && is_array($data))
		{
			$arrStrs = array();
			foreach($data as $key => $value)
			{
				$arrStrs[$key] = $this->objMysqli->real_escape_string((string)$value);
			}
			return $arrStrs;
		}
		else
		{
			return $this->objMysqli->real_escape_string((string)$data);
		}
	}
	public function query($strQuery, $arrParams=NULL, $flagSelectOutputFormat="ASSOC", &$nLastInsertIds=NULL)
	{
		if($arrParams)
		{
			$objPrepStmt = $this->objMysqli->prepare($strQuery);
			if(FALSE == $objPrepStmt)
			{
				$arrTraceHashes = debug_backtrace();
				$strCallerFile = $arrTraceHashes[0]["file"];
				$nCallerLine = $arrTraceHashes[0]["line"];
				$strAppMsg = "(i) Using incorrect DB or (ii) Table not present in DB or (iii) Incorrect query";
				$strErrMsg = "Mysqli errno: ".$this->objMysqli->errno."; Mysqli error: ".$this->objMysqli->error."; Query: ".$strQuery."; File: ".$strCallerFile."; Line: ".$nCallerLine;
				throw new App_exception($strAppMsg, $strErrMsg);
			}
			if(is_array($arrParams))
			{
				$flagArrayType = -1;
				foreach($arrParams as $arrParamsRow)
				{
					if(is_array($arrParamsRow))
					{
						foreach($arrParamsRow as $arrParamsSubRow)
						{
							if(is_array($arrParamsSubRow))
							{
								// $arrParams is an array with more than 2 dimensions: not allowed
								$flagArrayType = 3;
								break;
							}
							else
							{
								// $arrParams is a 2D array of arrays of literal types
								$flagArrayType = 2;
								break;
							}
						}
						break;
					}
					else
					{
						// $arrParams is a 1D array of literal types
						$flagArrayType = 1;			
						break;
					}
				}
				switch($flagArrayType)
				{
					case 1:
					{
						// 1D array passed
								
						// step 1 santizing data and forming types string. (all types taken as strings)
						$strBindTypes = "";
						foreach($arrParams as $key => $value)
						{
							$arrParams[$key] = (string)$value;
							$strBindTypes .= "s";
						}
						// step 2 binding
						// bind_param is a var arg method, it expects params as args not in an array
						// we have bind params in an array, so we have to use call_user_func_array
						array_unshift($arrParams, $strBindTypes);
						$arrRefsForBindParams = array();
						foreach($arrParams as $key=>$value)
						{
							$arrRefsForBindParams[] = &$arrParams[$key];
						}
						call_user_func_array(array($objPrepStmt, "bind_param"), $arrRefsForBindParams);
						if(strlen($strQuery)<3000)
                        {	
                            if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
                            {                    
							    File_logger_factory::getInstance()->logMessage($strQuery." [".$this->strRandomId."]", "info");
                            }
						}
						else
						{
                            if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
                            {
							    File_logger_factory::getInstance()->logMessage(substr($strQuery, 0, 3000)."... rest truncated in log [".$this->strRandomId."]", "info");
                            }
                        }

                        if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
                        {
						    File_logger_factory::getInstance()->logMessage(json_encode($arrParams), "info");
                        }

						$execTime = microTime(true);
						$objPrepStmt->execute();
						$execTime = round(microTime(true) - $execTime, 6) ;
						$this->dbQueryTime = $this->dbQueryTime + $execTime;                        
                        $arrTraceHashes = debug_backtrace();
                        if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
                        {
                            File_logger_factory::getInstance('db_query')->logMessage('File: '.$arrTraceHashes[0]["file"].':'.$arrTraceHashes[0]["line"].' Query: '.$strQuery.' Time: '.$execTime.', '.$this->dbQueryTime, "info");
                        }

						////////////////////////////////////////////////////////////////////////////////
						// start of code using result set meta data and 
						// binding array of references of array elements to hold result rows
						////////////////////////////////////////////////////////////////////////////////
						if($objMetadata = $objPrepStmt->result_metadata())
						{
							// The query is a select type query
							$objPrepStmt->store_result();
							$arrMetadataFields = $objMetadata->fetch_fields();
							$arrRefsOfFieldsForResult = array();
							$arrRefsOfFieldsForResult = array();
							$arrResultSet = array();
							$nIterator = 0;
							foreach($arrMetadataFields as $objField)
							{
								switch($flagSelectOutputFormat)
								{
									case "ASSOC":
									{
										$arrRefsOfFieldsForResult[] = &$arrFieldsForResult[$objField->name];
										break;
									}
									case "ARRAY":
									default:
									{
										$arrRefsOfFieldsForResult[] = &$arrFieldsForResult[$nIterator];
										break;
									}
								}
								$nIterator++;
							}
							call_user_func_array(array($objPrepStmt, "bind_result"), $arrRefsOfFieldsForResult);
							$nIter = 0;
							while($objPrepStmt->fetch())
							{
								$arrResultSet[$nIter] = array();
								foreach($arrFieldsForResult as $k=>$v)
								{
									$arrResultSet[$nIter][$k] = $v;
								}
								$nIter++;
							}
							$objPrepStmt->free_result();
							return $arrResultSet;
						}
						else
						{
							// The query is a non-select type query
							$nLastInsertIds=$this->objMysqli->insert_id;
							return $objPrepStmt->affected_rows;	
						}
						////////////////////////////////////////////////////////////////////////////////
						// end of code using result set meta data
						// binding array of references of array elements to hold result rows				
						////////////////////////////////////////////////////////////////////////////////
						/*
						if(method_exists($objPrepStmt, "get_result"))
						{
							//get_result method is supported						
						}
						else
						{
							//get_result method is not supported						
						}
						////////////////////////////////////////////////////////////////////////////////
						// start of code using get_result 
						// This method is currently in PHP svn only. Use this when it gets released.
						////////////////////////////////////////////////////////////////////////////////
						$objResultSet = $objPrepStmt->get_result();
						if($objResultSet)
						{
							// The query is a select type query
							switch($flagSelectOutputFormat)
							{
								case "ASSOC":
								{
									return $objResultSet->fetch_all(MYSQLI_ASSOC); // PHP version 5.3.6+
									break;
								}
								case "ARRAY":
								default:
								{
									return $objResultSet->fetch_all(MYSQLI_NUM); // PHP version 5.3.6+
									break;
								}
							}
						}
						else
						{
							// The query is a non-select type query
							if(NULL!=$nLastInsertIds)
							{
								$nLastInsertIds=$this->objMysqli->insert_id;
							}
							return $objPrepStmt->affected_rows;					
						}
						////////////////////////////////////////////////////////////////////////////////
						// end of code using get_result 
						// This method is currently in PHP svn only. Use this when it gets released.
						////////////////////////////////////////////////////////////////////////////////						
						*/
						break;
					}
					case 2:
					{
						$arrReturn = array();
						$nLastInsertIds = array();
						// 2D array passed (e.g. for multiple row inserts/updates with a single prepare)
						
						foreach($arrParams as $nKey => $arrRowParams)
						{
							// step 1 santizing data and forming types string. (all types taken as strings)
							$strBindTypes = "";
							foreach($arrRowParams as $key => $value)
							{
								$arrRowParams[$key] = (string)$value;
								$strBindTypes .= "s";
							}
							
							// step 2 binding
							// bind_param is a var arg method, it expects params as args not in an array
							// we have bind params in an array, so we have to use call_user_func_array
							array_unshift($arrRowParams, $strBindTypes);
							$arrRefsForBindParams = array();
							foreach($arrRowParams as $key=>$value)
							{
								$arrRefsForBindParams[] = &$arrRowParams[$key];
							}
							call_user_func_array(array($objPrepStmt, "bind_param"), $arrRefsForBindParams);

							$execTime = microTime(true);
							$objPrepStmt->execute();
							$execTime = round(microTime(true) - $execTime, 6);
							$this->dbQueryTime = $this->dbQueryTime + $execTime;                        
                            $arrTraceHashes = debug_backtrace();

                            if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
                            {
							    File_logger_factory::getInstance('db_query')->logMessage('File: '.$arrTraceHashes[0]["file"].':'.$arrTraceHashes[0]["line"].' Query: '.$strQuery.' Time: '.$execTime.', '.$this->dbQueryTime, "info");
                            }
							
							if($objMetadata = $objPrepStmt->result_metadata())
							{
								// The query is a select type query
								$objPrepStmt->store_result();
								$arrMetadataFields = $objMetadata->fetch_fields();
								$arrRefsOfFieldsForResult = array();
								$arrRefsOfFieldsForResult = array();
								$arrResultSet = array();
								$nIter = 0;
								foreach($arrMetadataFields as $objField)
								{
									switch($flagSelectOutputFormat)
									{
										case "ASSOC":
										{
											$arrRefsOfFieldsForResult[] = &$arrFieldsForResult[$objField->name];
											break;
										}
										case "ARRAY":
										default:
										{
											$arrRefsOfFieldsForResult[] = &$arrFieldsForResult[$nIter];
											break;
										}
									}
									$nIter++;
								}
								call_user_func_array(array($objPrepStmt, "bind_result"), $arrRefsOfFieldsForResult);
								$nIter = 0;
								while($objPrepStmt->fetch())
								{
									$arrResultSet[$nIter] = array();
									foreach($arrFieldsForResult as $k=>$v)
									{
										$arrResultSet[$nIter][$k] = $v;
									}
									$nIter++;
								}
								$arrReturn[$nKey]=$arrResultSet;
								$objPrepStmt->free_result();
							}
							else
							{
								$arrReturn[$nKey]=$objPrepStmt->affected_rows;
								$nLastInsertIds[$nKey]=$this->objMysqli->insert_id;
							}
						}
						return $arrReturn;
					}
					default:
					{
						$arrTraceHashes = debug_backtrace();
						$strCallerFile = $arrTraceHashes[0]["file"];
						$nCallerLine = $arrTraceHashes[0]["line"];
						$strAppMsg = "Improper query params input";
						$strErrMsg = "Query params array is neither proper 1D nor proper 2D. Params array: ".json_encode($arrParams)."; Query: $strQuery; File: $strCallerFile; Line: $nCallerLine";
						throw new App_exception($strAppMsg, $strErrMsg);
						break;
					}
				}
			}
			else
			{
				// string passed. 1D or 2D array not passed
				$strParam = $arrParams;
				$strParam = (string)$strParam;
				$objPrepStmt->bind_param("s", $strParam);
				$execTime = microTime(true);
				$objPrepStmt->execute();
				$execTime = round(microTime(true) - $execTime, 6);
				$this->dbQueryTime = $this->dbQueryTime + $execTime;                        
                $arrTraceHashes = debug_backtrace();

                if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
                {
                    File_logger_factory::getInstance('db_query')->logMessage('File: '.$arrTraceHashes[0]["file"].':'.$arrTraceHashes[0]["line"].' Query: '.$strQuery.' Time: '.$execTime.', '.$this->dbQueryTime, "info");
                }
				////////////////////////////////////////////////////////////////////////////////
				// start of code using result set meta data and 
				// binding array of references of array elements to hold result rows
				////////////////////////////////////////////////////////////////////////////////
				if($objMetadata = $objPrepStmt->result_metadata())
				{
					// The query is a select type query
					$objPrepStmt->store_result();
					$arrMetadataFields = $objMetadata->fetch_fields();
					$arrRefsOfFieldsForResult = array();
					$arrRefsOfFieldsForResult = array();
					$arrResultSet = array();
					$nIterator = 0;
					foreach($arrMetadataFields as $objField)
					{
						switch($flagSelectOutputFormat)
						{
							case "ASSOC":
							{
								$arrRefsOfFieldsForResult[] = &$arrFieldsForResult[$objField->name];
								break;
							}
							case "ARRAY":
							default:
							{
								$arrRefsOfFieldsForResult[] = &$arrFieldsForResult[$nIterator];
								break;
							}
						}
						$nIterator++;
					}
					call_user_func_array(array($objPrepStmt, "bind_result"), $arrRefsOfFieldsForResult);
					$nIter = 0;
					while($objPrepStmt->fetch())
					{
						$arrResultSet[$nIter] = array();
						foreach($arrFieldsForResult as $k=>$v)
						{
							$arrResultSet[$nIter][$k] = $v;
						}
						$nIter++;
					}
					$objPrepStmt->free_result();
					return $arrResultSet;
				}
				else
				{
					// The query is a non-select type query
					$nLastInsertIds=$this->objMysqli->insert_id;
					return $objPrepStmt->affected_rows;	
				}
				////////////////////////////////////////////////////////////////////////////////
				// end of code using result set meta data and
				// binding array of references of array elements to hold result rows				
				////////////////////////////////////////////////////////////////////////////////
				/*
				// code to check get_result method is supported or not
				if(method_exists($objPrepStmt, "get_result"))
				{
					// get_result method is supported
				}
				else
				{
					// get_result method is not supported
				}
				////////////////////////////////////////////////////////////////////////////////
				// start of code using get_result 
				// This method is currently in PHP svn only. Use this when it gets released.
				////////////////////////////////////////////////////////////////////////////////
				$objResultSet = $objPrepStmt->get_result(); 
				if($objResultSet)
				{
					// The query is a select type query
					echo "The query is a select type query<br/>";
					switch($flagSelectOutputFormat)
					{
						case "ASSOC":
						{
							return $objResultSet->fetch_all(MYSQLI_ASSOC);
							break;
						}
						case "ARRAY":
						default:
						{
							return $objResultSet->fetch_all(MYSQLI_NUM);
							break;
						}
					}
				}
				else
				{
					// The query is a non-select type query
					echo "The query is a non-select type query<br/>";
					if(NULL!=$nLastInsertIds)
					{
						// if no parameter is passed by ref for $nLastInsertIds, it will be NULL
						$nLastInsertIds=$this->objMysqli->insert_id;
					}
					return $objPrepStmt->affected_rows;	
				}
				////////////////////////////////////////////////////////////////////////////////
				// end of code using get_result 
				// This method is currently in PHP svn only. Use this when it gets released.
				////////////////////////////////////////////////////////////////////////////////
				*/
			}
		}
		else
		{
			//added cases for transactions
			switch($strQuery)
			{
				case "BEGIN":
				{
					$this->objMysqli->autocommit(FALSE);
					break;
				}
				case "COMMIT":
				{
					$this->objMysqli->commit(); 
					$this->objMysqli->autocommit(TRUE);
					break;
				}
				case "ROLLBACK":
				{
					$this->objMysqli->rollback();
					$this->objMysqli->autocommit(TRUE); 
					break;
				}
				default:
				{
					// simple direct query without params
					$objResultSet = $this->objMysqli->query($strQuery);
					if(strlen($strQuery)<3000)
					{	
						if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
						{
							File_logger_factory::getInstance()->logMessage($strQuery." [".$this->strRandomId."]", "info");
						}
					}
					else
					{
						if(defined("DEBUG_MODE") && (TRUE==DEBUG_MODE))
						{
							File_logger_factory::getInstance()->logMessage(substr($strQuery, 0, 3000)."... rest truncated in log [".$this->strRandomId."]", "info");
						}
					}
					if($objResultSet)
					{
						if(is_object($objResultSet))
						{
							// The query is a select type query
							switch($flagSelectOutputFormat)
							{
								case "ASSOC":
								{
									$arrResult = array();
									while($arrRow = $objResultSet->fetch_array(MYSQLI_ASSOC))
									{
										$arrResult[] = $arrRow;
									}
									
									$objResultSet->free();
									while($this->objMysqli->more_results())
									{
										$this->objMysqli->next_result();
										$objResultSet = $this->objMysqli->use_result();
										if($objResultSet)
										{
											while($arrRow = $objResultSet->fetch_array(MYSQLI_ASSOC))
											{
												$arrResult[] = $arrRow;
											}
											$objResultSet->free();
										}
										else
										{
											break;
										}
									}
									
									return $arrResult;
									// fetch_all function requires native mysqlInd driver 
									// return $objResultSet->fetch_all(MYSQLI_ASSOC);
									
									break;
								}
								case "ARRAY":
								default:
								{
									$arrResult = array();
									while($arrRow = $objResultSet->fetch_array(MYSQLI_NUM))
									{
										$arrResult[] = $arrRow;
									}
									
									$objResultSet->free();
									while($this->objMysqli->more_results())
									{
										$this->objMysqli->next_result();
										$objResultSet = $this->objMysqli->use_result();
										if($objResultSet)
										{
											while($arrRow = $objResultSet->fetch_array(MYSQLI_NUM))
											{
												$arrResult[] = $arrRow;
											}
											$objResultSet->free();
										}
										else
										{
											break;
										}
									}
									
									return $arrResult; 
									// fetch_all function requires native mysqlInd driver
									// return $objResultSet->fetch_all(MYSQLI_NUM);
									
									break;
								}
							}
						}
						else
						{
							// The query is a non-select type query
							$nLastInsertIds=$this->objMysqli->insert_id;
							return $this->objMysqli->affected_rows;					
						}
					}
					else
					{
						$arrTraceHashes = debug_backtrace();
						$strCallerFile = $arrTraceHashes[0]["file"];
						$nCallerLine = $arrTraceHashes[0]["line"];
						$strAppMsg = "Query failure";
						$strErrMsg = "Param-less query failed. Mysqli errno: ".$this->objMysqli->errno."; Mysqli error: "; 
						$strErrMsg.= $this->objMysqli->error."; Query: $strQuery; File: $strCallerFile; Line: $nCallerLine"; 
						throw new App_exception($strAppMsg, $strErrMsg);
					}
					break;
				}
			}
		}
	}
}
?>
