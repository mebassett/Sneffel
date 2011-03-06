<?php
class Whiteboard 
{
	public $id;
	public $name;
	public $userId;
	public $height;
	public $width;
	public $backgroundColor;
	public $brandImage;
	public $expireDate;
	public $replayLeft;
	public $schoolId;
	public $users;
	
	public function claim($userId)
	{
		if(!is_numeric($userId))
			return;
		
		$db = Application::getInstance()->db;				
		try
		{
			/*$s = $db->prepare("update DoodleBoard set userId = :u where id = :id");
			$s->bindParam(':u',$userId);
			$s->bindPAram(':id',$this->id);
			$s->execute();*/
			$this->userId=$userId;
		}catch(Exception $e)
		{
			return;	
		}
	}
	
	public function getTimeLeft()
	{
		$timeLeft = $this->expireDate - time();
		$days = floor($timeLeft / 86400);
		$hours = floor(($timeLeft - 86400*$days) / 3600);
		$minutes = floor( ($timeLeft - 3600 * $hours - 3600*24*$days) / 60);
		return $days > 0 ? "$days days, $hours hours and $minutes minutes"  : "$hours hours and $minutes minutes";
	}
	
	public function getNameUrl($subdomain=false)
	{
		$s = Application::getInstance()->db->prepare("select email from User where id=:i");
		$s->bindParam(':i',$this->userId);
		$s->execute();
		$obj = $s->fetchObject();
		if(!$subdomain)
			return $this->name ? "http://www.sneffel.com/user/".$this->userId."/".urlencode($this->name) : "http://www.sneffel.com/".$this->id;
		else
			return $this->name ? "http://".$subdomain."/".$this->name : "http://".$subdomain."/".$this->id;
	}
	public function getEmbed($width,$height,$subdomain=false)
	{
		if(!$subdomain)
			$subdomain = "www.sneffel.com";
		return '<!-- START sneffelDoodleEmbed --><div id="sneffelDoodleEmbed'.$this->id.'"><script type="text/javascript" src="http://'.$subdomain.'/Sneffel/embedjs.jsp?roomId='.$this->id.'&sneffelWidth='.$width.'&sneffelHeight='.$height.'"/></script></div><!-- END sneffelDoodleEmbed -->';
	}	
	public function getEmbedReplay($width,$height,$subdomain=false)
	{
		if(!$subdomain)
			$subdomain = "www.sneffel.com";		
		return '<!-- START sneffelDoodleEmbed --><div id="sneffelDoodleEmbed'.$this->id.'"><script type="text/javascript" src="http://'.$subdomain.'/Sneffel/embedjs.jsp?replay=1&roomId='.$this->id.'&sneffelWidth='.$width.'&sneffelHeight='.$height.'"/></script></div><!-- END sneffelDoodleEmbed -->';
	}	
	public function setName($name)
	{
		$db = Application::getInstance()->db;				
		try
		{
			$s = $db->prepare("update DoodleBoard set name = :u where id = :id");
			$s->bindParam(':u',$name);
			$s->bindPAram(':id',$this->id);
			$s->execute();
			$this->name=$name;
		}catch(Exception $e)
		{
			return;	
		}		
	}
	
