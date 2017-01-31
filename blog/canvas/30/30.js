//Map类的put(), print()功能测试  
function myDraw() {  
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
    }  
      
    var s = map.print();  
      
    document.body.appendChild(document.createTextNode(s));  
  
  
}  
  
//Map类的remove(), each()功能测试  
function myDraw() {  
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
        map.put(i, -i * i);  
        map.put(i, 2 * i * i);  
    }  
    map.remove(5);  
    map.remove(-7);  
      
    var s = map.print();  
      
      
    document.body.appendChild(document.createTextNode(s));  
  
    map.each(total);  
      
  
}  
  
function total(key, value, nth) {  
    var arr = new Array();  
    arr = value;  
    var sum = 0;  
      
    s = '第'+nth.toFixed(0)+'次：';  
    for (var i = 0; i < arr.length; i++) {  
        sum += arr[i];  
    }  
      
    s += '['+key.toFixed(0)+']'+'总计'+sum.toFixed(0);  
      
    document.body.appendChild(document.createTextNode(s));  
  
}  
  
//Map类的entrySize()功能测试  
function myDraw() {  
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
        map.put(i, -i * i);  
        map.put(i, 2 * i * i)  
  
    }  
      
    var s = map.entrySize().toFixed(0);  
      
    document.body.appendChild(document.createTextNode(s));  
}  
  
//Map类的entrys()功能测试  
function myDraw() {  
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
        map.put(i, -i * i);  
        map.put(i, 2 * i * i)  
  
    }  
      
    var array = new Array();  
    array = map.entrys();  
    var len = array.length;  
    var s = '';  
    for (var i = 0; i < len; i++) {  
        s+='['+array[i][0].toFixed(0)+', '+array[i][1].toFixed(0)+']';  
    }  
      
    document.body.appendChild(document.createTextNode(s));  
  
      
  
}  
  
//Map类的empty(), isEmpty()功能测试  
function myDraw() {  
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
        map.put(i, -i * i);  
        map.put(i, 2 * i * i)  
  
    }  
      
      
    var s = '';  
    s = map.print();  
      
    document.body.appendChild(document.createTextNode(s));  
  
    map.empty();  
    s = '+++++++++'+map.print();  
      
    document.body.appendChild(document.createTextNode(s));  
      
    s = '------'+map.isEmpty();  
    document.body.appendChild(document.createTextNode(s));  
  
}  
  
//Map类的toString()功能测试  
function myDraw() {  
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
        map.put(i, -i * i);  
        map.put(i, 2 * i * i);  
        map.put(i, 5 * i +15);  
  
    }  
      
    var s = '';  
    s = map.toString();  
      
    document.body.appendChild(document.createTextNode(s));  
  
      
  
}  



/** 
* @usage  映射类map 
* @author  mw 
* @date    2015年11月28日  星期六  08:01:13  
* @param 
* @return 
* 
*/  
function Map(){    
    /** 存放键的数组(遍历用到) */      
    this.keys = new Array();       
    /** 存放数据 */      
    this.data = new Array();       
           
    /**    
     * 放入一个键值对    
     * @param {String} key    
     * @param {Object} value    
     */      
    this.put = function(key, value) {       
        if(this.data[key] == null){       
            this.keys.push(key);      
            this.data[key] = new Array();  
        }  
          
        //避免坐标重复  
        var i = 0;  
        for (; i < this.data[key].length; i++) {  
            if (value == this.data[key][i]) break;  
        }  
        if ( i == this.data[key].length) {   
            this.data[key].push(value);     
        }  
    };       
      
    /** 
    * @usage   排序 
    * @author  mw 
    * @date    2015年12月05日  星期六  10:46:22  
    * @param 
    * @return 
    * 
    */  
    this.sort = function() {  
        this.keys.sort(function (a, b) { return a-b;});  
          
        for (var i = 0; i < this.size(); i++) {  
            this.data[this.keys[i]].sort(function (a, b) { return a-b;});  
        }  
      
    };  
           
    /** 
<span style="white-space:pre">    </span>* @usage   判断坐标点是否在map中 
<span style="white-space:pre">    </span>* @author  mw 
<span style="white-space:pre">    </span>* @date    2015年12月06日  星期日  07:50:37  
<span style="white-space:pre">    </span>* @param   点(x, y)， 映射集合map 
<span style="white-space:pre">    </span>* @return  true 或 false 
<span style="white-space:pre">    </span>* 
<span style="white-space:pre">    </span>*/  
<span style="white-space:pre">    </span>this.hasElement = function(x, y) {  
  
  
<span style="white-space:pre">        </span>var array = new Array();  
<span style="white-space:pre">        </span>array = this.entrys();  
<span style="white-space:pre">        </span>var len = this.entrySize();  
<span style="white-space:pre">        </span>var tolerance = 100 * Number.MIN_VALUE;  
<span style="white-space:pre">        </span>  
<span style="white-space:pre">        </span>for (var i = 0; i < len ; i++) {  
<span style="white-space:pre">            </span>if (Math.abs(x - array[i][0]) < tolerance &&  
<span style="white-space:pre">                </span>Math.abs(y - array[i][1]) < tolerance) {  
<span style="white-space:pre">                </span>   
<span style="white-space:pre">                </span>return true;  
<span style="white-space:pre">            </span>}  
<span style="white-space:pre">        </span>}  
<span style="white-space:pre">        </span>  
<span style="white-space:pre">        </span>return false;  
<span style="white-space:pre">    </span>}     
    /** 
    * @usage   打印坐标数组 
    * @author  mw 
    * @date    2015年12月05日  星期六  10:50:30  
    * @param 
    * @return  字符串 
    * 
    */  
    this.print = function() {  
        this.sort();  
          
        var s = "";  
        var len = this.size();  
        var key = 0;  
        var val = 0;  
          
        for (var i = 0; i < len; i++) {  
            key = this.keys[i];  
            s += "[key]" + key+ "[/key]";  
              
            for (var j =0 ; j < this.get(key).length; j++) {  
                s += this.get(key)[j] + ", ";  
            }  
        }  
          
        return s;  
    };  
  
  
    /**    
     * 获取某键对应的值    
     * @param {String} key    
     * @return {Object} value    
     */      
    this.get = function(key) {       
        return this.data[key];       
    };       
           
    /**    
     * 删除一个键值对    
     * @param {String} key    
     */      
    this.remove = function(key) {  
        var len = this.size();  
        for (var i = 0; i < len; i++) {  
            if (key == this.keys[i]) {  
                //先把键对应的值置空，再清除键值  
                //Array.splice(start, count, value)会直接修改原数组  
                this.data[key] = null;     
                this.keys.splice(i, 1);               
                break;  
            }  
        }  
            
    };       
           
    /**    
     * 遍历Map,执行处理函数    
     *     
     * @param {Function} 回调函数 function(key,value,index){..}    
     */      
    this.each = function(fn){       
        if(typeof fn != 'function'){       
            return;       
        }      
        this.sort();  
        var len = this.keys.length;       
        for(var i=0;i<len;i++){       
            var k = this.keys[i];       
            fn(k,this.data[k],i);       
        }       
    };       
           
    /**    
     * 获取键值数组(类似Java的entrySet())    
     * @return 键值对象{key,value}的数组    
     */      
    this.entrys = function() {    
        this.sort();  
          
        var keySize = this.size();  
        var entrySize = this.entrySize();     
        var valueSize  = 0;  
          
        var entrys = new Array(entrySize);    
        var nth = 0;  
          
        for (var i = 0; i < keySize; i++) {       
            valueSize = this.get(this.keys[i]).length;  
            for (var j = 0; j < valueSize; j++) {  
                entrys[nth] = [this.keys[i], this.get(this.keys[i])[j]];  
                nth++;  
            }  
        }       
        return entrys;       
    };       
           
    /**    
     * 判断Map是否为空    
     */      
    this.isEmpty = function() {       
        return this.keys.length == 0;       
    };       
  
    /** 
    * @usage   清空map 
    * @author  mw 
    * @date    2015年12月06日  星期日  09:20:54  
    * @param 
    * @return 
    * 
    */  
  
    this.empty = function() {  
        this.data.length = 0;  
        this.keys.length = 0;  
    }  
           
    /**    
     * 获取键数量    
     */      
    this.size = function(){       
        return this.keys.length;       
    };       
      
    /** 
    * @usage   获取键值对的数量，即每个键所对应的值的数量的总和 
    * @author  mw 
    * @date    2016年01月07日  星期四  09:57:46  
    * @param 
    * @return 
    * 
    */  
    this.entrySize = function() {  
        var keySize = this.size();  
        var totalSize = 0;  
          
        for (var i = 0; i < keySize; i++) {  
            totalSize += this.data[this.keys[i]].length;  
        }  
          
        return totalSize;  
    }  
      
      
    /**    
     * 重写toString     
     */      
    this.toString = function(){       
        var array = new Array();  
        array = this.entrys();  
        var len = array.length;  
        var s = '';  
        for (var i = 0; i < len; i++) {  
            s+='['+array[i][0].toFixed(0)+', '+array[i][1].toFixed(0)+'], ';  
        }  
        return s;  
    };        
      
}    


