/** 
* @usage   除法竖式 
* @author  mw 
* @date    2016年01月06日  星期三  11:05:09  
* @param 
* @return 
* 
*/  
function verticalDiv(dividend, divisor) {  
    /* 
        被除数 dividend 
        除数 divisor 
        商数 quotient 
        余数 remainder 
    */  
      
    var r = 20;  
      
    var lenOfDividend =dividend.toFixed(0).length;  
    var lenOfDivisor = divisor.toFixed(0).length;  
    var quotient = Math.floor(dividend/divisor);  
    var lenOfQuotient = quotient.toFixed(0).length;  
    var remainder = dividend - quotient * divisor;  
      
    a = [divisor, dividend, quotient, remainder];  
      
    //除数位置  
    var x0 = 20 + lenOfDivisor * r, y0= 0;  
    //被除数位置  
    var x1 = x0 + r + lenOfDividend * r, y1 = y0;  
    //商位置  
    var x2 = x1, y2 = y1 - 2 * r;  
      
    plot.beginPath()  
        .bezierCurveTo(x0-r, y0+r, x0-0.5*r, y0+0.5*r, x0-0.2*r, y0-0.5*r, x0, y0-r)  
/* 
        .moveTo(x0-r, y0+r) 
        .lineTo(x0, y0-1*r)*/  
        .closePath()  
        .stroke();  
    plot.beginPath()  
        .moveTo(x0, y0-1*r)  
        .lineTo(x2+r, y0-1*r)  
        .closePath()  
        .stroke();  
          
    rightAlign(a[0], x0, y0, r);  
    rightAlign(a[1], x1, y1, r);  
    rightAlign(a[2], x2, y2, r);  
      
  
    var tmp1, tmp2, tmp3, x, y;  
  
    //x, y的初始位置  
    x = x1 - (lenOfQuotient-1) *r, y = y1 + 1.5 * r;  
      
    if (lenOfQuotient > 1) {  
        for (var i = 0; i < lenOfQuotient; i++) {  
            if (i == 0) {  
                //待减  
                tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
                //被减  
                tmp2 = Math.floor(dividend / Math.pow(10, lenOfQuotient-i-2));  
                //减得的差进入下一轮  
                tmp3 = tmp2 - tmp1 * 10;  
                  
                rightAlign(tmp1, x, y, r);  
                plot.beginPath()  
                    .moveTo(x0, y+r)  
                    .lineTo(x1 +r, y+r)  
                    .closePath()  
                    .stroke();  
                rightAlign(tmp3,x+r, y+2*r, r);  
                  
                //位置递增  
                x += r;  
                y += 3.5*r;  
            }   
            else if (i < lenOfQuotient-1 ) {  
                //中间轮数  
                tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
  
                tmp3 = tmp3*10 + (dividend.toFixed(0)[i+lenOfDividend-lenOfQuotient+1]-'0')-tmp1*10;  
                  
                rightAlign(tmp1, x, y, r);  
                plot.beginPath()  
                    .moveTo(x0, y+r)  
                    .lineTo(x1 +r, y+r)  
                    .closePath()  
                    .stroke();  
                rightAlign(tmp3,x+r, y+2*r, r);  
                  
                x += r;  
                y += 3.5*r;  
                  
            }  
            else {  
                //最后一轮  
                tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
                rightAlign(tmp1, x, y, r);  
                plot.beginPath()  
                    .moveTo(x0, y+r)  
                    .lineTo(x1 +r, y+r)  
                    .closePath()  
                    .stroke();  
                      
                plot.beginPath()  
                    .moveTo(x0, y+r)  
                    .lineTo(x1 +r, y+r)  
                    .closePath()  
                    .stroke();  
                rightAlign(a[3],x, y+2*r, r);  
            }  
        }  
    }  
    else {  
        //最后一轮  
        tmp1 = quotient*divisor;  
        rightAlign(tmp1, x, y, r);  
        plot.moveTo(x0, y+r)  
            .lineTo(x1 +r, y+r)  
            .stroke();  
              
        plot.beginPath()  
            .moveTo(x0, y+r)  
            .lineTo(x1 +r, y+r)  
            .closePath()  
            .stroke();  
        rightAlign(a[3],x, y+2*r, r);  
    }  
  
}

function myDraw() {  
    plot.init();  
    setPreference();  
  
    var row = 1, col=3;  
    for (var i=0; i < col; i++) {  
        setSector(row, col, 1, i+0.5);  
        verticalDiv(11,  i+3);  
    }     
}


unction myDraw() {  
    plot.init();  
    setPreference();  
  
    var a = [43, 7,26,4,59,7];  
    var len = Math.floor(a.length/2);  
    var row = 1, col=4;  
      
    for (var i = 0; i < len; i++) {  
        setSector(row, col, 1, i+1);  
        verticalDiv(a[2*i], a[2*i+1]);  
    }     
  
}