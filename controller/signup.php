<?php
require_once "Mail.php";

	$schoolName = array_key_exists('schoolName',$_POST) ? $_POST['schoolName'] : "My School or Tutor Company";
	$domain = array_key_exists('domain',$_POST) ? str_replace(" ","",$_POST['domain']) :"yourschool";
	$email= array_key_exists('email',$_POST) ? $_POST['email'] :'';
	$name= array_key_exists('name',$_POST) ? $_POST['name'] :'';
	$password = array_key_exists('password',$_POST) ? $_POST['password'] : '';
	$step=0;
	
	if(array_key_exists('signup',$_POST))
	{
		switch($_POST['signup'])
		{
			case 'Step 2':
				if($_POST['captcha'] != $_COOKIE['SneffelRobotTrap'])
	 			{
	 				$errStr = "I think you're a robot.";
		 		}elseif(School::isDomainGood($domain))
		 		{
		 			$errStr = "I'm sorry, that domain is in use.  Try another one?";
		 		}else
		 		{
		 			$step=1;
					$controller = "signup2";
		 		}
			break;
			case 'Step 3':			
				$step=1;	$controller = "signup2";
				$email=$_POST['email'];$name=$_POST['name'];
				$emailValidator = new EmailAddressValidator;
				if (!$_POST['email'] || !$_POST['password'] || !$_POST['name'])
		 		{
		 			$errStr = "Please complete form.";
		 		}else if(!$emailValidator->check_email_address($_POST['email']))
		 		{
		 			$errStr = "Email is invalid.";
		 		}else if($_POST['password'] != $_POST['password2'])
		 		{
		 			$errStr = "Passwords don't match.";
		 		}else
		 		{		
		 			$step=2;
					$controller = "signup3";
		 		}		
			break;
			case 'Finish':
				$step=2;	$controller = "signup-payment";
				
				$s = $app->db->prepare("select name,brandImage, backgroundColor from TempBoard where id=:i");
				$s->bindParam(':i',$_POST['boardId']);
				$s->execute();
				$tb = $s->fetchObject();
				
				$school = School::createSchool($domain,$schoolName,$name,$email,$password,$tb->backgroundColor,$tb->brandImage);
				setcookie("SneffelSchool",$school->id,time()+3*3600,"/",".sneffel.com");
	
					$from = "Matthew Eric Bassett <matthew@sneffel.com>";
					$subject = "Your new SneffelSchool at ".$domain.".sneffel.com";
					
					
					$host = "ssl://smtp.gmail.com";
					$port = "465";
					$username = "sneffel@gegn.net";
					$password = "A_lpha1";	
					$to = $name . "<" . $email . ">";
					$body = "Dear ".stripslashes($name).",\n\n";
					$body .= "Thank you for creating your new SneffelSchool.  Assuming you've paid for a subscription or chosen the free trial, it";
					$body .= " should be online now at http://".$domain.".sneffel.com/ .\n";
					$body .= " I'll be in contact over the next few weeks to see how its going, and I'll be keen to hear your comments and suggestions.";
					$body .= " Sneffel is a young service, and I want to personally thank you for adopting it.  I'm confident that together we";
					$body .= " can improve distance learning and bring online education to a wider audience.";
					$body .= "\n\n\n\n";
					$body .= "Many thanks,\n-Matthew";
					//$body .= "You can view this updated at http://ouray.sneffel.com/".$obj->urlTitle." . To stop recieving these notifications, email matthew@sneffel.com and tell him to turn them off.\n\nThanks,\n\nOuray";
					
					$headers = array ('From' => $from,
					  'To' => $to,
						  'Subject' => $subject);
					$smtp = Mail::factory('smtp',
					  array ('host' => $host,
					    'port' => $port,
					    'auth' => true,
					    'username' => $username,
	    				'password' => $password));
					$mail = $smtp->send($to, $headers, $body);	
	
								
			break;
			case 'Start 14 Day Free Trial':
				$s = $app->db->prepare("update School set expireDate = :ex where id=:id");
				$s->bindParam(':id',$_POST['schoolId']);
				$time=(time()+3600*24*14);
				$s->bindParam(':ex',$time);
				$s->execute();
				
				$s = $app->db->prepare("select domain from School where id=:id");
				$s->bindParam(':id',$_POST['schoolId']);
				$s->execute();
				$obj=$s->fetchObject();
				header("Location: http://".$obj->domain.".sneffel.com/");
				exit;
			break;
		}
		

		
	}
	
	if(array_key_exists('schoolId',$_GET))
	{
				$s = $app->db->prepare("update School set expireDate = :ex where id=:id");
				$s->bindParam(':id',$_GET['schoolId']);
				$time=(time()+3600*24*14);
				$s->bindParam(':ex',$time);
				$s->execute();
				
				$s = $app->db->prepare("select domain from School where id=:id");
				$s->bindParam(':id',$_GET['schoolId']);
				$s->execute();
				$obj=$s->fetchObject();
				header("Location: http://".$obj->domain.".sneffel.com/");
				exit;	
	}
	
	if(!$step)
	{
		$firstNum = rand(1,15);
		$secNum = rand(8,11);
		
		$totalNum = $firstNum + $secNum;
		setcookie("SneffelRobotTrap",$totalNum);
		$captchaString = "$firstNum + $secNum";
	}
?>
