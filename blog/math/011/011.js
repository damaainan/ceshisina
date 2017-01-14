function Neves() {  
    this.R = 100;  
      
    this.init = function(r) {  
        this.R = r;  
    }  
  
    this.redTriangle = function(x, y, rotate) {  
        //红色大三角形  
        var r = this.R;  
          
        plot.save()  
            .setFillStyle('red')  
            .translate(x, y)  
            .rotate(-rotate)  
            .beginPath()  
            .moveTo(0, -r/2)  
            .lineTo(r/2, 0)  
            .lineTo(0, r/2)  
            .closePath()  
            .fill()  
            .restore();  
    }  
      
    this.yellowTriangle = function(x, y, rotate) {  
        //黄色大三角形  
        var r = this.R;  
          
        plot.save()  
            .setFillStyle('yellow')  
            .translate(x, y)  
            .rotate(-rotate)  
            .beginPath()  
            .moveTo(0, -r/2)  
            .lineTo(r/2, 0)  
            .lineTo(0, r/2)  
            .closePath()  
            .fill()  
            .restore();  
    }  
      
    this.violetTriangle = function(x, y, rotate) {  
        //紫色三角形  
        var r = this.R / 1.414;  
          
        plot.save()  
            .setFillStyle('#CC00FF')  
            .translate(x, y)  
            .rotate(-rotate)  
            .beginPath()  
            .moveTo(0, -r/2)  
            .lineTo(r/2, 0)  
            .lineTo(0, r/2)  
            .closePath()  
            .fill()  
            .restore();  
    }  
      
    this.pinkTriangle = function(x, y, rotate) {  
        //粉色小三角形  
        var r = this.R / 2;  
          
        plot.save()  
            .setFillStyle('pink')  
            .translate(x, y)  
            .rotate(-rotate)  
            .beginPath()  
            .moveTo(0, -r/2)  
            .lineTo(r/2, 0)  
            .lineTo(0, r/2)  
            .closePath()  
            .fill()  
            .restore();  
    }  
      
    this.cyanTriangle = function(x, y, rotate) {  
        //青色小三角形  
        var r = this.R / 2;  
          
        plot.save()  
            .setFillStyle('cyan')  
            .translate(x, y)  
            .rotate(-rotate)  
            .beginPath()  
            .moveTo(0, -r/2)  
            .lineTo(r/2, 0)  
            .lineTo(0, r/2)  
            .closePath()  
            .fill()  
            .restore();  
    }  
      
    this.greenSquare = function(x, y, rotate) {  
        //绿色正方形  
        var r = this.R / 2.828;  
          
        plot.save()  
            .setFillStyle('green')  
            .translate(x, y)  
            .rotate(-rotate)  
            .beginPath()  
            .moveTo(-r/2, -r/2)  
            .lineTo(r/2, -r/2)  
            .lineTo(r/2, r/2)  
            .lineTo(-r/2, r/2)  
            .closePath()  
            .fill()  
            .restore();  
      
    }  
      
    this.parallelogram = function(x, y, rotate, clip) {  
        //橙色平行四边形  
        var rv = this.R / 4;  
        var rh = this.R / 2;  
  
        plot.save()  
            .setFillStyle('orange')  
            .translate(x, y)  
            .rotate(-rotate);  
            if (!clip) {  
            plot.beginPath()              
                .moveTo(-rh/2+rv/2, -rv/2)  
                .lineTo(rh/2+rv/2, -rv/2)  
                .lineTo(rh/2-rv/2, rv/2)  
                .lineTo(-rh/2-rv/2, rv/2)             
                .closePath();  
            } else {  
            plot.beginPath()              
                .moveTo(-(-rh/2+rv/2), -rv/2)  
                .lineTo(-(rh/2+rv/2), -rv/2)  
                .lineTo(-(rh/2-rv/2), rv/2)  
                .lineTo(-(-rh/2-rv/2), rv/2)              
                .closePath();  
              
            }  
            plot.fill()  
                .restore();  
      
    }  
      
    this.total = function() {  
        plot.save()  
            .translate(-this.R/2,0);  
        this.redTriangle(0, 0, 0);  
        this.yellowTriangle(this.R/2, -this.R/2, -Math.PI/2);  
        this.cyanTriangle(this.R, -this.R/4, Math.PI);  
        this.violetTriangle(this.R * 0.75, this.R/4, -Math.PI/4);  
        this.parallelogram(this.R * (0.5/4+0.25), this.R*(0.5-0.5/4), Math.PI);  
        this.pinkTriangle(this.R/2, this.R/4, Math.PI/2);  
        this.greenSquare(this.R*0.75, 0, Math.PI/4);  
      
        plot.restore();  
    }  
      
    this.item = function(nth, x, y, rotate, clip) {  
        switch(nth) {  
            case 1: this.redTriangle(x, y, rotate);break;  
            case 2: this.yellowTriangle(x, y, rotate);break;  
            case 3: this.violetTriangle(x, y, rotate); break;  
            case 4: this.pinkTriangle(x, y, rotate); break;  
            case 5: this.cyanTriangle(x, y, rotate); break;  
            case 6: this.greenSquare(x, y, rotate); break;  
            case 7: this.parallelogram(x, y, rotate, clip); break;  
            default: break;  
          
        }  
    }  
}

