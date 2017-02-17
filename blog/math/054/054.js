/** 
* @usage   小数加法竖式 
* @author  mw 
* @date    2016年01月21日  星期四  10:49:56  
* @param 
* @return 
* 
*/  
    this.floatAdd = function(augend, addend, precision, x, y, r) {  
          
        var decimalPointPos = -1;  
          
        plot.save()  
            .setFillStyle('black');  
              
        var result = addend + augend;  
        var xBeg = x ? x : 300, yBeg = y ? y :100, r = r ? r : 20;        
        var maxBit = Math.max(addend, augend).toFixed(0).length;  
        var decimalString = '';  
          
        //整数和小数部分的分解  
        augendDecimalPart = augend > 0 ? augend - Math.floor(augend) : Math.ceil(augend)- augend;          
        addendDecimalPart = addend > 0 ? addend - Math.floor(addend) : Math.ceil(addend)-addend;  
        resultDecimalPart = result > 0 ? result - Math.floor(result) : Math.ceil(result)-result;  
          
        augend = augend > 0 ? Math.floor(augend) : Math.ceil(augend);          
        addend = addend > 0 ? Math.floor(addend) : Math.ceil(addend);  
        result = result > 0 ? Math.floor(result) : Math.ceil(result);          
          
          
        x = xBeg, y = yBeg + r;  
        var plusPos = x - (maxBit+2) * r;  
        this.rightAlign(augend, x, y, r);  
          
        decimalString = augendDecimalPart.toFixed(precision);  
        decimalPointPos = decimalString.indexOf('.');  
        if (decimalPointPos != -1) {  
            decimalString = decimalString.substr(decimalPointPos+1, precision);  
            augendDecimalPart = parseInt(decimalString);  
            plot.fillText('.', x, y+0.4*r, r);  
            this.leftAlign(augendDecimalPart, x + r, y , r);  
        }  
          
        y += 1.5 * r;  
        this.rightAlign(addend, x, y, r);  
          
        decimalString = addendDecimalPart.toFixed(precision);  
        decimalPointPos = decimalString.indexOf('.');  
        if (decimalPointPos != -1) {  
            decimalString = decimalString.substr(decimalPointPos+1, precision);  
            addendDecimalPart = parseInt(decimalString);  
            plot.fillText('.', x, y+0.4*r, r);  
            this.leftAlign(addendDecimalPart, x + r, y , r);  
        }  
              
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('+', plusPos, y+0.4*r, r);  
          
        y += r;  
        plot.beginPath()  
            .moveTo(plusPos - r, y)  
            .lineTo(x + (precision+ 2) *r, y)  
            .closePath()  
            .stroke();  
              
        y += r;  
        this.rightAlign(result, x, y, r);  
          
        decimalString = resultDecimalPart.toFixed(precision);  
        decimalPointPos = decimalString.indexOf('.');  
        if (decimalPointPos != -1) {  
            decimalString = decimalString.substr(decimalPointPos+1, precision);  
            resultDecimalPart = parseInt(decimalString);  
            plot.fillText('.', x, y+0.4*r, r);  
            this.leftAlign(resultDecimalPart, x + r, y , r);  
        }  
              
              
        plot.restore();  
    }  



    /** 
* @usage   小数减法竖式 
* @author  mw 
* @date    2016年01月21日  星期四  10:49:56  
* @param 
* @return 
* 
*/  
    this.floatSub = function(minuend, subtrahend, precision, x, y, r) {  
          
        var decimalPointPos = -1;  
          
        plot.save()  
            .setFillStyle('black');  
              
        var result = minuend -subtrahend ;  
        var xBeg = x ? x : 300, yBeg = y ? y :100, r = r ? r : 20;        
        var maxBit = Math.max(subtrahend, minuend).toFixed(0).length;  
        var decimalString = '';  
          
        //整数和小数部分的分解  
        minuendDecimalPart = minuend > 0 ? minuend - Math.floor(minuend) : Math.ceil(minuend)- minuend;        
        subtrahendDecimalPart = subtrahend > 0 ? subtrahend - Math.floor(subtrahend) : Math.ceil(subtrahend)-subtrahend;  
        resultDecimalPart = result > 0 ? result - Math.floor(result) : Math.ceil(result)-result;  
          
        minuend = minuend > 0 ? Math.floor(minuend) : Math.ceil(minuend);          
        subtrahend = subtrahend > 0 ? Math.floor(subtrahend) : Math.ceil(subtrahend);  
        result = result > 0 ? Math.floor(result) : Math.ceil(result);          
          
          
        x = xBeg, y = yBeg + r;  
        var minusPos = x - (maxBit+2) * r;  
        this.rightAlign(minuend, x, y, r);  
          
        decimalString = minuendDecimalPart.toFixed(precision);  
        decimalPointPos = decimalString.indexOf('.');  
        if (decimalPointPos != -1) {  
            decimalString = decimalString.substr(decimalPointPos+1, precision);  
            minuendDecimalPart = parseInt(decimalString);  
            plot.fillText('.', x, y+0.4*r, r);  
            this.leftAlign(minuendDecimalPart, x + r, y , r);  
        }  
          
        y += 1.5 * r;  
        this.rightAlign(subtrahend, x, y, r);  
          
        decimalString = subtrahendDecimalPart.toFixed(precision);  
        decimalPointPos = decimalString.indexOf('.');  
        if (decimalPointPos != -1) {  
            decimalString = decimalString.substr(decimalPointPos+1, precision);  
            subtrahendDecimalPart = parseInt(decimalString);  
            plot.fillText('.', x, y+0.4*r, r);  
            this.leftAlign(subtrahendDecimalPart, x + r, y , r);  
        }  
              
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('-', minusPos, y+0.4*r, r);  
          
        y += r;  
        plot.beginPath()  
            .moveTo(minusPos - r, y)  
            .lineTo(x + (precision+ 2) *r, y)  
            .closePath()  
            .stroke();  
              
        y += r;  
        this.rightAlign(result, x, y, r);  
          
        if (result < 0) {  
            plot.fillText('-', minusPos, y+0.2*r, r);  
        }  
          
        decimalString = resultDecimalPart.toFixed(precision);  
        decimalPointPos = decimalString.indexOf('.');  
        if (decimalPointPos != -1) {  
            decimalString = decimalString.substr(decimalPointPos+1, precision);  
            resultDecimalPart = parseInt(decimalString);  
            plot.fillText('.', x, y+0.4*r, r);  
            this.leftAlign(resultDecimalPart, x + r, y , r);  
        }  
              
              
        plot.restore();  
    }

    function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
      
    var vertExp = new VerticalExpression();  
      
    var x = 300, y = 20, r = 20;  
      
      
    vertExp.floatAdd(1.23, 20.54, 2, x, y , r);  
      
    y += 200;  
    vertExp.floatSub(1.23, 20.54, 2, x, y , r);  
  
      
}