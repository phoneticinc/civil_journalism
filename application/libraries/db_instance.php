<?php
interface Db_instance 
{
	public function sanitize($data);
	public function query($strQuery, $arrParams=NULL, $flagSelectOutputFormat="ARRAY", &$nLastInsertIds=NULL);
}
?>
