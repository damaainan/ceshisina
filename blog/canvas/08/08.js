/** 
* @usage   数字形状 
* @author  mw 
* @date    2015年12月01日  星期二  15:57:45  
* @param 
* @return 
* 
*/  
    function Digit() {  
        var shape = plot;
          
        //8  
        this.eight = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
          
        this.one = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
              
            //填充  
            //shape.fillRect(x, y, h, w0); //中横  
            //shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            //shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            //shape.fillRect(x, y-h, h, w0); //上横  
            //shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
      
        this.two = function(x, y, r) {  
            plot.save();  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            //shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            shape.fillRect(x-w, y+w, w0, h); //下左竖  
            //shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
      
        this.three = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            //shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            //shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
      
        this.four = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            //shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            //shape.fillRect(x, y-h, h, w0); //上横  
            //shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
          
        this.five = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            shape.fillRect(x-w, y-w, w0, h); //上左竖  
            //shape.fillRect(x+w, y-w, w0, h); //上右竖  
            //shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
          
        this.six = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            shape.fillRect(x-w, y-w, w0, h); //上左竖  
            //shape.fillRect(x+w, y-w, w0, h); //上右竖  
            shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
          
        this.seven = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            //shape.fillRect(x, y, h, w0); //中横  
            //shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            //shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            //shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
          
        this.nine = function(x, y, r) {  
            plot.save();  
  
              
            var h = r * 0.45; //字半高  
            var w = h / 2.2; //字半宽  
            var w0 = r *0.1; //填充宽  
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            shape.fillRect(x, y, h, w0); //中横  
            shape.fillRect(x-w, y-w, w0, h); //上左竖  
            shape.fillRect(x+w, y-w, w0, h); //上右竖  
            //shape.fillRect(x-w, y+w, w0, h); //下左竖  
            shape.fillRect(x+w, y+w, w0, h); //下右竖  
            shape.fillRect(x, y-h, h, w0); //上横  
            shape.fillRect(x, y+h, h, w0); //下横  
              
            plot.restore();  
          
        }  
          
        this.zero = function(x, y, r) {  
            plot.save();  //0  50  30 
  
              
            var h = r * 0.45; //字半高  13.5
            var w = h / 2.2; //字半宽  6
            var w0 = r *0.1; //填充宽  3
              
            /* 
            shape.strokeRect(x, y, h, w0); //中横 
            shape.strokeRect(x-w, y-w, w0, h); //上左竖 
            shape.strokeRect(x+w, y-w, w0, h); //上右竖 
            shape.strokeRect(x-w, y+w, w0, h); //下左竖 
            shape.strokeRect(x+w, y+w, w0, h); //下右竖 
            shape.strokeRect(x, y-h, h, w0); //上横 
            shape.strokeRect(x, y+h, h, w0); //下横 
            */  
            //填充  
            //shape.fillRect(x, y, h, w0); //中横  
            shape.fillRect(x, y, w0, h); //上左竖  
            shape.fillRect(x+2*w, y, w0, h); //上右竖  
            shape.fillRect(x, y+2*w, w0, h); //下左竖  
            shape.fillRect(x+2*w, y+2*w, w0, h); //下右竖  
            shape.fillRect(x, y, h, w0); //上横  
            shape.fillRect(x, y+2*h, h, w0); //下横  
              
            plot.restore();  
          
        }     
        /** 
		* @usage   绘制数字 
		* @author  mw 
		* @date    2015年12月01日  星期二  16:50:23  
		* @param  n [0-9] 要绘制的数字 x, y, 中心点 r 外接圆尺寸 
		* @return 
		* 
		*/  
        this.number = function(n, x, y, r) {  
            switch (n) {  
                case 0: this.zero(x, y, r); break;  
                case 1: this.one(x, y, r); break;  
                case 2: this.two(x,y,r); break;  
                case 3: this.three(x, y, r); break;  
                case 4: this.four(x,y,r);break;  
                case 5: this.five(x,y,r);break;  
                case 6: this.six(x,y,r); break;  
                case 7: this.seven(x,y,r); break;  
                case 8: this.eight(x,y,r); break;  
                case 9: this.nine(x,y,r); break;  
                default:break;  
              
            }  
        }  
    }  

setPreference();


    var digit = new Digit();  
// digit.eight(50,20,90);  
// digit.one(100, 20, 90);  
// digit.two(150, 20, 90);  
// digit.three(220, 20, 90);  
// digit.four(270, 20, 90);  
// digit.five(320, 20, 90);  
// digit.six(370, 20, 90);  
// digit.seven(420, 20, 90);  
// digit.nine(470, 20, 90);  
// digit.zero(520, 20, 90);


// for (var i = 0; i < 10; i++) {  
    digit.number(0, 0 * 50, 50, 30);  
// }  