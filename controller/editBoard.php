<?php
	if($user->id < 0)
		{header("Location: /login");exit;}
	
	if(!(array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId'])))
		die("invalid Board");
		
	$board = Whiteboard::getById($_GET['boardId']);
	if(!$board || $board->userId != $user->id)
		die("invalid Board");
	$boardColor = $board->backgroundColor;
	if(!$board->name)
		$board->name = "Board #".$board->id;
	
	
	if(array_key_exists('next',$_POST))
	{
		if(!is_numeric($_POST['tempBoardId']))
			die("bad id");
		
		$s = $app->db->prepare("select name,brandImage, backgroundColor from TempBoard where id=:i");
		$s->bindParam(':i',$_POST['tempBoardId']);
		$s->execute();
		$tb = $s->fetchObject();
		
		$s = $app->db->prepare("update DoodleBoard set name=:n, backgroundColor=:c,brandImage=:g where id=:i");
		$s->bindParam(':n',$tb->name);
		$s->bindParam(':c',$tb->backgroundColor);
		$s->bindParam(':g',$tb->brandImage);
		$s->bindParam(':i',$board->id);	
		$s->execute();
		header("Location: /dashboard?err=SaveBoard");
			
	}
?>