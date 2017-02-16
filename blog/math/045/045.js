function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    var r = 40;  
    var row = 6, col = 13;  
    var x = (600-r*(col-1))/2, y = (400-r*(row-1))/2;  
      
    for (var i = 0; i < col; i++) {  
        for (var j = 0; j < row; j++) {  
            shape.strokeRect(x + i*r, y + j*r, r, r);  
        }  
    }  
      
    var array = [1,1,1,2,1,3,1,4];  
      
    plot.save()  
        .setFillStyle('red');  
          
        for (var i = 0; i < array.length / 2; i++) {  
            shape.fillRect(x + array[2*i]*r, y + array[2*i+1]*r, r, r);  
        }  
          
    plot.restore();  
          
    x += 2*r;  
    array = [1,1,2,1,3,1,4,1];  
      
    plot.save()  
        .setFillStyle('blue');  
          
        for (var i = 0; i < array.length / 2; i++) {  
            shape.fillRect(x + array[2*i]*r, y + array[2*i+1]*r, r, r);  
        }  
          
    plot.restore();  
      
    y += 2 * r;  
    array = [1,1,1,2,2,1,2,2];  
      
    plot.save()  
        .setFillStyle('pink');  
          
        for (var i = 0; i < array.length / 2; i++) {  
            shape.fillRect(x + array[2*i]*r, y + array[2*i+1]*r, r, r);  
        }  
          
    plot.restore();  
      
    x += 3 * r;  
    array = [1,2,2,1,2,2,2,3];  
    plot.save()  
        .setFillStyle('orange');  
          
        for (var i = 0; i < array.length / 2; i++) {  
            shape.fillRect(x + array[2*i]*r, y + array[2*i+1]*r, r, r);  
        }  
          
    plot.restore();  
      
    x += 2 * r;  
    y -= 2 * r;  
    array = [1,1,2,1,2,2,2,3];  
    plot.save()  
        .setFillStyle('purple');  
          
        for (var i = 0; i < array.length / 2; i++) {  
            shape.fillRect(x + array[2*i]*r, y + array[2*i+1]*r, r, r);  
        }  
          
    plot.restore();  
      
    x+= 3 * r;  
    array = [1,1,1,2,2,2,2,3];  
      
    plot.save()  
        .setFillStyle('cyan');  
          
        for (var i = 0; i < array.length / 2; i++) {  
            shape.fillRect(x + array[2*i]*r, y + array[2*i+1]*r, r, r);  
        }  
          
    plot.restore();  
  
}