/** 
* @usage   贝塞尔曲线绘图 
*/  
function myplot() {  
        // plot.init();  
        setPreference();  
          
        setSector(1,1,1,1);  
        //axis(0, 0, 180);  
                  
        var h = -80;  
        //头发  
        hair(0, h, 115);  
  
        //左眉  
        eyebow(-20, h+100, 50, 0);  
        //右眉  
        eyebow(20, h+100, 50, 1);         
        //左眼  
        eye(-50, h+110, 50, 0, -Math.PI/24);  
        //右眼  
        eye(50, h+110, 50, 1, Math.PI/24);        
          
        face(-5, h+70, 220);          
        nose(0, h+110, 85);       
          
        mouth(0, h+210 , 50);  
                  
        //左耳  
        ear(-118, h+80, 120, 0);  
        //右耳  
        ear(108, h+80, 120, 1);  
          
        plot.fillText("帅哥降临 真mw肖像", 100, 150, 300);  
        plot.fillText("Hello 大家好，我是mw", 100, -150, 300);  
          
}  
  
/** 
* @usage   绘制耳朵 
*/  
  
function ear(x, y, r, left) {  
    r = Math.abs(r);  
      
    plot.save();  
      
    left = left ? left : 0;  
    if (left == 1) {  
    plot.translate(x, y)  
        .rotate(Math.PI/ 8)           
        .moveTo(0,0.3 * r)  
        .bezierCurveTo(r * 0.2, 0, r *0.4, r * 0.3, r *0.15, r * 0.75)  
        .quadraticCurveTo(r * 0.2,  0.98 * r, 0.02 * r, r)    
        .stroke();  
          
        //内圈耳  
    plot.moveTo(0.05*r,0.3 * r)  
        .bezierCurveTo(r * 0.15, 0.2 * r, r *0.3, r * 0.2, r *0.078, r * 0.85)  
        .stroke();  
          
        plot.restore();  
          
    }  
    else {  
    plot.translate(x, y)  
        .rotate(-Math.PI/ 8)      
        .moveTo(0,0.3 * r)  
        .bezierCurveTo(-r * 0.2, 0, -r *0.4, r * 0.3, -r *0.15, r * 0.75)  
        .quadraticCurveTo(-r * 0.2,  0.98 * r, -0.02 * r, r)      
        .stroke();  
          
        //内圈耳  
    plot.moveTo(-0.05*r,0.3 * r)  
        .bezierCurveTo(-r * 0.15, 0.2 * r, -r *0.3, r * 0.2, -r *0.078, r * 0.85)  
        .stroke();  
          
        plot.restore();  
    }  
      
  
  
}  
  
/** 
* @usage   绘制嘴 
*/  
  
function mouth(x, y, r) {  
    plot.save()  
        .translate(x-.5 * r, y-r / 6)  
        .beginPath()  
        .moveTo(0, 0)  
        .bezierCurveTo(r / 3, r/16, r * 2 / 3, r/ 16, r, 0)  
        .bezierCurveTo(r * 2 / 3, r/3, r/3, r/3, 0, 0)  
        .closePath()  
        .stroke()  
        .restore();  
}  
  
  
/** 
* @usage   绘制鼻子 
*/  
  
 function nose(x, y, r) {  
    var x0 = 0;  
    var y0 = 0;  
      
    plot.save()  
        .translate(x, y)  
        .moveTo(-2, 0)  
        .quadraticCurveTo(-0.1 * r, 0.8 * r,  
                          -0.16 * r, 0.9 * r)  
        .bezierCurveTo(-0.15* r, 0.9 * r,  
                      -0.08 * r, 0.8 * r,  
                      0, 0.95 * r)  
        .stroke();  
          
        plot  
        .moveTo(2, 0)  
        .quadraticCurveTo(0.08 * r, 0.8 * r,  
                          0.16 * r, 0.9 * r)  
        .bezierCurveTo(0.15* r, 0.9 * r,  
                      0.08 * r, 0.8 * r,  
                      0, 0.95 * r)  
        .stroke();  
          
        plot.restore();  
 }  
  
  
  
  
/** 
* @usage  绘制脸 
*/  
  
    function face(x, y, r) {  
        var x0 = x - r / 2;  
        var y0 = y;  
        var xstep = r / 3;  
        var ystep = xstep * 1.8;  
          
        plot.moveTo(x0, y0)  
            .bezierCurveTo(x0 + xstep, y0 + 2 * ystep,  
                           x0 + 2 * xstep, y0 + 2 * ystep,  
                           x0 + 3 * xstep, y0)            
            .stroke();  
    }  
  
  
  
