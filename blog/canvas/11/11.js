/*
效果并不理想 点分的不太清楚
 */


function myplot() {       
        // plot.init();  
        setPreference();  
          
        //图片  
        var image = new Image();  
        var oimg= document.getElementsByTagName("img")[0]; 
        image.src = oimg.src; 
        image.width=oimg.width;
        image.height=oimg.height;
        // image.src = '1.jpg'; 
        // console.log(image.width);
        var width = 600;  
        var height = 400;  
        //结果  
        var retArray = new Array();  
        // var pointInfo = "[";  
          
        image.onload = function() {  
            plot.drawImage(image);
            var imagedata = plot.getImageData(0, 0, width, height);  
            /**
             * 清空原矩形 clearRect
             */
            plot.clearRect(0,0,600,400);
              
              
            var pos = 0;  
            var R0 = 0;  
            var R1 = 0;  
            var G0 = 0;  
            var G1 = 0;  
            var B0 = 0;  
            var B1 = 0;  
            var gap = 60;  //色彩差异值 越大 点越稀疏
              
              
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
                    if (Math.abs(R1-R0) > gap ||   Math.abs(G1-G0)>gap ||   Math.abs(B1-B0)>gap) {  
                        retArray.push(col);  
                        retArray.push(row);  
                          
                        //记录坐标，打印信息  
                        // pointInfo += "["+col.toString()+", "+row.toString()+"], ";  
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
                    if (Math.abs(R1-R0) > gap ||    Math.abs(G1-G0)>gap ||   Math.abs(B1-B0)>gap) {  
                        retArray.push(col);  
                        retArray.push(row);  
                          
                        //记录坐标，打印信息  
                        // pointInfo += "["+col.toString()+", "+row.toString()+"], ";  
                    }  
                }  
            }         
              
            plot.translate(0, 0);  
  
              
            while (retArray.length  > 4) {  
                fillCircle(retArray.shift()*1, retArray.shift()*1, 1);  
                  
            }  
                          
            // pointInfo += "]";  
            // var pointInfoNode = document.createTextNode(pointInfo);  
            // document.body.appendChild(pointInfoNode);  
              
    }     
}  

myplot()