//PlotConfiguration类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
      
    for (var i = 0; i < 4; i++) {  
        for (var j = 0; j < 5;j++) {  
            config.setSector(4, 5, i+1, j+1);  
            config.axis2D(0, 0, 40);  
        }  
    }  
      
  
}  

/** 
* @usage   规格化绘图配置类 
* @author  mw 
* @date    2016年01月07日  星期四  08:41:47  
* @param   依赖封装了Html5 Canvas所有函数方法的全局变量var plot。 
* @return 
* 
*/  
function PlotConfiguration() {  
    //默认画布大小  
    this.canvasWidth = 600;  
    this.canvasHeight = 400;  
      
    //整个绘图任务开始时必须调用一次  
    //会读取并配置canvas画布  
    this.init = function() {  
        return plot.init();  
    }  
      
    /** 
    * @usage   设置用户倾向使用的个性配置 
    * @author  mw 
    * @date    2015年11月27日  星期五  08:41:41  
    * @param 
    * @return 
    * 
    */  
    this.setPreference = function() {                 
        //颜色  
        plot.setStrokeStyle("black")  
            .setFillStyle('white')  
            .fillRect(0, 0, this.canvasWidth, this.canvasHeight)  
            .setFillStyle('#666666')  
            //阴影  
            .setShadowColor('white')  
            .setShadowBlur(0)  
            .setShadowOffsetX(0)  
            .setShadowOffsetY(0)  
            //直线  
            .setLineCap("round")  
            .setLineJoin("round")  
            .setLineWidth(2)  
            .setMiterLimit(10)  
            //文字  
            .setFont("normal normal normal 20px arial")  
            .setTextAlign("left")  
            .setTextBaseline("alphabetic")  
            .setGlobalCompositeOperation("source-over")  
            .setGlobalAlpha(1.0)  
            .save();  
    }  
      
    //将画布分为row*col个区，并且相对于m*n的基准绘图    
    this.setSector = function(row, col, m, n) {  
        var width = this.canvasWidth;  
        var height = this.canvasHeight;  
        m = Math.abs(m);  
        n = Math.abs(n);  
          
        if (m<=row && n<=col) {  
            plot.setTransform(1,0,0,1,width*(n-0.5)/col,height*(m-0.5)/row);  
        }  
  
    }  
      
    /** 
    * @usage   绘制直角坐标系 
    * @author  mw 
    * @date    2015年11月28日  星期六  14:17:34  
    * @param 
    * @return 
    * 
    */  
  
    this.axis2D = function(x, y, r) {  
        plot.save();  
          
        plot.setFillStyle('black')  
            .setStrokeStyle('black');  
              
        plot.beginPath()  
            .moveTo(x-r,y)  
            .lineTo(x+r,y)  
            .closePath()  
            .stroke();  
              
        plot.beginPath()  
            .moveTo(x,y-r)  
            .lineTo(x,y+r)  
            .closePath()  
            .stroke();        
  
          
        var r0 = 10;  
          
        //x轴箭头  
        plot.beginPath()  
            .moveTo(x+r- r0*Math.cos(Math.PI/3), y-r0*Math.sin(Math.PI/3))  
            .lineTo(x+r+r0*Math.sin(Math.PI/3), y)  
            .lineTo(x+r -r0*Math.cos(Math.PI/3), y+r0*Math.sin(Math.PI/3))  
            .closePath()  
            .fill()  
          
        plot.fillText("X", x+r, y-10, 20);  
        plot.fillText("Y", x+10, y-r+10, 20);  
          
        //y轴箭头  
        plot.beginPath()  
            .moveTo(x+ r0*Math.sin(Math.PI/3), y-r+r0*Math.cos(Math.PI/3))  
            .lineTo(x, y-r-r0*Math.sin(Math.PI/3))  
            .lineTo(x-r0*Math.sin(Math.PI/3), y-r+r0*Math.cos(Math.PI/3))  
            .closePath()  
            .fill()  
          
        plot.restore();  
    }     
  
}  
//常见形状shape类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    config.axis2D(0, 0, 180);  
      
      
    shape.fillRect(0, 0, 100, 50);  
    shape.strokeRect(0, 0, 50, 100);  
    shape.strokeDraw(shape.nEdge(0, 0, 100, 5, -Math.PI/2));  
    shape.strokeDraw(shape.nEdge(0, 0, 100, 4, -Math.PI/2));  
    shape.fillDraw(shape.nEdge(-100, 0, 30, 6, -Math.PI/2));  
    shape.strokeDraw(shape.nStar(0, 0, 100, 7, -Math.PI/2));  
    shape.strokeEllipse(-100, 0, 100, 60, 0);  
    shape.fillEllipse(-100, 0, 80, 60, 0);  
      
  
}  


