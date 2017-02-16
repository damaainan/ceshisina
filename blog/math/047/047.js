function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    var vertExp = new VerticalExpression();    
    var row = 1, col=3, width = 600, height = 400;    
    var r = 20;    
    var x = 0, y=20;    
        
    quest = [[563,344],[928,687], [889, 142]];    
    len = quest.length;    
        
    for (var i = 0; i < row;  i++) {    
        for (var j=0; j < col; j++) {    
            x = width/col*(j+1);    
            y = 20 + height/row*i;    
            if (i*col+j != 1 ){  
                vertExp.add(quest[i*col+j][0], quest[i*col+j][1], x, y, r);    
            }  
            else {  
                vertExp.sub(quest[i*col+j][0], quest[i*col+j][1], x, y, r);   
            }  
        }    
    }   
  
  
}