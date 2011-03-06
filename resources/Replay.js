WEB_SOCKET_SWF_LOCATION = "/resources/WebSocketMain.swf";
WEB_SOCKET_DEBUG = true;
var socket=null;
var numScribs=0;
var doodleBoard;
var timer;
var heightAdjust=108;
$(document).ready(function() 
{
	resize();
	
	doodleBoard = new DoodleBoard("drawPad",function(){},false);
		
	$("#restart").click(function(){
		next=0;
		clearTimeout(timer);
		doodleBoard.clear();
		timer=setTimeout('getNext()',time);
	});
		 
	var host = "ws://www.sneffel.com:443/websocket.php";	
	try{
		
		socket = new WebSocket(host);
		socket.onopen = function (msg) {
		if(picturesLoaded)				
				socket.send('{"type":"SYSreplayBoard","roomId":'+roomId+'}');
			
		};
		socket.onmessage = onMessage;
		socket.onerror = function (error){
			
		}
		socket.onclose = function (msg) {failMsg("I've lost the connection to the server! Please try again.")};
	}catch(ex)
	{
		failMsg("I cannot connect to my server! Please try again.  Sorry.")
	}	
		      $('.window .close').click(function (e) {         
         e.preventDefault();  
         $('#mask, .window').hide();  
     });
     $('#mask').click(function () {  
         $(this).hide();  
         $('.window').hide();  
     });      
     showModal('#loading');
     startPreload();
});
$(window).resize(function()
{
	resize();
});

function resize()
{
	var adj = parseInt(heightAdjust) + 120;
	$("#drawPadBox").css("width",$(window).width());
	$("#drawPadBox").css("height",$(window).height()-adj);
	$("#controls").css("width",$(window).width());	
	$("#controls").css("height",$(window).height()-adj+45);	
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
function failMsg(msg)
{
	$("#errMsg").html(msg);
	showModal("#error");
}

function onMessage(msg)
{
	if(msg==null)
		return;
	
	try{
		var obj = $.parseJSON(msg.data);

		switch(obj.type)
		{
			case 'SYSinit':
				numScribs = obj.numScribs;	
				timer=setTimeout('getNext()',time);				
			break;
			case 'SYSendBoard':
			break;
			default:	
				
				timer=setTimeout('getNext()',time);
				doodleBoard.processScribble(obj);
		}
		
	}catch(e)
	{

		//console.log(e);
	}
	
}

var next=0;
var time=150;
var percent=0;

function getNext()
{
	var serverMsg = new Object;
	serverMsg.type = "nextScribble";
	serverMsg.num = next;
	sendScribble(serverMsg);
	next++;
	percent = Math.round(100*(next/parseInt(numScribs)));
	if(percent>100)
		percent=100;
	
	$("#marker").html(percent + "&#37; Complete.");
	
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
