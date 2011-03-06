<?php
	if(!(array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId'])))
		die("invalid Board");
		
	$board = Whiteboard::getById($_GET['boardId']);
	if(!$board || $board->userId != $user->id)
		die("invalid Board");

	if($user->credit < priceClear)
	{
		header("Location: /purchase?err=priceClear");
		exit;
	}	
		
	if(array_key_exists('confirm',$_POST))
	{
		$user->chargeCredits(priceClear);
		$s = $app->db->prepare("delete from Scribble where DoodleBoardId=:id");
		$s->bindParam(':id',$board->id);
		$s->execute();
		unlink("/var/www/html/sneffel.com/imgData/".$board->id.".png");
		header("Location: /dashboard?err=Clear");
		exit;
	}
?>