<?php
	setcookie("SneffelSchool",$school->id,time()+3*3600,"/",".sneffel.com");
	if($school->expireDate < time())
	{
		include "view/expiredSchool.phtml";
		exit;
	}
	
	if($uri[0]=='new') 
	{		
		$s = $app->db->prepare("insert into DoodleBoard (timeCreated, schoolId) values (:t,:s)");
		$s->bindParam(':t',time());
		$s->bindParam(':s',$school->id);
		$s->execute();
		$newId = $app->db->lastInsertId();
		header ('Location: /'.$newId);
		
		exit;
	}	
	$board = null;
	if(is_numeric($uri[0]))	
		$board = Whiteboard::getBySchoolId($uri[0],$school->id);
	else
		$board = Whiteboard::getbySchoolName($uri[0],$school->id);
	
	if($board)
	{
		$roomId=$board->id;
		$board->brandImage = $school->bgImg;
		$board->backgroundColor = $school->bgColor;
		$color=randomColor();
		$inviteText = $board->getNameUrl($school->domain.".sneffel.com");
		$embedText = $board->getEmbed(500,500,$school->domain.".sneffel.com");		
		if($board->brandImage)
		{	$size = getimagesize("./".$board->brandImage);
			$heightAdjust = $size[1] + 25;
		}else
			$heightAdjust=25;
				
		$controller = "subdomainBoard";	
	}else
	{
		$controller =  "subdomain";
		if($school->id ==1)
			$controller = "community";
		$s = $app->db->prepare("select DoodleBoard.id, DoodleBoard.name,BoardUsers.users,DoodleBoard.views from DoodleBoard left join BoardUsers on BoardUsers.boardId=DoodleBoard.id where DoodleBoard.schoolId = :id order by DoodleBoard.timeCreated desc");
		$s->bindParam(':id',$school->id);
		$s->execute();
		$boards = array();
		while($obj = $s->fetchObject('Whiteboard'))
		{
			$obj->createThumb();
			if(!$obj->users )
				$obj->users ="0";
			array_push($boards,$obj);
		}
	}
		
		
function randomColor()
{
	$r = dechex(rand(17,190));$b=dechex(rand(17,190));$g=dechex(rand(17,190));
	return $r.$g.$b;	
}	
	
	
