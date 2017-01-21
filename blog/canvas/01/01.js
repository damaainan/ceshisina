


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