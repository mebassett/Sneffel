<?php
class MySQL
{
	public static $link = "";
	
	private $dbHandle;
	private $current=array();
	
	function __construct($dataHost=db_host,$dataName=db_name,$dataUser=db_user,$dataPass=db_pass)
	{
		if(MySQL::$link == "") 
			MySQL::openLink($dataHost,$dataUser,$dataPass);
		$this->dbHandle = &MySQL::$link;
		$tempcheck = mysql_select_db($dataName);		
		if(!($this->dbHandle && $tempcheck))
			throw new Exception("cannot connect to database");
	}
	
	public function ret($id=0)
	{
		return mysql_fetch_array($this->current[$id]); //yeah, you should only have to change this command to match your DB..right?
	}	
	
	public function send($sql,$id=0)
	{
		$this->current[$id]=0;
		$this->current[$id] = mysql_query($sql);
		if(!$this->current[$id]) throw new Exception("MySQL Error Query: \"".$sql."\"<p> <strong>Error</strong>: ".mysql_error()."</p>");
	}
	
	
	public static function openLink($dataHost,$dataUser,$dataPass)
	{
		MySQL::$link = mysql_connect($dataHost,$dataUser,$dataPass);
	}
}
?>