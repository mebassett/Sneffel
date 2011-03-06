<?php
	if(!(array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId'])))
		die("invalid Board");
		
	$board = Whiteboard::getById($_GET['boardId']);
	if(!$board || $board->userId != $user->id)
		die("invalid Board");

	if($user->credit < priceReplay)
	{
		header("Location: /purchase?err=priceReplay");
		exit;
	}
		
	if(array_key_exists('confirm',$_POST))
	{
		$user->chargeCredits(priceReplay);
		
		$s = $app->db->prepare("update DoodleBoard set replayLeft = replayLeft + 250 where id=:i");
		$s->bindParam(':i',$board->id);
		$s->execute();
		
		header("Location: /dashboard?err=replay");
		exit;
	}
?>