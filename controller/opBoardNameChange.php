<?php
	$boardId = $_GET['boardId'];
	$newName = urlencode($_GET['newName']);
	
	$s = $app->db->prepare("select id from DoodleBoard where name=:n and schoolId=:id");
	$s->bindParam(':n',$newName);
	$s->bindParam(':id',$school->id);
	$s->execute();
	$obj = $s->fetchObject();
	
	if($obj)
		echo '{"response":"nameUsed"}';
	else
	{
		$s = $app->db->prepare("update DoodleBoard set name=:n where id=:id");
		$s->bindParam(':n',$newName);
		$s->bindParam(':id',$boardId);
		$s->execute();
		echo '{"response":"success","newname":"'.$newName.'"}';
	}

?>
