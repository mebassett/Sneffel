<?php
	if(!array_key_exists('boardId',$_GET))
		exit;
	ini_set("memory_limit","128M");
	$board = $_GET['boardId'];
	if($school)
		$board = Whiteboard::getBySchoolId($board,$school->id);
	else
		$board = Whiteboard::getById($board);
	$im=$board->createPng(false,false);
	
	$filename = '';
	if($im)
	{
		$filename = "/imgData/".$board->id.".png";
		imagepng($im,"/var/www/html/sneffel.com".$filename);
	}
	$board->createThumb($im);
	$s = $app->db->prepare("select type,metaData,xCoords,yCoords,width,color,timeCreated from Scribble where DoodleBoardId=:bid and (type = 'Image' or type='RImage' )");
	$s->bindParam(':bid',$board->id);
	$s->execute();
	
	$ret = array();
	
	while($obj = $s->fetchObject())
	{
		$obj->xCoords = explode(":",$obj->xCoords);		
		$obj->yCoords = explode(":",$obj->yCoords);
		$obj->color = substr(dechex($obj->color + hexdec('FF000000')),2);
		$ret[$obj->timeCreated] = $obj;
	}
	$retNew = array();
	foreach($ret as $o)
		array_push($retNew,$o);
	$ret= $retNew;
	
	$obj = new DataObj;
	$obj->filename = $filename;
	$obj->data = $ret;
	
	$out = json_encode($obj);
		
class DataObj
{
	public $filename;
	public $data;
}
?>
