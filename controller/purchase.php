<?php
	if($user->id < 0)
		{header("Location: /login");exit;}
	

    $errStr=""; $cost=0;
	if(array_key_exists('err',$_GET))
	{	switch($_GET['err'])
		{
			case 'priceBrandBoard': $cost=priceBrandBoard; break;
			case 'priceExport': $cost=priceExport; break;
			case 'priceClear': $cost=priceClear; break;
			case 'priceClaim': $cost=priceClaim; break;
			case 'priceReplay': $cost=priceReplay; break;
			
		}
		$errStr = "I'm sorry, you need <strong>$cost</strong> SneffelCredits to do this; you have <strong>".$user->credit."</strong>.";
	}
?>
