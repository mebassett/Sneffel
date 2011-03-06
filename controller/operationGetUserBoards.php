<?php

	if($user->id < 0)
		{header("Location: /login");exit;}
		
	$userBoards = $app->getSql("select id,name,userId,brandImage,backgroundColor,expireDate,replayLeft from DoodleBoard where userId=:i order by timeCreated desc",array(':i' => $user->id),'Whiteboard');	
?>