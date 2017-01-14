/** 
* @usage  读取图片，获取上下左右边界以及边界内像素点信息 
* @author  mw 
* @date    2016年01月01日  星期五  09:34:40  
* @param 
* @return 
* 
*/  
function readPic() {  
    //图片  
    var image = new Image();  
    var width = 600;  
    var height = 400;  
    var gap = 50;  
    var retArray = new Array();  
    var R, G, B;      
    var pos, pos2;  
    var top, bottom, left, right;  
      
    image.src = "./1.jpg";  
      
    image.onload = function() {  
        plot.drawImage(image);  
          
        var imagedata = plot.getImageData(0, 0, width, height);   
        var len = imagedata.data.length / 4;  
  
        //四边界  
        for (var i = 1; i < width; i++) { //搜margin-left  
            for (var j = 0; j < height; j++) {//从顶向下  
                pos = j * width + i;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[4*(pos-1)]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] - imagedata.data[4*(pos-1)+1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] - imagedata.data[4*(pos-1)+2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                      
                    left = i-1;  
                    break;  
                }  
            }  
        }  
      
        for (var i = width-2; i > -1; i--) { //搜margin-right  
            for (var j = 0; j < height; j++) {//从顶向下  
                pos = j * width + i;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[4*(pos+1)]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] - imagedata.data[4*(pos+1)+1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] - imagedata.data[4*(pos+1)+2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    right = i+1;  
                    break;  
                }  
            }  
        }  
  
        for (var j = 1; j < height; j++) {//margin-top  
                for (var i = 0; i < width; i++) {   
                pos = j * width + i;  
                pos2 = pos-width;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[4*pos2])<= gap &&  
                    Math.abs(imagedata.data[4*pos+1] - imagedata.data[4*pos2+1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] - imagedata.data[4*pos2+2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    top = j-1;  
                    break;  
                }  
            }  
        }  
          
        for (var j = height-2; j >-1; j--) {//margin-bottom  
            for (var i = 0; i < width; i++) {   
                pos = j * width + i;  
                pos2 = pos+width;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[4*pos2]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] - imagedata.data[4*pos2+1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] - imagedata.data[4*pos2+2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    bottom = j+1;  
                    break;  
                }  
            }  
        }  
          
        //imagedata的数据其实是把图象右上角的象素存在最后的  
        //坐标系之间是有差异的，  
        var tmp;  
        if (top > bottom) {  
            tmp = bottom;  
            bottom = top;  
            top = tmp;  
        }  
        if (left > right) {  
            tmp = left;  
            left = right;  
            right = tmp;  
        }  
          
        var range = Math.max(bottom-top, right-left);  
        var scale = 1;  
        if (range != 0) {  
            scale = 400 / range;  
        }  
          
        var x, y, repeat, count;  
  
        for (var i = top;i < bottom;i+=5) {  
            for (var j = left; j < right; j+=5) {  
                pos = i * width + j;  
                R = imagedata.data[4*pos];  
                G = imagedata.data[4*pos+1];  
                B = imagedata.data[4*pos+2];  
                x = Math.round((j-(left+right)/2) * scale);  
                y = Math.round((i-(top+bottom)/2)*scale);             
                  
                repeat = 0;  
                count = retArray.length;  
                for (; repeat < count; repeat++) {  
                    if (retArray[repeat][0]==x && retArray[repeat][1] == y) {  
                        break;  
                    }  
                }  
                if (repeat >= count) {  
                    retArray.push([x, y, R, G, B]);   
                }  
                  
            }  
        }  
  
          
        var len2 = retArray.length;  
        var info = '';  
          
        info += "$picDataArray = [";  
        for (var i = 0; i < len2; i++) {  
            info += '['  
                +retArray[i][0].toFixed(0)+', '  
                +retArray[i][1].toFixed(0)+', '  
                +retArray[i][2].toFixed(0)+', '  
                +retArray[i][3].toFixed(0)+', '  
                +retArray[i][4].toFixed(0)+'], ';  
        }  
        info +='];';  
        info += '//len:beg:end'+len.toFixed(0) + ','   
            +'left:top:right:bottom['+left.toFixed(0)+', '  
            +top.toFixed(0)+', '+right.toFixed(0)+', '  
            +bottom.toFixed(0)+']';  
        document.body.appendChild(document.createTextNode(info));  
    }  
}  
  
//left:top:right:bottom[5, 13, 260, 15]


/** 
* @usage   画一条与给定长度等长的线段 
* @author  mw 
* @date    2016年01月01日  星期五  09:14:35  
* @param 
* @return 
* 
*/  
//原图与处理结果对比  
function drawLine() {  
        //图片  
        var image = new Image();  
        image.src = "./1.jpg";  
  
        image.onload = function() {  
            plot.drawImage(image);  
              
            plot.save();  
            plot.setLineWidth(3);  
              
            //样式字符串  
            var s;  
            //颜色值  
            var R = 0, G = 0 , B = 0;  
              
            //起点和终点的坐标  
            //left:top:right:bottom[5, 13, 260, 15]  
            var x1 = 5, x2 = 260, y;  
  
            for (var i = 0; i < 10; i++) {  
                s = "";  
                y = 22+i*10;  
                  
                R = (R + 10 * i) % 255;  
                G = (G + 15 * i) % 255;  
                B = (B + 20 * i) % 255;  
                  
                s = s + 'rgba(' + R.toFixed(0)+','+G.toFixed(0)+','+B.toFixed(0)+',1.0)';  
          
                plot.setStrokeStyle(s);   
                  
                //fillCircle(x1, y, 3);  
                //fillCircle(x2, y, 3);  
                  
                plot.beginPath()  
                    .moveTo(x1, y)  
                    .lineTo(x2, y)  
                    .closePath()  
                    .stroke();            
              
            }  
              
            plot.restore();   
  
        }         
  
}  
  
