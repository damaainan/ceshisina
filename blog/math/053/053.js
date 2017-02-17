/** 
* @usage   标记边界点，填充内部域 
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
          
          
        var map = new Map();  
        var pos = 0;  
          
        image.onload = function() {  
            plot.drawImage(image);  
            var imagedata = plot.getImageData(0, 0, width, height);           
  
              
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
                          
                        map.put(row, col);  
                      
                    } else {                          
                        maskArray[pos] = 0;                       
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
                          
                        map.put(row, col);  
  
                    } else {  
                        maskArray[pos] = 0;  
                    }  
                }  
            }     
              
              
            map.sort();  
            //document.body.appendChild(document.createTextNode(map.print()));    
              
              
            var size = map.size();  
            var key = 0, value1 = value2 = 0;  
            var valueArray = new Array();  
              
            for (var i = 0; i < size; i++) {  
                key = map.keys[i];  
                valueArray = map.get(key);  
                  
                var len = valueArray.length;  
                var index = 0;  
                  
                //预判有多少段需填充的线段  
                for (var j = 0; j < len; j++) {  
                    value1 = valueArray[j];  
                    value2 = valueArray[j+1];  
                      
                    if (value2 - value1 > 5 ) {  
                        index++;  
                    }             
                }  
                  
                //对于奇数段，一般是中间有间隔的空白段  
                if (index % 2 == 1) {  
                    index = 0;  
  
                    for (var j = 0; j < len; j++) {  
                        value1 = valueArray[j];  
                        value2 = valueArray[j+1];  
                          
                        if (value2 - value1 > 5 ) {  
                            index++;  
                              
                            if (index % 2 == 1) {  
                                for (var k = value1; k < value2; k++) {  
                                    pos = key * width + k;  
                                    if (maskArray[pos] != 1) {  
                                        maskArray2[pos] = 1;  
                                    }  
                                }  
                            }             
                        }  
                                              
                    }  
                }  
                //对于偶数段，应该全部填充  
                else {  
                    value1 = valueArray[0];  
                    value2 = valueArray[len-1];  
                    for (var k = value1; k < value2; k++) {  
                        pos = key * width + k;  
                        if (maskArray[pos] != 1) {  
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
                          
                    }   
                    else if (maskArray2[pos] == 1) {  
                         //颜值相近点，一般为内部  
                         imagedata.data[4 * pos] = 0;  
                         imagedata.data[4 * pos+1] = 255;  
                         imagedata.data[4 * pos+2] = 0;                       
                      
                    }  
                    else {  
                        /* 
                         //作为背景处理 
                         imagedata.data[4 * pos] = 255; 
                         imagedata.data[4 * pos+1] = 255; 
                         imagedata.data[4 * pos+2] = 255;*/  
                    }  
                }  
            }  
              
            plot.putImageData(imagedata, 0, 0);  
            plot.drawImage(image);  
    }     
}




