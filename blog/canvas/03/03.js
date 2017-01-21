/**
 * 封装一个形状类，加入绘制矩形的函数，其它的函数以后需要时补充
 */

function Shape() {  
    this.rect = function(x, y, w, h) {  
        w = Math.abs(w);  
        h = Math.abs(h);  
        return plot.strokeRect(x-w/2, y-h/2, w, h);  
    };  
      
}  


 function axis(x, y, r) {  
        plot.beginPath()  
            .moveTo(x-r,y)  
            .lineTo(x+r,y)  
            .closePath()  
            .stroke();  
              
        plot.beginPath()  
            .moveTo(x,y-r)  
            .lineTo(x,y+r)  
            .closePath()  
            .stroke();  
          
        plot.setFillStyle('black');  
          
        var r0 = 10;  
          
        //x轴箭头  
        plot.beginPath()  
            .moveTo(x+r- r0*Math.cos(Math.PI/3), y-r0*Math.sin(Math.PI/3))  
            .lineTo(x+r+r0*Math.sin(Math.PI/3), y)  
            .lineTo(x+r -r0*Math.cos(Math.PI/3), y+r0*Math.sin(Math.PI/3))  
            .closePath()  
            .fill()  
          
        //y轴箭头  
        plot.beginPath()  
            .moveTo(x+ r0*Math.sin(Math.PI/3), y-r+r0*Math.cos(Math.PI/3))  
            .lineTo(x, y-r-r0*Math.sin(Math.PI/3))  
            .lineTo(x-r0*Math.sin(Math.PI/3), y-r+r0*Math.cos(Math.PI/3))  
            .closePath()  
            .fill()  
          
        plot.setFillStyle('#666666');  
}

setPreference();
setSector(2,3,2,2);  
axis(0,0,150);  


//效果 一
/*
var shape = new Shape();  
shape.rect(0, 0, 100, 100);  
  
plot.rotate(-Math.PI/4);  
shape.rect(0, 0, 100, 100); */


//效果 二
/*
var shape = new Shape();  
  
var r = 50; //中心连线的圆半径  
var range = 5; //圆内接几边形  
  
for (var i=0; i<r; i++) {  
    if ( i!= 0) {  
        plot//.translate(-r, 0)  
            .rotate(-2 * Math.PI /range);  
    }  
  
    plot.translate(r, 0);             
    shape.rect(0, 0, 100, 10);  
  
}*/  


//效果 三

var shape = new Shape();  
  
var r = 50; //中心连线的圆半径  
var range = 10; //圆内接几边形  
  
for (var i=0; i<r; i++) {  
    if ( i!= 0) {  
        plot.translate(-r, 0)  
            .rotate(-2 * Math.PI /range);  
    }  
  
    plot.translate(r, 0);             
    shape.rect(0, 0, 100, 10);  
  
}  
