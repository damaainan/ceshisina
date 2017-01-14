function drawClock(xOff, yOff, r, hour, minute) {  
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
      
    var thitaM = minute / 60 * Math.PI * 2 - Math.PI/2;  
    var thitaH = (hour + minute / 60 ) / 12 * Math.PI * 2-Math.PI/2;  
      
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
          
    plot.restore();  
  
}  
function clock(hour, minute) {  
    return drawClock(0, 0, 70, hour, minute);  
}