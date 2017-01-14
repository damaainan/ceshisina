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