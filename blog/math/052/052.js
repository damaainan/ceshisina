/** 
* @usage   标记边界点 
* @author  mw 
* @date    2016年01月14日  星期四  14:57:23  
* @param 
* @return 
* 
*/  
    function signBoundPoint(gap1, gap2) {     
      
        //图片  
        var image = new Image();  
        image.src = "./1.jpg";  
        //只处理这100*100个象素  
        var width = 600;  
        var height = 400;  
        var gap = gap1 ? gap1 : 30;  
        var gap2 = gap2 ? gap2 : 10;  
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
                //指示位  
                var first = last = 0;  
                  
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
                      
                        if ((first == 0 && last == 0) || first <= last-10) {  
                            first = col;  
                        } else if (first > last + 10) {  
                            last = col;  
  
                        }  
  
                    } else {                          
                        maskArray[pos] = 0;  
                          
                    }  
                      
                    //非边界  
                    if (maskArray[pos] != 1) {  
                          
                        if (first < last) {  
                            maskArray2[pos] = 1;  
                        }  
                      
                    }  
                }  
            }  
              
              
            //垂直方向找差异  
            for (var col = 0; col < width; col++) {  
                var first = last = 0;  
                var index = 0;  
                  
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
                          
  
                        if ((first == 0 && last == 0) || first <= last-10) {  
                            first = row;  
                        } else if (first > last + 10) {  
                            last = row;  
  
                        }  
  
                    } else {  
                        maskArray[pos] = 0;  
                    }  
                      
                    //非边界  
                    if (maskArray[pos] != 1) {  
                        if (first < last) {  
                            maskArray2[pos] = 1;  
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
      
    var row = 10, col = 20;  
    var r = 28;  
    var x0 = (600-col*r)/2, y0 = (400-row*r)/2;  
      
    var color = ['red', 'blue', 'green', 'orange', 'cyan', 'purple', 'yellow'];  
    //方格纸  
    for (var i = 0; i < col; i++) {  
        for (var j = 0; j < row; j++) {  
            shape.strokeRect(x0+(i+0.5)*r, y0+(j+0.5)*r, r, r);  
        }  
    }  
      
    var w = 16, h = 1;  
    var index = 0;  
      
    for (var i = 0; i <= col-w; i+=w) {  
        for (var j = 0; j <= row-h; j+=h) {  
            index++;  
            plot.setStrokeStyle(color[index % 8]);  
            plot.strokeRect(x0 + i*r, y0 + j*r, w*r, h*r);  
            plot.fillText(index.toFixed(0), x0+(i+0.5)*r, y0+(j+0.5)*r, r);  
        }  
    }  
      
    w = 2, h = 8;  
      
    for (var i = 16; i <= col-w; i+=w) {  
        for (var j = 0; j <= row-h; j+=h) {  
            index++;  
            plot.setStrokeStyle(color[index % 8]);  
            plot.strokeRect(x0 + i*r, y0 + j*r, w*r, h*r);  
            plot.fillText(index.toFixed(0), x0+(i+0.5)*r, y0+(j+0.5)*r, r);  
        }  
    }  
  
}

function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var row = 10, col = 20;  
    var r = 28;  
    var x0 = (600-col*r)/2, y0 = (400-row*r)/2;  
      
    var color = ['red', 'blue', 'green', 'orange', 'cyan', 'purple', 'yellow','pink'];  
    //方格纸  
    for (var i = 0; i < col; i++) {  
        for (var j = 0; j < row; j++) {  
            shape.strokeRect(x0+(i+0.5)*r, y0+(j+0.5)*r, r, r);  
        }  
    }  
      
    var w = 16, h = 1;  
    var index = 0;  
      
    for (var i = 0; i <= col-w; i+=w) {  
        for (var j = 0; j <= row-h; j+=h) {  
            index++;  
            plot.setFillStyle(color[index % 8]);  
            plot.fillRect(x0 + i*r, y0 + j*r, w*r, h*r);  
            plot.strokeText(index.toFixed(0), x0+(i+0.5)*r, y0+(j+0.5)*r, r);  
        }  
    }  
      
    w = 2, h = 8;  
      
    for (var i = 16; i <= col-w; i+=w) {  
        for (var j = 0; j <= row-h; j+=h) {  
            index++;  
            plot.setFillStyle(color[index % 8]);  
            plot.fillRect(x0 + i*r, y0 + j*r, w*r, h*r);  
            plot.strokeText(index.toFixed(0), x0+(i+0.5)*r, y0+(j+0.5)*r, r);  
        }  
    }