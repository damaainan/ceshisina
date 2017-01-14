/** 
* @usage   统计类 
* @author  mw 
* @date    2016年01月05日  星期二  10:14:34  
* @param 
* @return 
* 
*/  
function Statistic() {  
    this.statisticalSample = 0;  
    this.sampleSize = 0;  
  
    //初始化  
    this.init = function(array) {  
        this.statisticalSample = new Array();  
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
      
      
}

function myDraw() {  
    plot.init();  
    setPreference();  
  
    plot.save();  
    plot.translate(50, 350);  
    axis(0, 0, 600);  
    /* 
        统计样本 
        statistical sample 
        统计个数 
        Number of Statistics 
        个数 
        Number 
        长度 
        length 
    */  
    var sample = [9,6,15,8];  
    var color = ['red', 'yellow', 'blue', '#888888'];  
    var text = ['红色', '黄色', '蓝色', '白色'];  
  
    var height = 200, width = 300;  
      
    var stat = new Statistic();  
    stat.init(sample);  
      
    var max = stat.max();  
    var min = stat.min();  
  
      
    var size = stat.size();  
    var perH = Math.floor(height / (max+1));  
    var perW = Math.floor(width / (size*1.5 - 1));  
    var perDiff = -perH;  
      
    plot.setStrokeStyle('#CCCCCC')  
        .setTextAlign('right');  
    for (var i = 0; i < 1.2 * max; i+=5) {     
        if (i > 0) {  
            plot.beginPath()  
                .moveTo(0, 0+i * perDiff)  
                .lineTo(width+100, 0 + i*perDiff)  
                .closePath()  
                .stroke();  
        }  
          
          
        plot.fillText((i).toFixed(0), -5, i*perDiff, 100);  
      
    }  
    plot.setTextAlign('left');  
    for (var i = 0; i < size; i++) {  
        plot.setFillStyle(color[i]);  
        plot.fillRect(5+1.5*i*perW, 0, perW, -sample[i]*perH);  
        plot.fillText(text[i], 5+(1.5 * i+0.2) * perW, -(sample[i]+1)*perH, 100);  
    }  
      
  
    plot.restore();  
      
}

function myDraw() {  
    plot.init();  
    setPreference();  
  
    plot.save();  
    plot.translate(50, 350);  
    axis(0, 0, 600);  
    /* 
        统计样本 
        statistical sample 
        统计个数 
        Number of Statistics 
        个数 
        Number 
        长度 
        length 
    */  
    var sample = [15, 12, 4];  
    var color = ['red', 'yellow', 'blue', '#888888'];  
    var text = ['晴天', '阴天', '下雪'];  
  
    var height = 200, width = 300;  
      
    var stat = new Statistic();  
    stat.init(sample);  
      
    var max = stat.max();  
    var min = stat.min();  
  
      
    var size = stat.size();  
    var perH = Math.floor(height / (max+1));  
    var perW = Math.floor(width / (size*1.5 - 1));  
    var perDiff = -perH;  
      
    plot.setStrokeStyle('#CCCCCC')  
        .setTextAlign('right');  
    for (var i = 0; i < 1.2 * max; i+=5) {     
        if (i > 0) {  
            plot.beginPath()  
                .moveTo(0, 0+i * perDiff)  
                .lineTo(width+100, 0 + i*perDiff)  
                .closePath()  
                .stroke();  
        }  
          
          
        plot.fillText((i).toFixed(0), -5, i*perDiff, 100);  
      
    }  
    plot.setTextAlign('left');  
    for (var i = 0; i < size; i++) {  
        plot.setFillStyle(color[i]);  
        plot.fillRect(5+1.5*i*perW, 0, perW, -sample[i]*perH);  
        plot.fillText(text[i], 5+(1.5 * i+0.2) * perW, -(sample[i]+1)*perH, 100);  
    }  
      
  
    plot.restore();  
      
} 

function myDraw() {  
    plot.init();  
    setPreference();  
  
    plot.save();  
    plot.translate(50, 350);  
    axis(0, 0, 600);  
    /* 
        统计样本 
        statistical sample 
        统计个数 
        Number of Statistics 
        个数 
        Number 
        长度 
        length 
    */  
    var sample = [6, 8, 33,12];  
    var color = ['red', 'green', 'blue', '#888888'];  
    var text = ['面包车', '大巴车', '小轿车','摩托车'];  
  
    var height = 200, width = 300;  
      
    var stat = new Statistic();  
    stat.init(sample);  
      
    var max = stat.max();  
    var min = stat.min();  
  
      
    var size = stat.size();  
    var perH = Math.floor(height / (max+1));  
    var perW = Math.floor(width / (size*1.5 - 1));  
    var perDiff = -perH;  
      
    plot.setStrokeStyle('#CCCCCC')  
        .setTextAlign('right');  
    for (var i = 0; i < 1.2 * max; i+=5) {     
        if (i > 0) {  
            plot.beginPath()  
                .moveTo(0, 0+i * perDiff)  
                .lineTo(width+100, 0 + i*perDiff)  
                .closePath()  
                .stroke();  
        }  
          
          
        plot.fillText((i).toFixed(0), -5, i*perDiff, 100);  
      
    }  
    plot.setTextAlign('left');  
    for (var i = 0; i < size; i++) {  
        plot.setFillStyle(color[i]);  
        plot.fillRect(5+1.5*i*perW, 0, perW, -sample[i]*perH);  
        plot.fillText(text[i], (1.5 * i+0.2) * perW, -(sample[i]+1)*perH, 100);  
    }  
      
  
    plot.restore();  
      
} 