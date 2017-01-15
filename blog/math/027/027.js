//填充九九乘法表  
function myDraw() {  
    plot.init();  
    setPreference();  
      
    //图片  
    var image = new Image();  
    image.src = "./1.jpg";  
      
    image.onload = function() {  
        plot.drawImage(image);  
          
        var x0=140, y0=88, dx=32, dy=36;  
          
        plot.setFillStyle('red');  
        for (var i= 1; i<10; i++) {  
            for (var j=1;j<10;j++) {  
                  
                if (i != j) {  
                    plot.fillText((i*j).toFixed(0), x0, y0, 20);  
                }  
                x0 += dx;  
            }  
            y0 += dy;  
            x0 = 140;  
        }  
          
    }  
      
} 

/** 
* @usage   七巧板--鹤 
* @author  mw 
* @date    2016年01月04日  星期一  14:06:05  
* @param 
* @return 
* 
*/  
//七巧板--鹤  
function step2() {  
    plot.init();  
    setPreference();  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var scale = 50;  
    var R = 4 * scale;  
    var x0=0, y0=0;  
      
    var neves = new Neves();  
    neves.init(R);  
      
    var x1 = x0-2, y1=y0+2,  
        x2 = x0+1, y2=y0+1,  
        x3 = x0, y3 = y0+3,  
        x4 = x0+3, y4=y0-2,  
        x5 = x0+2, y5=y0+1;  
        x6 = x0+2.5, y6=y0-0.5;  
    neves.item(1, x0*scale, y0*scale, Math.PI);  
    neves.item(2, x1*scale, y1*scale, Math.PI/2);  
    neves.item(3, x2*scale, y2*scale, Math.PI*0.75);  
    neves.item(4, x3*scale, y3*scale, Math.PI/2);  
    neves.item(5, x4*scale, y4*scale, -Math.PI/2);  
    neves.item(6, x5*scale, y5*scale, Math.PI/4);  
    neves.item(7, x6*scale, y6*scale, Math.PI/2);  
} 
/** 
* @usage   七巧板--鱼 
* @author  mw 
* @date    2016年01月04日  星期一  14:06:05  
* @param 
* @return 
* 
*/  
//七巧板--鱼  
function step2() {  
    plot.init();  
    setPreference();  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
      
    var scale = 50;  
    var R = 4 * scale;  
    var x0=-2, y0=2;  
      
    var neves = new Neves();  
    neves.init(R);  
      
    var x1 = x0, y1=y0-2.828,  
        x2 = x0+1.414*3, y2=y0-1.414-0.707,  
        x3 = x0+1.414+0.707, y3 = y0,  
        x4 = x3, y4=y1,  
        x5 = x3, y5=(y0+y1)/2;  
        x6 = x0+1.414+.707+1.414, y6=y2+0.707+0.707;  
    neves.item(1, x0*scale, y0*scale, Math.PI/4);  
    neves.item(2, x1*scale, y1*scale, -Math.PI*0.25);  
    neves.item(3, x2*scale, y2*scale, Math.PI);  
    neves.item(4, x3*scale, y3*scale, Math.PI*0.75);  
    neves.item(5, x4*scale, y4*scale, -Math.PI*0.75);  
    neves.item(6, x5*scale, y5*scale, 0);  
    neves.item(7, x6*scale, y6*scale, -Math.PI/4, 1);  
}