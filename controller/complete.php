<?php
	if(array_key_exists('SneffelSchool',$_COOKIE))
	{
		$schoolId = $_COOKIE['SneffelSchool'];
		$s = $app->db->prepare("select domain from School where id=:id");
		$s->bindParam(":id",$schoolId);
		$s->execute();
		$obj =$s->fetchObject();
		
		header("Location: http://".$obj->domain.".sneffel.com/");
	}
?>
