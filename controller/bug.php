<?php
	$errStr='';
	if( isset($_POST['commentType']))
	{
//		$fName = mysql_real_escape_string ( $_POST['name']);
//		$email = mysql_real_escape_string ( $_POST['email']);
//		$message = mysql_real_escape_string ( $_POST['message']);
		//$mType = mysql_real_escape_string ( $_POST['commentType']);
//		$d = new MySQL();
/*		$d->send("insert into Feedback2 (email,name, type,comments,httpc,ip) values (" .
				"'".$email."'," .
				"'".$fName."'," .
				"'".$mType."'," .
				"'".$message."'," . 
				"'".addslashes($_SERVER['HTTP_USER_AGENT'])."'," .
				"'".$_SERVER['REMOTE_ADDR']."')");*/
		$s = $app->db->prepare("insert into Feedback2 (email,name, type,comments,httpc,ip) values (:e,:n,:t,:c,:h,:i)");
		$s->bindParam(':e',$_POST['email']);

		$s->bindParam(':n',$_POST['name']);
		$s->bindParam(':t',$_POST['commentType']);
		$s->bindParam(':c',$_POST['message']);
		$s->bindParam(':h',$_SERVER['HTTP_USER_AGENT']);
		$s->bindParam(':i',$_SERVER['REMOTE_ADDR']);
		$s->execute();



		$errStr = "<h2>Thank you!  I'll read your comments soon!</h2>";
		mail("rudeboy124@gmail.com","new email @ sneffel.com","you better check the database matthew, you got a new email.");
	}
?>