function picData() {  
    plot.init();  
    setPreference();  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
  
    var r = 200;  
    var neves = new Neves();  
    neves.init(r);  
      
    //绿色正方形  
    var x1 = 0, y1 = 0;  
    //左边红色三角  
    var x2 = x1-1.414/8*r-1.414/4*r, y2=y1-1.414/8*r+1.414/4*r;  
    //左边橙色平行四边形  
    var x3 = x1-1.414/8*r-1.414/2*r, y3=y1-1.414/8*r+1.414/2*r+1/6*r;  
      
    var x4 = x3+1/6*r, y4 = y3;  
    //右边黄三角  
    var x5 = -x2, y5 = y2;  
    //右边青三角  
    var x6 = x1+1.414/8*r+1.414/2*r-1.414/8*r, y6=y3;  
    //右边紫三角  
    var x7 = x1+1.414/8*r+1.414/2*r, y7= y6+1.414/8*r;  
      
    neves.item(6, x1, y1);  
    neves.item(1, x2, y2, -Math.PI/4);  
    neves.item(7, x3, y3, Math.PI/4);  
    neves.item(4, x4, y4, -Math.PI/4);  
    neves.item(2, x5, y5, -Math.PI * 3 / 4);  
    neves.item(5, x6, y6, Math.PI * 3/4);  
    neves.item(3, x7, y7, Math.PI /2);  
}  
function picData() {  
    plot.init();  
    setPreference();  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
  
    var r = 200;  
    var neves = new Neves();  
    neves.init(r);  
      
    var x1 = 0, y1 = 0;  
    var x2 = x1 - 1/2*r, y2 = y1 + 1/2*r;  
    var x3 = x1+0.707/2*r-r/4, y3 = y1 + 0.707/2*r + 1/4*r;  
    var x4 = x1 + 0.707/2*r, y4=y1-0.707/2*r-1/4*r;  
    var x5 = x1 + 0.707/2*r-1.414/8*r, y5 = x1-0.707/2*r-1.414/8*r;  
    var x6 = x4+1/4*r, y6 = y4+1/4*r;  
    var x7 = x2-1.414/4*r-r*1/4, y7=y1;  
      
    neves.item(1, x1, y1, -Math.PI/4);  
    neves.item(2, x2, y2, Math.PI*3/4);  
    neves.item(3, x3, y3, Math.PI/4);  
    neves.item(4, x4, y4, 0);  
    neves.item(5, x5, y5, Math.PI/4);  
    neves.item(6, x6, y6, Math.PI/4);  
    neves.item(7, x7, y7, -Math.PI/4, 1);  
      
  
}


picData() {  
    plot.init();  
    setPreference();  
    setSector(1,1,1,1);  
    axis(0, 0, 180);  
  
    var r = 200;  
    var neves = new Neves();  
    neves.init(r);  
      
    //红黄紫粉青绿橙按顺序排  
    //绿橙为方和平行四边形  
    var x1 = 100, y1 = 50;  
    var x2 = x1 - 1/2*r-r/4, y2 = y1 + 1/4*r;  
    var x3 = x1-0.707/2*r, y3 = y1 -r/2;  
    var x4 = x3-1.414/4*r, y4=y3-r/4;  
    var x5 = x2-r/2, y5 = y1+r/2-r/4;  
    var x6 = x1-1.414/8*r, y6 = y1-r/2-1.414/8*r;  
    var x7 = x1-1/4*r-r/8, y7=y1+r/2-r/8;  
      
    neves.item(1, x1, y1, Math.PI);  
    neves.item(2, x2, y2, Math.PI/2);  
    neves.item(3, x3, y3, -Math.PI/2);  
    neves.item(4, x4, y4, -Math.PI/2);  
    neves.item(5, x5, y5, -Math.PI/2);  
    neves.item(6, x6, y6, Math.PI/8);  
    neves.item(7, x7, y7, 0, 1);  
      
  
}  