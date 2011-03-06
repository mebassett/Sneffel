<?php
	if($user->id < 0)
		{header("Location: /login");exit;}

	include "operationGetPossibleBoards.php";
	include "operationGetUserBoards.php";
	
	$errStr="";
	
	if(array_key_exists('setDefault',$_GET) && is_numeric($_GET['setDefault']))
	{
		$s = $app->db->prepare("update DoodleBoard set backgroundColor = '' where id = :id and userId=:uid ");
		$s->bindParam(':id',$_GET['setDefault']);
		$s->bindParam(':uid',$user->id);
		$s->execute();
		$errStr="Board restored!";
	}
	if(array_key_exists('err',$_GET))
		switch($_GET['err'])
		{
			case 'SaveBoard': $errStr="Your board was saved."; break;
			case 'Clear': $errStr="Your board was cleared."; break;
			case 'Reactivate': $errStr="Your board was re-activated."; break;
			case 'replay': $errStr="Replays purchased."; break;
			case 'cancelPurchase': $errStr="SneffelCredit purchased canceled."; break;
			case 'confirmPurchase': $errStr="Thank you for purchasing SneffelCredits, they should be credited to your account."; break;
			
		}
?>
