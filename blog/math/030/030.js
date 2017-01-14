function myDraw() {  
    plot.init();  
    setPreference();  
  
        //图片  
    var image = new Image();  
    image.src = "./1.jpg";  
    image.onload = function() {  
        plot.drawImage(image);  
          
        var x0 = 58, y0=180,  
            x1 = 220, y1 = y0,  
            x2 = 389, y2 = 173;  
              
        var r = 15;  
          
        plot.setStrokeStyle('yellow');  
        strokeCircle(x0, y0, r*1.414);  
        strokeCircle(x1, y1, r*1.414);  
        strokeCircle(x2, y2, r);  
          
        plot.setStrokeStyle('blue');  
        strokeCircle(x0, y0, r*2*1.414);  
        strokeCircle(x1, y1, r*Math.sqrt(20));  
        strokeCircle(x2, y2, r*Math.sqrt(13));  
          
          
        plot.setStrokeStyle('red');  
        strokeCircle(x0, y0, r*3*1.414);  
        strokeCircle(x1, y1, r*Math.sqrt(13));  
        strokeCircle(x2, y2, r*Math.sqrt(5));  
          
          
    }  
      
}