<?php
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
			$test = move_uploaded_file($_FILES['boardPic']['tmp_name'],fileUpload2."/".$uploadName);
			if(!$test)
				$imgName = "";
			else
				$imgName = fileUploadURLPath2."/".	$uploadName;	
				
		
		$size = getimagesize(fileUpload2."/".$uploadName);
		$heightAdjust = $size[1];
	}
?>