<?php
/*	index.php
 * 	one big happy switchboard to process an incomming HTTP request
 *  written by gegn corp (http://www.gegn.net)
 */
	require "model/loadApi.php";	

	function setView($setName)
	{
		global $controllerPageMap,$controller;
		$controllerPageMap[$controller][1] = $setName;
	}
	
	//some magic to grab the uri
	$replace = substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], "/index.php"));
	$uri = substr(str_replace($replace,'',$_SERVER['REQUEST_URI']),1); //strip the app's dir from the uri
	$subdomain = current(explode('.', $_SERVER['HTTP_HOST']));

	
	
	if(strrpos($uri,'?'))
		$uri = substr($uri,0,strrpos($uri,'?'));
	$uri = explode("/",$uri); //expload the URI into happy information!
	
	$errStr="";
	$user="";
	
	//this singleton class may (or may not) contain some useful functions for this app
	$app = Application::getInstance();
	$user = new User(true);
	
	$school = School::getFromDomain($subdomain);
	
	
	if($subdomain != "www" && !$school)
	{
		header("Location: http://www.sneffel.com");
		exit;
	}
	
	
	
	if(!$school || $subdomain == "www")
	{
		$default = "main";	
	}else
	{
		$default = "subdomain";
	}
	//includes the appropriate controller/view
	$controller=$uri[0];	
	if($controller=='new')
		$controller=$default;


	/*if (!isset($controllerPageMap[$controller]) || 	!file_exists('view/'.$controllerPageMap[$controller][1]))
		$controller="index";	*/
			
	try
	{
	
	
		if( file_exists('controller/'.$controller.".php"))
			include 'controller/'.$controller.".php";
	
	
	
		if(file_exists('view/'.$controller.".phtml"))
			include 'view/'.$controller.".phtml";
		else{
			$controller=$default;
			include 'controller/'.$default.'.php';
		
			include 'view/'.$controller.'.phtml';
		}
	}catch(Exception $e)
	{
		echo "error! ".$e->getMessage();
	}


?>
