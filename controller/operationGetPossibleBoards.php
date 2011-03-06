<?php

	if($user->id < 0)
		{header("Location: /login");exit;}
		
	$possibleBoards = $app->getSql("select id,name,userId from DoodleBoard where phpCreateSession=:i and userId=0",array(':i' => $user->sessionId),'Whiteboard');
	
?>