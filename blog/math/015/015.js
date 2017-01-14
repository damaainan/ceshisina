
function readPic() {  
    //图片  
    var image = new Image();  
    var width = 600;  
    var height = 400;  
    var gap = 5;  
    var retArray = new Array();  
    var R, G, B;      
    var pos;  
    var top, bottom, left, right;  
      
    image.src = "./1.jpg";  
      
    image.onload = function() {  
        plot.drawImage(image);  
          
        var imagedata = plot.getImageData(0, 0, width, height);   
        var len = imagedata.data.length / 4;  
  
        //四边界  
        for (var i = 0; i < width; i++) { //搜margin-left  
            for (var j = 0; j < height; j++) {//从顶向下  
                pos = j * width + i;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[0]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] == imagedata.data[1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] == imagedata.data[2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    left = i;  
                    break;  
                }  
            }  
        }  
      
        for (var i = width-1; i > -1; i--) { //搜margin-right  
            for (var j = 0; j < height; j++) {//从顶向下  
                pos = j * width + i;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[0]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] == imagedata.data[1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] == imagedata.data[2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    right = i;  
                    break;  
                }  
            }  
        }  
  
        for (var j = 0; j < height; j++) {//margin-top  
                for (var i = 0; i < width; i++) {   
                pos = j * width + i;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[0]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] == imagedata.data[1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] == imagedata.data[2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    top = j;  
                    break;  
                }  
            }  
        }  
          
        for (var j = height-1; j >-1; j--) {//margin-bottom  
            for (var i = 0; i < width; i++) {   
                pos = j * width + i;  
                if (Math.abs(imagedata.data[4*pos] - imagedata.data[0]) <= gap &&  
                    Math.abs(imagedata.data[4*pos+1] == imagedata.data[1])<=gap &&  
                    Math.abs(imagedata.data[4*pos+2] == imagedata.data[2])<=gap) {  
                    //视为背景  
                    continue;                 
                }  
                else {  
                    bottom = j;  
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
            scale = 100 / range;  
        }  
          
        var x, y, repeat, count;  
  
        for (var i = top;i < bottom;i+=2) {  
            for (var j = left; j < right; j+=2) {  
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
  
function drawWithColor() {  
    var len = $picDataArray.length;  
    plot.save();  
    setSector(1,1,1,1);  
    var s = '';  
    var x, y, R, G, B;  
    plot.translate(-100, -50);  
    plot.scale(4, 4);  
      
      
    for (var i =0; i < len; i++) {  
        s = '';  
        x = $picDataArray[i][0];  
        y = $picDataArray[i][1];  
        R = $picDataArray[i][2];  
        G = $picDataArray[i][3];  
        B = $picDataArray[i][4];  
        s = s + 'rgba(' + R.toFixed(0)+','+G.toFixed(0)+','+B.toFixed(0)+',1.0)';  
          
        plot.setFillStyle(s);  
          
        for (var j = 0; j < 30; j++) {  
            fillCircle(x+ j* 5, y+j*6, 1);  
        }  
      
    }  
  
  
}  