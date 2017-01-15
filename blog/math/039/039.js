function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
  
    var vertExp = new VerticalExpression();  
    var r = 20, x = 200, y = 20;      
    vertExp.add(41, 42, x, y, r);     
    x = 400;      
    vertExp.add(39,43, x, y, r);  
      
    x = 200, y = 140;     
    vertExp.add(33, 36, x, y, r);     
    x = 400;      
    vertExp.add(36,38, x, y, r);  
      
    x = 200, y = 260;     
    vertExp.add(35, 34, x, y, r);     
    x = 400;      
    vertExp.add(39,44, x, y, r);  
      
    x = 550, y = 20,  
    vertExp.continuousAdd([41,42,39,43,33,36,36,38,35,34,39,44],x,y,10);  
      
    x = 80, y = 20,  
    vertExp.continuousAdd([83,82,69,74,69,83],x,y,10);  
      
      
}