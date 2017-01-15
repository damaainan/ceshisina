function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();    
   
    //config.axis2D(0, 0, 180);    
  
    var clock = new Clock();  
      
    document.write(clock.timeToSecond([0,9,0])+'<br/>');  
    document.write(clock.timeToSecond([4,0,0])+'<br/>');  
    document.write(clock.timeToSecond([0,24,0])+'<br/>');  
    document.write(clock.timeToSecond([0,1,15])+'<br/>');  
    document.write(clock.timeToSecond([0,0,65])+'<br/>');  
    document.write(clock.timeToSecond([3,0,0])+'<br/>');  
    document.write(clock.timeToSecond([0,200,0])+'<br/>');  
    document.write(clock.timeToSecond([0,0,140])+'<br/>');  
    document.write(clock.timeToSecond([0,2,0])+'<br/>');  
    document.write(clock.timeToSecond([0,1,30])+'<br/>');  
    document.write(clock.timeToSecond([0,90,0])+'<br/>');  
}    


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();    
   
    //config.axis2D(0, 0, 180);    
  
    var clock = new Clock();  
    document.write(clock.secondToTime(clock.elapsedTime([7,30,0],[7,45,0]))+'<br/>');   
}  


/** 
* @usage  钟表类 
* @author  mw 
* @date    2016年01月07日  星期四  15:09:16  
* @param 
* @return 
* 
*/  
  
function Clock() {  
    /** 
    * @usage   绘制钟表 
    * @author  mw 
    * @date    2015年12月19日  星期六  14:04:24  
    * @param 
    * @return 
    * 
    */  
    this.drawClock = function(xOff, yOff, r, hour, minute, second) {  
        second = second ? second : 0;  
        hour = hour % 12;  
        minute = minute % 60;  
        second = second % 60;  
          
        plot.save()  
            .translate(xOff, yOff);  
          
        //钟面  
        strokeCircle(0, 0, r);  
        var x = 0, y = 0;  
        fillCircle(x, y, r * 0.05);  
        for (var i = 0 ; i < 12; i++) {  
            x = 0.88 * r * Math.cos(Math.PI / 6 * i);  
            y = 0.88 * r * Math.sin(Math.PI / 6 * i);  
              
            if (i % 3 == 0) {  
                fillCircle(x, y, r * 0.1);  
            }  
            else {  
                fillCircle(x, y, r * 0.05);  
            }     
        }  
          
        var thitaS = second / 60 * Math.PI * 2 - Math.PI/2;  
        var thitaM = (second / 60 + minute) / 60 * Math.PI * 2 - Math.PI/2;  
        var thitaH = (hour + (second / 60 + minute) / 60 ) / 12 * Math.PI * 2-Math.PI/2;  
          
        //时钟  
        var x1 = 0.5 * r * Math.cos(thitaH),   
            y1 = 0.5 * r * Math.sin(thitaH),  
            x2 = 0.15 * r * Math.cos(thitaH-Math.PI/18),  
            y2 = 0.15 * r * Math.sin(thitaH-Math.PI/18),  
            x3 = 0.15 * r * Math.cos(thitaH+Math.PI/18),  
            y3 = 0.15 * r * Math.sin(thitaH+Math.PI/18);  
          
        plot.setLineWidth(3)  
            .beginPath()  
            .moveTo(0, 0)  
            .lineTo(x2, y2)  
            .lineTo(x1, y1)  
            .lineTo(x3, y3)  
            .closePath()  
            .stroke();  
          
        //分钟  
            x1 = 0.75 * r * Math.cos(thitaM),   
            y1 = 0.75 * r * Math.sin(thitaM),  
            x2 = 0.15 * r * Math.cos(thitaM-Math.PI/18),  
            y2 = 0.15 * r * Math.sin(thitaM-Math.PI/18),  
            x3 = 0.15 * r * Math.cos(thitaM+Math.PI/18),  
            y3 = 0.15 * r * Math.sin(thitaM+Math.PI/18);          
  
        plot.setLineWidth(3)  
            .beginPath()  
            .moveTo(0, 0)  
            .lineTo(x2, y2)  
            .lineTo(x1, y1)  
            .lineTo(x3, y3)  
            .closePath()  
            .stroke();  
              
        //秒钟  
        x1 = 0.85 * r * Math.cos(thitaS),   
        y1 = 0.85 * r * Math.sin(thitaS);  
        plot.setStrokeStyle('red')  
            .beginPath()  
            .moveTo(0, 0)  
            .lineTo(x1, y1)  
            .closePath()  
            .stroke();  
              
              
        plot.restore();  
  
    }  
      
    this.clock = function(r, hour, minute, second) {  
        return this.drawClock(0, 0, r, hour, minute, second);  
    }  
      
/** 
* @usage   打印时间字符串 
* @author  mw 
* @date    2016年01月11日  星期一  13:00:23  
* @param 
* @return 
* 
*/  
    this.time = function(hour, minute, second) {  
        var s = '';  
        s += hour.toFixed(0)+ ' : ';  
        if (minute < 10)   
            s += '0';  
        s += minute.toFixed(0) + ' : ';  
        if (second < 10)  
            s += '0';  
        s += second.toFixed(0);  
          
        return s;  
    }  
      
/** 
* @usage   计算经过的时间 
* @author  mw 
* @date    2016年01月11日  星期一  13:00:23  
* @param 
* @return  单位为秒 
* 
*/  
    this.elapsedTime = function(timeArray1, timeArray2) {  
        //时间矩阵[h, m, s]  
        return (timeArray2[0]-timeArray1[0])*3600 + (timeArray2[1]-timeArray1[1])*60   
            + (timeArray2[2]-timeArray1[2]);  
    }  
      
    this.secondToTime = function(second) {  
        var hour = Math.floor(second / 3600);  
        var minute = Math.floor(second / 60) - hour * 60;  
        var secondRemain = second % 60;  
        var s = second.toFixed(0) + '秒 是 ' +hour.toFixed(0)+'小时' + minute.toFixed(0)+'分钟'+secondRemain.toFixed(0)+'秒';  
        return s;  
    }  
      
    this.timeToSecond = function(timeArray) {  
        var second = this.elapsedTime([0,0,0], timeArray);  
        var s = timeArray[0].toFixed(0)+'小时'+timeArray[1].toFixed(0)+'分'+  
            timeArray[2].toFixed(0)+'秒 是 '+second.toFixed(0)+'秒';  
              
        return s;  
    }  
          
}


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();    
   
    //config.axis2D(0, 0, 180);    
  
    var clock = new Clock();  
      
    //10万秒是多长的时间  
    document.write(clock.secondToTime(100000) + '<br/>');  
    //一天有多少秒  
    document.write(clock.timeToSecond([24,0,0])+'<br/>');  
      
      
}