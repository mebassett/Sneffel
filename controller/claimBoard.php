<?php
	if(!(array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId'])))
		die("invalid Board");
		
	$board = Whiteboard::getById($_GET['boardId']);
	if(!$board || $board->userId > 0)
		die("invalid Board");


	if($user->id > 0 && $user->credit < priceClaim)
	{
		header("Location: /purchase?err=priceClaim");
		exit;
	}	
	if($user->id == -1)
	{
		setcookie("SignupRedirect","claimBoard",time()+3600);
		setcookie("SignupRedirectData",$_GET['boardId'],time()+3600);
		header("Location: /signup?SignupRedirect=claimBoard");
		exit;
	}
		
	if(array_key_exists('confirm',$_POST) && $user->id > 0)
	{
		
		$user->chargeCredits(priceClaim);
		
		$db = $app->db;
		$s = $db->prepare("update DoodleBoard set userId=:u where id=:i and userId=0");
		$s->bindParam(':i',$board->id);
		$s->bindParam(':u',$user->id);
		$s->execute();
		
		header("Location: /dashboard");
		exit;
	}
?>