function myplot() {  
        // plot.init();  
        setPreference();                  
          
        var image = new Image();  
        image.src = "./sample.png";  
          
          
        image.onload = function() {  
            plot.drawImage(image,0,0);  
            var width = 10;  
            var height = 10;  
            var xOffset = 300;  
            var yOffset = 200;  
            var x0 = xOffset;  
            var y0 = yOffset;  
            var x1 = xOffset + width;  
            var y1 = yOffset + height;  
            var imagedata = plot.getImageData(x0, y0, width, height);  
            plot.putImageData(imagedata, x0, y0);  
  
            // var pointInfo = "<br/>";  
            for (var i = 0, len = imagedata.data.length; i < len; i += 4) {  
                // pointInfo += "<br/><X, Y>"+x0.toString() +", "+y0.toString() + "<R, G, B, A>";  
                  
                // for (var j = 0; j<4; j++) {  
                //     pointInfo += imagedata.data[i+j].toString() + ", ";  
                // }  
                x0++;  
                //行结束  
                if (x0 == x1) {  
                    x0 = xOffset;  
                    y0++;  
                }  

                
                var rand=Math.floor(Math.random()*20);
                imagedata = plot.getImageData(x0, y0, width, height);  
            	plot.putImageData(imagedata, x0+rand, y0+rand);  
            }  
            // document.write(pointInfo);
              
            // var pointInfoNode = document.createTextNode(pointInfo);  
            // document.body.appendChild(pointInfoNode);  
        };        
    }  

    myplot();
    



    //自己生成的方法未解决
/*setPreference(); 
var image = new Image();  
image.width=600;
image.height=400;
    // var imageData = new ImageData(100, 100);  
    plot.drawImage(image)
    var imageData = plot.getImageData(0, 0, 600, 400);
    for (var i = 0; i < 600; i++) {  
        for (var j = 0; j < 400; j++) {  
            var pos = i*600 + j;  
            imageData.data[pos * 4 + 0] = 255;  
            imageData.data[pos * 4 + 1] = 255;  
            imageData.data[pos * 4 + 2] = 0;  
            imageData.data[pos * 4 + 3] = 255;  
        }  
    }             
      console.log(imageData);
    image.onload = function() { 
        // plot.drawImage(image, 0, 0);
          
        plot.putImageData(imageData, 0, 0);  
    };     */   