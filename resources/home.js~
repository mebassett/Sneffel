$(document).ready(function() 
{
	$(".modal").click(function (e){
		e.preventDefault();		
		$("#vid1").hide();
   		$("#vid2").hide();
   		$("#vid3").hide();
		$($(this).attr('href')).show();
		
	});
	$('.window .close').click(function (e) {         
         e.preventDefault();  
         $('#mask, .window').hide();  
     });
     $('#mask').click(function () {  
         $(this).hide();  
         $('.window').hide();  
     });     
    $("#vid1").hide();
    $("#vid2").hide();
    

});
	
function showModal(id)
{
	var maskHeight = $(document).height();  
    var maskWidth = $(window).width();
    $('#mask').css({'width':maskWidth,'height':maskHeight});
    $('#mask').fadeTo("fast",0.8);    
    var winH = $(window).height();  
    var winW = $(window).width();
    $(id).css('top',  80);  
    $(id).css('left', winW/2-$(id).width()/2);    
    $(id).fadeIn(2000);	
}
