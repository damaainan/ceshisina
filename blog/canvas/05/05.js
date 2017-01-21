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
function sleep(delay) {
    var start = new Date().getTime();
    while (new Date().getTime() < start + delay)
        ;
}

/** 
* @usage   星形系列图形 
*/  
    function myplot(before,after) {  
        // plot.init();  
        // setPreference();              
          plot.restore();
        var row = 4;  
        var col = 5;  
        for (var m = 1; m <= row; m++) {  
            for (var n = 1; n <= col; n++) {  
              
                setSector(row,col,m,n);  
                //axis(0,0,180 / Math.max(row, col));                         
  
                var shape = new Shape();                  
                  
                var range = (m-1) * col + n +2; //圆内接几边形  
                var r = 200 / range; //中心连线的圆半径  
  
                //plot.translate(-r/2, r/2);  
                var barr=check(before);
                var aarr=check(after);
                  
                                  
                for (var i=0; i<range; i++) {  
                    if ( i!= 0) {  
                        //当此处的r从0取值到r时，会出现很多各种样式的雪花形状  
                        // plot.translate(0, r/2)  
                        //     .rotate(-2 * Math.PI /range);  
                            // console.log(before);
                            plot.translate(r*barr[0], r*barr[1])  
                            .rotate(-2 * Math.PI /range);  
                    }  
                      
                    // plot.translate(0, 0);   
                              // console.log(after);
                    plot.translate(r*aarr[0], r*aarr[1]);             
                    shape.rect(0, 0, r, 1);  
  
                }             
              
  
                var text = "旋转前平移X: 0"+" Y: r/2";  
                var text2 = "旋转后平移X: 0"+" Y: 0";  
                  
                plot.setFillStyle('black')  
                    .setTransform(1,0,0,1,0,0)  
                    .fillText(text, 10, 30, 200)  
                    .fillText(text2, 210, 30, 200);  
            }             
          
        }  
    }  
function check(arr){
	var sarr=[];
	for(var ke in arr){
		if(arr[ke]===0)
			sarr[ke]=0;
		else
			sarr[ke]=parseFloat(1/arr[ke]);
	}
	return sarr;
}

var bpara=[
[0,2],
[1,0],
[2,0],
[16,0],
[4,0],
];
var apara=[
[0,0],
[0,0],
[-2,0],
[16,0],
[6,0],
];
	myplot(bpara[6],apara[6]);



    // myplot();