/** 
* @usage   2016年日历 
* @author  mw 
* @date    2016年01月20日  星期三  13:59:32  
* @param 
* @return 
* 
*/  
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var dayOfMonth =   
//1  
[[[0, 0, 0, 0, 1, 2, 3],   
[4, 5, 6, 7, 8, 9, 10],   
[11, 12, 13, 14, 15, 16, 17],   
[18, 19, 20, 21, 22, 23, 24],   
[25, 26, 27, 28, 29, 30, 31]],  
//2   
[[1, 2, 3, 4, 5, 6, 7],   
[8, 9, 10, 11, 12, 13, 14],   
[15, 16, 17, 18, 19, 20, 21],   
[22, 23, 24, 25, 26, 27, 28],   
[29, 0, 0, 0, 0, 0, 0]],  
//3  
[[0, 1, 2, 3, 4, 5, 6],   
[7, 8, 9, 10, 11, 12, 13],   
[14, 15, 16, 17, 18, 19, 20],   
[21, 22, 23, 24, 25, 26, 27],   
[28, 29, 30, 31, 0, 0, 0]],  
//4  
[[0, 0, 0, 0, 1, 2, 3],   
[4, 5, 6, 7, 8, 9, 10],   
[11, 12, 13, 14, 15, 16, 17],   
[18, 19, 20, 21, 22, 23, 24],   
[25, 26, 27, 28, 29, 30, 0]],  
//5  
[[0, 0, 0, 0, 0, 0, 1],   
[2, 3, 4, 5, 6, 7, 8],   
[9, 10, 11, 12, 13, 14, 15],   
[16, 17, 18, 19, 20, 21, 22],   
[23, 24, 25, 26, 27, 28, 29],   
[30, 31, 0, 0, 0, 0, 0]],  
//6  
[[0, 0, 1, 2, 3, 4, 5],   
[6, 7, 8, 9, 10, 11, 12],   
[13, 14, 15, 16, 17, 18, 19],   
[20, 21, 22, 23, 24, 25, 26],   
[27, 28, 29, 30, 0, 0, 0]],  
//7  
[[0, 0, 0, 0, 1, 2, 3],   
[4, 5, 6, 7, 8, 9, 10],   
[11, 12, 13, 14, 15, 16, 17],   
[18, 19, 20, 21, 22, 23, 24],   
[25, 26, 27, 28, 29, 30, 31]],  
//8  
[[1, 2, 3, 4, 5, 6, 7],   
[8, 9, 10, 11, 12, 13, 14],   
[15, 16, 17, 18, 19, 20, 21],   
[22, 23, 24, 25, 26, 27, 28],   
[29, 30, 31, 0, 0, 0, 0]],  
//9  
[[0, 0, 0, 1, 2, 3, 4],   
[5, 6, 7, 8, 9, 10, 11],   
[12, 13, 14, 15, 16, 17, 18],   
[19, 20, 21, 22, 23, 24, 25],  
 [26, 27, 28, 29, 30, 0, 0]],  
//10  
[[0, 0, 0, 0, 0, 1, 2],   
[3, 4, 5, 6, 7, 8, 9],   
[10, 11, 12, 13, 14, 15, 16],  
 [17, 18, 19, 20, 21, 22, 23],   
 [24, 25, 26, 27, 28, 29, 30],  
 [31, 0, 0, 0, 0, 0, 0]],  
//11  
[[0, 1, 2, 3, 4, 5, 6],  
 [7, 8, 9, 10, 11, 12, 13],   
 [14, 15, 16, 17, 18, 19, 20],  
 [21, 22, 23, 24, 25, 26, 27],  
 [28, 29, 30, 0, 0, 0, 0]],  
//12  
[[0, 0, 0, 1, 2, 3, 4],  
 [5, 6, 7, 8, 9, 10, 11],   
 [12, 13, 14, 15, 16, 17, 18],  
 [19, 20, 21, 22, 23, 24, 25],   
 [26, 27, 28, 29, 30, 31, 0]]];  
  
      
  
    var text = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期日'];  
      
    var weeks = 0;  
    var r = 25;  
    var x0 = (600-20*r)/2, y0 = 20;  
      
    for (var m = 10; m < 12; m++) {  
        plot.save();      
        plot.setTextAlign('center');  
        plot.fillText('2016 年 '+(m+1).toFixed(0)+' 月', 300, y0, 200);  
          
        y0 += r;  
        weeks = dayOfMonth[m].length;  
        for (var j = 0; j < 7; j++) {  
            if (j < 5) {  
                plot.setFillStyle('black');  
                  
            }  
            else {  
                plot.setFillStyle('red');  
            }  
            plot.fillText(text[j], x0 + j * 3 * r, y0, 100);  
        }  
          
        y0 +=0.5 * r;  
          
        for (var i = 0; i < weeks; i++) {  
            y0 += r;  
            for (var j = 0; j < 7; j++) {  
                if (dayOfMonth[m][i][j] != 0) {  
                    if (j < 5) {  
                        plot.setFillStyle('black');  
                          
                    }  
                    else {  
                        plot.setFillStyle('red');  
                    }                 
                      
                    plot.fillText(dayOfMonth[m][i][j].toFixed(0), x0 + j * 3 * r, y0, 100);  
                }  
            }  
        }  
          
        plot.restore();   
          
        if (weeks > 5) {  
            y0 += r;  
        }  
        else {  
            y0 += 2 * r;  
        }  
    }  
  
}