function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var x = 100, y=20, r = 20;  
      
    var vertExp = new VerticalExpression();  
      
    vertExp.add(149, 278, x, y, r);  
      
    x += 150;  
    vertExp.sub(782, 368, x, y, r);  
      
    x += 150;  
    vertExp.mul(126, 3, x, y, r);  
      
    x += 50;  
    vertExp.div(60, 7, x, y, r);  
      
      
  
}


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var x = 20, y=20, r = 15;  
      
    var vertExp = new VerticalExpression();  
      
    vertExp.div(432, 2, x, y, r);  
      
    x += 150;  
    vertExp.div(522, 3, x, y, r);  
      
    x += 150;  
    vertExp.div(857, 5, x, y, r);  
      
    x += 150;  
    vertExp.div(635, 4, x, y, r);  
      
      
  
}




/** 
    * @usage   除法竖式（简便写法） 
    * @author  mw 
    * @date    2016年01月06日  星期三  11:05:09  
    * @param 
    * @return 
    * 
    */  
    this.div = function(dividend, divisor, xOffset, yOffset, r) {  
        plot.save();  
        /* 
            被除数 dividend 
            除数 divisor 
            商数 quotient 
            余数 remainder 
        */  
  
        var lenOfDividend =dividend.toFixed(0).length;  
        var lenOfDivisor = divisor.toFixed(0).length;  
        var quotient = Math.floor(dividend/divisor);  
        var lenOfQuotient = quotient.toFixed(0).length;  
        var remainder = dividend - quotient * divisor;  
          
        a = [divisor, dividend, quotient, remainder];  
          
        //除数位置  
        var x0 = xOffset + lenOfDivisor * r, y0= yOffset + 2 * r;  
        //被除数位置  
        var x1 = x0 + r + lenOfDividend * r, y1 = y0;  
        //商位置  
        var x2 = x1, y2 = yOffset;  
          
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
              
        this.rightAlign(a[0], x0, y0, r);  
        this.rightAlign(a[1], x1, y1, r);  
        this.rightAlign(a[2], x2, y2, r);  
          
  
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
                      
                    this.rightAlign(tmp1, x, y, r);  
                      
                    y += r;  
                      
                    plot.beginPath()  
                        .moveTo(x0, y)  
                        .lineTo(x1 +r, y)  
                        .closePath()  
                        .stroke();  
                    y += r;  
                      
                    if (tmp3 != 0 && quotient.toFixed(0)[i+1] - '0' > 0) {     
                        this.rightAlign(tmp3,x+r, y, r);  
                        y += 1.5 * r;  
                          
                    }  
                      
                    //位置递增  
                    x += r;  
                          
                }   
                else if (i < lenOfQuotient-1 ) {  
                    //中间轮数  
                    tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
  
                    tmp3 = tmp3*10 + (dividend.toFixed(0)[i+lenOfDividend-lenOfQuotient+1]-'0')-tmp1*10;  
                    if (tmp1 != 0) {  
                        this.rightAlign(tmp1, x, y, r);  
                      
                        y += r;  
                        plot.beginPath()  
                            .moveTo(x0, y)  
                            .lineTo(x1 +r, y)  
                            .closePath()  
                            .stroke();  
                        y += 1.5 * r;  
                    }  
  
                    if (tmp3 != 0 && quotient.toFixed(0)[i+1] - '0' > 0) {  
                        this.rightAlign(tmp3,x+r, y, r);  
                        y += 1.5 * r;  
                    }  
                      
                    x += r;                   
                      
                }  
                else {  
                    //最后一轮  
                    tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
                    if (tmp1 != 0) {  
                        this.rightAlign(tmp1, x, y, r);  
                        y += r;  
                        plot.beginPath()  
                            .moveTo(x0, y)  
                            .lineTo(x1 +r, y)  
                            .closePath()  
                            .stroke();  
                        y += r;  
                    }                     
                      
                    this.rightAlign(a[3],x, y, r);  
                }  
            }  
        }  
        else {  
            //最后一轮  
            tmp1 = quotient*divisor;  
            this.rightAlign(tmp1, x, y, r);  
            plot.moveTo(x0, y+r)  
                .lineTo(x1 +r, y+r)  
                .stroke();  
                  
            plot.beginPath()  
                .moveTo(x0, y+r)  
                .lineTo(x1 +r, y+r)  
                .closePath()  
                .stroke();  
            this.rightAlign(a[3],x, y+2*r, r);  
        }  
  
    }