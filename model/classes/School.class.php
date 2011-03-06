<?php
class School
{
	public $id;
	public $domain;
	public $schoolName;
	public $contactName;
	public $contactEmail;
	public $contactPassword;
	public $bgColor;
	public $bgImg;
	public $createDate;
	public $expireDate;
	
	const fieldList = "id,domain,schoolName,contactName,contactEmail,contactPassword,bgColor,bgImg,createDate,expireDate";
	public function save()
	{
		$db = Application::getInstance()->db;
		$qryString = "domain=:d,schoolName=:sn,contactName=:cn,contactEmail=:ce,contactPassword=:cp,bgColor=:bc,bgImg=:bi";
		if($this->id){
			$s = $db->prepare("update School set ".$qryString." where id=:id");
			$s->bindParam(':id',$this->id);
		}else
		{
			$s = $db->prepare("insert into School set ".$qryString.",createDate = :cd");
			$s->bindParam(':cd',time());
		}
		$s->bindParam(':d',str_replace(" ","",$this->domain));
		$s->bindParam(':sn',$this->schoolName);
		$s->bindParam(':cn',$this->contactName);
		$s->bindParam(':ce',$this->contactEmail);
		$s->bindParam(':cp',$this->contactPassword);
		$s->bindParam(':bc',$this->bgColor);
		$s->bindParam(':bi',$this->bgImg);
		$s->execute();

		if(!$this->id)
			$this->id = $db->lastInsertId();
		
	}

	public static function createSchool($domain,$schoolName,$contactName,$contactEmail,$password,$bgColor,$bgImg)
	{
		$newSchool = new School;
		$newSchool->domain = $domain;
		$newSchool->schoolName = $schoolName;
		$newSchool->contactName = $contactName;
		$newSchool->contactEmail = $contactEmail;
		$newSchool->contactPassword = sha1($password);
		$newSchool->bgColor = $bgColor;
		$newSchool->bgImg = $bgImg;
		$newSchool->save();
		
		return $newSchool;
	}
	public static function getFromDomain($domain)
	{
		$db = &Application::getInstance()->db;
		$s = $db->prepare("select ".School::fieldList." from School where domain=:d");
		$s->bindParam(':d',$domain);
		$s->execute();
		return $s->fetchObject();		
	}
	public static function isDomainGood($domain)
	{
		return (!School::getFromDomain($domain) == null);
	}
	
}


