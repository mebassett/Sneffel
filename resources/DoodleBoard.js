function DoodleBoard(canvasId,sendScribbleFunction,editable)
{
	if(editable==null)
		editable=true;
		
	var canvas = document.getElementById(canvasId);
	var context = canvas.getContext("2d");
	var onSendScribble = sendScribbleFunction; //should be a function

	var currentState = "NONE";	
	var penSize = 1;
	var penColor = "000000";
	var currentScribble=null;
	var lastX = -1;
	var lastY = -1;
	var mouseDown = false;
	var layers = new Array();
	var selectedLayer = null;
	
	var drawMode = "DRAW";
	
	this.setDrawMode = function(str) {drawMode=str; }
	
	var canvas2 = document.createElement('canvas');
	if (typeof G_vmlCanvasManager != "undefined") 
		G_vmlCanvasManager.initElement(canvas2);
	

	canvas2.width = 2000;
	canvas2.height = 2000;
	
	var context2 = canvas2.getContext('2d');
	context.globalAlpha = 1;	
	
	context2.globalAlpha = 1;

	
	var canvasOffsetX=0;
	var canvasOffsetY=0;
	
	var scaleImg = new Image();
	scaleImg.src = "/resources/resize.gif";
	
	var moveImg = new Image();
	moveImg.src = "/resources/move.png";
	
	var deleteImg = new Image();
	deleteImg.src = "/resources/delete.png";
	
	if(editable==true)
	{
		$("#" + canvasId).mousemove(onMouseMove);
		$("#" + canvasId).mousedown(onMouseDown);
		$("#" + canvasId).mouseup(onMouseUp);
		$("#" + canvasId).mouseout(onMouseOut);
	}
	

	this.updateOffset = function()
	{
		
		canvasOffsetX=$("#" + canvasId).offset().left + $("#" + canvasId).scrollLeft();
		canvasOffsetY=$("#" + canvasId).offset().top + $("#" + canvasId).scrollTop();		
	}
	function addLayer(image,timestamp,imgLoc)
	{
		
		var layer = new Layer(image, $("#drawPadBox").scrollLeft()+10,$("#drawPadBox").scrollTop()+10,timestamp,imgLoc);
		layer.scale(image.width,image.height);
		layers.push(layer);
		reDraw();
		
	}
	function addLayerAndSize(image,timestamp,imgLoc,x,y,w,h)
	{
		
		var layer = new Layer(image, $("#drawPadBox").scrollLeft()+10,$("#drawPadBox").scrollTop()+10,timestamp,imgLoc);		
		layers.push(layer);
		layer.scale(parseInt(w),parseInt(h));
		layer.offsetX = parseInt(x);
		
		layer.offsetY = parseInt(y);
		reDraw();
	}
	this.addLayer = addLayer
	this.setPenSize = function (newSize)
	{
		penSize=newSize;
		
	};
	
	this.setPenColor = function (newColor)
	{
		penColor=newColor;
		console.log(newColor);
	};
	this.processScribble = function (scribble)
	{
		var erase = false
		if(scribble.type=="Erase")
		{
			scribble.type="Draw";
			scribble.color = "ERASE";
			erase=true;
		}else
		{
			scribble.color = "#"+scribble.color;
		}
		switch(scribble.type)
		{
			case "Draw":
				
				for (var i=1;i<scribble.xCoords.length-1;i++)
					drawLine(parseInt(scribble.xCoords[i-1]), parseInt(scribble.yCoords[i-1]),parseInt(scribble.xCoords[i]), parseInt(scribble.yCoords[i]),parseInt(scribble.width),scribble.color);
				
			break;
			case "Image":
				var scribId = getLayerIdFromTimestamp(scribble.timeCreated);
				
				if(scribId == -1)
				{
					var newImg = new Image();
					function onLoadImage()  {
						
						scribId = getLayerIdFromTimestamp(scribble.timeCreated); //we want to make sure it *still* hasn't loaded
						if(scribId == -1)
						{
							if(scribble.xCoords[0] ==null || scribble.xCoords.length < 2)						
								addLayer(this,scribble.timeCreated,scribble.metaData);
							else							
								addLayerAndSize(this,scribble.timeCreated,scribble.metaData,scribble.xCoords[0],scribble.yCoords[0],scribble.xCoords[1],scribble.yCoords[1]);
						}else if(scribble.xCoords[0] !=null && scribble.xCoords.length == 2)
						{						
							layers[scribId].scale(parseInt(scribble.xCoords[1]), parseInt(scribble.yCoords[1]));				
							layers[scribId].offsetX = parseInt(scribble.xCoords[0]) ;
							layers[scribId].offsetY = parseInt(scribble.yCoords[0]) ;
							
							//denoteSelectedLayer(layers[scribId]);							
						}
					};
															
					newImg.onload = onLoadImage
					newImg.src= scribble.metaData;
									
				}else
				{
					
					if(scribble.xCoords[0] !=null && scribble.xCoords.length == 2)
					{		
						layers[scribId].scale(parseInt(scribble.xCoords[1]), parseInt(scribble.yCoords[1]));				
						layers[scribId].offsetX = parseInt(scribble.xCoords[0]) ;
						layers[scribId].offsetY = parseInt(scribble.yCoords[0]) ;
						
						denoteSelectedLayer(layers[scribId]);
					}					
				}
			
			break;
			case "RImage":
				scribId = getLayerIdFromTimestamp(scribble.timeCreated);
				if(scribId==-1)
					return;
				deleteLayer(scribId);
			break;
		}
	};
	
	function getLayerIdFromTimestamp(timestamp)
	{
		var ret = -1;

		for(i in layers)		
			if(layers[i].timestamp == timestamp)
				ret = i
			
			
		return ret;	
	}
	
	function reDraw()
	{
		context.clearRect(0, 0, canvas.width, canvas.height);

		for(i in layers)
		{
			context.save();
			
			context.translate(layers[i].offsetX + (layers[i].width / 2), layers[i].offsetY + (layers[i].height / 2));							
			context.drawImage(layers[i].getCanvas(), 0 - (layers[i].width / 2), 0 - (layers[i].height / 2));
			
			
			context.restore();
		}
		context.drawImage(canvas2,0,0,2000,2000);		
	}
	this.clear = function ()
	{
		context.clearRect(0, 0, canvas.width, canvas.height);
		context2.clearRect(0, 0, canvas.width, canvas.height);
		layers = new Array();
		reDraw();		
	}
	this.loadFromFile = function(filename)
	{
		 var now = new Date();
		
		var loadImage = new Image;
		loadImage.onload = function()
		{
			context.clearRect(0, 0, canvas.width, canvas.height);
			context2.clearRect(0, 0, canvas.width, canvas.height);
			context.drawImage(this,0,0,2000,2000);		
			context2.drawImage(this,0,0,2000,2000);
			reDraw();
		};
		loadImage.src = filename+"?"+ now.getTime();
		
	}
	this.loadFromImage = function (img)
	{
			context.clearRect(0, 0, canvas.width, canvas.height);
			context2.clearRect(0, 0, canvas.width, canvas.height);
			context.drawImage(img,0,0,2000,2000);		
			context2.drawImage(img,0,0,2000,2000);
			reDraw();		
			
	}
	function onMouseMove(e)
	{
		
		e.preventDefault();
		var currentX = e.pageX - canvasOffsetX;
		var currentY = e.pageY - canvasOffsetY;
		if(mouseDown)
			switch(currentState)
			{
				case "DRAW":
					var theColor;
					if(drawMode=="ERASE")
					{
						theColor="ERASE";
					}else
						theColor = "#"+penColor;
					if(currentScribble == null)
						return;				
			
					if(lastX != -1 && lastY != -1)
						drawLine(lastX, lastY,currentX, currentY,penSize,theColor);
					
					if(currentScribble.xCoords.length>=50)
					{
						onSendScribble(currentScribble);
						newScrib = new ScribbleDatum(drawMode == "ERASE" ? "Erase" : "Draw",penSize,penColor);
						newScrib.xCoords = currentScribble.xCoords.slice(48);
						newScrib.yCoords = currentScribble.yCoords.slice(48);
						currentScribble=newScrib;
					}			
					lastX = currentX;
					lastY = currentY;
			
					currentScribble.xCoords.push(lastX);
					currentScribble.yCoords.push(lastY);			
				break;
				case "SCALE":
					var square = selectedLayer.getSquare();
					var scaleToWidth = Math.sqrt(Math.pow(square.d.x - (e.pageX - canvasOffsetX), 2) + Math.pow(square.d.y - (e.pageY - canvasOffsetY), 2));
					var scaleToHeight = Math.sqrt(Math.pow(square.b.x - (e.pageX - canvasOffsetX), 2) + Math.pow(square.b.y - (e.pageY - canvasOffsetY), 2));			
					selectedLayer.scale(scaleToWidth, scaleToHeight);
					denoteSelectedLayer(selectedLayer);
					lastX = -1;
					lastY = -1;
				break;
				case "MOVE":
				
					selectedLayer.offsetX = currentX   ;
					selectedLayer.offsetY = currentY  ;
					denoteSelectedLayer(selectedLayer);
					lastX = currentX;
					lastY = currentY;
				break;								
			}	
		else
		{
			var intersect=false
			if(layers.length > 0)					
				for (var i=0;i<layers.length;i++)						
					if (layers[i].intersect(currentX, currentY)) 
					{
						
						selectedLayer = layers[i];
						intersect=true;
						if (isScalingArea(currentX, currentY, layers[i]))
						{
							
							$("#"+canvasId).addClass("clicker");
							currentState = "SCALE"; 
						}else if (isMovingArea(currentX,currentY,layers[i]))
						{
						
							$("#"+canvasId).addClass("clicker");
							currentState = "MOVE";
						}else if(isDeleteArea(currentX,currentY,layers[i]))
						{
							
							$("#"+canvasId).addClass("clicker");
							currentState = "DELETE";
						}else
						{
							currentState = "DRAW";
							$("#"+canvasId).removeClass("clicker");
						}							
						
							
						denoteSelectedLayer(layers[i]);	
						break;						
					}
			if(!intersect)
			{
				currentState = "DRAW";
				cursorOffset = 32;
				$("#"+canvasId).removeClass("clicker");
				reDraw();
			}
		}
	}
	
	function onMouseDown(e)
	{
		e.preventDefault();
		if(	currentState == "DRAW")
			startDrawing();
		mouseDown=true;
	}
	function onMouseUp(e)
	{
		//e.preventDefault();
		if(	currentState == "DRAW")
			endDrawing();
		else if(currentState == "SCALE" || currentState == "MOVE")
			endScaling();
		else if(currentState=="DELETE")
		{
			var scrib = new ScribbleDatum("RImage",0,0);
			scrib.metaData = selectedLayer.timestamp;
			scrib.timeCreated = selectedLayer.timestamp;
			
			onSendScribble(scrib);
			deleteLayer(getLayerIdFromTimestamp(selectedLayer.timestamp));			
		}
		lastX=-1;lastY=-1;
		//currentState = "NONE";
		mouseDown=false;
	}
	function onMouseOut(e)
	{
		//e.preventDefault();
		if(	currentState == "DRAW")
			endDrawing();

		lastX=-1;lastY=-1;
		currentState = "NONE";
		mouseDown=false;
	}

	
	function deleteLayer(i)
	{
		i = parseInt(i);
		if (i >= 0 && i < layers.length) {
			layers.splice(i,1);
			reDraw();
		}		
	}

	function startDrawing()
	{
		
		penSize = $("#slider").slider("value");
		penColor =drawMode == "ERASE" ? "ffffff" : $("#penColor").val();
		
		currentScribble = new ScribbleDatum(drawMode == "ERASE" ? "Erase" : "Draw" ,penSize,penColor);
	}
	function endDrawing()
	{
		
		
		onSendScribble(currentScribble);
		currentScribble = null;
		
		
	}
	function endScaling()
	{
		currentScribble = new ScribbleDatum("Image",0,0);	
		currentScribble.xCoords = new Array(selectedLayer.offsetX,selectedLayer.width);
		currentScribble.yCoords = new Array(selectedLayer.offsetY,selectedLayer.height);
		currentScribble.timeCreated = selectedLayer.timestamp;
		currentScribble.metaData = selectedLayer.imgLoc;
		onSendScribble(currentScribble);
		currentScribble = null;

	}
	
	function denoteSelectedLayer(layer)
	{
		reDraw();
		context.save();
		context.translate(layer.offsetX + (layer.width / 2), layer.offsetY + (layer.height / 2));
		if(editable)
		{
			context.lineWidth=1;
			context.strokeStyle = "#fff";
			context.globalAlpha = 0.75;	
			context.strokeRect(0 - (layer.width / 2), 0 - (layer.height / 2),layer.width, layer.height);		
			context.drawImage(scaleImg,	(layer.width / 2) - scaleImg.width,	(layer.height / 2) - scaleImg.height);	
			context.drawImage(moveImg,0 - (layer.width / 2), 0 - (layer.height / 2));
			context.drawImage(deleteImg,(layer.width / 2) - deleteImg.width,0 - (layer.height / 2));
		}
		

		context.restore();
		context.globalAlpha = 1;	
	
	}
	
	function imageLine (x0,y0,x1,y1,width,color)
	{

		if(width > 2)
			width=width/2;
		else
			width=2;
		
		if(color=="ERASE")
		{			
			context.globalCompositeOperation = "copy";
			context2.globalCompositeOperation = "copy";
			color="rgba(0,0,0,0)";
		}		
		context.fillStyle   = color;
		context.strokeStyle = color;
		context.lineWidth   = width;
		context.beginPath();
		context.moveTo(x0,y0);
		context.lineTo(x1,y1);		
		context.stroke();	
		context.closePath();	
		
		context2.fillStyle   = color;
		context2.strokeStyle = color;
		context2.lineWidth   = width;
		context2.beginPath();
		context2.moveTo(x0,y0);
		context2.lineTo(x1,y1);		
		context2.stroke();	
		context2.closePath();	
		context.globalCompositeOperation = "source-over";
		context2.globalCompositeOperation = "source-over";
	}
	function drawLine(x0, y0,x1, y1,radius,color)
	{
		//imageLine (x0,y0,x1,y1,color)
		radius=parseInt(radius);
		if(radius == 1)
		{
			imageLine (x0,y0,x1,y1,1,color)
		}else
		{
			radius -= 1;
			var f = 1 - radius;
			var ddF_x= 1;
			var ddF_y = -2 * radius;
			var x= 0;
			var y = radius;
			
			imageLine(x0, y0 + radius,x1, y1 + radius,radius,color);
			imageLine(x0, y0 - radius,x1, y1 - radius,radius,color);
			imageLine(x0 + radius, y0,x1 + radius, y1,radius,color);
			imageLine(x0 - radius, y0,x1 - radius, y1,radius,color);	
			
			while(x< y)
			{
				if(f >= 0)
				{
					y--;
				    ddF_y += 2;
				    f += ddF_y;
				}
				x++;
				ddF_x+= 2;
				f += ddF_x;
				imageLine(x0 + x, y0 + y,x1 + x, y1 + y,radius,color);
				imageLine(x0 - x, y0 + y,x1 - x, y1 + y,radius,color);
				imageLine(x0 + x, y0 - y,x1 + x, y1 - y,radius,color);
				imageLine(x0 - x, y0 - y,x1 - x, y1 - y,radius,color);
				imageLine(x0 + y, y0 + x,x1 + y, y1 + x,radius,color);
				imageLine(x0 - y, y0 + x,x1 - y, y1 + x,radius,color);
				imageLine(x0 + y, y0 - x,x1 + y, y1 - x,radius,color);
				imageLine(x0 - y, y0 - x,x1 - y, y1 - x,radius,color);			
			}	
		}
		
	}
	function isScalingArea(mX, mY, layer) 
	{
		var scaleOffsetX = layer.offsetX + layer.width;
		var scaleOffsetY = layer.offsetY + layer.height;
		
		var square = new Square(
				new Vector(scaleOffsetX - scaleImg.width, scaleOffsetY - scaleImg.height),
				new Vector(scaleOffsetX, scaleOffsetY - scaleImg.height),
				new Vector(scaleOffsetX, scaleOffsetY),
				new Vector(scaleOffsetX - scaleImg.width, scaleOffsetY));

		
		square.alignBottomRight(layer.getSquare().c);
		
		return square.intersect(new Vector(mX, mY));
		
	}
	function isMovingArea(mX,mY,layer)
	{
		var scaleOffsetX = layer.offsetX + layer.width;
		var scaleOffsetY = layer.offsetY + layer.height;
		
		var square = new Square(
				new Vector(scaleOffsetX, scaleOffsetY),
				new Vector(scaleOffsetX+moveImg.width, scaleOffsetY),
				new Vector(scaleOffsetX+moveImg.width, scaleOffsetY+moveImg.height),
				new Vector(scaleOffsetX, scaleOffsetY+moveImg.height));
		
		square.alignTopLeft(layer.getSquare().a);

		return square.intersect(new Vector(mX, mY));	
	}
	function isDeleteArea(mX,mY,layer)
	{
		var scaleOffsetX = layer.offsetX + layer.width;
		var scaleOffsetY = layer.offsetY + layer.height;
		
		var square = new Square(
				new Vector(scaleOffsetX - deleteImg.width, layer.offsetY),
				new Vector(scaleOffsetX, layer.offsetY),
				new Vector(scaleOffsetX, layer.offsetY + deleteImg.height),
				new Vector(scaleOffsetX - deleteImg.width, layer.offsetY + deleteImg.height));

		
		square.alignTopRight(layer.getSquare().b);
			
		return square.intersect(new Vector(mX, mY));
	}
	
	

	
}
function ScribbleDatum(type,width,color)
{
	this.doodleBoardId=0;
	this.type = type;		
	this.xCoords = new Array();
	this.yCoords = new Array();
	this.color=color;		
	this.metaData='';
	this.sizeX='';
	this.sizeY='';
	this.width=width;	
	this.undoName='';	
	this.bitmapData='';	
	this.timeCreated='';
}
function Layer(img,offX,offY,timestamp,imgLoc) {
	this.img = img;	
	this.imgLoc = imgLoc;
	this.offsetX = offX;
	this.offsetY = offY;
	this.timestamp = timestamp;
	this.width = img.naturalHeight;
	this.height = img.naturalHeight;
	
	this.canvas = document.createElement('canvas');
	if (typeof G_vmlCanvasManager != "undefined") 
		G_vmlCanvasManager.initElement(this.canvas);
	this.canvas.width = this.width;
	this.canvas.height = this.height;
	this.context = this.canvas.getContext('2d');
	this.context.save();
	this.context.translate(this.width / 2, this.height / 2);
	


	this.getImage = function() {
		return this.img;
	}
	
	this.setCompositeOperation = function(compositeOperation) {
		this.compositeOperation = compositeOperation;
	}
	this.getCompositeOperation = function() {
		return this.compositeOperation;
	}
	
	this.redraw = function() {
		var startX = this.width / 2 - this.width;
		var startY = this.height / 2 - this.height;
		
		this.context.clearRect(startX, startY, this.canvas.width, this.canvas.height);
		this.context.drawImage(this.img, startX, startY, this.width, this.height);
	}
	
	this.getCanvas = function() {
		this.redraw();
		return this.canvas;
	}
	
	this.intersect = function(x, y) {

		var square = new Square(
				new Vector(this.offsetX, this.offsetY),
				new Vector(this.offsetX + this.width, this.offsetY),
				new Vector(this.offsetX + this.width, this.offsetY + this.height),
				new Vector(this.offsetX, this.offsetY + this.height));
		
		
		
		return square.intersect(new Vector(x, y));
	}
	
	this.getSquare = function() {
		var square = new Square(
				new Vector(this.offsetX, this.offsetY),
				new Vector(this.offsetX + this.width, this.offsetY),
				new Vector(this.offsetX + this.width, this.offsetY + this.height),
				new Vector(this.offsetX, this.offsetY + this.height));
				
		
		
		return square;
	}
	
	this.scale = function(width, height) {
		this.context.restore();
		
		this.width = width;
		this.height = height;
		this.canvas.width = width;
		this.canvas.height = height;
		
		this.context.translate(this.width / 2, this.height / 2);
		
		this.redraw();
	}
	
	
	
	

}

