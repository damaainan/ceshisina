/** 
* @usage   算盘 Abacus 
* @author  mw 
* @date    2016年01月08日  星期五  12:37:26  
* @param 
* @return 
* 
*/  
  
function Abacus() {  
    this.abacus = function(num, range) {  
        plot.save();  
        if (range) {  
            plot.scale(range/460, range/460);  
        }  
          
        var number = new Array([0, 0, 0, 0, 9, 8, 7, 6, 5, 4, 3, 2, 1]);  
          
        if (num) {  
            for (var i = 12; i>=0; i--) {  
                number[i] = num % 10;  
                num = Math.floor(num / 10);  
                if (num <= 0) {  
                    break;  
                }  
            }  
          
        }  
          
        var w = 420, h = 200, xcenter = 0, ycenter = 0, yBeam = ycenter - 39;  
        var r = (h-20)/12;  
        var beamH = 16;  
          
        plot.setFillStyle('#CC0000');  
        shape.fillRect(0, 0, w+40, h+40);  
  
          
        plot.setFillStyle('white');  
        shape.fillRect(0, 0, w, h);  
          
        plot.setFillStyle('#FF6622');  
        shape.fillRect(0, -39, w, beamH);  
          
        var gap = 32;  
          
        for (var i = 0; i < 13; i++) {  
            shape.fillRect(-193+gap*i, 0, 8, 200);  
        }  
          
          
          
        plot.setFillStyle('black');  
        var x, yUp, yUpBeam, yDown, yDownBeam;  
          
        for (var i = 0; i < 13; i++) {  
            x = -193+gap*i;  
            yDown = h/2-r/2;  
            yDownBeam = yBeam + beamH/2 + r/2;  
              
            yUp = -h/2 + r/2;  
            yUpBeam = yBeam - beamH/2 - r/2;  
              
              
            switch (number[i]) {  
                default:  
                case 0:  
                    for (var j = 0; j<5; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                      
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 1:  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<4; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 2:  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<3; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 3:  
                    for (var j = 0; j<3; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 4:  
                    for (var j = 0; j<4; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 5:  
                    for (var j = 0; j<5; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUpBeam-j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 6:  
                    for (var j = 0; j<4; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUpBeam-j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 7:  
                    for (var j = 0; j<3; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUpBeam-j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 8:  
                    for (var j = 0; j<2; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<3; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUpBeam-j * r*1.1, r, r/2);  
                    }  
                    break;  
                case 9:  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yDown-j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<4; j++) {  
                        shape.fillEllipse(x, yDownBeam+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUp+j * r*1.1, r, r/2);  
                    }  
                    for (var j = 0; j<1; j++) {  
                        shape.fillEllipse(x, yUpBeam-j * r*1.1, r, r/2);  
                    }  
                    break;  
              
            }  
        }     
          
        plot.restore();  
      
    }  
      
    this.demo = function() {  
        return this.abacus(1234567890);  
    }  
  
  
}

function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,2,1,2);  
    //config.axis2D(0, 0, 180);  
      
    var abacus = new Abacus();  
      
    var a = [17, 254, 180, 309, 600];  
    var r = 200;  
    var row = 3, col = 2;  
    var count = 0;  
      
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            if (count < a.length) {  
                config.setSector(row, col, i+1, j+1);  
                abacus.abacus(a[count], r);  
                count++;  
            }  
        }  
    }  
  
  
}


function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,2,1,2);  
    //config.axis2D(0, 0, 180);  
      
    var abacus = new Abacus();  
      
    var a = [270,538,406];  
    var r = 250;  
    var row = 2, col = 2;  
    var count = 0;  
      
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            if (count < a.length) {  
                config.setSector(row, col, i+1, j+1);  
                abacus.abacus(a[count], r);  
                plot.fillText(a[count].toFixed(0), -30, 90, 100);  
                count++;  
            }  
        }  
    }  
  
  
}

function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,2,1,2);  
    //config.axis2D(0, 0, 180);  
      
    var abacus = new Abacus();  
      
    var a = [4632,2508,9999];  
    var r = 250;  
    var row = 2, col = 2;  
    var count = 0;  
      
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            if (count < a.length) {  
                config.setSector(row, col, i+1, j+1);  
                abacus.abacus(a[count], r);  
                plot.fillText(a[count].toFixed(0), -30, 90, 100);  
                count++;  
            }  
        }  
    }  
  
  
}