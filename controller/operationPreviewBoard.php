<?php
	$imgName='';$heightAdjust=0;
	/*if($user->id < 0)
		die("need login!");*/
			
	if(!array_key_exists('upload',$_POST))
		die("no form");
	
	if(array_key_exists('boardPic',$_FILES) && $_FILES['boardPic']['name'] )
	{
		if($_FILES['boardPic']['error'])
			die($_FILES['boardPic']['error']);	
		
	  	$filename = basename($_FILES['boardPic']['name']);
	  	$ext = substr($filename, strrpos($filename, '.') + 1);
	  	
	  	$whitelist = array('jpg', 'png', 'gif', 'jpeg');
		$fileSize = @filesize($_FILES['boardPic']['tmp_name']);
			
		if(!in_array($ext,$whitelist))
			die("bad file");
		
		if($fileSize > 4194304)
			die("too big");		
		
			$uploadName = uniqid().'.'.$ext;
			$test = move_uploaded_file($_FILES['boardPic']['tmp_name'],fileUpload."/".$uploadName);
			if(!$test)
				$imgName = "";
			else
				$imgName = fileUploadURLPath."/".	$uploadName;	
				
		
		$size = getimagesize(fileUpload."/".$uploadName);
		$heightAdjust = $size[1];
	}
	$db = $app->db;
	$newId=0;$boardExists='false';
	
	if(array_key_exists('boardName',$_POST)){
		
		$testBoard = Whiteboard::getByName($_POST['boardName'],$user->id);
		if($testBoard)
			$boardExists='true';		
	}	
	
	if(array_key_exists('edit',$_POST) && is_numeric($_POST['edit']) && $testBoard)
	{
		
		if($_POST['edit'] == $testBoard->id)
			$boardExists='false';
	}
	
	
	
	if(array_key_exists('tempId',$_GET))
	{
		if(!is_numeric($_GET['tempId']))
			die("bad id");
		$s=$db->prepare("update TempBoard set name=:n,brandImage=:b,backgroundColor=:c where id=:i and sessionId=:u");
		$s->bindParam(':i',$_GET['tempId']);
		$s->bindParam(':u',$user->sessionId);
		$s->bindParam(':n',$_POST['boardName']);
		$s->bindParam(':c',$_POST['boardColor']);
		$s->bindParam(':b',$imgName);
		$s->execute();	
		$newId=$_GET['tempId'];
	}else
	{
		$s=$db->prepare("insert into TempBoard (sessionId,timeCreated,name, brandImage,backgroundColor, height,width) values (:u,:t,:n,:b,:c,500,500)");
		$s->bindParam(':u',$user->sessionId);
		$s->bindParam(':t',time());
		$s->bindParam(':n',$_POST['boardName']);
		$s->bindParam(':c',$_POST['boardColor']);
		$s->bindParam(':b',$imgName);
		$s->execute();
		$newId= $db->lastInsertId();		
	}
		
?>
