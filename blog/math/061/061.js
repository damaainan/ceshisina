function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var row = 1, col=4, width = 600, height = 400;          
    var r = 20;          
    var x = 50, y=20;          
              
    quest = [[573,60],[625,28],[472,33],[850,70]];          
    len = quest.length;          
              
    var vertExp = new VerticalExpression();  
      
    for (var i = 0; i < row;  i++) {          
        for (var j=0; j < col; j++) {          
            x = 20+width/col*(0.2*(i%2)+j);          
            y = 20 + height/row*i;          
      
            vertExp.div(quest[i*col+j][0], quest[i*col+j][1], x, y, r);          
      
        }          
    }   
  
  
              
}