WEB_SOCKET_SWF_LOCATION = "/resources/WebSocketMain.swf";
 WEB_SOCKET_DEBUG = true;
var heightAdjust=87;
var drawing = false;
var context=null;

var lastX=-1;
var lastY=-1;

var currentScribble = null;
var socket=null;
var doodleBoard = null;

$(document).ready(function() 
{
	//drawPad stuff
	
	$("#drawPadBox").scroll(function (){doodleBoard.updateOffset();});
	$("#slider").slider({ orientation: 'vertical',min:1,max:15,
		slide: showSize,change:showSize
		 });
	$("#drawButton").click(function (){
		$("#drawButton").addClass("buttonClick");	
		$("#eraseButton").removeClass("buttonClick");	
		doodleBoard.setDrawMode("DRAW");
			
		$("#drawPad").removeClass("erase");
	});
	$("#eraseButton").click(function (){
		$("#drawButton").removeClass("buttonClick");	
		$("#eraseButton").addClass("buttonClick");		
		doodleBoard.setDrawMode("ERASE");	
		$("#drawPad").addClass("erase");
	});

	
	doodleBoard = new DoodleBoard("drawPad",sendScribble);
	
	$("#penSize").change(function (e){
		doodleBoard.setPenSize($("#penSize").val());
	});
	$("#penColor").change(function(e){
		$("#formColor").val($("#penColor").val());
	
	});
	

	
	
	//websocket stuff
	var host = "ws://www.sneffel.com:443/websocket.php";
	
	try{
		
		socket = new WebSocket(host);
		socket.onopen = function (msg) {
			if(this.readyState == 1)				
		    {
				socket.send('{"type":"SYSregBoard","roomId":'+roomId+'}');
			loadedConnection=true;
			closeLoader();
		    }	else 
				failMsg("I've lost the connection to the server! Please try again.")
			log("connected" + this.readyState);
		};
		socket.onmessage = onMessage;
		socket.onerror = function (error){
			log("error!"+error);
		}
		socket.onclose = function (msg) {failMsg("I've lost the connection to the server! Please try again.")};
	}catch(ex)
	{
		failMsg("I cannot connect to my server! Please try again.  Sorry.")
	}
	doodleBoard.updateOffset();
	showModal('#loading');
	
	$("#loader").attr("src",'/opLoadBoard?boardId='+roomId)
	
	
     
	$('button#uploadButton').click(function(e){
        
        e.preventDefault();              
       	showModal("#uploadBox");
     });
     $('.window .close').click(function (e) {         
         e.preventDefault();  
         $('#mask, .window').hide();  
     });
     $('#mask').click(function () {  
         $(this).hide();  
         $('.window, .diagBox').hide();  
     });   
     resize(); 
     	
});
$(window).resize(function()
{
	resize();
});

function resize()
{
	var adj = parseInt(heightAdjust) + 150;
	$("#drawPadBox").css("width",$(window).width()-32);	
	$("#drawPadBox").css("height",$(window).height()-adj );
	$("#controls").css("width",$(window).width());	
	$("#controls").css("height",$(window).height()-adj +45);	
	$('input.textPost').css("width",$(window).width()-254);
	doodleBoard.updateOffset();	
}

function showModal(id)
{
	var maskHeight = $(document).height();  
    var maskWidth = $(window).width();
    $('#mask').css({'width':maskWidth,'height':maskHeight});
    $('#mask').fadeTo("fast",0.8);    
    var winH = $(window).height();  
    var winW = $(window).width();
    $(id).css('top',  winH/2-$(id).height()/2);  
    $(id).css('left', winW/2-$(id).width()/2);    
    $(id).fadeIn(2000);	
}
function showSize(e)
{
	$("#showSize").html($("#slider").slider("value"));
	$("#formSize").val($("#slider").slider("value"));
	//doodleBoard.penSize = $("#slider").value();
}
function log(msg)
{
	
}
function failMsg(msg)
{
	$("#errMsg").html(msg);
	showModal("#error");
}


function sendScribble(scribble)
{
	
	if(scribble == null)
		return;
	var serverText = JSON.stringify(scribble);
	if(socket!=null && socket.readyState == 1)
		socket.send(serverText);	
	else
	{
		failMsg("Something has went terribly wrong.  Please try again. Sorry.");	
	}
}

function onMessage(msg)
{
	try{
		var obj = $.parseJSON(msg.data);
		switch(obj.type)
		{
			case 'SYSusers':
				if(obj.num == 1)
					$("#usersOnline").html(obj.num + " Person drawing.");
				else
					$("#usersOnline").html(obj.num + " Persons drawing.");
			break;
			default:
				doodleBoard.processScribble($.parseJSON(msg.data));
			
		}
		
	}catch(e)
	{

		//console.log(e);
	}
	
}

var firstLoad = false;
function onHelperLoad()
{	
	if(!firstLoad)
	{
		firstLoad=true;
		return;
	}
    $("#mask").hide();  
    $('.window').hide();  
	var boardResponse = $.parseJSON($("#formHelper").contents().find("body").html());
	var newImg = new Image();
	newImg.onload = function(){
		
		var scrib = new ScribbleDatum("Image",0,0);
		scrib.timeCreated = Math.round(new Date().getTime() / 1000);
		scrib.metaData = boardResponse.picSource;
		doodleBoard.addLayer(this,scrib.timeCreated,scrib.metaData);
		sendScribble(scrib);
		
		
	};
	if(boardResponse != null)
		newImg.src= boardResponse.picSource;
	
}

var firstLoader = true
function onLoader()
{


	var data = $.parseJSON($("#loader").contents().find("body").html());
	if(data != null)
	{
		doodleBoard.loadFromFile(data.filename);
		if(firstLoader)
		{
			firstLoader=false;
			for(var i=0;i<data.data.length;i++)		
				doodleBoard.processScribble(data.data[i]);
		}	
	    loadedImg=true;
	    closeLoader();
	}  	
}


var loadedImg=false;
var loadedConnection=false;
function closeLoader()
{
    if(loadedImg && loadedConnection)
	$('#mask, .window').hide();
	resize(); 
}
