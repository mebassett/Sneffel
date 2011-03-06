<?php
	if($user->id < 0)
		die("need login!");
	ini_set("memory_limit","64M");
	if($user->credit < priceExport)
		die("not enough credits");
	
	$user->chargeCredits(priceExport);
	$boardId = $_POST['boardId'];
	
	$board = Whiteboard::getById($boardId);
	$im = $board->createPng();



	if($im)
	{
		header ('Content-type: image/png');
		imagepng($im);
		imagedestroy($im);
		exit();
	}
	
	
?>