	public function createThumb($img1=false)
	{
		$app = Application::getInstance();
		$filename = "/var/www/html/sneffel.com/imgData/".$this->id."-thumb.png";
		$old = "/var/www/html/sneffel.com/imgData/".$this->id.".png";
		if(!file_exists($filename) || (time() - filemtime($filename)) > 3600*24)
		{
			if(!$img1)
				if(file_exists($old))
					$img1 = imagecreatefrompng($old);//$this->createPng();
				else 
					$img1 = null;


			$imgArray = array();
			$s = $app->db->prepare("select * from Scribble where DoodleBoardId=:i and type='Image' order by timeCreated asc");
			$s->bindParam(':i',$this->id);
			$s->execute();
			while($obj=$s->fetchObject())
			{
				$tmp = new ImageStruct;
				$tmp->source = $obj->metaData;
				$xData = explode(':',$obj->xCoords);
				$yData = explode(':',$obj->yCoords);
				$tmp->x = $xData[0];
				$tmp->y = $yData[0];
				$tmp->w = (array_key_exists('1',$xData)) ? $xData[1] : -1;
				$tmp->h = (array_key_exists('1',$yData)) ? $yData[1] : -1;
				$imgArray[$obj->timeCreated] = $tmp;		
			}
			$s = $app->db->prepare("select metaData from Scribble where DoodleBoardId=:i and type='RImage' order by timeCreated asc");
			$s->bindParam(':i',$this->id);
			$s->execute();
			while($obj=$s->fetchObject())
			{
				$id = substr(strrchr($obj->metaData,":"),1);
				$imgArray[$id]=null;
		
			}
			$finalImgArray = array();
			foreach($imgArray as $key=>$img)
				if($img != null)
					array_push($finalImgArray,array($key,$img));
					
			$s = $app->db->prepare("select metaData from Scribble where DoodleBoardId=:i and type='FrontImage' order by timeCreated asc");
			$s->bindParam(':i',$this->id);
			$s->execute();
			while($obj=$s->fetchObject())
			{
				
				$id = substr(strrchr($obj->metaData,":"),1);
				$index = getIndex($id,$finalImgArray);
				$temp = $finalImgArray[$index];
				$finalImgArray[$index] = null;
				array_push($finalImgArray,$temp);		
			}
			
			foreach($finalImgArray as $img)
			{
				$img = $img[1];
				if($img != null)
				{
					$cpImg = null;
					$fileName="/var/www/html/sneffel.com".$img->source;
					$ext = substr($img->source, strrpos($img->source, '.') + 1);
					switch($ext)
					{
						case 'gif': //gif
							$cpImg = imagecreatefromgif($fileName);
						break;	
						case 'jpeg': //jpeg
							$cpImg = imagecreatefromjpeg($fileName);
						break;	
						case 'jpg': //jpeg
							$cpImg = imagecreatefromjpeg($fileName);
						break;
						case 'pgn': //png
							$cpImg = imagecreatefrompng($fileName);
						break;
					}
					if($img->w == -1) 
						$img->w = imagesx($cpImg);
					if($img->h == -1)
						$img->h = imagesy($cpImg);
					if($cpImg)
					{
						imagecopyresampled($img1,$cpImg,(int)$img->x,(int)$img->y,0,0,$img->w,$img->h,imagesx($cpImg),imagesy($cpImg));
						imagedestroy($cpImg);
					}
				}			
			}


			$thumb = imagecreatetruecolor(150, 150);
			
			$white = convertHexColor($thumb,'ffffff');
			imagefill($thumb,0,0,$white);		
			if($img1)
				imagecopyresampled($thumb,$img1,0,0,0,0,150,150,800,800);
			imagepng($thumb,$filename);
			//imagedestroy($img1);
			imagedestroy($thumb);
		}
	}
	
