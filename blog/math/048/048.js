function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
      
    var r = 10;  
    var R = 70;  
      
    var ruler = new Ruler();  
    ruler.compass(R, 0, 0, 0);  
      
    ruler.compass(R, 50, -100);  
    ruler.compass(R, -100, 50, Math.PI/3);  
    ruler.compass(1.5*R, 150, 100, -Math.PI/10);  
  
}  
  
/** 
* @usage   指南针 
* @author  mw 
* @date    2016年01月16日  星期六  15:36:25  
* @param 
* @return 
* 
*/  
  
//Compass 指南针  
  
    this.compass = function(r0, x, y, rot) {  
        r0 = r0 ? r0 : 100;  
          
        var r = 10 * r0 / 100;  
        var R = 50 * r0 / 100;  
  
        plot.save()  
            .translate(x, y)  
            .rotate(-rot);  
              
        for (var i = 0; i < 8; i++) {  
            plot.setStrokeStyle('orange')  
                .setLineWidth(5)  
                .beginPath()  
                .moveTo(0, 0)  
                .lineTo(R*Math.cos(Math.PI/4*i), -R*Math.sin(Math.PI/4*i))  
                .closePath()  
                .stroke();    
            shape.fillDraw(shape.nEdge(R*Math.cos(Math.PI/4*i),  
                                        -R*Math.sin(Math.PI/4*i), r, 3,   
                                        Math.PI/4*i-Math.PI/2), 'red');  
  
        }         
          
        var lable = ['E', 'NE', 'N', 'NW', 'W', 'SW', 'S', 'SE'];  
        var text = ['东', '东北', '北', '西北', '西', '西南', '南', '东南'];  
        for (var i = 0; i < 8; i++) {  
            plot.fillText(text[i], -10+R*1.5 * Math.cos(Math.PI/4*i),   
                            5-R*1.5 * Math.sin(Math.PI/4*i), 20);  
        }  
        shape.strokeCircle(0, 0, R*1.9);  
      
        plot.restore();  
    }