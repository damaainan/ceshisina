
/** 
* @usage   绘制圆形 
* @author  mw 
* @date    2015年11月27日  星期五  12:11:38  
* @param 
* @return 
* 
*/  
function strokeCircle(x, y, r) {  
        plot.beginPath()  
        .arc(x, y, r, 0, 2*Math.PI, true)  
        .closePath()  
        .stroke();  
}  
  
function fillCircle(x, y, r) {  
    plot.beginPath()  
        .arc(x, y, r, 0, 2*Math.PI, true)  
        .closePath()  
        .fill();  
}  
  
/** 
* @usage   绘制三角形 
* @author  mw 
* @date    2015年11月27日  星期五  12:11:38  
* @param 
* @return 
* 
*/  
function strokeTri(x, y, r) {  
    plot.beginPath()  
        .moveTo(x+r*Math.sin(Math.PI/3), y+r*Math.cos(Math.PI/3))  
        .lineTo(x, y-r*Math.sin(Math.PI/3))  
        .lineTo(x-r*Math.sin(Math.PI/3), y+r*Math.cos(Math.PI/3))  
        .closePath()  
        .stroke()  
          
}  
  
function fillTri(x, y, r) {  
    plot.beginPath()  
        .moveTo(x+r*Math.sin(Math.PI/3), y+r*Math.cos(Math.PI/3))  
        .lineTo(x, y-r*Math.sin(Math.PI/3))  
        .lineTo(x-r*Math.sin(Math.PI/3), y+r*Math.cos(Math.PI/3))  
        .closePath()  
        .fill()  
          
}  


  
/** 
* @usage   绘制正方形 
* @author  mw 
* @date    2015年11月27日  星期五  12:11:38  
* @param 
* @return 
* 
*/  
function strokeSquare(x, y, r) {  
    var a = r/2;  
      
    plot.beginPath()  
        .moveTo(x-a,y-a)  
        .lineTo(x+a, y-a)  
        .lineTo(x+a, y+a)  
        .lineTo(x-a,y+a)  
        .closePath()  
        .stroke()  
}  
  
function fillSquare(x,y,r) {  
    var a = r/2;  
      
    plot.beginPath()  
        .moveTo(x-a,y-a)  
        .lineTo(x+a, y-a)  
        .lineTo(x+a, y+a)  
        .lineTo(x-a,y+a)  
        .closePath()  
        .fill()  
}  
  
/** 
* @usage   绘制梯形 
* @author  mw 
* @date    2015年11月27日  星期五  09:44:10  
* @param 
* @return 
* 
*/  
  
    function strokeTrapezoid(x, y, r) {  
        var sqrt5 =  2.236;  
        var a = r * 2 / sqrt5;  
          
        plot.beginPath()  
        .moveTo(x-a/2,y-a/2)  
        .lineTo(x+a/2, y-a/2)  
        .lineTo(x+a, y+a/2)  
        .lineTo(x-a,y+a/2)  
        .closePath()  
        .stroke()  
    }  
      
    function fillTrapezoid(x, y, r) {  
        var sqrt5 =  2.236;  
        var a = r * 2 / sqrt5;  
          
        plot.beginPath()  
        .moveTo(x-a/2,y-a/2)  
        .lineTo(x+a/2, y-a/2)  
        .lineTo(x+a, y+a/2)  
        .lineTo(x-a,y+a/2)  
        .closePath()  
        .fill()  
    }  
      
      
/** 
* @usage  绘制菱形 
* @author  mw 
* @date    2015年11月27日  星期五  09:44:10  
* @param 
* @return 
* 
*/  
    function strokeDiamond(x, y, r) {  
        var cos30 = Math.cos(Math.PI/6);  
        var sin30 = Math.sin(Math.PI/6);  
          
        var a = r * cos30;  
        var h = r * sin30;  
        var b = r/cos30-a;  
          
        plot.beginPath()  
            .moveTo(x-b,y-h)  
            .lineTo(x+a, y-h)  
            .lineTo(x+b, y+h)  
            .lineTo(x-a,y+h)  
            .closePath()  
            .stroke()  
          
    }  
      
    function fillDiamond(x, y, r) {  
        var cos30 = Math.cos(Math.PI/6);  
        var sin30 = Math.sin(Math.PI/6);  
          
        var a = r * cos30;  
        var h = r * sin30;  
        var b = r/cos30-a;  
          
        plot.beginPath()  
            .moveTo(x-b,y-h)  
            .lineTo(x+a, y-h)  
            .lineTo(x+b, y+h)  
            .lineTo(x-a,y+h)  
            .closePath()  
            .fill()  
      
    }  
  