//left:top:right:bottom[27, 9, 583, 82]</span>  

/** 
* @usage   定出刻度线 
* @author  mw 
* @date    2016年01月01日  星期五  09:14:35  
* @param 
* @return 
* 
*/  
//原图与处理结果对比  
function drawLine() {  
        //图片  
        var image = new Image();  
        image.src = "./1.jpg";  
  
        image.onload = function() {  
            plot.drawImage(image);  
              
            plot.save();  
            plot.setLineWidth(3);  
              
            //样式字符串  
            var s;  
            //颜色值  
            var R = 255, G = 0 , B = 0;  
              
            //起点和终点的坐标  
            //3cm = 113px 5cm = 189px 10cm=378px  
            var x1 = 27, x2 = x1+113, x3=x1+189, x4=x1+378, y=82, y2=y-10;  
            plot.fillText('起点', x1-10, y2+30, 100);  
  
                plot.beginPath()  
                    .moveTo(x2, y)  
                    .lineTo(x2, y2)  
                    .closePath()  
                    .stroke();    
                plot.fillText('3cm', x2-10, y2+30, 100);  
                  
                plot.beginPath()  
                    .moveTo(x3, y)  
                    .lineTo(x3, y2)  
                    .closePath()  
                    .stroke();    
                plot.fillText('5cm', x3-10, y2+30, 100);  
                  
                plot.beginPath()  
                    .moveTo(x4, y)  
                    .lineTo(x4, y2)  
                    .closePath()  
                    .stroke();    
                plot.fillText('10cm', x4-10, y2+30, 100);  
              
            plot.restore();   
  
        }         
  
}  
/** 
* @usage   用图片对应像素信息填充 
* @author  mw 
* @date    2016年01月01日  星期五  11:12:15  
* @param 
* @return 
* 
*/  
function drawWithColor(array, xOffset, yOffset, scale) {  
    var len = array.length;  
      
    var s = '';  
    var x, y, R, G, B;  
    var dotR = 2;  
  
      
    for (var i =0; i < len; i++) {  
        s = '';  
        x = Math.floor(xOffset + array[i][0]*scale);  
        y = Math.floor(yOffset + array[i][1]*scale);  
          
      
        R = array[i][2];  
        G = array[i][3];  
        B = array[i][4];  
        s = s + 'rgba(' + R.toFixed(0)+','+G.toFixed(0)+','+B.toFixed(0)+',1.0)';  
          
        plot.setFillStyle(s);         
  
        fillCircle(x+0.5*dotR, y+0.5*dotR, 2);  
      
    }  
}

/** 
* @usage   定出刻度线，加画小图样 
* @author  mw 
* @date    2016年01月01日  星期五  09:14:35  
* @param 
* @return 
* 
*/  
//原图与处理结果对比  
function drawLineAndPic() {  
  
        //图片  
        var image = new Image();  
        image.src = "./1.jpg";  
  
        var width = 600;  
        var height = 400;         
  
        plot.translate(0, 100);  
        image.onload = function() {  
            plot.drawImage(image);  
            var imagedata = plot.getImageData(0, 0, width, height);           
  
            plot.setLineWidth(3);  
              
            //样式字符串  
            var s;  
            //颜色值  
            var R = 255, G = 0 , B = 0;  
              
            //起点和终点的坐标  
            //3cm = 113px 5cm = 189px 10cm=378px  
            var x1 = 43, x2 = x1+113, x3=x1+189, x4=x1+378, y=82, y2=y-10;  
            var yoff = 60;  
            drawWithColor($picDataArray_1, x2, y-yoff, 0.15);  
            drawWithColor($picDataArray_2, x3, y-yoff, 0.15);  
            drawWithColor($picDataArray_3, x4, y-yoff, 0.15);  
              
            plot.setFillStyle('black');  
            plot.fillText('起点', x1-10, y2+30, 100);  
              
                plot.beginPath()  
                    .moveTo(x2, y)  
                    .lineTo(x2, y2)  
                    .closePath()  
                    .stroke();    
                plot.fillText('3cm', x2-10, y2+30, 100);  
                  
                plot.beginPath()  
                    .moveTo(x3, y)  
                    .lineTo(x3, y2)  
                    .closePath()  
                    .stroke();    
                plot.fillText('5cm', x3-10, y2+30, 100);  
                  
                plot.beginPath()  
                    .moveTo(x4, y)  
                    .lineTo(x4, y2)  
                    .closePath()  
                    .stroke();    
                plot.fillText('10cm', x4-10, y2+30, 100);  
  
        }     
  
}<