/** 
* @usage  绘制眼睛 
*/  
  
    function eye(x, y, r, left, angle) {  
        plot.save();  
          
        left = left ? left : 0;  
        angle = angle ? angle : 0;  
        var x0 =  -r / 2;  
        var y0 = 0;  
        var xstep = r / 3;  
        var ystep = xstep / 1.5;  
          
        plot.translate(x, y)  
            .rotate(-angle)   
            .setFillStyle('black')  
            .beginPath()  
            .moveTo(x0, y0)  
            .bezierCurveTo(x0 + xstep, y0 + ystep, x0 + 2 * xstep, y0 + ystep,   
                x0 + 3 * xstep, y0)  
            .bezierCurveTo(x0 + 2 * xstep, y0-ystep, x0 + xstep, y0-ystep, x0, y0)  
            .closePath()  
            .stroke();  
      
              
        fillCircle(x0 + 1.5 * xstep, y0, ystep*0.68);  
        plot.setFillStyle('white');  
        //这个值取0是左眼，取1是右眼  
        if (left == 0) {  
            fillCircle(x0 + 1.5 * xstep - 0.28 * xstep, y0- ystep*0.2, ystep*0.2);  
        }  
        else {  
            fillCircle(x0 + 1.5 * xstep + 0.28 * xstep, y0- ystep*0.2, ystep*0.2);  
        }  
        plot.restore();  
  
      
    }  
/** 
* @usage   绘制眉毛 
*/  
  
//眉毛  
function eyebow(x, y, r, left, thick, angle) {  
      
        plot.save()  
            .setLineWidth(1);         
  
        r = Math.abs(r);  
        left = left ? left : 0;  
        //从x轴逆时针偏移角度  
        angle = angle ? -angle : (left ? -Math.PI/12 : -Math.PI/16);  
        //粗细  
        thick = thick ? thick : 30;  
        var xstep = r / 3;  
        var ystep = -r/10;        
        var thick = 30;  
        var x0 =  x  ;  
        var y0 = y;  
          
        //左眉  
        if (left == 0) {   
              
            xstep = -xstep;  
            angle = -angle;  
        }  
          
        for (var i = 0; i < thick; i++) {  
            plot.moveTo(x0, y0)  
                .bezierCurveTo(x0 + xstep,   
                   y0 + ystep * (2.2 + Math.random()) + xstep * Math.sin(angle),   
                   x0 + 2 * xstep,   
                   y0 + ystep * (1.2 + Math.random()) + 2 * xstep * Math.sin(angle),  
                   x0 + 3 * xstep + 0.5 * xstep * Math.random(),   
                   y0 + 0.2* thick * Math.random() + 3 * xstep * Math.sin(angle));  
              
            x0 += Math.random()-0.5;  
            y0 += 0.05 * thick * (Math.random()-0.5);  
              
              
        }  
          
        plot.stroke();  
          
        plot.restore();  
  
}  
  
/** 
* @usage   绘制头发 
*/  
  
//头发  
    function hair(xCenter, yCenter, r) {  
        plot.save()  
            .setLineWidth(1);     
              
        var x = 0;  
        var y = 0;  
        var len = r;  
          
        var arr = new Array();  
          
        var thick = r / 2;  
        for (var i = -thick; i < thick; i+= 2 ) {  
            arr.push(i, (i * i) / (0.6 * thick));  
        }  
          
        var xstep = r / 10;  
        var ystep = -r / 6;  
        while (arr.length > 0) {  
            x = xCenter + r/thick * arr.shift();  
            y = yCenter + arr.shift();  
              
            for (var i = 0; i < 10; i++) {  
                plot.moveTo(x - xstep * (Math.random()-0.5), y)  
                .bezierCurveTo(x + xstep * (Math.random()-0.5),   
                                y + ystep,  
                                x - 2 * (Math.random()-0.6) * xstep ,   
                                y + (2 + 2 * (Math.random()-0.5)) * ystep,  
                                x - 20 * xstep * (Math.random()-0.3),   
                                y + (4 + 2 * (Math.random()-0.5)) * ystep);  
            }  
        }  
        plot.stroke();  
        plot.restore();  
      
      
      
    }  

    myplot()