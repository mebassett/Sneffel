<?php
/* Application.class.php
 * This file just defines a static class for some globally used functions.
 */

class Application 
{
	public $db;
	private static $init;
	
	public function __construct()
	{
		try
		{
			$this->db = new PDO(db_ConnectionString,db_user,db_pass);
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
	public function getSql($sql,$args,$type=null)
	{
		try
		{
			$ret = array();
			$s = $this->db->prepare($sql);
			
			foreach($args as $key => $value)				
				$s->bindParam($key,$value);
			
			
			$s->execute();
			
			do
			{
					$obj = ($type) ? $s->fetchObject($type) : $s->fetchObject();
					if($obj)
						array_push($ret,$obj);
			}while($obj != null)	;
				
			return $ret;
		}catch(Exception $e)
		{
			echo $e->getMessage();
			return null;
		}
	}
	
	/*public function fetchParameter($key)
	{
		$query = "select `value` from `".tPref."parameters` where `key` ='".mysql_real_escape_string($key)."'";
		$this->db->send($query);
		$result = $this->db->ret();
		if($result == "") throw new Exception("cannot find parameter ".$key);
		return stripslashes($result['value']);
	}*/
	
	public static function getInstance()
	{
		if(!Application::$init)
			$init = new Application();
		return $init;
	}
public static function getTimeLeft($expire)
	{
		$timeLeft = $expire - time();
		$days = floor($timeLeft / 86400);
		$hours = floor(($timeLeft - 86400*$days) / 3600);
		$minutes = floor( ($timeLeft - 3600 * $hours - 3600*24*$days) / 60);
		return $days > 0 ? "$days days, $hours hours and $minutes minutes"  : "$hours hours and $minutes minutes";
	}	
}
?>
