<?php
/*
 * Required to enable CI logging and threshold setting in config file
 * 
 * logs folder should not have public access. Use htaccess file to limit its access to public
 * 
 * Application should have write permission to the logs folder
 * 
 * */
interface Logger
{
    public function logMessage($strMsg, $strLevel = "ERROR");
}
?>