/** 
* @usage   常用形状类 
* @author  mw 
* @date    2015年11月29日  星期日  10:21:18  
* @param 
* @return 
* 
*/  
  
var shape = function Shape() {  
      
    //以给定点为中点的矩形  
    this.strokeRect = function(x, y, w, h) {  
        w = Math.abs(w);  
        h = Math.abs(h);  
        return plot.strokeRect(x-w/2, y-h/2, w, h);  
    }  
      
    //以给定点为中点的矩形  
    this.fillRect = function(x, y, w, h) {  
        w = Math.abs(w);  
        h = Math.abs(h);  
        return plot.fillRect(x-w/2, y-h/2, w, h);  
    }  
      
    this.fillDraw = function(array) {  
        plot.save();  
          
        if (array.length > 2 && array.length % 2 == 0) {    
            plot.beginPath()    
                .moveTo(array.shift(), array.shift());    
            while (array.length > 0) {    
                plot.lineTo(array.shift(), array.shift());    
            }    
            plot.closePath()    
                .fill();    
        }    
        plot.restore();  
      
    }  
      
    this.strokeDraw = function(array) {  
        plot.save();  
          
        if (array.length > 2 && array.length % 2 == 0) {    
            plot.beginPath()    
                .moveTo(array.shift(), array.shift());    
            while (array.length > 0) {    
                plot.lineTo(array.shift(), array.shift());    
            }    
            plot.closePath()    
                .stroke();    
        }    
          
        plot.restore();  
      
    }  
      
    /** 
    * @usage  以顶点递推方式绘制正多边形 #1 
    * @author  mw 
    * @date    2015年12月01日  星期二  09:42:33  
    * @param  (x, y)图形中心坐标，r 外接圆半径 edge 边数 
    * @return 
    * 
    */  
  
    this.nEdge = function(x, y, r, edge, angle0) {  
        var retArray = new Array();  
          
        var perAngle = Math.PI * 2 / edge;  
          
        var a = r * Math.sin(perAngle / 2);  
        var angle = -angle0;  
        var xOffset = r * Math.sin(perAngle / 2 - angle0);  
        var yOffset = r * Math.cos(perAngle / 2 - angle0);  
                      
                  
        var x1 = x-xOffset;  
        var y1 = y+yOffset;       
          
        for (var i=0; i < edge; i++) {             
            retArray.push(x1);  
            retArray.push(y1);  
            x1 = x1 + 2 * a * Math.cos(angle);  
            y1 = y1 + 2 * a * Math.sin(angle);  
            angle -= perAngle;  
              
        }  
      
        return retArray;  
      
    }  
      
    /** 
    * @usage   空心星形   #2 #201 #202 
    * @author  mw 
    * @date    2015年12月01日  星期二  10:06:13  
    * @param 
    * @return 
    * 
    */    
    this.nStar = function(x, y, r, edge, angle0, arg1, arg0) {  
        var retArray=new Array();  
          
        var perAngle = Math.PI * 2 / edge;  
          
          
        var r0 = arg0 ? arg0 * r : r / (2 * (1 + Math.cos(perAngle)));  
        var scale = arg1 ? arg1 : 0.5;  
        var angle = 0.5 * perAngle - angle0 * scale / 0.5;  
        var xOffset = x;  
        var yOffset = y;  
          
        for (var i =0; i< edge; i++) {  
            retArray.push(r0 * Math.cos(angle) + xOffset);  
            retArray.push(r0 * Math.sin(angle) + yOffset);  
            retArray.push(r * Math.cos(angle - scale * perAngle) + xOffset);  
            retArray.push(r * Math.sin(angle - scale * perAngle) + yOffset);  
              
            angle -= perAngle;  
        }     
  
        return retArray;  
  
    }  
      
    /** 
    * @usage   绘制圆形 
    * @author  mw 
    * @date    2015年11月27日  星期五  12:11:38  
    * @param 
    * @return 
    * 
    */  
    this.strokeCircle = function(x, y, r) {  
            plot.beginPath()  
            .arc(x, y, r, 0, 2*Math.PI, true)  
            .closePath()  
            .stroke();  
    }  
  
    this.fillCircle = function(x, y, r) {  
        plot.beginPath()  
            .arc(x, y, r, 0, 2*Math.PI, true)  
            .closePath()  
            .fill();  
    }  
      
    //绘制椭圆  
    this.strokeEllipse = function(x, y, a, b, rotate) {  
        //关键是bezierCurveTo中两个控制点的设置   
        //0.5和0.6是两个关键系数（在本函数中为试验而得）   
        var ox = 0.5 * a,   
        oy = 0.6 * b;   
        var rot = rotate ? -rotate : 0;  
        plot.save()  
            .rotate(rot)  
            .translate(x, y)  
            .beginPath()  
            //从椭圆纵轴下端开始逆时针方向绘制   
            .moveTo(0, b)  
            .bezierCurveTo(ox, b, a, oy, a, 0)  
            .bezierCurveTo(a, -oy, ox, -b, 0, -b)  
            .bezierCurveTo(-ox, -b, -a, -oy, -a, 0)  
            .bezierCurveTo(-a, oy, -ox, b, 0, b)  
            .closePath()  
            .stroke()  
            .restore();   
  
    }  
    //绘制椭圆  
    this.fillEllipse = function(x, y, a, b, rotate) {  
        //关键是bezierCurveTo中两个控制点的设置   
        //0.5和0.6是两个关键系数（在本函数中为试验而得）   
        var ox = 0.5 * a,   
        oy = 0.6 * b;   
        var rot = rotate ? -rotate : 0;  
        plot.save()  
            .rotate(rot)  
            .translate(x, y)  
            .beginPath()  
            //从椭圆纵轴下端开始逆时针方向绘制   
            .moveTo(0, b)  
            .bezierCurveTo(ox, b, a, oy, a, 0)  
            .bezierCurveTo(a, -oy, ox, -b, 0, -b)  
            .bezierCurveTo(-ox, -b, -a, -oy, -a, 0)  
            .bezierCurveTo(-a, oy, -ox, b, 0, b)  
            .closePath()  
            .fill()  
            .restore();   
  
    }  
      
    return {  
        fillRect:fillRect,  
        strokeRect:strokeRect,  
        fillCircle:fillCircle,  
        strokeCircle:strokeCircle,  
        strokeEllipse:strokeEllipse,  
        fillEllipse:fillEllipse,  
          
        strokeDraw:strokeDraw,  
        fillDraw:fillDraw,  
          
        nEdge:nEdge,  
        nStar:nStar  
      
    };  
}();  



