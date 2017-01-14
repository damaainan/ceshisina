function myDraw() {  
    plot.init();  
    setPreference();  
      
    var x = 100, y=50;  
      
    var color = ['red', 'blue', 'orange'];  
    var type = 3;  
      
    for (var i = 0; i < type; i++) {  
        for (var j = 0; j < type; j++) {  
            if (i != j) {  
                plot.setFillStyle(color[i]);  
                plot.fillRect(x, y, 100, 30);  
                x += 200;  
                plot.setFillStyle(color[j]);  
                plot.fillRect(x, y, 100, 30);  
                plot.moveTo(x-250, y+40)  
                    .lineTo(x+150, y+40)  
                    .stroke();  
                      
                x -= 200;  
                y += 50;  
            }  
        }  
    }  
      
}