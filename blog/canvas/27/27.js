//绘制椭圆  
function  BezierEllipse1(x, y, a, b, rotate) {  
    //关键是bezierCurveTo中两个控制点的设置   
    //0.5和0.6是两个关键系数（在本函数中为试验而得）   
    var ox = 0.5 * a,   
    oy = 0.6 * b;   
    var rot = rotate ? -rotate : 0;  
    context.save();   
    context.rotate(rot);  
    context.translate(x, y);   
    context.beginPath();   
    //从椭圆纵轴下端开始逆时针方向绘制   
    context.moveTo(0, b);   
    context.bezierCurveTo(ox, b, a, oy, a, 0);   
    context.bezierCurveTo(a, -oy, ox, -b, 0, -b);   
    context.bezierCurveTo(-ox, -b, -a, -oy, -a, 0);   
    context.bezierCurveTo(-a, oy, -ox, b, 0, b);   
    context.closePath();   
    context.stroke();   
    context.restore();   
  
}  
setPreference(); 
/*
//绘制三维体  
var arr = [-0.5, -0.5, 0.5, - 0.5, 0.5, 0.5, -0.5, 0.5];  
var arr2 = [-0.5, -0.5, 0.5, - 0.5, 0.5, 0.5, -0.5, 0.5];  
var xtrans = [-0.5, 0, -0.5, 0, 0.5, 0, 0.5, 0];  
  
var len = arr.length;  
var point = Math.floor(arr.length / 2);  
var scale = 200;  
var ydown = 100;  
var xdown = 0;  
  
//上面  
//x  
for (var i = 0; i < len; i+=2) {  
    arr[i] += xtrans[i];  
    arr[i] = arr[i] * scale ;         
}  
//y  
for (var i = 1; i < len; i+=2) {  
    arr[i] += xtrans[i];  
    arr[i] = -arr[i] * scale * 0.5-100;  
}  
  
//下面  
//x  
for (var i = 0; i < len; i+=2) {  
    arr2[i] += xtrans[i];  
    arr2[i] = arr2[i] * scale+xdown;          
}  
//y  
for (var i = 1; i < len; i+=2) {  
    arr2[i] += xtrans[i];  
    arr2[i] = -arr2[i] * scale * 0.5+ydown;  
}  
  
fillCircle(arr[0], arr[1], 20);  
fillCircle(arr[2], arr[3], 10);  
  
plot.beginPath()  
    .moveTo(arr[0], arr[1]);  
for (var i = 1; i < point; i++) {  
    plot.lineTo(arr[2 * i], arr[2 * i + 1]);  
}  
plot.closePath()  
    .stroke();  
      
plot.beginPath()  
    .moveTo(arr2[0], arr2[1]);  
for (var i = 1; i < point; i++) {  
    plot.lineTo(arr2[2 * i], arr2[2 * i + 1]);  
}  
plot.closePath()  
    .fill();  
      
for (var i = 0; i < point; i++) {  
    plot.moveTo(arr[2*i], arr[2*i+1])  
        .lineTo(arr2[2*i], arr2[2*i+1]);  
}  
plot.stroke();  */





//改变参数  
/*var arr = [-0.5, -0.5, 0.5, - 0.5, 0.5, 0.5, -0.5, 0.5];  
var arr2 = [-0.5, -0.5, 0.5, - 0.5, 0.5, 0.5, -0.5, 0.5];  
var xtrans = [-0.5, 0, -0.5, 0, 0.8, 0, 0.8, 0];  
  
arr[i] = arr[i] * scale *Math.sin(Math.PI / (i+1));  
arr[i] = (-arr[i] * scale * 0.5)*Math.cos(Math.PI/(i+1))-100; 
*/
/*
//绘制立体图形  
    var arr = [-0.5, -0.5, 0.5, - 0.5, 0.5, 0.5, -0.5, 0.5];  
    var arr2 = [-0.5, -0.5, 0.5, - 0.5, 0.5, 0.5, -0.5, 0.5];  
    var xtrans = [-0.5, 0, -0.1, 0, 0.8, 0, 0.2, 0];  
      
    var len = arr.length;  
    var point = Math.floor(arr.length / 2);  
    var scale = 200;  
    var ydown = 100;  
    var xdown = 0;  
    //圆弧半圈数  
    var c = 0.2;  
      
    //上面  
    //x  
    for (var i = 0; i < len; i+=2) {  
        arr[i] += xtrans[i];  
        arr[i] = arr[i] * scale *Math.sin(Math.PI * c / (i+1)) ;          
    }  
    //y  
    for (var i = 1; i < len; i+=2) {  
        arr[i] += xtrans[i];  
        arr[i] = (-arr[i] * scale * 0.5)*Math.cos(Math.PI* c/(i+1))-100;  
    }  
      
    //下面  
    //x  
    for (var i = 0; i < len; i+=2) {  
        arr2[i] += xtrans[i];  
        arr2[i] = arr2[i] * scale *Math.sin(Math.PI *c/ (i+1))+xdown;         
    }  
    //y  
    for (var i = 1; i < len; i+=2) {  
        arr2[i] += xtrans[i];  
        arr2[i] = (-arr2[i] * scale * 0.5)*Math.cos(Math.PI*c/(i+1))+ydown;  
    }  
      
    fillCircle(arr[0], arr[1], 20);  
    fillCircle(arr[2], arr[3], 10);  
      
    plot.beginPath()  
        .moveTo(arr[0], arr[1]);  
    for (var i = 1; i < point; i++) {  
        plot.lineTo(arr[2 * i], arr[2 * i + 1]);  
    }  
    plot.closePath()  
        .stroke();  
          
    plot.beginPath()  
        .moveTo(arr2[0], arr2[1]);  
    for (var i = 1; i < point; i++) {  
        plot.lineTo(arr2[2 * i], arr2[2 * i + 1]);  
    }  
    plot.closePath()  
        .fill();  
          
    for (var i = 0; i < point; i++) {  
        plot.moveTo(arr[2*i], arr[2*i+1])  
            .lineTo(arr2[2*i], arr2[2*i+1]);  
    }  
    plot.stroke();

*/





    //绘制圆柱体  
    var x1=0, y1=-100, x2=x1, y2=y1+200;  
    var a1=100, b1=50, a2=100, b2=50;  
      
    plot.ellipse(x1, y1, a1, b1);  
    plot.fillellipse(x2, y2, a2, b2);  
      
    plot.moveTo(x1-a1, y1)  
        .lineTo(x2-a2, y2)  
        .stroke();  
          
    plot.moveTo(x1+a1, y1)  
        .lineTo(x2+a2, y2)  
        .stroke();





        //绘制球体  
    var x1=0, y1=0, x2=x1, y2=y1+200;  
    var a1=100, b1=50, a2=100, b2=50;  
      
    plot.fillellipse(x1, y1, a1, b1);  
    plot.ellipse(x1, y1, a1, b1, Math.PI/4);  
    plot.ellipse(x1, y1, a1, b1, -Math.PI/4);  
      
    strokeCircle(x1, y1, a1);