<?php
/*
 * Created on Jun 18, 2010
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 	if(isset($_POST['login']))
	{
		try{
			$user->login($_POST['email'],$_POST['password']);  //this saves session/cookie data
			
			if(array_key_exists('SignupRedirect',$_COOKIE))
			{
				switch($_COOKIE['SignupRedirect'])
				{
					case 'boardCreator':
						if(is_numeric($_COOKIE['SignupRedirectData']))
							header("Location: /boardCreator?boardId=".$_COOKIE['SignupRedirectData']);
					break;
				}
			}else
				header("Location: /dashboard");
		}catch(Exception $e)
		{
			$errStr = $e->getMessage();
		}
		
	}
?>
