<?php
/*	User.class.php
 *  Models a user session for a single-user application
 *  written by gegn corp (http://www.gegn.net)
 */
class User
{
	public $sessionId;
	public $email;
	public $name;
	public $id;
	public $credit;

	function __construct($new=false)
	{
		if($new){
		$sessID = isset($_COOKIE['sessID']) ? $_COOKIE['sessID'] : '';
		
		if($sessID)
		{
			
            session_id($sessID);
			session_start();
			if(array_key_exists('userId',$_SESSION) && $_SESSION['userId'] > -1)
			{				
				$this->id=$_SESSION['userId'];
				$this->load();
			}else
				$this->id=-1;
						
		}else
		{
        	mt_srand((double)microtime()*100000);
            $sessID = md5(uniqid(mt_rand()));
            setcookie('sessID', $sessID, time()+(6*3600),'/');
            session_id($sessID);
            session_name($sessID);
			@session_start();
			session_register('userId','userTime');			
			$_SESSION['userId'] = -1;
			$this->id=-1;
			$_SESSION['userTime'] = time();			
		}
		$this->sessionId = $sessID;}
	}
	
	public function load()
	{
		if($this->id < 0)
			return;
		$db = Application::getInstance()->db;
			$s = $db->prepare("select email,name,password,credit from ".tPref."User where id=:i");
			$s->bindParam(':i',$this->id);
			$s->execute();
			$user = $s->fetchObject();
		if($user)
		{
			$this->name = $user->name;
			$this->email= $user->email;
			$this->credit= $user->credit;
		}		
	}
	
	public function signup($email,$name,$pass,$updates)
	{
		if(!is_numeric($updates))
			die("bad!");
		$db = Application::getInstance()->db;		
		$s = $db->prepare("insert into ".tPref."User (email,name,password,emailUpdates) values (:e,:n,:p,:u)");
		$s->bindParam(':e',$email);//$variable,$data_type,$length,$driver_options)
		$s->bindParam(':n',$name);
		$s->bindParam(':p',sha1($pass));
		$s->bindParam(':u',$updates);		
		$s->execute();
		$this->id = $db->lastInsertId();
		$this->email=$email;
		if(!$this->id)
			throw new Exception("There was an error creating the User record.");	
		$_SESSION['userId'] = $this->id;	
		
		
		
		/*$s = $db->prepare("update DoodleBoard set userId=:u where phpCreateSession = :s");
		$s->bindParam(':u',$this->id);
		$s->bindParam(':s',$this->sessionId);
		$s->execute();*/
		
	}
	
	public function login($email,$pass)
	{
		$db = Application::getInstance()->db;
		try
		{
			$s = $db->prepare("select id,email,name,password,credit from ".tPref."User where email=:u and password=:p");
			$s->bindParam(':u',$email);//$variable,$data_type,$length,$driver_options)
			$s->bindParam(':p',sha1($pass));
			$s->execute();
			$user = $s->fetchObject('User');

		}catch(PDOException $e)
		{
			throw new Exception("Database failure!");
		}
		
		if(!$user)
			throw new Exception("Login invalid!");
		else	
		{
			$this->email=$user->email;
			$this->name=$user->name;
			$this->credit=$user->credit;
			$this->id=$user->id;
			$_SESSION['userId'] = $this->id;
			$s = $db->prepare("update User set lastLogin=now() where id=:id");
			$s->bindParam(':id',$user->id);
			$s->execute();
			return $user;
		}			
	}
	public function logout()
	{
		$_SESSION['userId'] = -1;
		$this->id = -1;
		$this->email='';
		$this->name='';
		$this->credit=0;
	}
	public function addCredits($credits)
	{
		if(!is_numeric($credits))
			return;
		$db = Application::getInstance()->db;
		$s = $db->prepare("update User set credit = credit + :c where id = :id");
		$s->bindParam(':c',$credits);
		$s->bindParam(':id',$this->id);
		$this->credit += $credits;
		$s->execute();
	}
	public function chargeCredits($credits)
	{
		if(!is_numeric($credits))
			return;
		$db = Application::getInstance()->db;
		$s = $db->prepare("update User set credit = credit - :c where id = :id");
		$s->bindParam(':c',$credits);
		$s->bindParam(':id',$this->id);
		$this->credit -= $credits;
		$s->execute();
	}	
	public static function checkEmailExists($email)
	{
		$db = Application::getInstance()->db;
		$s = $db->prepare("select email from User where email = :e");
		$s->bindParam(':e',$email);
		$s->execute();
		$obj = $s->fetchObject();
		return $obj != '';
	}
	public static function getUserById($id)
	{
		if(!is_numeric($id))
			return null;
		$db = Application::getInstance()->db;
		$s = $db->prepare("select email,name from User where id=:id");
		$s->bindParam(':id',$id);
		$s->execute();
		return $s->fetchObject('User');
	}
	

	
}
?>
