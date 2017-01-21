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



/*var row = 3;  
var col = 3;  
for (var m = 1; m <= row; m++) {  
    for (var n = 1; n <= col; n++) {  
      
        setSector(row,col,m,n);  
        axis(0,0,180 / Math.max(row, col));           
      
        var shape = new Shape();                  
          
        var range = (m-1) * col + n +2; //圆内接几边形  
        var r = 200 / range; //中心连线的圆半径  
  
        plot.translate(-r/2, r/2);  
          
        for (var i=0; i<range; i++) {  
            if ( i!= 0) {  
                plot.translate(r/2, 0)  
                    .rotate(-2 * Math.PI /range);  
            }  
              
            plot.translate(r/2, 0);           
            // shape.rect(0, 0, r, 5);  
            shape.rect(0, 0, r, 1);//另一种效果  
  
        }     
    }
}*/





/** 
 * @usage  查看圆内接正N边形顶点坐标 
 * 
 */
function mytable(count) {
    var table = document.getElementsByTagName("table")[0];

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
    var ttr = document.createElement("tr")
    var ttd = document.createElement("td");
    var tty = document.createTextNode(count + "边形顶点坐标");
    ttd.appendChild(tty);
    ttr.appendChild(ttd);
    node.appendChild(ttr);

    //生成映射数据          
    var r = 100; //中心连线的圆半径  
    var range = count; //圆内接几边形  
    // var posArray = new Map(); //存放平衡量         
    var posArray = {}; //改用 ES5 对象实现    
    for (var i = 0; i < range; i++) {
        var x = Math.ceil(r * Math.cos(2 * Math.PI * i / range));
        var y = Math.ceil(r * Math.sin(2 * Math.PI * i / range));
        console.log(x + '***' + y);
        // posArray.put(x, y);  //ES6的特性 暂不支持
        if (posArray[y] != undefined) {
            posArray[y].unshift(x);
        } else {
            posArray[y] = [x];
        }
    }
    console.log(posArray);


    //单元格插入  
    // for (var i = 0; i<posArray.size(); i++) {  
    for (var ke in posArray) {
        // var xval = posArray.keys[i];      
        //    var yarr = posArray.get(xval);  

        var xval = parseFloat(ke);
        var yarr = posArray[ke];

        //插入<tr>            
        var tr = document.createElement("tr")
            //插入<td> x  
        var td = document.createElement("td");
        var x = document.createTextNode(xval.toFixed(3));
        td.appendChild(x);
        tr.appendChild(td);


        for (var j = 0; j < yarr.length; j++) {
            var yval = parseFloat(yarr[j]);

            //插入<td> y  
            var td = document.createElement("td");
            var y = document.createTextNode(yval.toFixed(3));
            td.appendChild(y);
            tr.appendChild(td);
        }

        node.appendChild(tr);
    }

}

mytable(3);
mytable(5);


var row = 3;
var col = 3;
for (var m = 1; m <= row; m++) {
    for (var n = 1; n <= col; n++) {

        setSector(row, col, m, n);
        axis(0, 0, 180 / Math.max(row, col));

        r = 10 * m * n;
        var shape = new Shape();
        plot.rotate(((m * row) + n) * Math.PI / 3);
        shape.tri(0, 0, r);
        setSector(row, col, m, n);
        plot.rotate(((m * row) + n) * Math.PI / 5);
        shape.pantagon(0, 0, r);

    }

}