/** 
* @usage   统计类 
* @author  mw 
* @date    2016年01月05日  星期二  10:14:34  
* @param 
* @return 
* 
*/  
function Statistic() {  
    this.statisticalSample = new Array();  
    this.sampleSize = 0;  
  
    //初始化  
    this.init = function(array) {  
        this.statisticalSample = array;  
        this.sampleSize = this.statisticalSample.length;  
    }  
      
    //最大值  
    this.max = function() {  
        var max = Number.NEGATIVE_INFINITY;  
        for (var i = 0; i < this.sampleSize; i++) {  
      
            if (max < this.statisticalSample[i]) {  
                max = this.statisticalSample[i];  
            }  
        }  
        return max;  
    }  
      
    //最小值  
    this.min = function() {  
        var min = Number.POSITIVE_INFINITY;  
        for (var i = 0; i < this.sampleSize; i++) {  
            if (min > this.statisticalSample[i]) {  
                min = this.statisticalSample[i];  
            }  
        }  
        return min;  
    }  
      
    //样本数量  
    this.size = function() {  
        return this.sampleSize;  
    }  
      
    //直方图  
    this.histogram = function(lableArray, xOffset, yOffset) {  
        lableArray = lableArray ? lableArray : [];  
        var lables = lableArray.length;  
        xOffset = xOffset ? xOffset : 0;  
        yOffset = yOffset ? yOffset : 0;  
          
        var colorArray = ['red', 'orange', '#0088FF', 'green', 'cyan', 'blue', '#FF00FF',  
            '#888888', 'black'];  
        var colors = colorArray.length;  
              
        var height = 300, width = 400;  
          
        plot.save()  
            .translate(xOffset, yOffset);  
        plot.setLineWidth(5)  
            .strokeRect(0, 0, width+2.5, height+2.5)  
            .setLineWidth(2);  
              
        for (var i = 0; i < height; i += height / 10) {  
            plot.beginPath()  
                .moveTo(0, i)  
                .lineTo(width, i)  
                .closePath()  
                .stroke();  
        }  
          
        var max = this.max();    
        var min = this.min();   
          
        var size = this.size();    
        var perH = Math.floor(height / (max+1));    
        var perW = Math.floor((width-20) / (size*1.5-0.5));  
          
        for (var i = 0; i < size; i++) {    
            plot.setFillStyle(colorArray[i%colors]);    
            plot.fillRect(10+1.5*i*perW, height, perW, -this.statisticalSample[i]*perH);   
              
            plot.setTextAlign('center');  
            if (i < lables) {  
                plot.fillText(lableArray[i], 10+(0.5 + 1.5 * i) * perW,   
                    height+20, 100);    
  
            }  
            plot.fillText(this.statisticalSample[i].toFixed(0), 10+(0.5 + 1.5 * i) * perW, height+40, 100);    
        }    
  
          
        plot.restore();  
      
    }  
}  
  
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();    
    //config.setSector(1,1,1,1);    
    //config.axis2D(0, 0, 180);    
       
    var stat = new Statistic();  
    var lable = ['跳绳', '舞蹈', '乒乓球', '踢毯', '其他'];  
    var sample = [20, 14, 17, 25, 9];  
      
    stat.init(sample);  
    stat.histogram(lable, 100, 50);  
      
  
} 