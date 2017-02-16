function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    var vertExp = new VerticalExpression();  
  
  
      
    var row = 2, col=2, width = 600, height = 400;    
    var r = 20;    
    var x = 0, y=20;    
        
    quest = [[34,2],[12,4], [312,3], [211,4]];    
    len = quest.length;    
        
    for (var i = 0; i < row;  i++) {    
        for (var j=0; j < col; j++) {    
            x = width/col*(j+0.5);    
            y = 20 + height/row*i;    
            vertExp.mul(quest[i*col+j][0], quest[i*col+j][1], x, y, r);    
        }    
    }    
  
      
}  
  
  
/** 
* @usage   乘法竖式 
* @author  mw 
* @date    2016年01月14日  星期四  09:00:33  
* @param 
* @return 
* 
*/  
  
/* 
    multiplicand multiplier product 
*/  
  
    this.mul = function(multiplicand, multiplier, x, y, r) {  
        plot.save()  
            .setFillStyle('black');  
              
        var result = multiplicand * multiplier;  
        var xBeg = x ? x : 300, yBeg = y ? y :100, r = r ? r : 20;        
        var maxBit = Math.max(multiplicand, multiplier).toFixed(0).length;  
          
        x = xBeg, y = yBeg + r;  
        var mulPos = x - (maxBit+2) * r;  
        this.rightAlign(multiplicand, x, y, r);  
        y += 1.5 * r;  
        this.rightAlign(multiplier, x, y, r);  
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('×', mulPos, y+0.4*r, r);   
              
        y += r;  
        plot.beginPath()  
            .moveTo(mulPos - r, y)  
            .lineTo(x + r, y)  
            .closePath()  
            .stroke();  
              
        y += r;  
          
        var multiplierArray = new Array();  
        var tmp1 = multiplier;  
        var tmp2 = tmp1 % 10;  
          
        while (true) {  
            multiplierArray.push(tmp2);  
            tmp1 = (tmp1 - tmp2) / 10;  
            if (tmp1 <= 0)   
                break;  
            tmp2 = tmp1 % 10;  
        }  
          
        var len = multiplierArray.length;  
        var product = 0;  
          
        for (var i = 0; i < len; i++) {  
              
            if (multiplierArray[i] == 0) {  
                if (i == 0) {  
                    product = multiplierArray[i] * multiplicand;  
                    this.rightAlign(product, x, y, r);  
                    y += 1.5*r;  
                }  
            }  
            else {  
                product = multiplierArray[i] * multiplicand;  
                this.rightAlign(product, x, y, r);  
                if (i < len-1) {  
                    y += 1.5*r;  
                }  
            }  
            x -= r;  
        }  
          
        if (len > 1) {  
            y += r;  
            plot.beginPath()  
                .moveTo(mulPos - r, y)  
                .lineTo(xBeg + r, y)  
                .closePath()  
                .stroke();  
                  
            y += r;  
              
            this.rightAlign(result, xBeg, y, r);  
        }  
          
        plot.restore();  
    }