function Square(a, b, c, d) {
	this.a = a;
	this.b = b;
	this.c = c;
	this.d = d;
	this.origin = centerSquareOrigin(a,b,c,d);
	
	this.intersect = function(mouse) {
		return (!intersectWithLine(this.origin, mouse, this.a, this.b) &&
			!intersectWithLine(this.origin, mouse, this.b, this.c) &&
			!intersectWithLine(this.origin, mouse, this.c, this.d) &&
			!intersectWithLine(this.origin, mouse, this.d, this.a));
	}
	

	
	this.alignBottomRight = function(alignPoint) {
		var diff = new Vector(alignPoint.x - this.c.x, alignPoint.y - this.c.y);
		
		this.a = this.a.add(diff);
		this.b = this.b.add(diff);
		this.c = this.c.add(diff);
		this.d = this.d.add(diff);
		this.origin = centerSquareOrigin(this.a, this.b, this.c, this.d);
	}
	
	this.alignTopRight = function(alignPoint) {
		var diff = new Vector(alignPoint.x - this.b.x, alignPoint.y - this.b.y);
		
		this.a = this.a.add(diff);
		this.b = this.b.add(diff);
		this.c = this.c.add(diff);
		this.d = this.d.add(diff);
		this.origin = centerSquareOrigin(this.a, this.b, this.c, this.d);
	}
	
	this.alignTopLeft = function(alignPoint) {
		var diff = new Vector(alignPoint.x - this.a.x, alignPoint.y - this.a.y);
		
		this.a = this.a.add(diff);
		this.b = this.b.add(diff);
		this.c = this.c.add(diff);
		this.d = this.d.add(diff);
		this.origin = centerSquareOrigin(this.a, this.b, this.c, this.d);
	}
	
	var epsilon = 10e-6;
	
	function centerSquareOrigin(a, b, c, d) {
		p = a;
		r = c.subtract(a);
		q = b;
		s = d.subtract(b);
		
		rCrossS = cross(r, s);
		if(rCrossS <= epsilon && rCrossS >= -1 * epsilon){
			return;
		}
		t = cross(q.subtract(p), s)/rCrossS;
		u = cross(q.subtract(p), r)/rCrossS;
		if (0 <= u && u <= 1 && 0 <= t && t <= 1){
			intPoint = p.add(r.scalarMult(t));
			return new Vector(intPoint.x, intPoint.y);
		}
		
		return null;
	}
	
	function cross(v1, v2) {
		return v1.x * v2.y - v2.x * v1.y;
	}
	
	function intersectWithLine(l1p1, l1p2, l2p1, l2p2) {
		p = l1p1;
		r = l1p2.subtract(l1p1);
		q = l2p1;
		s = l2p2.subtract(l2p1);
		
		rCrossS = cross(r, s);
		if(rCrossS <= epsilon && rCrossS >= -1 * epsilon){
			return false;
		}
		
		t = cross(q.subtract(p), s)/rCrossS;
		u = cross(q.subtract(p), r)/rCrossS;
		if(0 <= u && u <= 1 && 0 <= t && t <= 1){
			return true;
		} else{
			return false;
		}
		
	}
}

/**
 * http://bloggingmath.wordpress.com/2009/05/29/line-segment-intersection/
 */
function Vector(x, y) {
	this.x = x;
	this.y = y;

	this.scalarMult = function(scalar){
		return new Vector(this.x * scalar, this.y * scalar);
	}
	this.dot = function(v2) {
		return this.x * v2.x + this.y * v2.y;
	};
	this.perp = function() {
		return new Vector(-1 * this.y, this.x);
	};
	this.subtract = function(v2) {
		return this.add(v2.scalarMult(-1));//new Vector(this.x - v2.x, this.y - v2.y);
	};
	this.add = function(v2) {
		return new Vector(this.x + v2.x, this.y + v2.y);
	}
}
