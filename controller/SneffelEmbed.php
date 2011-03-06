<?php
   $roomId =  (is_numeric($_GET["roomId"])) ? $_GET["roomId"] : 0;
   $replay = array_key_exists('replay',$_GET) ? 1 : 0;
   
   
   
   if($school)
   {
		if($school->expireDate < time())
		{
			include "view/expiredSchool.phtml";
			exit;
		}   
   	 $board = Whiteboard::getBySchoolId($roomId,$school->id);
	}else	
	   $board = Whiteboard::getById($roomId);
   $embed=1;
   
   if(!$board)
   	die("no board!");
   	
   	/*if(!$replay && $board->expireDate < time())
	{
		include "view/expiredBoard.phtml";
		exit;
	}*/	
	
	if($board->backgroundColor)
	{
		if($board->brandImage)
		{	$size = getimagesize("./".$board->brandImage);
			$heightAdjust = $size[1];
		}else
			$heightAdjust=0;   	
	}else
	{
		$board->name = "www.sneffel.com";
		$board->brandImage="/images/header.gif";	
		$board->backgroundColor="8adaf3";
		$heightAdjust=35;   	
	}
   $width =  (is_numeric($_GET["width"])) ? $_GET["width"] : 500;
   $height = (is_numeric($_GET["width"])) ? $_GET["height"]-$heightAdjust : 500;
      

	$color=randomColor();
	
	if($replay)
	{
		
		/*if($board->replayLeft < 1)
		{
			include "view/noReplay.phtml";
			exit;
		}else*/
		//{
			$s = $app->db->prepare("select metaData from Scribble where DoodleBoardId=:id and type='Image' group by metaData");
			$s->bindParam(':id',$roomId);
			$s->execute();
			$imgs = array();
			while($temp=$s->fetchObject())
				array_push($imgs,$temp->metaData);
			
			$imgText = json_encode($imgs);			
			include "view/SneffelEmbedReplay.phtml";
			exit;
		//}
	}
	function randomColor()
	{
		$r = dechex(rand(17,190));$b=dechex(rand(17,190));$g=dechex(rand(17,190));
		return $r.$g.$b;	
	}
?>
