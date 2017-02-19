/** 
    * @usage 
    * @author  mw 
    * @date    2016年01月23日  星期六  11:00:30  
    * @param 
    * @return 
    * 
    */  
    //Protractor 量角器  
    this.protractor = function(xOffset, yOffset, r, rotate, showText) {  
        r = r ? r : 100;  
          
        plot.save()  
            .setFillStyle('red')              
            .translate(xOffset, yOffset)  
            .rotate(rotate);  
          
        //r0 是刻度线内圆上的点，r是外圆上的点  
        var r0 = 0.2 * r;  
                  
        var angle, sin1, cos1, sin2, cos2;  
          
        plot.setTextAlign('center');  
          
        for (var i = 0; i < 180; i+=5) {  
            angle = -Math.PI/180 * i;  
              
            if ( i % 90 == 0) {  
                plot.setLineWidth(4)  
                    .setStrokeStyle('red');  
                r0 = 0.1 * r;  
            }  
            else if (i % 15 == 0) {  
                plot.setLineWidth(2)  
                    .setStrokeStyle('CC0000');  
                r0 = 0.2 * r;  
            }  
            else {  
                plot.setLineWidth(1)  
                    .setStrokeStyle('880000');  
                r0 = 0.8 * r;  
            }  
              
            sin1 = r*Math.sin(angle);  
            sin2 = r0*Math.sin(angle);  
            cos1 = r*Math.cos(angle);  
            cos2 = r0*Math.cos(angle);  
              
            plot.beginPath()  
                .moveTo(cos1, sin1)  
                .lineTo(cos2, sin2)  
                .closePath()  
                .stroke();  
              
            if (showText == true) {  
                if (i % 15 == 0) {  
                    plot.fillText(i.toFixed(0), 1.2*cos1, 1.2*sin1, 20);  
                }  
            }  
        }  
          
        plot.setLineWidth(5)  
            .setStrokeStyle('CC8888')  
            .beginPath()    
            .arc(0, 0, r, Math.PI, 0)    
            .closePath()    
            .stroke();  
              
        plot.restore();  
    }

    function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var ruler = new Ruler();  
      
    ruler.protractor(0, -20, 100,0,true);  
    ruler.protractor(-20, 150, 150,0, false);  
  
      
}

function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var image = new Image();  
    image.src = './1.jpg';  
      
    image.onload = function() {  
  
        plot.drawImage(image);  
          
        var ruler = new Ruler();          
        var x = 241, y=326;       
        ruler.protractor(x, y, 80,0,true);  
      
  
    }     
}

function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
  
  
    //画角  
    var ruler = new Ruler();          
    var x = 0, y=0;       
    ruler.protractor(x, y, 100,true);  
      
    var angle = 60;  
      
    var x0, y0, x1, y1, r=150;  
      
    x0 = Math.cos(-Math.PI/180*angle)*r;  
    y0 = Math.sin(-Math.PI/180*angle)*r;  
    x1 = r;  
    y1 = 0;  
      
    plot.setLineWidth(3)  
        .beginPath()  
        .moveTo(0, 0)  
        .lineTo(x0, y0)  
        .moveTo(0, 0)  
        .lineTo(x1, y1)  
        .closePath()  
        .stroke();  
          
            plot.setLineWidth(1)  
            .setStrokeStyle('CC8888')  
            .beginPath()    
            .arc(0, 0, 20, -Math.PI/180*angle, 0)    
            .closePath()    
            .stroke();  
              
            plot.fillText(angle.toFixed(0), Math.cos(-Math.PI/360*angle)*25,  
                Math.sin(-Math.PI/360*angle)*25, 50);  
              
}