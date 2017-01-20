// console.log(ctx);
// // ctx.strokeStyle="#333333";
// ctx.save();
// console.log(ctx);
function Plot() {  
    //此处封装Canvas API  
    // return obj;
}

/**
 * 初始化函数
 * 重写canvas 原生方法，实现连贯操作
 */
// function init(){

// }
Plot.prototype ={
    // log:function(){
    //     console.log(ctx);
    //     return ctx;
    // },
    
    init:function(){//怎样实现把 canvas 对象传递给 this
        // console.log(ctx);
        for (var key in ctx) {
            this[key]=ctx[key];
        }
        // console.log(this);
        // this = ctx;
        return this;
    },
    /*
    颜色、样式和阴影

    属性  描述
    fillStyle   设置或返回用于填充绘画的颜色、渐变或模式
    strokeStyle 设置或返回用于笔触的颜色、渐变或模式
    shadowColor 设置或返回用于阴影的颜色
    shadowBlur  设置或返回用于阴影的模糊级别
    shadowOffsetX   设置或返回阴影距形状的水平距离
    shadowOffsetY   设置或返回阴影距形状的垂直距离

    方法  描述
createLinearGradient()  创建线性渐变（用在画布内容上）
createPattern() 在指定的方向上重复指定的元素
createRadialGradient()  创建放射状/环形的渐变（用在画布内容上）
addColorStop()  规定渐变对象中的颜色和停止位置
    */
    setStrokeStyle:function(color){
        this.strokeStyle=color;
        return this;
    },
    setFillStyle:function(color){
        this.fillStyle=color;
        return this;
    },
    //.setShadowColor('#CCCCCC')  
    setShadowColor:function(color){
        this.shadowColor=color;
        return this;
    },
        //     .setShadowBlur(20)  
    setShadowBlur:function(num){
        this.shadoBlur=num;
        return this;
    },
        //     .setShadowOffsetX(10)  
    setShadowOffsetX:function(num){
        this.shadowOffsetX=num;
        return this;
    },
        //     .setShadowOffsetY(10)  
    setShadowOffsetY:function(num){
        this.shadowOffsetY=num;
        return this;
    },

    /*
线条样式
属性  描述
lineCap 设置或返回线条的结束端点样式
lineJoin    设置或返回两条线相交时，所创建的拐角类型
lineWidth   设置或返回当前的线条宽度
miterLimit  设置或返回最大斜接长度
    */
        //     //直线  
        //     .setLineCap("round")  
    setLineCap:function(style){
        this.lineCap=style;
        return this;
    },
        //     .setLineJoin("round")  
    setLineJoin:function(style){
        this.lineJoin=style;
        return this;
    },
        //     .setLineWidth(3)  
    setLineWidth:function(num){
        this.lineWidth=num;
        return this;
    },
        //     .setMiterLimit(10)  
    setMiterLimit:function(num){
        this.miterLimit=num;
        return this;
    },
    /*
矩形
方法  描述
rect()  创建矩形
fillRect()  绘制“被填充”的矩形
strokeRect()    绘制矩形（无填充）
clearRect() 在给定的矩形内清除指定的像素
    */

    /*
路径
方法  描述
fill()  填充当前绘图（路径）
stroke()    绘制已定义的路径
beginPath() 起始一条路径，或重置当前路径
moveTo()    把路径移动到画布中的指定点，不创建线条
closePath() 创建从当前点回到起始点的路径
lineTo()    添加一个新点，然后在画布中创建从该点到最后指定点的线条
clip()  从原始画布剪切任意形状和尺寸的区域
quadraticCurveTo()  创建二次贝塞尔曲线
bezierCurveTo() 创建三次方贝塞尔曲线
arc()   创建弧/曲线（用于创建圆形或部分圆）
arcTo() 创建两切线之间的弧/曲线
isPointInPath() 如果指定的点位于当前路径中，则返回 true，否则返回 false
    */


    /*
转换
方法  描述
scale() 缩放当前绘图至更大或更小
rotate()    旋转当前绘图
translate() 重新映射画布上的 (0,0) 位置
transform() 替换绘图的当前转换矩阵
setTransform()  将当前转换重置为单位矩阵。然后运行 transform()
    */

    newTransform:function(a,b,c,d,e,f){
        this.transform(a,b,c,d,e,f);
        return this;
    },




    /*
文本
属性  描述
font    设置或返回文本内容的当前字体属性
textAlign   设置或返回文本内容的当前对齐方式
textBaseline    设置或返回在绘制文本时使用的当前文本基线
方法  描述
fillText()  在画布上绘制“被填充的”文本
strokeText()    在画布上绘制文本（无填充）
measureText()   返回包含指定文本宽度的对象
    */
        //     //文字  
        //     .setFont("normal normal normal 18px arial")  
    setFont:function(style){
        this.font=style;
        return this;
    },
        //     .setTextAlign("left")  
    setTextAlign:function(style){
        this.textAlign=style;
        return this;
    },
        //     .setTextBaseline("alphabetic") 
    setTextBaseline:function(style){
        this.textBaseline=style;
        return this;
    }, 
    /*
合成
属性  描述
globalAlpha 设置或返回绘图的当前 alpha 或透明值
globalCompositeOperation    设置或返回新图像如何绘制到已有的图像上

    */
        //     .setGlobalCompositeOperation("source-over") 
    setGlobalCompositeOperation:function(style){
        this.globalCompositeOperation=style;
        return this;
    }, 
        //     .setGlobalAlpha(1.0)  
    setGlobalAlpha:function(num){
        this.globalAlpha=num;
        return this;
    },


    /*
图像绘制
方法  描述
drawImage() 向画布上绘制图像、画布或视频
像素操作
属性  描述
width   返回 ImageData 对象的宽度
height  返回 ImageData 对象的高度
data    返回一个对象，其包含指定的 ImageData 对象的图像数据
方法  描述
createImageData()   创建新的、空白的 ImageData 对象
getImageData()  返回 ImageData 对象，该对象为画布上指定的矩形复制像素数据
putImageData()  把图像数据（从指定的 ImageData 对象）放回画布上
    */


    /*其他
方法  描述
save()  保存当前环境的状态
restore()   返回之前保存过的路径状态和属性
createEvent()    
getContext()     
toDataURL()  */
        //     .save();  
    Save:function(){
        // console.log(this);
        this.save();
        return this;
    }
};



