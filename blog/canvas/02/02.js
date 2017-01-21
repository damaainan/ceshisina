/** 
* @usage   查看数据的表格 
* @author  mw 
* @date    2015年11月28日  星期六  13:00:45  
* @param 
* @return 
* 
*/  
    function mytable() {  
        // var table = $$("table");  
        var table = document.getElementsByTagName("table")[0];  
        /**
         * node
         */
        // console.log(table);
        // for(var ke in table){
        // 	console.log(table[ke]);
        // }
          
        //找<tbody>  
        var node = table.firstChild;  
        // console.log(node);
        
  
        while (null != node) {  
        /* 
            想知道都有哪些子节点，用这个 
                var text = document.createTextNode(node.nodeName); 
                document.body.appendChild(text); 
        */  
                  
            if ("TBODY" == node.nodeName)   
                break;            
  
            node = node.nextSibling;  
        }  
          
        //生成映射数据  
        var xArray = genData(10);  
        var yArray = linearMap(xArray);  
  
        //单元格插入  
        for (var i = 0; i<10; i++) {  
            //插入<tr>            
            var tr = document.createElement("tr")             
              
            //插入<td> x  
            var td = document.createElement("td");  
            var x = document.createTextNode(xArray[i].toFixed(3));  
            td.appendChild(x);  
            tr.appendChild(td);  
              
            //插入<td> y  
            var td = document.createElement("td");  
            var y = document.createTextNode(yArray[i].toFixed(3));  
            td.appendChild(y);  
            tr.appendChild(td);  
  
              
            node.appendChild(tr);  
        }         
    }  

mytable();
    /** 
* @usage   生成X轴测试随机数据 
* @author  mw 
* @date    2015年11月28日  星期六  12:10:34  
* @param 
* @return 
* 
*/    
    function genData(n) {  
        var xArray = new Array();  
          
        //生成n个随机数  
        for (var i = 0; i < n; i++) {  
            var x = Math.random() * 200 - 100;  
            xArray.push(x); //值在-100到100之间  
        }  
          
        return xArray;  
      
    }  


    /** 
* @usage   直线函数映射 
* @author  mw 
* @date    2015年11月28日  星期六  12:11:59  
* @param 
* @return 
* 
*/  
    function linearMap(xArray) {  
        var yArray = new Array();  
        //直线方程y = kx + b  
        var k = -1;  
        var b = 0;  
        for (var i = 0; i < xArray.length; i++) {  
            var y = k * xArray[i] + b;  
            yArray.push(y);  
        }  
          
        return yArray;  
    }  
      
      
/** 
* @usage   正弦函数映射 
* @author  mw 
* @date    2015年11月28日  星期六  14:10:11  
* @param 
* @return 
* 
*/            
  
    function sinMap(xArray) {  
        var yArray = new Array();  
        //方程y = sin(x)  
  
        for (var i = 0; i < xArray.length; i++) {  
            var y = 100* Math.sin(xArray[i]/100*Math.PI*2);  
            yArray.push(y);  
        }  
          
        return yArray;  
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
        this.data[key].push(value);       
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
                s+=this.data[k][j];     
            }  
        }       
        s+="}";       
        return s;       
    };     
}    



    /** 
* @usage   绘制直角坐标系 
* @author  mw 
* @date    2015年11月28日  星期六  14:17:34  
* @param 
* @return 
* 
*/  
  
    function axis(x, y, r) {  
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
          
        plot.setFillStyle('black');  
          
        var r0 = 10;  
          
        //x轴箭头  
        plot.beginPath()  
            .moveTo(x+r- r0*Math.cos(Math.PI/3), y-r0*Math.sin(Math.PI/3))  
            .lineTo(x+r+r0*Math.sin(Math.PI/3), y)  
            .lineTo(x+r -r0*Math.cos(Math.PI/3), y+r0*Math.sin(Math.PI/3))  
            .closePath()  
            .fill()  
          
        //y轴箭头  
        plot.beginPath()  
            .moveTo(x+ r0*Math.sin(Math.PI/3), y-r+r0*Math.cos(Math.PI/3))  
            .lineTo(x, y-r-r0*Math.sin(Math.PI/3))  
            .lineTo(x-r0*Math.sin(Math.PI/3), y-r+r0*Math.cos(Math.PI/3))  
            .closePath()  
            .fill()  
          
        plot.setFillStyle('#666666');  
}  

function fillCircle(x, y, r) {  
    plot.beginPath()  
        .arc(x, y, r, 0, 2*Math.PI, true)  
        .closePath()  
        .fill();  
}  



    function myplot() {  
        setPreference();              
  
        //生成映射数据  
        var xArray = genData(100);  
        xArray = xArray.sort();  
        var yArray = linearMap(xArray);  
          
        var xArray2 = genData(100);  
        var yArray2= sinMap(xArray2);  
          
        axis(300,200,190);  
          
        //绘制  
        plot.setTransform(1.5,0,0,1.5, 300,200);          
          
        /* 
        //第一种取数据法 
        for (var i=0; i<xArray.length; i++) { 
            fillCircle(xArray[i],-yArray[i],2); 
        } 
        */  
  
        //第二种取数据法  
        var xyMap = new Map();  
        //第一个函数  
        for (var i = 0; i < xArray.length; i++) {  
            xyMap.put(xArray[i], yArray[i]);  
        }  
        //第二个函数  
        for (var i = 0; i < xArray2.length; i++) {  
            xyMap.put(xArray2[i], yArray2[i]);  
        }  
          
        //绘出映射  
        if (!xyMap.isEmpty()) {  
              
            for (var i=0; i <xyMap.size(); i++) {  
                var x = xyMap.keys[i];  
                var y = xyMap.get(x);  
                  
                for (var j=0;j<y.length;j++) {  
                    fillCircle(x,y[j],2);  
                }     
            }  
        }  
          
        /* 
        //数据检查 
        var text = document.createTextNode(xyMap.toString()); 
        document.body.appendChild(text); 
        */  
  
    }  

    myplot();