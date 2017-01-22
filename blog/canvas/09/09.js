/** 
* @usage   封装常用及精美图形 
* @author  mw 
* @date    2015年12月02日  星期三  09:22:00  
* @param   每个图形中参数 
*          (xCenter, yCenter)作为路径旋转中心， 
*          r作为外接圆半径来限定范围,  
*          angle作为初始旋转值，为0时默认x轴为起始或作为尖角对称轴， 
*          edge为图形系列边数。 
* @return  路径中顶点按顺序存储的数组[x1, y1, x2, y2,...,xn, yn]。 
* 
*/  
  
function ShapeProfile() {  
    this.retArray = new Array();  
/** 
* @usage   调用接口  
* @author  mw 
* @date    2015年12月02日  星期三  09:55:31  
* @param   index是图形在档案中的序号 
* @return 
* 
*/  
    this.draw = function(index, xCenter, yCenter, r, edge, angle){  
          
        index = Math.abs(Math.round(index));  
        xCenter = xCenter ? xCenter:0;  
        yCenter = yCenter ? yCenter:0;  
        r = r? r : 100;  
        edge = edge? edge : 5;  
        angle = angle ? angle : 0;  
          
          
        switch (index) {  
            //正多边形  
            case 1: this.nEdge(xCenter, yCenter, r, edge, angle); break;  
            //空心星形  
            case 2: this.nStar(xCenter, yCenter, r, edge, angle); break;  
            case 201: this.nStar(xCenter,yCenter, r, edge, angle, 0.2);break;  
            case 202: this.nStar(xCenter,yCenter, r, edge, angle, 0.3, 3.0); break;  
            //其它  
            default:break;  
        }  
      
    };  
      
      
/** 
* @usage  以顶点递推方式绘制正多边形 #1 
* @author  mw 
* @date    2015年12月01日  星期二  09:42:33  
* @param  (x, y)图形中心坐标，r 外接圆半径 edge 边数 
* @return 
* 
*/  
  
    this.nEdge = function(x, y, r, edge, angle0) {  
        this.retArray.length = 0;  
          
        var perAngle = Math.PI * 2 / edge;  
          
        var a = r * Math.sin(perAngle / 2);  
          
        var angle = -angle0 ;  
        var xOffset = r * Math.sin(perAngle / 2 - angle0);  
        var yOffset = r * Math.cos(perAngle / 2 - angle0);  
                      
                  
        var x1 = x-xOffset;  
        var y1 = y+yOffset;       
          
        for (var i=0; i < edge; i++) {             
            this.retArray.push(x1);  
            this.retArray.push(y1);  
            x1 = x1 + 2 * a * Math.cos(angle);  
            y1 = y1 + 2 * a * Math.sin(angle);  
            angle -= perAngle;  
              
        }  
      
        return this.retArray;  
      
    };    
          
/** 
* @usage   空心星形   #2 #201 #202 
* @author  mw 
* @date    2015年12月01日  星期二  10:06:13  
* @param 
* @return 
* 
*/    
        this.nStar = function(x, y, r, edge, angle0, arg0, arg1) {  
            this.retArray.length = 0;  
              
            var perAngle = Math.PI * 2 / edge;  
              
              
            var r0 = arg0 ? arg0 * r : r / (2 * (1 + Math.cos(perAngle)));  
            var scale = arg1 ? arg1 : 0.5;  
            var angle = 0.5 * perAngle -angle0;  
            var xOffset = x;  
            var yOffset = y;  
              
            for (var i =0; i< edge; i++) {  
                this.retArray.push(r0 * Math.cos(angle) + xOffset);  
                this.retArray.push(r0 * Math.sin(angle) + yOffset);  
                this.retArray.push(r * Math.cos(angle - scale * perAngle) + xOffset);  
                this.retArray.push(r * Math.sin(angle - scale * perAngle) + yOffset);  
                  
                angle -= perAngle;  
            }     
  
            return this.retArray;  
  
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

function myplot() {  
        // plot.init();  
        setPreference();                  
  
        setSector(1,1,1,1);  
        axis(0, 0, 180);  
          
        var shape = new ShapeProfile();  
        //偏移  
        shape.draw(1,100,100);  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("A", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
          
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
        //逆时针为正向，角度以弧度计算  
        shape.draw(1,0,0,100, 4, Math.PI/8);  
        /* 
                X   Y 
            -38.268 -92.388 
            92.388  -38.268 
            38.268  92.388 
            -92.388 38.268 
        */  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("B", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
        //缺省实现  
        shape.draw(1);  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("C", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
    }  

    // myplot()


    function myplot2() {  
        plot.init();  
        setPreference();                  
  
        setSector(1,1,1,1);  
        axis(0, 0, 180);  
          
        var shape = new ShapeProfile();  
        //偏移  
        shape.draw(2,100,100,50, 5, Math.PI/2);  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("A", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
          
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot//.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot//.closePath()  
                .stroke();  
        }  
          
        //逆时针为正向，角度以弧度计算  
        shape.draw(2,0,100,100, 4, Math.PI/8);  
  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("B", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
        //缺省实现  
        shape.draw(2);  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("C", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
    }  

    // myplot2()

    function myplot3() {  
        plot.init();  
        setPreference();                  
  
        setSector(1,1,1,1);  
        axis(0, 0, 180);  
          
        var shape = new ShapeProfile();  
        //偏移  
        shape.draw(201,100,100,50, 5, 0);  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("A", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
          
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
        //逆时针为正向，角度以弧度计算  
        shape.draw(202,-200,100,100, 4, Math.PI/8);  
        /* 
                    X   Y 
            -172.284    -111.481 
            -238.268    -192.388 
            -188.519    -72.284 
            -107.612    -138.268 
            -227.716    -88.519 
            -161.732    -7.612 
            -211.481    -127.716 
            -292.388    -61.732 
        */  
  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("B", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
        //缺省实现  
        shape.draw(2, 0, 0, 100, 7, Math.PI/2);  
        fillCircle(shape.retArray[0], shape.retArray[1], 10);  
        plot.fillText("C", shape.retArray[0]+20, shape.retArray[1]-20, 30);  
        if (shape.retArray.length > 2 && shape.retArray.length % 2 == 0) {  
            plot.beginPath()  
                .moveTo(shape.retArray.shift(), shape.retArray.shift());  
            while (shape.retArray.length > 0) {  
                plot.lineTo(shape.retArray.shift(), shape.retArray.shift());  
            }  
            plot.closePath()  
                .stroke();  
        }  
          
    }  

    myplot3()

    function mytable() {  
        var table = $$("table");  
        var caption = table.createCaption();  
        caption.innerHTML="顶点映射" +"<p>";  
        //找<tbody>  
        var node = table.firstChild;  
  
        while (null != node) {  
        /* 
            想知道都有哪些子节点，用这个 
                var text = document.createTextNode(node.nodeName); 
                document.body.appendChild(text); 
        */  
                  
            if ("TBODY" == node.nodeName)   
                break;            
  
            node = node.nextSibling;  
        }  
          
        var shape = new ShapeProfile();  
          
        shape.draw(1,0,0,100, 4, Math.PI/8);  
  
        //单元格插入  
        while (shape.retArray.length > 0) {  
                  
            //插入<tr>            
            var tr = document.createElement("tr")     
            //插入<td> x  
            var td = document.createElement("td");  
            var x = document.createTextNode(shape.retArray.shift().toFixed(3));  
            td.appendChild(x);  
            tr.appendChild(td);  
                      
            //插入<td> y  
            var td = document.createElement("td");  
            var y = document.createTextNode((-shape.retArray.shift()).toFixed(3));  
            td.appendChild(y);  
              
            tr.appendChild(td);  
  
              
            node.appendChild(tr);  
        }         
    }  