/** 
* @usage   初始化环境 
* @author  mw 
* @date    2015年11月27日  星期五  08:41:41  
* @param 
* @return 
* 
*/  
var plot = new Plot();
plot.init();
// plot.init().setStrokeStyle("dd").setFillStyle("sss");
// console.log(2222);
// console.log(plot);
// plot.log();
    function setPreference() {  
        plot.init()  
            //颜色  
            .setStrokeStyle("black")  
            .setFillStyle('#666666')  
            //阴影  
            .setShadowColor('#CCCCCC')  
            .setShadowBlur(20)  
            .setShadowOffsetX(10)  
            .setShadowOffsetY(10)  
            //直线  
            .setLineCap("round")  
            .setLineJoin("round")  
            .setLineWidth(3)  
            .setMiterLimit(10)  
            //文字  
            .setFont("normal normal normal 18px arial")  
            .setTextAlign("left")  
            .setTextBaseline("alphabetic")  
            .setGlobalCompositeOperation("source-over")  
            .setGlobalAlpha(1.0)  
            .Save();//这个问题解决不了了
    }




//将画布分为row*col个区，并且相对于m*n的基准绘图   
function setSector(row, col, m, n) {  
    var width = 600;  
    var height = 400;  
      
    if (m<=row && n<=col)   
        plot.newTransform(1,0,0,1,width*(n-1)/col,height*(m-1)/row);  
  
}









/** 
* @usage   绘制圆形 
* @author  mw 
* @date    2015年11月27日  星期五  12:11:38  
* @param 
* @return 
* 
*/  
/*function strokeCircle(x, y, r) {  
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
} */ 
  
/** 
* @usage   绘制三角形 
* @author  mw 
* @date    2015年11月27日  星期五  12:11:38  
* @param 
* @return 
* 
*/  
/*function strokeTri(x, y, r) {  
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
          
}  */
  
/** 
* @usage   绘制正方形 
* @author  mw 
* @date    2015年11月27日  星期五  12:11:38  
* @param 
* @return 
* 
*/  
/*function strokeSquare(x, y, r) {  
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
} */ 
  
/** 
* @usage   绘制梯形 
* @author  mw 
* @date    2015年11月27日  星期五  09:44:10  
* @param 
* @return 
* 
*/  
  
  /*  function strokeTrapezoid(x, y, r) {  
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
    }  */
      
      
/** 
* @usage  绘制菱形 
* @author  mw 
* @date    2015年11月27日  星期五  09:44:10  
* @param 
* @return 
* 
*/  
   /* function strokeDiamond(x, y, r) {  
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
      
    }  */
  
/** 
* @usage  绘制五边形 
* @author  mw 
* @date    2015年11月27日  星期五  10:13:24  
* @param 
* @return 
* 
*/    
    /*function strokePentagon(x, y, r) {  
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
      
    }  */
      
/** 
* @usage   绘制五角星 
* @author  mw 
* @date    2015年11月27日  星期五  11:19:42  
* @param 
* @return 
* 
*/    
  
/*    function strokeStar5p(x, y, r) {  
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
      
    } */ 


// function myplot() {  
//     setPreference();  
  
//     setSector(4, 6, 1, 1);  
//     //绘制三角形  
//     strokeTri(50, 50, 40);  
//     fillTri(50,50,40);  
  
//     setSector(4, 6, 1, 2);    
//     //绘制圆形  
//     strokeCircle(50, 50, 40);  
//     fillCircle(50,50,40);  
  
//     setSector(4, 6, 1, 3);  
//     //绘制正方形  
//     strokeSquare(50, 50, 40);  
//     fillSquare(50,50,40);  
  
//     /* 
//     [单词本] 
//         梯形 Trapezoid 
//         平行四边形 Parallel quadrilateral 
//         菱形 Diamond 
//         五角星 Five-pointed star 
//         五边形 Pentagon 
//         六边形 Hexagon 
//     */  
      
//     setSector(4, 6, 4, 4);  
//     //绘制梯形  
//     strokeTrapezoid(50, 50, 40);  
//     fillTrapezoid(50, 50, 40);  
      
//     setSector(4, 6, 4, 5);  
//     //绘制菱形  
//     strokeDiamond(50, 50, 40);  
//     fillDiamond(50, 50, 40);  
      
//     setSector(4, 6, 3, 3);  
//     //绘制五角星  
//     strokeStar5p(50, 50, 40);  
//     fillStar5p(50,50,40);  
          
//     setSector(4,6,3,2);  
//     fillStar5p(50, 50, 40);   
      
//     setSector(4,6,3,4);  
//     fillStar5p(50, 50, 40);  
      
//     setSector(4, 6, 2, 3);  
//     //绘制五边形  
//     strokePentagon(50, 50, 40);  
//     fillPentagon(50,50,40);  
      
//     setSector(4, 6, 4, 6);  
//     //绘制五边形  
//     strokePentagon(50, 50, 40);  
//     fillPentagon(50,50,40);  
  
  
// }