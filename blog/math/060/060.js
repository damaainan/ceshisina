/** 
* @usage   平行线， 平行四边形， 梯形 
* @author  mw 
* @date    2016年01月24日  星期日  11:14:43  
* @param 
* @return 
* 
*/  
/* 
平行线 Parallel lines 
平行四边形 Parallel quadrilateral 
梯形 trapezoid 
*/  
    this.paraline = function(x, y, r, rot) {  
        rot = rot ? -rot : 0;  
        y = y ? -y : 0;  
        plot.beginPath()  
            .moveTo(x, y)  
            .lineTo(x + r * Math.cos(rot), y + r*Math.sin(rot))  
            .moveTo(x, y + r/ 10)  
            .lineTo(x + r * Math.cos(rot), y+r/10 + r*Math.sin(rot))  
            .closePath()  
            .stroke();    
  
    };  
      
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    config.axis2D(0, 0, 180);     
      
    shape.paraline(15, 20, 100, Math.PI/4);  
      
    shape.paraline(15, 20, 200, -Math.PI/3);  
  
              
}

this.paraquad = function(x, y, rot, a, b, angle) {  
        angle = angle ? Math.abs(angle) : 0;  
        rot = rot ? rot : 0;  
        //参数说明：  
        //平行四边形的两条边a, b, 以及它们之间的夹角angle  
        //这个平行四边形的起始点(x, y), 以及整个图形与x轴的夹角rot  
              
        var retArray = new Array();  
        retArray.push(x, -y);  
        retArray.push(x + a * Math.cos(rot), -(y + a * Math.sin(rot)));  
        retArray.push(x + a * Math.cos(rot)+ b * Math.cos(rot+angle),   
                      -(y + a * Math.sin(rot)+ b * Math.sin(rot+angle)));  
        retArray.push(x + b * Math.cos(rot+angle), -(y + b * Math.sin(rot+angle)));  
          
        return retArray;  
    }  
      
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    config.axis2D(0, 0, 180);     
      
    shape.strokeDraw(shape.paraquad(50, 50, Math.PI/6, 100, 50, Math.PI/6), 'red');  
      
    shape.fillDraw(shape.paraquad(50, -50, -Math.PI/6, 100, 50, Math.PI/6), 'blue');  
  
              
}  
  
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    config.axis2D(0, 0, 180);     
      
    shape.strokeDraw(shape.paraquad(50, 50, Math.PI/6, 100, 50, Math.PI/6), 'red');  
      
    shape.fillDraw(shape.paraquad(50, -50, -Math.PI/6, 100, 50, Math.PI/6), 'blue');  
      
    shape.fillDraw(shape.paraquad(-50, -50, 3*Math.PI/4, 100, 50, Math.PI/2), 'orange');  
  
              
}


this.trapezoid = function(x, y, rot, a, b, angle) {  
        angle = angle ? Math.abs(angle) : 0;  
        rot = rot ? rot : 0;  
        //参数说明：  
        //等腰梯形的下底边a，腰b, 以及它们之间的夹角angle  
        //假设下底 > 上底，那么上底 = (a - b * Math.cos(angle)*2)/2  
        //这个平行四边形的起始点(x, y), 以及整个图形与x轴的夹角rot  
          
        var c = (a - b * Math.cos(angle)*2)/2;  
          
        var retArray = new Array();  
        if (c < 0) {  
            //说明给的条件不对  
            //缺省画上底是下底一半的梯形  
              
        }  
        else {  
            retArray.push(x, -y);  
            retArray.push(x + a * Math.cos(rot), -(y + a * Math.sin(rot)));  
            retArray.push(x + b * Math.cos(rot+angle)+2*c * Math.cos(rot),   
                          -(y + b * Math.sin(rot+angle)+2*c*Math.sin(rot)));  
  
            retArray.push(x + b * Math.cos(rot+angle), -(y + b * Math.sin(rot+angle)));  
        }  
          
        return retArray;  
    }  
      
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    config.setSector(1,1,1,1);  
   
    config.axis2D(0, 0, 180);     
      
    //trapezoid  
    shape.strokeDraw(shape.trapezoid(50, 50, Math.PI/6, 100, 50, Math.PI/3), 'red');  
      
    shape.fillDraw(shape.trapezoid(50, -50, -Math.PI/6, 100, 50, Math.PI/3), 'blue');  
      
    shape.fillDraw(shape.trapezoid(-50, -50, 3*Math.PI/4, 100, 50, Math.PI/2), 'orange');  
  
              
}