//数字形状Digit类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    config.axis2D(0, 0, 180);  
      
      
    var digit = new Digit();  
      
    for (var i = 0; i < 10; i++) {  
        digit.number(i, -200 + i * 50, 0, 50);  
    }  
  
}  



/** 
* @usage   数字形状 
* @author  mw 
* @date    2015年12月01日  星期二  15:57:45  
* @param 
* @return 
* 
*/  
function Digit() {  
    //8  
    this.eight = function(x, y, r) {  
        plot.save();  
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
          
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
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
          
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
  
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
  
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
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
        plot.save();  
          
        var h = r * 0.4; //字半高  
        var w = h / 2.2; //字半宽  
        var w0 = r *0.1; //填充宽  
  
        //填充  
        //shape.fillRect(x, y, h, w0); //中横  
        shape.fillRect(x-w, y-w, w0, h); //上左竖  
        shape.fillRect(x+w, y-w, w0, h); //上右竖  
        shape.fillRect(x-w, y+w, w0, h); //下左竖  
        shape.fillRect(x+w, y+w, w0, h); //下右竖  
        shape.fillRect(x, y-h, h, w0); //上横  
        shape.fillRect(x, y+h, h, w0); //下横  
          
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
            case 0:case '0': this.zero(x, y, r); break;  
            case 1:case '1': this.one(x, y, r); break;  
            case 2:case '2': this.two(x,y,r); break;  
            case 3:case '3': this.three(x, y, r); break;  
            case 4:case '4': this.four(x,y,r);break;  
            case 5:case '5': this.five(x,y,r);break;  
            case 6:case '6': this.six(x,y,r); break;  
            case 7:case '7': this.seven(x,y,r); break;  
            case 8:case '8': this.eight(x,y,r); break;  
            case 9:case '9': this.nine(x,y,r); break;  
            default:break;  
          
        }  
    }  
}  

//MapModify类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    config.axis2D(0, 0, 180);  
      
      
    var map = new Map();  
      
    for (var i = -10; i < 10; i++) {  
        map.put(i, i * i);  
        map.put(i, -i * i);  
        map.put(i, 2 * i * i);  
    }  
      
    var modify = new MapModify();  
    modify.init(map, 100);  
    var mapModified = new Map();  
    mapModified = modify.normalization();  
      
    var s = mapModified.print();      
    document.body.appendChild(document.createTextNode(s));  
      
    mapModified = modify.revert();  
    s = mapModified.toString();   
    document.body.appendChild(document.createTextNode(s));  
      
    if (mapModified.hasElement(49, 7)) {  
        s = '有这个点';  
    }  
    else {  
        s = '没这个点';  
    }  
    document.body.appendChild(document.createTextNode(s));  
  
}  

