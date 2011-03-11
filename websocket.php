<?php  
/*
This is terrible, I know, using PHP for this sort of thing.  Actually, as a toy project in Erlang, I want to rewrite this (on my TODO.) In any case, this is meant to be run as from the shell:

php websocket.php > ./mylogfile.log

*/

require "model/loadApi.php";
require "model/loadSocketApi.php";

ini_set("memory_limit","128M");

$ip = "127.0.0.1"; //this should be somewhere else, like /model/config

$master=new WebSocket($ip,443);
class WebSocket
{
	var $master;
  	var $sockets = array();
  	var $users   = array();
  	var $debug   = false;
 	
 	private $processor;
  	function __construct($address,$port)
  	{
  		
	    error_reporting(E_ALL);
	    set_time_limit(0);
	    ob_implicit_flush();
	
	    $this->master=socket_create(AF_INET, SOCK_STREAM, SOL_TCP)     or die("socket_create() failed");
	    socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)  or die("socket_option() failed");
	    socket_bind($this->master, $address, $port)                    or die("socket_bind() failed");
	    socket_listen($this->master,20)                                or die("socket_listen() failed");
	    $this->sockets[] = $this->master;
	    $this->say("Server Started : ".date('Y-m-d H:i:s'));
	    $this->say("Listening on   : ".$address." port ".$port);
	    $this->say("Master socket  : ".$this->master."\n");
	    $this->processor = new Processor($this);

    	while(true)
    	{
      		$changed = $this->sockets;
		    socket_select($changed,$write=NULL,$except=NULL,NULL);
      		foreach($changed as $socket)
      		{
        		if($socket==$this->master)
        		{
          			$client=socket_accept($this->master);
          			if($client<0)
          			{ 
          				$this->log("socket_accept() failed"); 
          				continue; 
          			}else
          			$this->connect($client);          			
        		}else
        		{
          			$bytes = @socket_recv($socket,$buffer,2048,0);
          			if($bytes==0)
          				$this->disconnect($socket); 
          			else
            			$user = $this->getuserbysocket($socket);
            			
            		if(!$user->handshake)
            		{
            			$this->dohandshake($user,$buffer);
            			$ret = array("type"=>"ServerMessage","metaData"=>"Welcome!");
            			$this->send($user->socket,json_encode($ret));
            		} 
            		else
            			$this->processor->processesMessage($user,$this->unwrap($buffer)); 
          		}
			}
		}
	}
  


  	public function send($client,$msg)
	{
	    $this->say("SEND> ".$msg);
    	$msg = $this->wrap($msg);
    	socket_write($client,$msg,strlen($msg));
  	}


  	function connect($socket)
  	{
    	$user = new SocketUser();
	    $user->id = uniqid();
	    $user->socket = $socket;
	    array_push($this->users,$user);
    	array_push($this->sockets,$socket);
    	$this->log($socket." CONNECTED!");
	}

  	function disconnect($socket)
  	{
    	$found=null;
    	$n=count($this->users);
    	for($i=0;$i<$n;$i++)    	
      		if($this->users[$i]->socket==$socket)
      		{
      			$found=$i; 
      			break;
      		}	 
    	
    	$boardId = false;
    	if(!is_null($found))
    	{
    		$boardId = $this->users[$i]->doodleBoardId;
    		array_splice($this->users,$found,1);
    		
    	}	
    	if($boardId !== false && $boardId != 0)
    		$this->processor->updateNumber($boardId); 
    		
    	$index=array_search($socket,$this->sockets);
    	socket_close($socket);
    	$this->log($socket." DISCONNECTED!");
    	if($index>=0)
    		array_splice($this->sockets,$index,1); 
  	}

  	function dohandshake($user,$buffer)
  	{
    	$this->log("\nRequesting handshake...");
    	$this->log($buffer);
    	$handshake = (string)new WebSocketHandshake($buffer);
    	$this->log("Handshaking...");
    	/*$upgrade  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
                	"Upgrade: WebSocket\r\n" .
                	"Connection: Upgrade\r\n" .
                	"WebSocket-Origin: " . $origin . "\r\n" .
                	"WebSocket-Location: ws://" . $host . $resource . "\r\n" .
                	"\r\n";*/
    	socket_write($user->socket,$handshake.chr(0),strlen($handshake.chr(0)));
    	$user->handshake=true;
    	$this->log($handshake);
    	$this->log("Done handshaking...");
    	return true;
  	}

  	function getheaders($req)
  	{
    	$r=$h=$o=null;
    	if(preg_match("/GET (.*) HTTP/"   ,$req,$match)) 
    		$r=$match[1];
    		 
    	if(preg_match("/Host: (.*)\r\n/"  ,$req,$match)) 
    		$h=$match[1]; 
    	
    	if(preg_match("/Origin: (.*)\r\n/",$req,$match)) 
    		$o=$match[1];
    		 
    	return array($r,$h,$o);
  	}

	function getuserbysocket($socket)
  	{
    	$found=null;
    	foreach($this->users as $user)    	
	      	if($user->socket==$socket)
	      	{ 
	      		$found=$user; 
	      		break; 
	      	}
    
    	return $found;
  	}

  function     say($msg="")
	{ 
		//echo "\n".$msg."\n"; 
	}
  function     log($msg=""){ if($this->debug){ echo $msg."\n"; } }
  function    wrap($msg=""){ return chr(0).$msg.chr(255); }
  function  unwrap($msg=""){ return substr($msg,1,strlen($msg)-2); }
	

}



?>
