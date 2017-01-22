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


function myplot() {  
    // plot.init();  
    setPreference();  
      
    //setSector(1,1,1,1);  
    //axis(0, 0, 180);  
      
    //数据录入  数据来源？
    var map = new Map();  
    for (var i = 0; i < $picDataArray.length; i++) {  
        map.put($picDataArray[i][0], $picDataArray[i][1]);  
    }  
      
    map.sort();   
      
    //不在循环中创建，但为了在循环中调用  
    var x = 0;  
    var y = 0;  
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
          
    //轮廓勾勒图  
    //y为最小值的上半圈       
      
    //第round圈  
    var round = 1;  
    //是否起点  
    var begin = 0;  
      
    plot.translate(200, 0);  
      
      
    for (; round<2; round++) {  
        for (var i = 0; i < map.size(); i++) {             
            if (lenArray[i] > 2 * (round-1) + 1) {  
                //起点未定  
                if (!begin) {  
                    plot.moveTo(map.keys[i], map.get(map.keys[i])[round-1]);  
                    begin = 1;  
                } else {  
                    x = map.keys[i];                          
                    y = map.get(x)[round-1];      
                    plot.lineTo(x, y);  
                }  
            }                 
        }  
        plot.stroke();  
        begin = 0;  
                  
        //y为最大值的下半圈  
        for (var i = 0; i < map.size(); i++) {             
            if (lenArray[i] > 2 * (round-1) + 1) {  
                if (!begin) {  
                    plot.moveTo(map.keys[i], map.get(map.keys[i])[lenArray[i]-round]);  
                    begin = 1;  
                } else {  
                    x = map.keys[i];                          
                    y = map.get(x)[lenArray[i]-round];        
                    plot.lineTo(x, y);  
                }  
            }                 
        }  
        plot.stroke();    
        begin = 0;  
    }  
      
      
    plot.translate(200, 0);  
      
    for (; round<10; round++) {  
        for (var i = 0; i < map.size(); i++) {             
            if (lenArray[i] > 2 * (round-1) + 1) {  
                //起点未定  
                if (!begin) {  
                    plot.moveTo(map.keys[i], map.get(map.keys[i])[round-1]);  
                    begin = 1;  
                } else {  
                    x = map.keys[i];                          
                    y = map.get(x)[round-1];      
                    plot.lineTo(x, y);  
                }  
            }                 
        }  
        plot.stroke();  
        begin = 0;  
          
          
        //y为最大值的下半圈  
        for (var i = 0; i < map.size(); i++) {             
            if (lenArray[i] > 2 * (round-1) + 1) {  
                if (!begin) {  
                    plot.moveTo(map.keys[i], map.get(map.keys[i])[lenArray[i]-round]);  
                    begin = 1;  
                } else {  
                    x = map.keys[i];                          
                    y = map.get(x)[lenArray[i]-round];        
                    plot.lineTo(x, y);  
                }  
            }                 
        }  
        plot.stroke();    
        begin = 0;  
    }  
}  
myplot();