/** 
* @usage   Map修改类, 暂时实现为在拷贝上改动，而不直接改原映射 
* @author  mw 
* @date    2016年01月07日  星期四  12:48:09  
* @param 
* @return 
* 
*/  
function MapModify() {  
    this.map = new Map();  
    this.range = 0;  
      
    this.init = function(map, range) {  
        this.map = map;  
        this.range = range;  
        if (this.range < 0) {  
            this.range = 100;  
        }  
    }  
      
    /** 
    * @usage  居中标准化 
    * @author  mw 
    * @date    2015年12月22日  星期二  10:09:09  
    * @param 
    * @return 
    * 
    */  
  
    this.normalization = function() {  
        this.map.sort();  
          
        var map = new Map();  
                  
        //map键数目  
        var size = this.map.size();  
        //键对应的值数组的长度  
        var len = 0;  
        //键取值范围  
        var xmin = this.map.keys[0];  
        var xmax = this.map.keys[size-1];  
        //键中值  
        var xCenter = ( xmin + xmax)/2;  
        //值取值范围  
        var ymin = Number.POSITIVE_INFINITY, ymax = Number.NEGATIVE_INFINITY;  
        //临时变量  
        var y1=0, y2=0;  
        for (var i = 0; i < size; i++) {  
            len = this.map.data[this.map.keys[i]].length;  
            y1 = this.map.data[this.map.keys[i]][0];  
            y2 = this.map.data[this.map.keys[i]][len-1];  
            if (y1 < ymin) ymin = y1;  
            if (y2 > ymax) ymax = y2;  
        }  
        var yCenter = (ymin+ymax) / 2;  
        var range = Math.max(xmax-xmin, ymax-ymin);  
          
        if (range != 0) {  
            var x, y;  
              
            for (var i = 0; i < size; i++) {  
                x =  (this.map.keys[i] - xCenter) / range * this.range;  
                len = this.map.data[this.map.keys[i]].length;  
                for (var j = 0; j < len; j++) {  
                    y = (this.map.data[this.map.keys[i]][j] - yCenter) / range * this.range;  
                    map.put(Math.round(x),Math.round(y));  
  
                }  
                  
            }  
            map.sort();  
        }  
                  
        return map;  
    }  
  
    /**  
    * @usage   转置Map(把Map的键和值的地位对换) 
    * @author  mw 
    * @date    2015年12月22日  星期二  11:31:41  
    * @param 
    * @return 
    * 
    */  
    this.revert = function(){  
        var map = new Map();  
          
        var size = this.map.size();  
        var len = 0;  
        var x= 0, y=0;  
          
        for (var i = 0; i < size; i++) {  
            x = this.map.keys[i];  
            len = this.map.get(x).length;  
            for (var j = 0; j < len; j++) {  
                y = this.map.get(x)[j];  
                map.put(y, x);  
            }  
        }  
        map.sort();  
        return map;   
    }  
      
    /** 
    * @usage   判断坐标点是否在map中 
    * @author  mw 
    * @date    2015年12月06日  星期日  07:50:37  
    * @param   点(x, y)， 映射集合map 
    * @return  true 或 false 
    * 
    */  
    this.hasElement = function(x, y) {  
        return this.map.hasElement(x, y);  
    }  
  
}  

//四则运算竖式类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    //config.axis2D(0, 0, 180);  
      
      
    var verticalExpression = new VerticalExpression();  
    verticalExpression.continuousAdd([153, 22, 24], 300, -150, 30);  
    verticalExpression.continuousSub([153, 22, 24], -100, -200, 30);  
    verticalExpression.add(153, 1, 100, -100, 20);  
    verticalExpression.sub(12, 153, -200, 0, 10);  
    verticalExpression.div(1537, 22, -120, -100, 20);  
  
}  


