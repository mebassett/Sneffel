var firstLoad = false;

function onHelperLoad()
{	
	if(firstLoad)
	{
		$("#errorName").text("");
		var boardResponse = $.parseJSON($("#formHelper").contents().find("body").html());
		if(boardResponse.boardExists == 'false')
		{
			$("#editForm").attr("action","/operationPreviewBoard?tempId="+boardResponse.newId);
			
			$("#boardIdInput").attr("value",boardResponse.newId);
			$("#done").css("visibility","visible");
			
			
			$("#previewBox").css("height",500+boardResponse.heightAdjust);
			
			$("#previewBox").attr("src","/previewEmbedBoard?boardId="+boardResponse.newId);
		}else
		{
		
			$("#errorName").text("This name has already been used! Try again.");
		}
	}else
		firstLoad = true;
}

		function updateEmbed(roomId)
{
	var w = $("#width").val();
	var h = $("#height").val();
	$("#embedText").val('<!-- START sneffelDoodleEmbed --><div id="sneffelDoodleEmbed'+roomId+'"><script type="text/javascript" src="http://www.sneffel.com/Sneffel/embedjs.jsp?roomId='+roomId+'&sneffelWidth=' + w + '&sneffelHeight='+h+'/></script></div><!-- END sneffelDoodleEmbed -->');
}