<?php
	/*if($user->id < 0)
		die("need login!");*/
	$boardId = (array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId']) ) ? $_GET['boardId'] : -1;
	if($boardId==-1)
		die("invalid board");
	
	$s = $app->db->prepare("select name, brandImage, backgroundColor,height,width from TempBoard where id=:i and sessionId=:s");
	$s->bindParam(':i',$boardId);
	$s->bindParam(':s',$user->sessionId);
	$s->execute();
	$board = $s->fetchObject();	

	if($board->brandImage)
	{	$size = getimagesize("./".$board->brandImage);
		$heightAdjust = $size[1];
	}else
		$heightAdjust=0;
		

	$board->width="750";$board->height=500+$heightAdjust;	
	$flashVars= "roomId=-1&background=0x".$board->backgroundColor;
	
?>