/** 
* @usage   四则运算竖式 
* @author  mw 
* @date    2016年01月07日  星期四  12:36:11  
* @param 
* @return 
* 
*/  
function VerticalExpression() {  
    /** 
    * @usage   把一个数字按照基准点右对齐绘制 
    * @author  mw 
    * @date    2016年01月01日  星期五  13:59:42  
    * @param 
    * @return 
    * 
    */  
    this.rightAlign = function(num, x, y, r) {  
        var s = num.toFixed(0);  
        var digitBit = s.length;  
        var digit = new Digit();  
        var xpos=0, ypos=0;  
          
        for (var i = digitBit-1; i > -1; i--) {  
            xpos = x - r * (digitBit - i);  
            ypos = y;  
            digit.number(s.charAt(i), xpos, ypos, r);  
        }  
    }  
  
    /** 
    * @usage   加法竖式 
    * @author  mw 
    * @date    2016年01月01日  星期五  13:59:42  
    * @param 
    * @return 
    * 
    */  
  
    /* 
        算术竖式 
        Vertical arithmetic 
        加数 
        addend 
        被加数 
        Augend 
        加号 
        Plus 
    */  
    this.add = function(augend, addend, x, y, r) {  
        plot.save()  
            .setFillStyle('black');  
              
        var result = addend + augend;  
        var xBeg = x ? x : 300, yBeg = y ? y :100, r = r ? r : 20;        
        var maxBit = Math.max(addend, augend).toFixed(0).length;  
          
        x = xBeg, y = yBeg + r;  
        var plusPos = x - (maxBit+2) * r;  
        this.rightAlign(augend, x, y, r);  
        y += 1.5 * r;  
        this.rightAlign(addend, x, y, r);  
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('+', plusPos, y+0.4*r, r);  
          
        y += r;  
        plot.beginPath()  
            .moveTo(plusPos - r, y)  
            .lineTo(x + r, y)  
            .closePath()  
            .stroke();  
              
        y += r;  
        this.rightAlign(result, x, y, r);  
        plot.restore();  
    }  
  
    /** 
    * @usage   连加竖式 
    * @author  mw 
    * @date    2016年01月07日  星期四  14:39:43  
    * @param 
    * @return 
    * 
    */    
    this.continuousAdd = function(taskArray, x, y, r) {  
        plot.save()  
            .setFillStyle('black');  
        var array = new Array();  
        array = taskArray;  
        var len = array.length;  
          
        if (len < 2) return;       
          
        var result = array[0] + array[1];  
          
        var xBeg = x ? x : 300, yBeg = y ? y :100, large = r ? r : 20;  
          
        var maxBit = Math.max(array[0], array[1]).toFixed(0).length;  
          
        x = xBeg, y = yBeg + r;  
          
        var plusPos = x - (maxBit+2) * r;  
        this.rightAlign(array[0], x, y, r);  
          
        y += 1.5 * r;  
          
        this.rightAlign(array[1], x, y, r);  
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('+', plusPos, y+0.4*r, r);  
          
        y += 0.8 * r;  
          
        plot.beginPath()  
            .moveTo(plusPos - r, y)  
            .lineTo(x + r, y)  
            .closePath()  
            .stroke();  
          
        y += 1.0 * r;  
          
        this.rightAlign(result, x, y, r);  
          
        if (array.length > 2) {  
            for (var i = 2; i < array.length; i++) {           
                maxBit = Math.max(result, array[i]).toFixed(0).length;            
                plusPos = x - (maxBit+2) * r;  
                result += array[i];   
                  
                y += 1.0 * r;  
                this.rightAlign(array[i], x, y, r);  
                plot.fillText('+', plusPos, y+0.4*r, r);  
                  
                y += 0.8 * r;  
              
                plot.beginPath()  
                    .moveTo(plusPos - 1 * r, y)  
                    .lineTo(x + 1 * r, y)  
                    .closePath()  
                    .stroke();  
          
                y += 1.0 * r;  
                  
                this.rightAlign(result, x, y, r);  
            }  
          
        }  
    }  
      
    /** 
    * @usage   减法竖式 
    * @author  mw 
    * @date    2016年01月01日  星期五  13:59:42  
    * @param 
    * @return 
    * 
    */  
    /* 
        被减数 
        Minuend 
        减数 
        subtrahend 
        减号 
        Minus sign 
    */  
    this.sub = function(minuend, subtrahend, x, y, r) {  
        plot.save()  
            .setFillStyle('black');  
              
        var result = minuend - subtrahend;  
        var xBeg = x ? x : 300, yBeg = y ? y :100, large = r ? r : 20;  
          
        var maxBit = Math.max(minuend, subtrahend).toFixed(0).length;  
        var minusPos = x - (maxBit+2) * r;  
        x = xBeg, y = yBeg+r;  
          
        this.rightAlign(minuend, x, y, r);  
          
        y += 1.5*r;  
        this.rightAlign(subtrahend, x, y, r);  
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('-', minusPos, y+0.2*r, r);  
          
        y += 0.8*r;  
        plot.beginPath()  
            .moveTo(minusPos -  r, y)  
            .lineTo(x + r, y)  
            .closePath()  
            .stroke();  
              
        y += 1.0*r;  
        this.rightAlign(result, x, y, r);  
        if (result < 0) {  
            plot.fillText('-', minusPos, y+0.2*r, r);  
        }  
        plot.restore();  
    }  
      
    /** 
    * @usage   连减竖式 
    * @author  mw 
    * @date    2016年01月07日  星期四  14:39:43  
    * @param 
    * @return 
    * 
    */    
    this.continuousSub = function(taskArray, x, y, r) {  
        plot.save()  
            .setFillStyle('black');  
        var array = new Array();  
        array = taskArray;  
        var len = array.length;  
          
        if (len < 2) return;       
          
        var result = array[0] - array[1];  
          
        var xBeg = x ? x : 300, yBeg = y ? y :100, large = r ? r : 20;  
          
        var maxBit = Math.max(array[0], array[1]).toFixed(0).length;  
          
        x = xBeg, y = yBeg + r;  
          
        var minusPos = x - (maxBit+2) * r;  
        this.rightAlign(array[0], x, y, r);  
          
        y += 1.5 * r;  
          
        this.rightAlign(array[1], x, y, r);  
        plot.setFont('normal normal normal '+r.toFixed(0)+'px'+ ' arial')  
            .fillText('-', minusPos, y+0.2*r, r);  
          
        y += 0.8 * r;  
          
        plot.beginPath()  
            .moveTo(minusPos - r, y)  
            .lineTo(x + r, y)  
            .closePath()  
            .stroke();  
          
        y += 1.0 * r;  
          
        this.rightAlign(result, x, y, r);  
        if (result < 0) {  
            plot.fillText('-', minusPos, y+0.2*r, r);  
        }  
          
        if (array.length > 2) {  
            for (var i = 2; i < array.length; i++) {           
                maxBit = Math.max(result, array[i]).toFixed(0).length;            
                minusPos = x - (maxBit+2) * r;  
                result -= array[i];   
                  
                y += 1.0 * r;  
                this.rightAlign(array[i], x, y, r);  
                plot.fillText('-', minusPos, y+0.4*r, r);  
                  
                y += 0.8 * r;  
              
                plot.beginPath()  
                    .moveTo(minusPos - 1 * r, y)  
                    .lineTo(x + 1 * r, y)  
                    .closePath()  
                    .stroke();  
          
                y += 1.0 * r;  
                  
                this.rightAlign(result, x, y, r);  
                if (result < 0) {  
                    plot.fillText('-', minusPos, y+0.2*r, r);  
                }  
            }  
          
        }  
    }  
      
    /** 
    * @usage   除法竖式 
    * @author  mw 
    * @date    2016年01月06日  星期三  11:05:09  
    * @param 
    * @return 
    * 
    */  
    this.div = function(dividend, divisor, xOffset, yOffset, r) {  
        plot.save();  
        /* 
            被除数 dividend 
            除数 divisor 
            商数 quotient 
            余数 remainder 
        */  
  
        var lenOfDividend =dividend.toFixed(0).length;  
        var lenOfDivisor = divisor.toFixed(0).length;  
        var quotient = Math.floor(dividend/divisor);  
        var lenOfQuotient = quotient.toFixed(0).length;  
        var remainder = dividend - quotient * divisor;  
          
        a = [divisor, dividend, quotient, remainder];  
          
        //除数位置  
        var x0 = xOffset + lenOfDivisor * r, y0= yOffset + 2 * r;  
        //被除数位置  
        var x1 = x0 + r + lenOfDividend * r, y1 = y0;  
        //商位置  
        var x2 = x1, y2 = yOffset;  
          
        plot.beginPath()  
            .bezierCurveTo(x0-r, y0+r, x0-0.5*r, y0+0.5*r, x0-0.2*r, y0-0.5*r, x0, y0-r)  
    /* 
            .moveTo(x0-r, y0+r) 
            .lineTo(x0, y0-1*r)*/  
            .closePath()  
            .stroke();  
        plot.beginPath()  
            .moveTo(x0, y0-1*r)  
            .lineTo(x2+r, y0-1*r)  
            .closePath()  
            .stroke();  
              
        this.rightAlign(a[0], x0, y0, r);  
        this.rightAlign(a[1], x1, y1, r);  
        this.rightAlign(a[2], x2, y2, r);  
          
  
        var tmp1, tmp2, tmp3, x, y;  
  
        //x, y的初始位置  
        x = x1 - (lenOfQuotient-1) *r, y = y1 + 1.5 * r;  
          
        if (lenOfQuotient > 1) {  
            for (var i = 0; i < lenOfQuotient; i++) {  
                if (i == 0) {  
                    //待减  
                    tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
                    //被减  
                    tmp2 = Math.floor(dividend / Math.pow(10, lenOfQuotient-i-2));  
                    //减得的差进入下一轮  
                    tmp3 = tmp2 - tmp1 * 10;  
                      
                    this.rightAlign(tmp1, x, y, r);  
                    plot.beginPath()  
                        .moveTo(x0, y+r)  
                        .lineTo(x1 +r, y+r)  
                        .closePath()  
                        .stroke();  
                    this.rightAlign(tmp3,x+r, y+2*r, r);  
                      
                    //位置递增  
                    x += r;  
                    y += 3.5*r;  
                }   
                else if (i < lenOfQuotient-1 ) {  
                    //中间轮数  
                    tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
  
                    tmp3 = tmp3*10 + (dividend.toFixed(0)[i+lenOfDividend-lenOfQuotient+1]-'0')-tmp1*10;  
                      
                    this.rightAlign(tmp1, x, y, r);  
                    plot.beginPath()  
                        .moveTo(x0, y+r)  
                        .lineTo(x1 +r, y+r)  
                        .closePath()  
                        .stroke();  
                    this.rightAlign(tmp3,x+r, y+2*r, r);  
                      
                    x += r;  
                    y += 3.5*r;  
                      
                }  
                else {  
                    //最后一轮  
                    tmp1 = (quotient.toFixed(0)[i] - '0')*divisor;  
                    this.rightAlign(tmp1, x, y, r);  
                    plot.beginPath()  
                        .moveTo(x0, y+r)  
                        .lineTo(x1 +r, y+r)  
                        .closePath()  
                        .stroke();  
                          
                    plot.beginPath()  
                        .moveTo(x0, y+r)  
                        .lineTo(x1 +r, y+r)  
                        .closePath()  
                        .stroke();  
                    this.rightAlign(a[3],x, y+2*r, r);  
                }  
            }  
        }  
        else {  
            //最后一轮  
            tmp1 = quotient*divisor;  
            this.rightAlign(tmp1, x, y, r);  
            plot.moveTo(x0, y+r)  
                .lineTo(x1 +r, y+r)  
                .stroke();  
                  
            plot.beginPath()  
                .moveTo(x0, y+r)  
                .lineTo(x1 +r, y+r)  
                .closePath()  
                .stroke();  
            this.rightAlign(a[3],x, y+2*r, r);  
        }  
  
    }  
  
  
}  
//尺子类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    //config.axis2D(0, 0, 180);  
      
      
    var ruler = new Ruler();  
    ruler.ruler(15, -100, 0, 0);  
    ruler.angle30(0, 50, Math.PI/2);  
    ruler.angle60(-50, 50, Math.PI/2);  
    ruler.angle45(0,-50, Math.PI/2);  
}  

