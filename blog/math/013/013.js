function getPicData(str) {    
  
        plot.init();  
        setPreference();  
  
          
        //图片  
        var image = new Image();  
        image.src = "./1.jpg";  
        //只处理这100*100个象素  
        var width = 300;  
        var height = 400;  
        //结果  
        var retArray = new Array();  
        var pointInfo = "var $picDataArray = [";  
          
        image.onload = function() {  
            plot.drawImage(image);  
            var imagedata = plot.getImageData(0, 0, width, height);           
              
            var pos = 0;  
            var R0 = 0;  
            var R1 = 0;  
            var G0 = 0;  
            var G1 = 0;  
            var B0 = 0;  
            var B1 = 0;  
            var gap = 30;  
              
            /* 
            //背景颜色替换为白色 
            var gapback = 5; 
            var Rback = imagedata.data[0]; 
            var Gback = imagedata.data[1]; 
            var Bback = imagedata.data[2]; 
            var r = 255, g=255, b=255, max = -10000, min = 10000; 
            for (var row = 0; row < height; row++) { 
                for (var col = 0; col < width; col++) { 
                    pos =row * width  + col; 
                    r = imagedata.data[4 * pos]; 
                    g = imagedata.data[4 * pos+1]; 
                    b = imagedata.data[4 * pos+2]; 
                    max = Math.max(r, Math.max(g, b)); 
                    min = Math.min(r, Math.min(g, b)); 
                     
                    if (max - min < gapback) { //去除灰色和黑色 
                     
                 
                        imagedata.data[4 * pos] = 255; 
                        imagedata.data[4 * pos+1] = 255; 
                        imagedata.data[4 * pos+2] = 255;                         
                    } 
                } 
            }*/  
          
              
              
            //水平方向找差异  
            for (var row = 0; row < height; row++) {  
                for (var col = 1; col < width; col++) {  
                    //pos最小为1  
                    pos =row * width  + col;  
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
                        retArray.push(col);  
                        retArray.push(row);  
                          
                        //记录坐标，打印信息  
                        pointInfo += "["+col.toString()+", "+row.toString()+"], ";  
                    }  
                }  
            }  
              
              
            //垂直方向找差异  
            for (var col = 0; col < width; col++) {  
                for (var row = 1; row < height; row++) {  
                    //pos最小为第二行  
                    pos =row * width  + col;  
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
                        retArray.push(col);  
                        retArray.push(row);  
                          
                        //记录坐标，打印信息  
                        pointInfo += "["+col.toString()+", "+row.toString()+"], ";  
                    }  
                }  
            }         
              
            plot.translate(300, 0);  
              
            while (retArray.length  > 4) {  
                fillCircle(retArray.shift(), retArray.shift(), 1);  
                  
            }  
                          
            pointInfo += "];";  
            var pointInfoNode = document.createTextNode(pointInfo);  
            document.body.appendChild(pointInfoNode);  
              
    }     
}

function fill() {  
    plot.init();  
    setPreference();  
    setSector(1,1,1,1);  
      
    plot.save()  
        .setFillStyle('red');  
      
    //数据录入  
    var mapIn = new Map();  
    for (var i = 0; i < $picDataArray.length; i++) {  
        mapIn.put($picDataArray[i][0], $picDataArray[i][1]);  
    }  
      
    var map = new Map();  
    map = center(mapIn, 200);  
      
    s = map.print();  
    document.body.appendChild(document.createTextNode(s));  
      
    var size = map.size();  
    var key=0, value=0;  
    var len = 0;  
    var max = 0, min = 0;  
      
    for (var i = 0; i < size; i++) {  
        key = map.keys[i];  
        len = map.get(key).length;  
        min = map.get(key)[0];  
        max = map.get(key)[len-1];  
          
        for (var j = min; j < max; j++) {  
            if (!pointInMap(key, j, map)) {  
                fillCircle(key, j, 1);  
            }  
        }  
    }  
      
    plot.restore();  
      
  
}

function center(map, R) {  
        map.sort();  
        var mapOut = new Map();  
          
        R = R ? R : 100;  
                  
        //map键数目  
        var size = map.size();  
        //键对应的值数组的长度  
        var len = 0;  
        //键取值范围  
        var xmin = map.keys[0];  
        var xmax = map.keys[size-1];  
        //键中值  
        var xCenter = ( xmin + xmax)/2;  
        //值取值范围  
        var ymin = 0, ymax = 0;  
        //临时变量  
        var y1=0, y2=0;  
        for (var i = 0; i < size; i++) {  
            len = map.data[map.keys[i]].length;  
            y1 = map.data[map.keys[i]][0];  
            y2 = map.data[map.keys[i]][len-1];  
            if (y1 < ymin) ymin = y1;  
            if (y2 > ymax) ymax = y2;  
        }  
        var yCenter = (ymin+ymax) / 2;  
        var range = Math.max(xmax-xmin, ymax-ymin);  
          
        if (range != 0) {  
            var x, y;  
              
            for (var i = 0; i < size; i++) {  
                x =  (map.keys[i] - xCenter) / range * R;  
                len = map.data[map.keys[i]].length;  
                for (var j = 0; j < len; j++) {  
                    y = (map.data[map.keys[i]][j] - yCenter) / range * R;  
                    mapOut.put(Math.round(x),Math.round(y));  
                }  
                  
            }  
            mapOut.sort();  
        }  
                  
        return mapOut;  
    }

     function revertMap(mapIn) {  
        var mapOut = new Map();  
          
        var size = mapIn.size();  
        var len = 0;  
        var x= 0, y=0;  
          
        for (var i = 0; i < size; i++) {  
            x = mapIn.keys[i];  
            len = mapIn.get(x).length;  
            for (var j = 0; j < len; j++) {  
                y = mapIn.get(x)[j];  
                mapOut.put(y, x);  
            }  
        }  
        mapOut.sort();  
        return mapOut;    
    }  