function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var vertExp = new VerticalExpression();      
    var row = 2, col=3, width = 600, height = 400;      
    var r = 20;      
    var x = 0, y=20;      
          
    quest = [[14,12], [23,13],[33,31], [43,12], [11,22]];      
    len = quest.length;      
          
    for (var i = 0; i < row;  i++) {      
        for (var j=0; j < col; j++) {      
            x = width/col*(j+0.5);      
            y = 20 + height/row*i;      
  
            vertExp.mul(quest[i*col+j][0], quest[i*col+j][1], x, y, r);      
  
        }      
    }  
  
}


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var x0 = 85, y0 = 95, dx = 39, dy = 33;  
    var image = new Image();  
    image.src = './1.jpg';  
      
    image.onload = function() {  
        plot.drawImage(image);  
          
        plot.setFillStyle('red');  
      
        for (var i = 20; i <=60; i+=5) {  
            for (var j = 22; j <=30; j++) {  
                plot.fillText(i*j.toFixed(0), x0 + dx*((i-20)/5), y0+dy*((j-22)), 20);  
            }  
        }  
      
    }  
      
  
}