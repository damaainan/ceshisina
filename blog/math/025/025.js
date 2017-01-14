function myDraw() {  
    plot.init();  
    setPreference();  
      
    var row = 2;  
    var col = 4;  
    var r = 50;  
    var tmp, s;  
      
    var a = [7, 15, 7, 30, 7, 40, 8, 0, 1,20,10,45, 9,55, 6,5];  
    var len = a.length /2;  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {        
            setSector(row, col, i+1, j+1);  
            tmp = i*col+j;  
            if (tmp < len) {  
                clock(r, a[2*tmp], a[2*tmp+1]);  
                if (a[2*tmp+1]<10) {  
                    s = a[2*tmp].toFixed(0)+':'+'0'+a[2*tmp+1].toFixed(0);  
                }  
                else {  
                    s = a[2*tmp].toFixed(0)+':'+a[2*tmp+1].toFixed(0);  
                }  
                plot.fillText(s, -20, 1.4*r, 100);  
            }  
        }  
    }  
      
      
}  