/** 
* @usage   尺子类，含直尺，三角板等 
* @author  mw 
* @date    2016年01月07日  星期四  15:00:04  
* @param 
* @return 
* 
*/  
function Ruler() {  
    this.R = 100;  
    /** 
    * @usage   厘米和像素对照的尺子 
    * @author  mw 
    * @date    2016年01月03日  星期日  08:59:22  
    * @param 
    * @return 
    * 
    */        
    //一把任意长的尺子  
    this.ruler = function(rulerLong, xOffset, yOffset, rotate) {  
        //在分辨率1024*768, dpi = 96时，每厘米像素比率为37.8。  
        var pxPerCm = 37.8;  
        var cm10th =pxPerCm / 10;  
        var cm5th = pxPerCm / 5;  
        var cm2th = pxPerCm / 2;  
          
        plot.save();  
          
        plot.translate(xOffset, yOffset)  
            .rotate(-rotate);  
              
        rulerLong = rulerLong ? rulerLong : 10;  
        var L = pxPerCm * rulerLong;  
        for (var i = 0; i <L+10; i++) {  
            if (i % 100 == 0) {  
                plot.beginPath()  
                    .moveTo(i, 2 * pxPerCm - cm2th)  
                    .lineTo(i, 2 * pxPerCm-cm5th)  
                    .closePath()  
                    .stroke();  
              
            }  
            else if (i % 50 == 0) {  
                plot.beginPath()  
                    .moveTo(i, 2 * pxPerCm - 2 * cm5th)  
                    .lineTo(i, 2 * pxPerCm-cm5th)  
                    .closePath()  
                    .stroke();  
              
            }  
            else if (i % 10 == 0) {  
                plot.beginPath()  
                    .moveTo(i, 2 * pxPerCm - cm5th - cm10th)  
                    .lineTo(i, 2 * pxPerCm-cm5th)  
                    .closePath()  
                    .stroke();  
            }  
              
            if (i % 100 == 0) {  
                if (i == 0) {  
                    plot.fillText(i.toFixed(0), i-cm10th, 2 * pxPerCm - cm2th, 20);  
                }  
                else {  
                    plot.fillText(i.toFixed(0), i-3*cm10th, 2 * pxPerCm - cm2th, 20);  
                }  
              
            }  
        }  
                      
        var x=0, y=0, count = 0;  
        //cm刻度  
        plot.setStrokeStyle('red');  
        while (x <= L+10) {  
            plot.beginPath()  
                .moveTo(x, 0)  
                .lineTo(x, cm5th*2)  
                .closePath()  
                .stroke();  
                  
            if (count < 10) {  
                plot.fillText(count.toFixed(0), x-cm10th, 2 * cm2th, 20);  
            }  
            else {  
                plot.fillText(count.toFixed(0), x-cm5th, 2 * cm2th, 20);  
            }             
            x += pxPerCm;  
            count++;  
          
        }  
          
        //半厘米刻度  
        x=0, y=0, count = 0;  
        plot.setStrokeStyle('#CC0000');  
        while (x <= L) {  
                  
            if (count % 2 != 0) {  
            plot.beginPath()  
                .moveTo(x, 0)  
                .lineTo(x, cm5th)  
                .closePath()  
                .stroke();  
            }  
            x += cm2th;  
            count++;  
          
        }  
          
        //0.1cm刻度  
        plot.setStrokeStyle('#880000');  
        x=0, y=0, count = 0;  
          
        while (x <= L) {  
            if (count % 10 != 0 && count % 10 != 5) {  
                plot.beginPath()  
                    .moveTo(x, 0)  
                    .lineTo(x, cm10th)  
                    .closePath()  
                    .stroke();  
            }  
            x += cm10th;  
            count++;  
          
        }  
          
        x = -2 * cm5th, y=-cm5th;  
      
        plot.setLineWidth(4)  
            .setStrokeStyle('#FF8844');  
        plot.strokeRect(x, y, L + 4*cm5th, 2 * pxPerCm+cm5th);  
          
        plot.restore();  
      
    }  
  
/** 
* @usage   三角板 
* @author  mw 
* @date    2016年01月02日  星期六  09:02:38  
* @param 
* @return 
* 
*/  
/* 
三角板 
triangle ;set square; 
 
*/  
    this.angle30 = function(xOffset, yOffset, rotate) {  
        var edge30 = -this.R * Math.tan(Math.PI/6);       
        var edge60 = this.R;  
  
        var offset = this.R * 0.12;  
        var edge30_2 = -((this.R-offset) * Math.tan(Math.PI/6)-offset*(1/Math.cos(Math.PI/6)+1));  
        var edge60_2 = (this.R-offset)-offset*(1/Math.tan(Math.PI/6)+1/Math.sin(Math.PI/6));  
          
          
        //以直角点为坐标原点, 60度角所对边为x轴，30度角所对边为y轴  
        plot.save()  
            .setLineWidth(5)  
            .setStrokeStyle('blue');  
          
  
        plot.translate(xOffset, -yOffset)  
            .rotate(-rotate);  
              
        plot.beginPath()  
            .moveTo(0, 0)  
            .lineTo(edge60, 0)  
            .lineTo(0, edge30)  
            .closePath()  
            .stroke();  
          
          
        plot.beginPath()  
            .moveTo(offset, -offset)  
            .lineTo(offset + edge60_2, -offset)  
            .lineTo(offset, -offset + edge30_2)  
            .closePath()  
            .stroke();  
              
        plot.restore();  
    }  
      
    this.angle60 = function(xOffset, yOffset, rotate) {  
        var edge30 = this.R * Math.tan(Math.PI/6);        
        var edge60 = -this.R;  
  
        var offset = this.R * 0.12;  
        var edge30_2 = (this.R-offset) * Math.tan(Math.PI/6)-offset*(1/Math.cos(Math.PI/6)+1);  
        var edge60_2 = -((this.R-offset)-offset*(1/Math.tan(Math.PI/6)+1/Math.sin(Math.PI/6)));  
          
          
        //以直角点为坐标原点, 60度角所对边为x轴，30度角所对边为y轴  
        plot.save()  
            .setLineWidth(5)  
            .setStrokeStyle('green');  
          
  
        plot.translate(xOffset, -yOffset)  
            .rotate(-rotate);  
              
        plot.beginPath()  
            .moveTo(0, 0)  
            .lineTo(edge30, 0)  
            .lineTo(0, edge60)  
            .closePath()  
            .stroke();  
          
          
        plot.beginPath()  
            .moveTo(offset, -offset)  
            .lineTo(offset + edge30_2, -offset)  
            .lineTo(offset, -offset + edge60_2)  
            .closePath()  
            .stroke();  
              
        plot.restore();  
    }  
      
    this.angle45 = function(xOffset, yOffset, rotate) {  
        var edge45 = this.R * Math.sin(Math.PI/4);    
        var offset = this.R * 0.1;  
                  
        //以直角点为坐标原点, 45度角所对边为x轴和y轴  
        plot.save()  
            .setLineWidth(5)  
            .setStrokeStyle('red');  
  
        plot.translate(xOffset, -yOffset)  
            .rotate(-rotate);     
              
        plot.beginPath()  
            .moveTo(0, 0)  
            .lineTo(edge45, 0)  
            .lineTo(0, -edge45)  
            .closePath()  
            .stroke();  
          
        plot.beginPath()  
            .arc(edge45*0.42, -edge45 *0.42, edge45*0.3, Math.PI/4, Math.PI/4+Math.PI)  
            .closePath()  
            .stroke();  
          
        plot.restore();  
    }  
      
}  
//钟表类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    config.axis2D(0, 0, 180);  
      
    var clock = new Clock();  
    clock.drawClock(100, 100, 100, 10, 30);  
    clock.clock(100, 10, 30);  
}  



