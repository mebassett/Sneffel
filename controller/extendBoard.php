<?php
	if(!(array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId'])))
		die("invalid Board");
		
	setcookie('sneffel_board_id',$_GET['boardId'],time()+60*60*24,'/','.sneffel.com');
?>