<?php
	if(!array_key_exists(1,$uri))
		die("no board");
	
	$roomId=$uri[1];
	if(!is_numeric($roomId))
		die("bad board id");

	if($school)
	{
		if($school->expireDate < time())
		{
			include "view/expiredSchool.phtml";
			exit;
		}	
		$board = Whiteboard::getbySchoolId($roomId,$school->id);
	}else
		$board = Whiteboard::getById($roomId);
	
	
	$s = $app->db->prepare("select replayLeft, views from DoodleBoard where id=:id");
	$s->bindParam(':id',$roomId);
	$s->execute();
	$obj = $s->fetchObject();
	$embedText = ($school) ? $board->getEmbedReplay(500,500,$school->domain.".sneffel.com") : $board->getEmbedReplay(500,500);
	
	/*if($obj->replayLeft < 1)
	{
		setcookie('sneffel_replay',1,time()+60*60*24,'/','.sneffel.com');
		include "view/noReplay.phtml";
		exit;
	}else*/
//	{
		$s = $app->db->prepare("update DoodleBoard set replayLeft=replayLeft-1, views=views+1 where id=:id");
		$s->bindParam(':id',$roomId);
		$s->execute();
		$obj->replayLeft--;
		$obj->views++;
		
		$s = $app->db->prepare("select metaData from Scribble where DoodleBoardId=:id and type='Image' group by metaData");
		$s->bindParam(':id',$roomId);
		$s->execute();
		$imgs = array();
		while($temp=$s->fetchObject())
			array_push($imgs,$temp->metaData);
		
		$imgText = json_encode($imgs);
//	}
	if($school)
	{
		$roomId=$board->id;
		$board->brandImage = $school->bgImg;
		$board->backgroundColor = $school->bgColor;
		if($board->brandImage)
		{	$size = getimagesize("./".$board->brandImage);
			$heightAdjust = $size[1] + 25;
		}else
			$heightAdjust=25;	
		$controller = "subdomainReplay";	
	}
?>