/** 
* @usage  钟表类 
* @author  mw 
* @date    2016年01月07日  星期四  15:09:16  
* @param 
* @return 
* 
*/  
  
function Clock() {  
    /** 
    * @usage   绘制钟表 
    * @author  mw 
    * @date    2015年12月19日  星期六  14:04:24  
    * @param 
    * @return 
    * 
    */  
    this.drawClock = function(xOff, yOff, r, hour, minute) {  
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
    this.clock = function(r, hour, minute) {  
        return this.drawClock(0, 0, r, hour, minute);  
    }  
}  


//七巧板类功能测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    config.axis2D(0, 0, 180);  
      
    var neves = new Neves();  
    neves.total();  
    for (var i = 0; i < 7; i++) {  
        neves.item(1+i, -100+50*i, -100+40*i, Math.PI/10 * i);  
    }  
  
}  


/** 
* @usage   制作七巧板 
* @author  mw 
* @date    2015年12月21日  星期一  11:08:14  
* @param 
* @return 
* 
*/  
/* 
 单词：Neves-七巧板 rightTriangle-直角三角形 
        青色cyan 紫色violet 平行四边形parallelogram 
*/  
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


//各类功能综合测试  
function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
    config.axis2D(0, 0, 180);  
      
      
    var neves = new Neves();  
    neves.total();  
    for (var i = 0; i < 7; i++) {  
        neves.item(1+i, -100+50*i, -100+40*i, Math.PI/10 * i);  
    }  
      
    var ruler = new Ruler();  
    ruler.ruler(15, 0, 0, 0);  
      
    var clock = new Clock();  
    clock.clock(100, 15, 24);  
      
    var exp = new VerticalExpression();  
    exp.div(123450, 888, -280, -100, 20);  
      
    shape.fillDraw(shape.nStar(-200, -150, 100, 7, Math.PI/3));  
  
}  