	public function createPng($doImages=true,$doFill=true,$endTime=null)
	{
		$app = Application::getInstance();
		$im = imagecreatetruecolor(2000, 2000);
	   // imageantialias($im,true);
		$white = convertHexColor($im,'ffffff');
		
		imagefill($im,0,0,$white);
		if(!$doFill)
		{

			imagecolortransparent($im, $white);
		}	
		if($doImages)
		{
			$imgArray = array();
			$s = $app->db->prepare("select * from Scribble where DoodleBoardId=:i and type='Image' order by timeCreated asc");
			$s->bindParam(':i',$this->id);
			$s->execute();
			while($obj=$s->fetchObject())
			{
				$tmp = new ImageStruct;
				$tmp->source = $obj->metaData;
				$xData = explode(':',$obj->xCoords);
				$yData = explode(':',$obj->yCoords);
				$tmp->x = $xData[0];
				$tmp->y = $yData[0];
				$tmp->w = (array_key_exists('1',$xData)) ? $xData[1] : -1;
				$tmp->h = (array_key_exists('1',$yData)) ? $yData[1] : -1;
				$imgArray[$obj->timeCreated] = $tmp;		
			}
			$s = $app->db->prepare("select metaData from Scribble where DoodleBoardId=:i and type='RImage' order by timeCreated asc");
			$s->bindParam(':i',$this->id);
			$s->execute();
			while($obj=$s->fetchObject())
			{
				$id = substr(strrchr($obj->metaData,":"),1);
				$imgArray[$id]=null;
		
			}
			$finalImgArray = array();
			foreach($imgArray as $key=>$img)
				if($img != null)
					array_push($finalImgArray,array($key,$img));
					
			$s = $app->db->prepare("select metaData from Scribble where DoodleBoardId=:i and type='FrontImage' order by timeCreated asc");
			$s->bindParam(':i',$this->id);
			$s->execute();
			while($obj=$s->fetchObject())
			{
				
				$id = substr(strrchr($obj->metaData,":"),1);
				$index = getIndex($id,$finalImgArray);
				$temp = $finalImgArray[$index];
				$finalImgArray[$index] = null;
				array_push($finalImgArray,$temp);		
			}
			
			foreach($finalImgArray as $img)
			{
				$img = $img[1];
				if($img != null)
				{
					$cpImg = null;
					$fileName="/var/www/html/sneffel.com".$img->source;
					$ext = substr($img->source, strrpos($img->source, '.') + 1);
					switch($ext)
					{
						case 'gif': //gif
							$cpImg = imagecreatefromgif($fileName);
						break;	
						case 'jpeg': //jpeg
							$cpImg = imagecreatefromjpeg($fileName);
						break;	
						case 'jpg': //jpeg
							$cpImg = imagecreatefromjpeg($fileName);
						break;
						case 'pgn': //png
							$cpImg = imagecreatefrompng($fileName);
						break;
					}
					if($img->w == -1) 
						$img->w = imagesx($cpImg);
					if($img->h == -1)
						$img->h = imagesy($cpImg);
					imagecopyresampled($im,$cpImg,$img->x,$img->y,0,0,$img->w,$img->h,imagesx($cpImg),imagesy($cpImg));
					imagedestroy($cpImg);
				}			
			}
		}
		
		if($endTime)
		{
			$s = $app->db->prepare("select * from Scribble where DoodleBoardId=:i and (type='Draw' or type='Erase') and serverTimeCreated <= :end order by serverTimeCreated asc");
			$s->bindParam(':end',$endTime);
			$s->bindParam(':i',$this->id);
			$s->execute();
			
		}else
		{
			$s = $app->db->prepare("select * from Scribble where DoodleBoardId=:i and (type='Draw' or type='Erase') order by serverTimeCreated asc");		
			$s->bindParam(':i',$this->id);
			$s->execute();
		}
		while($obj=$s->fetchObject())
		{
			$xData = explode(':',$obj->xCoords);
			$yData = explode(':',$obj->yCoords);
			$obj->color = substr(dechex($obj->color + hexdec('FF000000')),2);
			$color = ($obj->type == 'Erase') ? $white :  convertHexColor($im,$obj->color);
			
			for($i=0;$i<count($xData)-1;$i++)		
				if($xData[$i] != -1 && $xData[$i+1] != -1)
					drawLine($im,$xData[$i],$yData[$i],$xData[$i+1],$yData[$i+1],$obj->width-1,$color);
				
			
		}		
		return $im;
	}
	
	
	
	public static function getById($id)
	{
		if(!is_numeric($id))
			return;		
		
		$db = Application::getInstance()->db;	
		$s=$db->prepare("select  id,name,brandImage,backgroundColor,expireDate,replayLeft from DoodleBoard where id=:id and schoolId=0");
		$s->bindParam(':id',$id);
		$s->execute();
		return $s->fetchObject('Whiteboard');		
	}
	
	public static function getBySchoolId($id,$school)
	{
		if(!is_numeric($id))
			return;		
		
		$db = Application::getInstance()->db;	
		$s=$db->prepare("select  id,name,brandImage,backgroundColor,expireDate,replayLeft from DoodleBoard where id=:id and schoolId=:sid");
		$s->bindParam(':id',$id);
		$s->bindParam(':sid',$school);
		$s->execute();
		return $s->fetchObject('Whiteboard');			
	}public static function getBySchoolName($name,$school)
	{
		$db = Application::getInstance()->db;	
		$s=$db->prepare("select  id,name,brandImage,backgroundColor,expireDate,replayLeft from DoodleBoard where name=:id and schoolId=:sid");
		$s->bindParam(':id',$name);
		$s->bindParam(':sid',$school);
		$s->execute();
		return $s->fetchObject('Whiteboard');			
	}
	
