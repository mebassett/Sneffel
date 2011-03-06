<?php

	ini_set("memory_limit","64M");

	

	$boardId = $_GET['boardId'];
	$endTime = array_key_exists('endTime',$_GET) ? $_GET['endTime'] : null;
	if(!is_numeric($boardId) )
		die("leave me alone!");
	
	$board = Whiteboard::getById($boardId);
	$im = $board->createPng(false,false,$endTime);



	if($im)
	{
		header ('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
		exit();
	}
	
	
?>
