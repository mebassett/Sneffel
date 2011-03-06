<?php

	if($user->id < 0)
		{header("Location: /login");exit;}
	
	$boardId = $_GET['id'];
	
	if(!is_numeric($boardId))
		die("bad data");
	
	$db = $app->db;
	$s = $db->prepare("update DoodleBoard set userId=:u where id=:i and phpCreateSession=:p and userId=0");
	$s->bindParam(':i',$boardId);
	$s->bindParam(':u',$user->id);
	$s->bindParam(':p',$user->sessionId);
	$s->execute();
	
?>