	public static function getByName($name,$owner)
	{
		$db = Application::getInstance()->db;	
		$s=$db->prepare("select  id,name,brandImage,backgroundColor,expireDate,replayLeft from DoodleBoard where name=:id and userId=:o and schoolId=0");
		$s->bindParam(':id',$name);
		$s->bindParam(':o',$owner);
		$s->execute();
		return $s->fetchObject('Whiteboard');			
	}
	public static function newBoard($name="",$owner=0,$height=500,$width=500,$brandImage="",$backgroundColor="",$expireDate=2000000000)
	{
		global $user;
		if(!is_numeric($owner))
			return;
		$db = Application::getInstance()->db;	
		if($name)
		{
			$s=$db->prepare("select id from DoodleBoard where name=:n and userId=:o");
			$s->bindParam(':n',$name);
			$s->bindParam(':o',$owner);
			$s->execute();
			$obj = $s->fetchObject();
			if($obj) throw new Exception("There's already a board with that name.'");
			
		}	
		$s=$db->prepare("insert into DoodleBoard (name, timeCreated,phpCreateSession,brandImage,backgroundColor,expireDate) values " .
												"(:n,:t,:php,:o,:bimg,:bcolor,:x)");
		$s->bindParam(':n',$name);
		$s->bindParam(':t',time());
		$s->bindParam(':php',$user->sessionId);
		//$s->bindParam(':o',$owner);
		$s->bindParam(':bimg',$brandImage);
		$s->bindParam(':bcolor',$backgroundColor);
		//$s->bindParam(':h',$height);
		//$s->bindParam(':w',$width);
		$s->bindParam(':x',$expireDate);
		
		$s->execute();
		$newBoard = new Whiteboard();
		$newBoard->id = $db->lastInsertId();
		$newBoard->name = $name;
		$newBoard->userId = $owner;
		$newBoard->height=$height;
		$newBoard->width=$width;
		$newBoard->backgroundColor=$backgroundColor;
		$newBoard->brandImage=$brandImage;
		$newBoard->expireDate=$expireDate;
		return $newBoard;
	}
}
class ImageStruct
{
	public $source;
	public $x;
	public $y;
	public $h;
	public $w;
}


	function convertHexColor($im, $htmlColor)
	{
		$red = hexdec(substr($htmlColor,0,2));
		$green = hexdec(substr($htmlColor,2,2));
		$blue = hexdec(substr($htmlColor,4,2));

		return imagecolorallocate($im,$red,$green,$blue);
	}
	function drawLine($image,$x0, $y0,$x1, $y1,$radius,$color)
	{
		
		imagesetthickness($image, $radius > 2 ?ceil( $radius / 2 ): 2);
		if($radius == 1)
		{
			imageline($image,$x0, $y0,$x1, $y1,$color);
			return;
		}
		$f = 1 - $radius;
		$ddF_x= 1;
		$ddF_y = -2 * $radius;
		$x= 0;
		$y = $radius;
		imageline($image,$x0, $y0 + $radius,$x1, $y1 + $radius,$color);
		imageline($image,$x0, $y0 - $radius,$x1, $y1 - $radius,$color);
		imageline($image,$x0 + $radius, $y0,$x1 + $radius, $y1,$color);
		imageline($image,$x0 - $radius, $y0,$x1 - $radius, $y1,$color);
		
		while($x< $y)
		{
			if($f >= 0)
			{
				$y--;
			    $ddF_y += 2;
			    $f += $ddF_y;
			}
			$x++;
			$ddF_x+= 2;
			$f += $ddF_x;
			imageline($image,$x0 + $x, $y0 + $y,$x1 + $x, $y1+ $y,$color);
			imageline($image,$x0 - $x, $y0 + $y,$x1 - $x, $y1 + $y,$color);
			imageline($image,$x0 + $x, $y0 - $y,$x1 + $x, $y1 - $y,$color);
			imageline($image,$x0 - $x, $y0 - $y,$x1 - $x, $y1 - $y,$color);
			imageline($image,$x0 + $y, $y0 + $x,$x1 + $y, $y1 + $x,$color);
			imageline($image,$x0 - $y, $y0 + $x,$x1 - $y, $y1 + $x,$color);
			imageline($image,$x0 + $y, $y0 - $x,$x1 + $y, $y1 - $x,$color);
			imageline($image,$x0 - $y, $y0 - $x,$x1 - $y, $y1 - $x,$color);
			
		}	
			
	}
	function getIndex($id,$arr)
	{
		$ret = 0;
		foreach($arr as $key=>$item)
			if($id == $item[0])
				$ret = $key;
		return $ret;
	}	
?>
