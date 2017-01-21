/**
 * 封装一个形状类，加入绘制矩形的函数，其它的函数以后需要时补充
 */

function Shape() {
    this.rect = function(x, y, w, h) {
        w = Math.abs(w);
        h = Math.abs(h);
        return plot.strokeRect(x - w / 2, y - h / 2, w, h);
    };
    this.tri = function(x, y, r) {

        plot.translate(x, y)
            .scale(r / 100, r / 100);

        var xarr = new Array(0, -87, 87);
        var yarr = new Array(100, -50, -50);
        var len = xarr.length;

        plot.beginPath()
            .moveTo(xarr[0], yarr[0]);
        for (var i = 1; i < len; i++) {
            plot.lineTo(xarr[i], yarr[i]);
        }
        return plot.closePath().fill();

    };
    this.pantagon = function(x, y, r) {

        plot.translate(x, y)
            .scale(r / 100, r / 100);

        var xarr = new Array(0, -95, -59, 59, 95);
        var yarr = new Array(100, 31, -81, -81, 31);
        var len = xarr.length;

        plot.beginPath()
            .moveTo(xarr[0], yarr[0]);
        for (var i = 1; i < len; i++) {
            plot.lineTo(xarr[i], yarr[i]);
        }
        return plot.closePath().stroke();

    };
    /** 
* @usage  以顶点递推方式绘制正多边形 
* @author  mw 
* @date    2015年12月01日  星期二  09:42:33  
* @param  (x, y)图形中心坐标，r 外接圆半径 edge 边数 
* @return 
* 
*/  
    //{Shape类}  
    this.nEdge = function(x, y, r, edge) {  
        plot.save();  
        //plot.translate(x, y);  
        //strokeCircle(x, y, r);      
  
        var perAngle = Math.PI * 2 / edge;  
        var a = r * Math.sin(perAngle / 2);  
        var xOff = a;  
        var yOff = r*Math.cos(perAngle / 2);  
        plot.translate(-xOff , -yOff);                
                  
        var x1 = x;  
        var y1 = y;  
        var x2 = x1 + 2 * a;  
        var y2 = y1;  
  
        var xArray = new Array(x1, x2);  
        var yArray = new Array(y1, y2);  
          
        var angle = 0;  
          
        for (var i=0; i < edge; i++) {  
            x2 = x1 + 2 * a * Math.cos(angle);  
            y2 = y1 + 2 * a * Math.sin(angle);  
              
            xArray.push(x2);  
            yArray.push(y2);  
              
            x1 = x2;  
            y1 = y2;  
            angle += perAngle;  
              
        }  
              
        plot.moveTo(xArray[0], yArray[0]);  
        for (var i=1; i< xArray.length; i++) {  
            plot.lineTo(xArray[i], yArray[i]);  
        }  
        plot.stroke()  
            .restore();  
      
    }  ;
    /** 
* @usage   空心星形 
* @author  mw 
* @date    2015年12月01日  星期二  10:06:13  
* @param 
* @return 
* 
*/    
        this.nStar = function(x, y, r, edge) {  
            plot.save();  
            plot.translate(x, y);  
          
            var perAngle = Math.PI * 2 / edge;  
              
            var r0 = r * 0.6;  
              
            var xArray = new Array();  
            var yArray = new Array();  
              
            for (var i =0; i<edge; i++) {  
                xArray.push(r0 * Math.cos(i * perAngle));  
                yArray.push(r0 * Math.sin(i * perAngle));  
                xArray.push(r * Math.cos(i * perAngle + perAngle));  
                yArray.push(r * Math.sin(i * perAngle + perAngle));  
            }  
              
            plot.beginPath()  
                .moveTo(xArray[0], yArray[0]);  
            for (var i=0; i < xArray.length; i++) {  
                plot.lineTo(xArray[i], yArray[i]);  
            }  
            plot.closePath()  
                .stroke()  
                .restore();       
          
        }  ;
        /** 
* @usage   空心星形 
* @author  mw 
* @date    2015年12月01日  星期二  10:06:13  
* @param 
* @return 
* 
*/    
        this.nStar2 = function(x, y, r, edge) {  
            plot.save();  
            plot.translate(x, y);  
          
            var perAngle = Math.PI * 2 / edge;  
              
            r0 = 0.1 * r;  
            // var r0 = r / 2 /(1 + Math.cos(perAngle));  
              
            var xArray = new Array();  
            var yArray = new Array();  
              
            for (var i =0; i<edge; i++) {  
                xArray.push(r0 * Math.cos(i * perAngle));  
                yArray.push(r0 * Math.sin(i * perAngle));  
                xArray.push(r * Math.cos(i * perAngle + 0.5 * perAngle));  
                yArray.push(r * Math.sin(i * perAngle + 0.5 * perAngle));  
            }  
              
            plot.beginPath()  
                .moveTo(xArray[0], yArray[0]);  
            for (var i=0; i < xArray.length; i++) {  
                plot.lineTo(xArray[i], yArray[i]);  
            }  
            plot.closePath()  
                .stroke()  
                .restore();       
          
        }  

}


