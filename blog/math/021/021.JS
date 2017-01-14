/** 
* @usage   三角板 
* @author  mw 
* @date    2016年01月02日  星期六  09:02:38  
* @param 
* @return 
* 
*/  
/* 
三角板 
triangle ;set square; 
 
*/  
  
function Triangle() {  
    this.R = 100;  
      
    this.init = function(r) {  
        this.R = r;  
    }  
  
    this.angle30 = function(xOffset, yOffset, rotate) {  
        var edge30 = -this.R * Math.tan(Math.PI/6);       
        var edge60 = this.R;  
  
        var offset = this.R * 0.12;  
        var edge30_2 = -((this.R-offset) * Math.tan(Math.PI/6)-offset*(1/Math.cos(Math.PI/6)+1));  
        var edge60_2 = (this.R-offset)-offset*(1/Math.tan(Math.PI/6)+1/Math.sin(Math.PI/6));  
          
          
        //以直角点为坐标原点, 60度角所对边为x轴，30度角所对边为y轴  
        plot.save()  
            .setLineWidth(5)  
            .setStrokeStyle('blue');  
          
  
        plot.translate(xOffset, -yOffset)  
            .rotate(-rotate);  
              
        plot.beginPath()  
            .moveTo(0, 0)  
            .lineTo(edge60, 0)  
            .lineTo(0, edge30)  
            .closePath()  
            .stroke();  
          
          
        plot.beginPath()  
            .moveTo(offset, -offset)  
            .lineTo(offset + edge60_2, -offset)  
            .lineTo(offset, -offset + edge30_2)  
            .closePath()  
            .stroke();  
              
        plot.restore();  
    }  
      
    this.angle60 = function(xOffset, yOffset, rotate) {  
        var edge30 = this.R * Math.tan(Math.PI/6);        
        var edge60 = -this.R;  
  
        var offset = this.R * 0.12;  
        var edge30_2 = (this.R-offset) * Math.tan(Math.PI/6)-offset*(1/Math.cos(Math.PI/6)+1);  
        var edge60_2 = -((this.R-offset)-offset*(1/Math.tan(Math.PI/6)+1/Math.sin(Math.PI/6)));  
          
          
        //以直角点为坐标原点, 60度角所对边为x轴，30度角所对边为y轴  
        plot.save()  
            .setLineWidth(5)  
            .setStrokeStyle('green');  
          
  
        plot.translate(xOffset, -yOffset)  
            .rotate(-rotate);  
              
        plot.beginPath()  
            .moveTo(0, 0)  
            .lineTo(edge30, 0)  
            .lineTo(0, edge60)  
            .closePath()  
            .stroke();  
          
          
        plot.beginPath()  
            .moveTo(offset, -offset)  
            .lineTo(offset + edge30_2, -offset)  
            .lineTo(offset, -offset + edge60_2)  
            .closePath()  
            .stroke();  
              
        plot.restore();  
    }  
      
    this.angle45 = function(xOffset, yOffset, rotate) {  
        var edge45 = this.R * Math.sin(Math.PI/4);    
        var offset = this.R * 0.1;  
                  
        //以直角点为坐标原点, 45度角所对边为x轴和y轴  
        plot.save()  
            .setLineWidth(5)  
            .setStrokeStyle('red');  
  
        plot.translate(xOffset, -yOffset)  
            .rotate(-rotate);     
              
        plot.beginPath()  
            .moveTo(0, 0)  
            .lineTo(edge45, 0)  
            .lineTo(0, -edge45)  
            .closePath()  
            .stroke();  
          
        plot.beginPath()  
            .arc(edge45*0.42, -edge45 *0.42, edge45*0.3, Math.PI/4, Math.PI/4+Math.PI)  
            .closePath()  
            .stroke();  
          
        plot.restore();  
    }  
}  
  
function myDraw() {  
    plot.init();  
    setPreference();  
      
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var tri = new Triangle();  
    tri.init(160);  
      
    tri.angle30(0, 0, Math.PI);  
    tri.angle60(0, 0);  
    tri.angle45(0, 0, -Math.PI/2 );  
  
}

//60度   
function myDraw() {  
    plot.init();  
    setPreference();  
      
    plot.fillText('这个角是60度。', 30, 50, 100);  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var r = 160;  
    var tri = new Triangle();  
    tri.init(r);  
      
    tri.angle60(0, 0,0);  
    tri.angle30(0,0, Math.PI/2);  
  
  
}

//75度  
function myDraw() {  
    plot.init();  
    setPreference();  
      
    plot.fillText('这个角是75度。', 30, 50, 100);  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var r = 160;  
    var tri = new Triangle();  
    tri.init(r);  
      
    tri.angle60(0, -r * (1-0.707),0);  
    tri.angle45(0,0, Math.PI/2);  
  
  
}

//105度  
function myDraw() {  
    plot.init();  
    setPreference();  
      
    plot.fillText('这个角是105度。', 30, 50, 100);  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var r = 160;  
    var tri = new Triangle();  
    tri.init(r);  
      
    tri.angle30(0, r*(0.707-1/1.732),0);  
    tri.angle45(0,0, Math.PI/2);  
  
  
}

//120度  
function myDraw() {  
    plot.init();  
    setPreference();  
      
    plot.fillText('这个角是120度。', 30, 50, 100);  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var r = 160;  
    var tri = new Triangle();  
    tri.init(r);  
      
    tri.angle60(0, 0,Math.PI/2);  
    tri.angle30(0,0,0 );  
  
  
}


//165度  
function myDraw() {  
    plot.init();  
    setPreference();  
      
    plot.fillText('这个角是165度。', 30, 50, 100);  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var r = 160;  
    var tri = new Triangle();  
    tri.init(r);  
      
    tri.angle60(0, -r/1.732,Math.PI/2);  
    tri.angle30(0,-r/1.732,0 );  
    tri.angle45(r*0.707*1.732/2,-r*0.707/2,Math.PI/3);  
  
  
}