/** 
* @usage  绘制五边形 
* @author  mw 
* @date    2015年11月27日  星期五  10:13:24  
* @param 
* @return 
* 
*/    
    function strokePentagon(x, y, r) {  
        var cos72 = Math.cos(2 * Math.PI/5);  
        var sin72 = Math.sin(2 * Math.PI/5);  
        var cos36 = Math.cos(Math.PI/5);  
        var sin36 = Math.sin(Math.PI/5);  
          
          
        var a = 2 * r * sin36;  
        var h = a*sin72-r*cos36;  
        var b = a/2+a*cos72;  
          
        plot.beginPath()  
            .moveTo(x-r*sin36,y-r*cos36)  
            .lineTo(x+r*sin36, y-r*cos36)  
            .lineTo(x+b, y+h)  
            .lineTo(x,y+r)  
            .lineTo(x-b,y+h)  
            .closePath()  
            .stroke()         
      
    }  
      
    function fillPentagon(x, y, r) {  
        var cos72 = Math.cos(2 * Math.PI/5);  
        var sin72 = Math.sin(2 * Math.PI/5);  
        var cos36 = Math.cos(Math.PI/5);  
        var sin36 = Math.sin(Math.PI/5);  
          
          
        var a = 2 * r * sin36;  
        var h = a*sin72-r*cos36;  
        var b = a/2+a*cos72;  
          
        plot.beginPath()  
            .moveTo(x-r*sin36,y-r*cos36)  
            .lineTo(x+r*sin36, y-r*cos36)  
            .lineTo(x+b, y+h)  
            .lineTo(x,y+r)  
            .lineTo(x-b,y+h)  
            .closePath()  
            .fill()       
      
    }  
      
/** 
* @usage   绘制五角星 
* @author  mw 
* @date    2015年11月27日  星期五  11:19:42  
* @param 
* @return 
* 
*/    
  
    function strokeStar5p(x, y, r) {  
        var cos72 = Math.cos(2 * Math.PI/5);  
        var sin72 = Math.sin(2 * Math.PI/5);  
        var cos36 = Math.cos(Math.PI/5);  
        var sin36 = Math.sin(Math.PI/5);  
          
          
        var a = 2 * r * sin36;  
        var h = a*sin72-r*cos36;  
        var b = a/2+a*cos72;  
          
        plot.beginPath()  
            .moveTo(x-r*sin36,y-r*cos36) //1              
            .lineTo(x+b, y+h) //3             
            .lineTo(x-b,y+h) //5  
            .lineTo(x+r*sin36, y-r*cos36) //2  
            .lineTo(x,y+r) //4  
            .closePath()  
            .stroke()     
      
    }  
  
    function fillStar5p(x, y, r) {  
        var cos72 = Math.cos(2 * Math.PI/5);  
        var sin72 = Math.sin(2 * Math.PI/5);  
        var cos36 = Math.cos(Math.PI/5);  
        var sin36 = Math.sin(Math.PI/5);  
          
          
        var a = 2 * r * sin36;  
        var h = a*sin72-r*cos36;  
        var b = a/2+a*cos72;  
          
        plot.beginPath()  
            .moveTo(x-r*sin36,y-r*cos36) //1              
            .lineTo(x+b, y+h) //3             
            .lineTo(x-b,y+h) //5  
            .lineTo(x+r*sin36, y-r*cos36) //2  
            .lineTo(x,y+r) //4  
            .closePath()  
            .fill()   
      
    }  


function myplot() {  
    setPreference();  
  
    setSector(4, 6, 1, 1);  
    // plot.setTransform(1,0,0,1,0,0);
    //绘制三角形  
    strokeTri(50, 50, 40);  
    fillTri(50,50,40);  
  
    setSector(4, 6, 1, 2);    
    // plot.setTransform(1,0,0,1,50,0);
    //绘制圆形  
    strokeCircle(50, 50, 40);  
    fillCircle(50,50,40);  
  
    setSector(4, 6, 1, 3);  
    // plot.setTransform(1,0,0,1,100,0);
    //绘制正方形  
    strokeSquare(50, 50, 40);  
    fillSquare(50,50,40);  
  
    /* 
    [单词本] 
        梯形 Trapezoid 
        平行四边形 Parallel quadrilateral 
        菱形 Diamond 
        五角星 Five-pointed star 
        五边形 Pentagon 
        六边形 Hexagon 
    */  
      
    setSector(4, 6, 4, 4);  
    // plot.setTransform(1,0,0,1,150,0);
    //绘制梯形  
    strokeTrapezoid(50, 50, 40);  
    fillTrapezoid(50, 50, 40);  
      
    setSector(4, 6, 4, 5);  
    //绘制菱形  
    strokeDiamond(50, 50, 40);  
    fillDiamond(50, 50, 40);  
      
    setSector(4, 6, 3, 3);  
    //绘制五角星  
    strokeStar5p(50, 50, 40);  
    fillStar5p(50,50,40);  
          
    setSector(4,6,3,2);  
    fillStar5p(50, 50, 40);   
      
    setSector(4,6,3,4);  
    fillStar5p(50, 50, 40);  
      
    setSector(4, 6, 2, 3);  
    //绘制五边形  
    strokePentagon(50, 50, 40);  
    fillPentagon(50,50,40);  
      
    setSector(4, 6, 4, 6);  
    //绘制五边形  
    strokePentagon(50, 50, 40);  
    fillPentagon(50,50,40);  
  
  
}


    myplot();