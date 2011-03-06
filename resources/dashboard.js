$(document).ready(function() 
{
	$(".board").hide();
	$("a.boardClick").click(function (e){
		e.preventDefault();
		var id = this.href.substring(this.href.indexOf('#'));
		$(id).slideToggle('fast');
	});
});

function onClaim(id)
{
	$.get("/operationClaimBoard",{'id':id},onClaimRequest);
}
function onClaimRequest(data)
{
	$("#possibleBoards").load("/operationGetPossibleBoards");
}
function updateEmbed(roomId)
{
	var w = $("#width"+roomId).val();
	var h = $("#height"+roomId).val();
	$("#embedText"+roomId).val('<!-- START sneffelDoodleEmbed --><div id="sneffelDoodleEmbed'+roomId+'"><script type="text/javascript" src="http://www.sneffel.com/Sneffel/embedjs.jsp?roomId='+roomId+'&sneffelWidth=' + w + '&sneffelHeight='+h+'/></script></div><!-- END sneffelDoodleEmbed -->');
}