function axis(x, y, r) {
    plot.beginPath()
        .moveTo(x - r, y)
        .lineTo(x + r, y)
        .closePath()
        .stroke();

    plot.beginPath()
        .moveTo(x, y - r)
        .lineTo(x, y + r)
        .closePath()
        .stroke();

    plot.setFillStyle('black');

    var r0 = 10;

    //x轴箭头  
    plot.beginPath()
        .moveTo(x + r - r0 * Math.cos(Math.PI / 3), y - r0 * Math.sin(Math.PI / 3))
        .lineTo(x + r + r0 * Math.sin(Math.PI / 3), y)
        .lineTo(x + r - r0 * Math.cos(Math.PI / 3), y + r0 * Math.sin(Math.PI / 3))
        .closePath()
        .fill()

    //y轴箭头  
    plot.beginPath()
        .moveTo(x + r0 * Math.sin(Math.PI / 3), y - r + r0 * Math.cos(Math.PI / 3))
        .lineTo(x, y - r - r0 * Math.sin(Math.PI / 3))
        .lineTo(x - r0 * Math.sin(Math.PI / 3), y - r + r0 * Math.cos(Math.PI / 3))
        .closePath()
        .fill()

    plot.setFillStyle('#666666');
}

setPreference();

var row = 1;  
var col = 3;  
var width = 300;  
var height = 200;  
var maxWidth = width/col;  

// var shape = new Shape();  
// setSector(row, col, 2, 1);  
// plot.fillText("图A", 10, -height/row+10, maxWidth);  
// axis(0, 0, 80);  
// shape.nEdge(0, 0, 50, 5);  
  
// setSector(row, col, 2, 2);  
// plot.fillText("图B", 10, -height/row+10, maxWidth);  
// axis(0, 0, 80);  
// shape.nEdge(0, 0, 50, 7);  
  
// setSector(row, col, 2, 3);  
// plot.fillText("图C", 10, -height/row+10, maxWidth);  
// axis(0, 0, 80);  
// shape.nEdge(50, -50, 40, 6);  
// shape.nEdge(-50, 50, 30, 8);  
// shape.nEdge(-50, -50, 40, 4);  
// shape.nEdge(50, 50, 30, 3);  



/** 
* @usage   绘制空心星形 
* @author  mw 
* @date    2015年12月01日  星期二  08:26:53  
* @param 
* @return 
* 
*/  
    function myplot() {  
        // plot.init();  
        // setPreference();                  
      
        var row = 1;  
        var col = 3;  
        var width = 300;  
        var height = 200;  
        var maxWidth = width/col;  
          
          
        //<1>  
        setSector(row, col, 1, 1);  
        plot.fillText("第1步：起点A", -width/col+10, -height/row+30, maxWidth);  
        axis(0,0,80);  
          
        plot.fillText("A", -20,-10, 10);  
        fillCircle(0,0,10);  
          
        //<2>  
        setSector(row, col, 1, 2);  
        axis(0,0,80);  
        plot.fillText("第2步：尖峰", -width/col+10, -height/row+30, maxWidth);  
      
              
        plot.fillText("A", -20,-10, 10);  
        fillCircle(0,0,10);  
          
        plot.moveTo(0, 0)  
            .lineTo(15, -50)  
            .lineTo(30,0)  
            .stroke();  
          
          
        //<3>  
        setSector(row, col, 1, 3);  
        plot.fillText("第3步：衍化", -width/col+10, -height/row+30, maxWidth);  
        axis(0,0,80);  
      
              
        axis(0,0,80);  
  
        var shape = new Shape();  
        shape.nStar(50, 0, 50, 5);  
        //shape.nEdge(50, 50, 50, 5);  
    }  

    // myplot();



    var shape = new Shape();  
//<1>  
setSector(row, col, 1, 1);  
plot.fillText("图A", -width/col+10, -height/row+30, maxWidth);  
axis(0,0,80);  
  
shape.nStar2(0, 0, 50, 5);  
  
//<2>  
setSector(row, col, 1, 2);  
axis(0,0,80);  
plot.fillText("图B", -width/col+10, -height/row+30, maxWidth);  
shape.nStar2(0, 0, 50, 7);  
  
  
//<3>  
setSector(row, col, 1, 3);  
plot.fillText("图C", -width/col+10, -height/row+30, maxWidth);  
axis(0,0,80);  
shape.nStar2(50, 0, 50, 5);  
shape.nEdge(0, 50, 50, 5);  