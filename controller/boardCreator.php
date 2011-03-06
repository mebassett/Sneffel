<?php
	/*if($user->id < 0)
		die("need login!");*/
	
	if(array_key_exists('boardId',$_GET) && is_numeric($_GET['boardId']))
	{
		$_POST['next'] = 'Step 2';
		$_POST['boardId'] = $_GET['boardId'];
	}


	if(array_key_exists('next',$_POST))
	{
		if(!is_numeric($_POST['boardId']))
			die("bad id");
		switch($_POST['next'])
		{			
			case 'Step 2':
				if($user->id < 0)
				{				
					setcookie("SignupRedirect","boardCreator",time()+3600);
					setcookie("SignupRedirectData",$_POST['boardId'],time()+3600);
					header("Location:/signup?SignupRedirect=boardCreator");
				}else if($user->credit < priceClaim)
				{
					header("Location: /purchase?err=priceClaim");
					exit;
				}else
				{
					$controller = "boardCreatorStep2";
					$boardId=$_POST['boardId'];
				}
			break;
			case 'Yes - Create my whiteboard.':
				if($user->credit < priceClaim)
                                {
                                        header("Location: /purchase?err=priceClaim");
                                        exit;
                                }
				$user->chargeCredits(priceClaim);
				$s = $app->db->prepare("select name,brandImage, backgroundColor from TempBoard where id=:i");
				$s->bindParam(':i',$_POST['boardId']);
				$s->execute();
				$tb = $s->fetchObject();
				
				$newBoard = Whiteboard::newBoard($tb->name,$user->id,500,500,$tb->brandImage,$tb->backgroundColor,time() + 6060606060606060*60*3);
				$controller = "boardCreatorFinish";
			break;
		}	
	}else
	{	
		$boardName = (array_key_exists('boardName',$_POST)) ? $_POST['boardName']: 'My New Whiteboard';
	}
?>
