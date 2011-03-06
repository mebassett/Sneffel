<?php
	if($user->id < 0)
		{header("Location: /login");exit;}
		
	if($user->credit < priceExport)
	{
		header("Location: /purchase?err=priceExport");
		exit;
	}	
	
	if(!array_key_exists('boardId',$_GET) || !is_numeric($_GET['boardId']))
		die("invalid board");
	
?>
