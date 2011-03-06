<?php
	$roomId=-1;$board=null;
	if(array_key_exists(0,$uri) && is_numeric($uri[0]))
		$roomId = $uri[0];
	if(array_key_exists(0,$uri) && $uri[0]=='user' && array_key_exists(1,$uri) && is_numeric($uri[1]))
	{	
		$board = Whiteboard::getByName(urldecode($uri[2]),$uri[1]);
		$roomId=$board->id;
	}
		
	/*if($roomId == -1 && array_key_exists('sneffel_board_id',$_COOKIE) && is_numeric($_COOKIE['sneffel_board_id']))
	{
		$roomId = $_COOKIE['sneffel_board_id'];
	}*/
	
	if(array_key_exists('sneffel_replay',$_COOKIE) && $_COOKIE['sneffel_replay'] == 1)
	{
		setcookie('sneffel_replay',0,time()-3600,'/','.sneffel.com');
		header("Location: /replay/".$roomId);
	}
	$d=new MySQL();
	
	
	if(!$board && $roomId > 0)
		$board = Whiteboard::getById($roomId);

	
	
	if($uri[0]=='new') //if(!$board || $uri[0]=='new')	
	{		
		$d->send("insert into DoodleBoard (timeCreated,phpCreateSession,expireDate) values ('".time()."', '".$user->sessionId."','".(time() + 60*60*3)."') ");
		$newId = mysql_insert_id();
		header ('Location: /'.$newId);
		
		exit;
	}
	
	if(!$board)
	{
		include "view/home.phtml";
		exit;
	}
	
	/*if($board->expireDate < time())
	{
		include "view/expiredBoard.phtml";
		exit;
	}	*/	
	setcookie('sneffel_board_id',$roomId,time()+60*60*24,'/','www.sneffel.com');
	$inviteText = $board->getNameUrl();
	$embedText = $board->getEmbed(500,500);
	if($board->backgroundColor)
	{
		if($board->brandImage)
		{	$size = getimagesize("./".$board->brandImage);
			$heightAdjust = $size[1];
		}else
			$heightAdjust=0;
		
		$controller="brandedBoard";	
	}
	$color=randomColor();
	
	function randomColor()
	{
		$r = dechex(rand(17,190));$b=dechex(rand(17,190));$g=dechex(rand(17,190));
		return $r.$g.$b;	
	}
?>
