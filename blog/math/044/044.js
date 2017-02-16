/** 
* @usage   标记边界点 
* @author  mw 
* @date    2016年01月14日  星期四  14:57:23  
* @param 
* @return 
* 
*/  
    function signBoundPoint() {   
      
        //图片  
        var image = new Image();  
        image.src = "./1.jpg";  
        //只处理这100*100个象素  
        var width = 600;  
        var height = 400;  
        var gap = 10;  
        var gap2 = 10;  
        //掩码矩阵 边界  
        var maskArray = new Array();  
        //掩码矩阵 内部  
        var maskArray2 = new Array();  
          
        image.onload = function() {  
            plot.drawImage(image);  
            var imagedata = plot.getImageData(0, 0, width, height);           
              
          
            //背景色  
            var Rback = Gback = Bback = 255;  
              
            //水平方向找差异  
            for (var row = 0; row < height; row++) {  
                for (var col = 1; col < width; col++) {  
                      
                    //pos最小为1  
                    pos =row * width  + col;  
                    if (maskArray[pos] == 1)   
                        continue;  
                          
                    R0 = imagedata.data[4 * (pos-1)];                 
                    R1 = imagedata.data[4 * pos];  
                    G0 = imagedata.data[4 * (pos-1)+1];  
                    G1 = imagedata.data[4 * pos+1];  
                    B0 = imagedata.data[4 * (pos-1)+2]  
                    B1 = imagedata.data[4 * pos + 2]  
                      
                      
                    //简单容差判断  
                    if (Math.abs(R1-R0) > gap ||   
                            Math.abs(G1-G0)>gap ||   
                            Math.abs(B1-B0)>gap) {  
                        maskArray[pos] = 1;  
  
                    } else {  
                        maskArray[pos] = 0;  
                    }  
                      
                    //非边界  
                    if (maskArray[pos] != 1) {  
                        //非背景色  
                        if (Math.abs(R1-Rback) > gap2 ||   
                                Math.abs(G1-Gback)>gap2 ||   
                                Math.abs(B1-Bback)>gap2) {  
                            //简单容差判断  
                            if (Math.abs(R1-R0) < gap2 &&   
                                    Math.abs(G1-G0)<gap2 &&  
                                    Math.abs(B1-B0)< gap2) {  
                                maskArray2[pos] = 1;  
              
                            }   
                        }  
                    }  
                }  
            }  
              
              
            //垂直方向找差异  
            for (var col = 0; col < width; col++) {  
                for (var row = 1; row < height; row++) {  
                    //pos最小为第二行  
                    pos =row * width  + col;  
                      
                    if (maskArray[pos] == 1)   
                        continue;  
                          
                    R0 = imagedata.data[4 * (pos-width)];                 
                    R1 = imagedata.data[4 * pos];  
                    G0 = imagedata.data[4 * (pos-width)+1];  
                    G1 = imagedata.data[4 * pos+1];  
                    B0 = imagedata.data[4 * (pos-width)+2];  
                    B1 = imagedata.data[4 * pos + 2];                         
  
                      
                    //简单容差判断  
                    if (Math.abs(R1-R0) > gap ||   
                            Math.abs(G1-G0)>gap ||   
                            Math.abs(B1-B0)>gap) {  
                        maskArray[pos] = 1;  
  
                    } else {  
                        maskArray[pos] = 0;  
                    }  
                      
                    //非边界  
                    if (maskArray[pos] != 1) {  
                        //非背景色  
                        if (Math.abs(R1-Rback) > gap2 ||   
                                Math.abs(G1-Gback)>gap2 ||   
                                Math.abs(B1-Bback)>gap2) {  
                            //简单容差判断  
                            if (Math.abs(R1-R0) < gap2 &&   
                                    Math.abs(G1-G0)<gap2 &&  
                                    Math.abs(B1-B0)< gap2) {  
                                maskArray2[pos] = 1;  
              
                            }   
                        }  
                    }  
                }  
            }     
              
            plot.translate(600, 0);  
              
            for (var col = 0; col < width; col++) {  
                for (var row = 0; row < height; row++) {  
                    pos = row * width  + col;  
                    //颜值突变点，一般为边界  
                    if (maskArray[pos] == 1) {  
                         imagedata.data[4 * pos] = 255;  
                         imagedata.data[4 * pos+1] = 0;  
                         imagedata.data[4 * pos+2] = 255;  
                          
                    } else if (maskArray2[pos] == 1) {  
                         //颜值相近点，一般为内部  
                         imagedata.data[4 * pos] = 0;  
                         imagedata.data[4 * pos+1] = 255;  
                         imagedata.data[4 * pos+2] = 0;                       
                      
                    }  
                    else {  
                         //作为背景处理  
                         imagedata.data[4 * pos] = 255;  
                         imagedata.data[4 * pos+1] = 255;  
                         imagedata.data[4 * pos+2] = 255;  
                    }  
                }  
            }  
              
            plot.putImageData(imagedata, 0, 0);  
            plot.drawImage(image);  
              
                          
              
    }     
}


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    //图片  
    var image = new Image();  
    image.src = "./1.jpg";  
    //只处理这100*100个象素  
    var width = 600;  
    var height = 400;  
      
    image.onload = function() {  
        plot.drawImage(image);  
          
        shape.strokeRect(235,110, 90,35);  
        shape.strokeRect(130,85, 35,35);  
    }     
}

function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    //图片  
    var image = new Image();  
    image.src = "./1.jpg";  
    //只处理这100*100个象素  
    var width = 600;  
    var height = 400;  
    var ruler = new Ruler();  
      
    image.onload = function() {  
        plot.drawImage(image);  
          
        ruler.ruler(15, 19, 330, 0);  
          
        var x = 19, y= 14;  
          
          
        plot.setLineWidth(5);  
        plot.strokeRect(x+37.8*2,y+37.8*3, 3*37.8,2*37.8);  
        plot.strokeRect(x+37.8*7,y+37.8*3, 4*37.8,4*37.8);  
    }     
}


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    var x = 10, y = 10, r = 50;  
    var count = 0, total = 18;  
  
    var row = 3, col = 6;  
    var xBeg = x, yBeg = y;  
  
    plot.save();  
    for (var i = 0; i < row && count < total; i++) {  
        for (var j = 0; j < col && count < total; j++) {  
            plot.strokeRect(x+r * j, y+r * i , r, r);  
            count++;  
        }  
    }  
      
    plot.setStrokeStyle('red')  
        .setLineWidth(5)  
        .strokeRect(xBeg, yBeg, r*col, r*row);  
    plot.restore();  
      
    x += r*(col+1);  
    xBeg = x, yBeg = y;  
    count = 0;  
    row = 5, col = 4;  
      
    plot.save();  
    for (var i = 0; i < row && count < total; i++) {  
        for (var j = 0; j < col && count < total; j++) {  
            plot.strokeRect(x+r * j, y+r * i , r, r);  
            count++;  
        }  
    }  
      
    plot.setStrokeStyle('red')  
        .setLineWidth(5)  
        .strokeRect(xBeg, yBeg, r*col, r*row);  
    plot.restore();  
      
      
              
  
}

