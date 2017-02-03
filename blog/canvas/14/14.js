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
    this.data = new Object();       
           
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
        //this.data[key] = value;  
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
          
        for (var i = 0; i < this.size(); i++) {  
            s += "[key]" + this.keys[i]+ "[/key]";  
              
            for ( var j =0 ; j < this.data[i].length; j++) {  
                s += this.data[i][j] + ", ";  
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
        this.keys.remove(key);       
        this.data[key] = null;       
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
        var len = this.keys.length;       
        var entrys = new Array(len);       
        for (var i = 0; i < len; i++) {       
            entrys[i] = {       
                key : this.keys[i],       
                value : this.data[i]       
            };       
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
     * 获取键值对数量    
     */      
    this.size = function(){       
        return this.keys.length;       
    };       
           
    /**    
     * 重写toString     
     */      
    this.toString = function(){       
        var s = "{";       
        for(var i=0;i<this.keys.length;i++,s+=','){       
            var k = this.keys[i];       
            s += k+"=";  
            for (var j=0; j<this.data[k].length;j++,s+=',') {  
                s+=this.data[k]/*[j]*/;     
            }  
        }       
        s+="}";       
        return s;       
    };     
      
      
}  
/** 
* @usage   获取轮廓 
* @author  mw 
* @date    2015年12月06日  星期日  07:50:37  
* @param 
* @return 
* 
*/  
function getSketch() {  
    // plot.init();  
    setPreference();  
      
    setSector(4,1,1,1);  
    //axis(0, 0, 180);  
      
    //数据录入  
    var map = new Map();  
    for (var i = 0; i < $picDataArray.length; i++) {  
        map.put($picDataArray[i][0], $picDataArray[i][1]);  
    }  
      
    map.sort();   
      
    //存放经过排序的map的每个x坐标对应的y坐标的数量  
    var lenArray = new Array();  
    var len = 0;  
      
    //数据打印校验          
    var s = "********";  
    for (var i = 0; i < map.size(); i++) {  
        x = map.keys[i];      
        len = map.get(x).length;  
        lenArray.push(len);  
        s += "<x =" + x + ", len = "+len+"> ";                          
    }  
      
    var pointInfo = map.print() + s;  
    var pointInfoNode = document.createTextNode(pointInfo);  
    document.body.appendChild(pointInfoNode);  
          
    //确保lenArray已经取得  
    //然后继续  
    //点图  
    for (var i = 0; i < map.size(); i++) {  
        x = map.keys[i];  
        //每个x对应的y序列  
          
        for (j = 0; j < lenArray[i]; j++) {  
            y = map.get(x)[j];  
            fillCircle(x, y, 1);  
        }  
    }  
          
    plot.translate(-200, 150);  
      
    var path = new Path();  
    path.setMap(map);  
    path.search();  
      
    //window.alert(pointInMap(41, 72, path.map));  
    var pathArr = new Array();  
    pathArr = path.pathArray;  
    var pointInfo2 = "**************" + pathArr.length + "********";  
    for (var i = 0; i < pathArr.length; i++) {  
        pointInfo2 += "[" + pathArr[i] + "], ";  
    }  
    var pointInfoNode2 = document.createTextNode(pointInfo2);  
    document.body.appendChild(pointInfoNode2);  
          
    for (var i=0; i < pathArr.length-1; i++) {  
        plot.moveTo(pathArr[i][0] * 1.2, pathArr[i][1]*1.2)           
            .lineTo(pathArr[i][2] * 1.2, pathArr[i][3]*1.2);  
    }  
    plot.stroke();  
}  


/** 
* @usage   判断坐标点是否在map中 
* @author  mw 
* @date    2015年12月06日  星期日  07:50:37  
* @param   点(x, y)， 映射集合map 
* @return  true 或 false 
* 
*/  
function pointInMap(x, y, map, range) {  
    //传入的映射集map一般是经过排序的，并且去除了重复元素。  
    var pmap = new Map();  
    pmap = map;  
    range = range ? range : 5;  
  
    for (var i = 0; i <pmap.size() ; i++) {  
        if (x == pmap.keys[i]) {                      
            var len = pmap.get(x).length;  
            for (var j = 0; j < len; j++) {  
                if (Math.abs(y - pmap.get(x)[j]) <= range) {  
                    return true;  
                }  
                  
            }  
        }         
    }  
      
    return false;  
}  

/** 
* @usage   判断坐标点是否在map中 
* @author  mw 
* @date    2015年12月06日  星期日  07:50:37  
* @param   点(x, y)， 映射集合map 
* @return  true 或 false 
* 
*/  
function pointInMap(x, y, map, range) {  
    //传入的映射集map一般是经过排序的，并且去除了重复元素。  
    var pmap = new Map();  
    pmap = map;  
    range = range ? range : 5;  
  
    for (var i = 0; i <pmap.size() ; i++) {  
        if (x == pmap.keys[i]) {                      
            var len = pmap.get(x).length;  
            for (var j = 0; j < len; j++) {  
                if (Math.abs(y - pmap.get(x)[j]) <= range) {  
                    return true;  
                }  
                  
            }  
        }         
    }  
      
    return false;  
}  
/** 
* @usage   路径搜寻 
* @author  mw 
* @date    2015年12月06日  星期日  08:01:32  
* @param   以(x, y)点为起点， 在map中进行递归的路径搜寻。 
*          搜寻条件是(x1, y1)必须满足x1<-[x, x+1], y1<-[y-1, y+1]并且在map中 
* @return  
* 
*/  
  
//Path类  
function Path() {  
    this.map = new Map();  
    //存放已经处理过的点  
    this.dealed = new Map();  
    this.pathArray = new Array();  
    //附带找出图形中心  
    this.xCenter = 0;  
    this.yCenter = 0;  
    this.yMax = -10000;  
    this.yMin = 10000;  
    //递归深度，代表连续线段的长度  
    this.level = 10;  
    this.LEVEL = 10;  
    //路径上的控制点  
    this.xCur = 0;  
    this.ycur = 0;  
    this.xOri = 0;  
    this.yOri = 0;  
      
    this.setMap = function(map) {  
        this.map = map;  
        this.pathArray.length = 0;  
    }  
      
    this.pathSearch = function(x, y, direct) {        
        //路径向左向下延伸，刚好是一半的圆周方向  
          
        switch (direct) {  
        case 1:  
            if (pointInMap(x+1, y-1, this.map)) {  
                this.level--;  
                this.xCur = x + 1;  
                this.yCur = y-1;  
  
                this.pathSearch(x+1, y-1, 1);//               
            }  
            break;  
          
        case 2:  
            if (pointInMap(x+1, y, this.map)) {  
                this.level--;  
                this.xCur = x + 1;  
                this.yCur = y;    
                this.pathSearch(x+1, y, 2); //        
                  
            }  
        break;  
          
        case 3:  
        if (pointInMap(x+1, y+1, this.map)) {  
            this.level--;  
            this.xCur = x + 1;  
            this.yCur = y+1;  
              
            this.pathSearch(x+1, y+1, 3);  
              
        }  
        break;  
          
        case 4:  
        if (pointInMap(x, y+1, this.map)) {  
            this.level--;  
            this.xCur = x ;  
            this.yCur = y-1;  
            this.pathSearch(x, y+1, 4);  
  
        }  
        break;  
          
        default: break;  
        }  
          
          
        if (this.level < 0) {  
            //此处简单处理，尽量减少处理量，增加处理速度，所以路径不会很全  
            //要是想要全路径需要把dealed升级为map，记录并对比方向量  
              
            var k = (this.yCur - this.yOri) / (this.xCur - this.xOri) ;  
            var n = Math.abs(this.xCur - this.xOri);  
            for (var i = 0; i < n-1; i++) {  
                this.dealed.put(this.xOri + i, this.yOri + k * i);  
            }  
              
            //增加处理量的方法  
            //this.dealed.put(this.xOri, this.yOri);  
              
              
            this.xOri = this.xOri - this.xCenter;  
            this.yOri = this.yOri - this.yCenter;  
            this.xCur = this.xCur - this.xCenter;  
            this.yCur = this.yCur - this.yCenter;  
            this.pathArray.push([this.xOri-1, this.yOri-1, this.xCur+1, this.yCur+1]);  
            this.level = this.LEVEL;  
        }  
          
          
        //最后必然有某点不具有下一个相邻点而结束递归。          
        return true;  
          
    };  
      
    this.search = function () {  
        if (!this.dealed.isEmpty()) {  
            this.dealed.empty();  
        }  
          
        if (0 == this.map.size()) return false;  
          
        this.map.sort();  
          
        var x0 = 0;  
        var y0 = 0;  
          
        var size = this.map.size();  
        var len = 0;  
        this.xCenter = (this.map.keys[0] + this.map.keys[size-1]) / 2;  
          
        //求y中心点  
        for (var i = 0; i < size; i++) {  
            x0 = this.map.keys[i];  
            len = this.map.get(x0).length;  
              
            for (var j =0; j < len; j++) {  
                y0 = this.map.get(x0)[j];  
                  
                if (y0 > this.yMax) this.yMax = y0;  
                if (y0 < this.yMin) this.yMin = y0;  
                  
  
            }  
        }  
          
        this.yCenter = (this.yMax + this.yMin) / 2;  
          
    //  window.alert(this.xCenter + ", " + this.yCenter);  
          
        //路径递归的耗时操作，相当耗时  
        for (var i = 0; i < size; i++) {  
            x0 = this.map.keys[i];  
            len = this.map.get(x0).length;  
              
            for (var j =0; j < len; j++) {  
                y0 = this.map.get(x0)[j];  
                  
                //整个处理过程x从小到大，y从小到大处理，但路径上的点会超前循环，  
                //所以要进行判断来提高效率  
                if (!pointInMap(x0, y0, this.dealed)) {  
                    //此处每个点都视作起点，看它们有没有足够长的路径  
                    this.level = this.LEVEL;  
                    this.xCur = x0;  
                    this.yCur = y0;  
                    this.xOri = x0;  
                    this.yOri = y0;  
                    //向四个方向找路径  
                    this.pathSearch(x0, y0, 1);  
                    this.level = this.LEVEL;  
                    this.xCur = x0;  
                    this.yCur = y0;  
                    this.xOri = x0;  
                    this.yOri = y0;                   
                    this.pathSearch(x0, y0, 2);  
                    this.level = this.LEVEL;  
                    this.xCur = x0;  
                    this.yCur = y0;  
                    this.xOri = x0;  
                    this.yOri = y0;  
                    this.pathSearch(x0, y0, 3);  
                    this.level = this.LEVEL;  
                    this.xCur = x0;  
                    this.yCur = y0;  
                    this.xOri = x0;  
                    this.yOri = y0;  
                    this.pathSearch(x0, y0, 4);  
                }  
            }  
        }                     
                  
        window.alert("finished");         
    };  
}  