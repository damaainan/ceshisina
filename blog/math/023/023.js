/** 
* @usage   厘米和像素对照的尺子 
* @author  mw 
* @date    2016年01月03日  星期日  08:59:22  
* @param 
* @return 
* 
*/  
function Ruler() {  
    //在分辨率1024*768, dpi = 96时，每厘米像素比率为37.8。  
    var pxPerCm = 37.8;  
    var cm10th =pxPerCm / 10;  
    var cm5th = pxPerCm / 5;  
    var cm2th = pxPerCm / 2;  
      
      
    //一把任意长的尺子  
    this.ruler = function(rulerLong, xOffset, yOffset, rotate) {  
        plot.save();  
          
        plot.translate(xOffset, yOffset)  
            .rotate(-rotate);  
              
        rulerLong = rulerLong ? rulerLong : 10;  
        var L = pxPerCm * rulerLong;  
        for (var i = 0; i <L+10; i++) {  
            if (i % 100 == 0) {  
                plot.beginPath()  
                    .moveTo(i, 2 * pxPerCm - cm2th)  
                    .lineTo(i, 2 * pxPerCm-cm5th)  
                    .closePath()  
                    .stroke();  
              
            }  
            else if (i % 50 == 0) {  
                plot.beginPath()  
                    .moveTo(i, 2 * pxPerCm - 2 * cm5th)  
                    .lineTo(i, 2 * pxPerCm-cm5th)  
                    .closePath()  
                    .stroke();  
              
            }  
            else if (i % 10 == 0) {  
                plot.beginPath()  
                    .moveTo(i, 2 * pxPerCm - cm5th - cm10th)  
                    .lineTo(i, 2 * pxPerCm-cm5th)  
                    .closePath()  
                    .stroke();  
            }  
              
            if (i % 100 == 0) {  
                if (i == 0) {  
                    plot.fillText(i.toFixed(0), i-cm10th, 2 * pxPerCm - cm2th, 20);  
                }  
                else {  
                    plot.fillText(i.toFixed(0), i-3*cm10th, 2 * pxPerCm - cm2th, 20);  
                }  
              
            }  
        }  
                      
        var x=0, y=0, count = 0;  
        //cm刻度  
        plot.setStrokeStyle('red');  
        while (x <= L+10) {  
            plot.beginPath()  
                .moveTo(x, 0)  
                .lineTo(x, cm5th*2)  
                .closePath()  
                .stroke();  
                  
            if (count < 10) {  
                plot.fillText(count.toFixed(0), x-cm10th, 2 * cm2th, 20);  
            }  
            else {  
                plot.fillText(count.toFixed(0), x-cm5th, 2 * cm2th, 20);  
            }             
            x += pxPerCm;  
            count++;  
          
        }  
          
        //半厘米刻度  
        x=0, y=0, count = 0;  
        plot.setStrokeStyle('#CC0000');  
        while (x <= L) {  
                  
            if (count % 2 != 0) {  
            plot.beginPath()  
                .moveTo(x, 0)  
                .lineTo(x, cm5th)  
                .closePath()  
                .stroke();  
            }  
            x += cm2th;  
            count++;  
          
        }  
          
        //0.1cm刻度  
        plot.setStrokeStyle('#880000');  
        x=0, y=0, count = 0;  
          
        while (x <= L) {  
            if (count % 10 != 0 && count % 10 != 5) {  
                plot.beginPath()  
                    .moveTo(x, 0)  
                    .lineTo(x, cm10th)  
                    .closePath()  
                    .stroke();  
            }  
            x += cm10th;  
            count++;  
          
        }  
          
        x = -2 * cm5th, y=-cm5th;  
      
        plot.setLineWidth(4)  
            .setStrokeStyle('#FF8844');  
        plot.strokeRect(x, y, L + 4*cm5th, 2 * pxPerCm+cm5th);  
          
        plot.restore();  
      
    }  
      
}


function myDraw() {  
    plot.init();  
    setPreference();  
      
    var ruler = new Ruler();  
      
    var x = 20, y = 20;  
    for (var i = 1; i < 5; i++) {  
        ruler.ruler(5*i, x, y, 0);  
        x-=50;  
        y += 100;  
    }  
  
}