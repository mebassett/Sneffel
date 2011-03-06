<?php
	if(!array_key_exists('text',$_POST))
		die("no form");
		
	$renderer = new LatexRender($_POST['formColor'],$_POST['formSize']);
	$imgName = $renderer->render ($_POST['text']);
	 
?>