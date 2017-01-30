/** 
* @usage   分块治图取轮廓 
* @author  mw 
* @date    2015年12月20日  星期日  10:46:21  
* @param 
* @return 
* 
*/  
function Nexus() {  
    //方格大小范围，比如2*2, 10*10, 100*100个象素  
    this.range = 100;  
    //方格序数  
    this.xth = 0;  
    this.yth = 0;  
      
    //方格开始和结束的x,y象素点坐标  
    this.xBeg = 0;  
    this.xEnd = 0;  
    this.yBeg = 0;  
    this.yEnd = 0;  
    //候选点的x, y极值  
    this.xMax = 0;  
    this.xMin = 0;  
    this.yMax = 0;  
    this.yMin = 0;  
    //候选点个数和比例  
    this.count = 0;  
    this.percent = 0;  
    //候选点的x, y总值  
    this.xTotal = 0;  
    this.yTotal = 0;  
    //候选点族的中心  
    this.xCenter = 0;  
    this.yCenter = 0;  
    //候选点族的半径区域  
    this.r = 0;  
  
    //需要传入参数xth, yth, range  
    this.init = function(xth, yth, range) {  
        this.xth = xth;  
        this.yth = yth;  
        this.range = range;  
        //方格开始和结束的x,y象素点坐标  
        this.xBeg = 0 + this.xth * this.range;  
        this.xEnd = 0 + (this.xth+1) * this.range;  
        this.yBeg = 0 + this.yth * this.range;  
        this.yEnd = 0 + (this.yth+1) * this.range;  
    };  
      
    //传入7个参数  
    this.calc = function(count, xTotal, yTotal, xMax, yMax, xMin, yMin) {  
        this.count = count;  
        this.xTotal = xTotal;  
        this.yTotal = yTotal;  
        this.xMax = xMax;  
        this.xMin = xMin;  
        this.yMax = yMax;  
        this.yMin = yMin;  
          
        this.percent = this.count / (this.range * this.range);  
  
        //候选点族的中心  
        this.xCenter = this.count==0 ? (this.xBeg+this.xEnd)/2 : this.xTotal / this.count;  
        this.yCenter = this.count==0 ? (this.yBeg+this.yEnd)/2 : this.yTotal / this.count;  
        //候选点族的半径区域  
        this.r = Math.min(  
            Math.abs(this.xMax - this.xCenter),  
            Math.abs(this.xMin - this.xCenter),  
            Math.abs(this.yMax - this.yCenter),  
            Math.abs(this.yMin - this.yCenter),  
            this.range    
        );  
      
    };  
  
}  


function step1() {  
    var arr = new Array();    
    var pointInfo="$picDataArray = [";  
      
  
    //图片  
    var image = new Image();  
    image.src = "./1.jpg";  
    //只处理这100*100个象素  
    var width = 600;  
    var height = 400;  
    //格子大小，行和列共有多少格  
    var range = 8;  
    var rows = height / range;  
    var cols = width / range;  
    //确定范围  
    var xBeg, xEnd, yBeg, yEnd;  
    //待计算参数  
    var count, xTotal, yTotal, xMin, yMin, xMax, yMax;  
      
    var gap = 20;  
  
    image.onload = function() {  
        plot.drawImage(image);  
        var imagedata = plot.getImageData(0, 0, width, height);           
        //计算  
        for (var i = 0; i < cols; i++) {  
            for (var j = 0; j < rows; j++) {  
                var nexus = new Nexus();  
                nexus.init(i, j, range);  
                  
                //确定范围  
                xBeg = nexus.xBeg;  
                xEnd = nexus.xEnd;  
                yBeg = nexus.yBeg;  
                yEnd = nexus.yEnd;  
                //待计算参数  
                xMin = nexus.xMin;  
                xMax = nexus.xMax;  
                yMin = nexus.yMin;  
                yMax = nexus.yMax;        
                count = 0;  
                xTotal = 0;  
                yTotal = 0;               
                  
  
                //水平方向找差异  
                for (var col = xBeg+1; col < xEnd; col++) {  
                    for (var row = yBeg; row<yEnd; row++) {  
                        //pos最小为1  
                        pos =row * width + col;  
                        R0 = imagedata.data[4 * (pos-1)];                 
                        R1 = imagedata.data[4 * pos];  
                        G0 = imagedata.data[4 * (pos-1)+1];  
                        G1 = imagedata.data[4 * pos+1];  
                        B0 = imagedata.data[4 * (pos-1)+2]  
                        B1 = imagedata.data[4 * pos + 2]  
                          
                        //简单容差判断  
                        if (Math.abs(R1-R0) > gap ||   
                                Math.abs(G1-G0)>gap ||   
                                Math.abs(B1-B0)>gap){  
                              
                            count++;  
                            xTotal += col;  
                            yTotal += row;  
                            if (xMin > col) xMin = col;  
                            if (xMax < col) xMax = col;  
                            if (yMin > row) yMin = row;  
                            if (yMax < row) yMax = row;  
                        }  
                    }  
                }  
                  
                //垂直方向找差异  
                for (var col = xBeg; col < xEnd; col++) {  
                    for (var row = yBeg+1; row<yEnd; row++) {  
                        //pos最小为第二行  
                        pos =row * width  + col;  
                        R0 = imagedata.data[4 * (pos-width)];                 
                        R1 = imagedata.data[4 * pos];  
                        G0 = imagedata.data[4 * (pos-width)+1];  
                        G1 = imagedata.data[4 * pos+1];  
                        B0 = imagedata.data[4 * (pos-width)+2];  
                        B1 = imagedata.data[4 * pos + 2];  
                          
                        //简单容差判断  
                        if (Math.abs(R1-R0) > gap ||   
                                Math.abs(G1-G0)>gap ||   
                                Math.abs(B1-B0)>gap) {  
                          
                            count++;  
                            xTotal += col;  
                            yTotal += row;  
                            if (xMin > col) xMin = col;  
                            if (xMax < col) xMax = col;  
                            if (yMin > row) yMin = row;  
                            if (yMax < row) yMax = row;  
                        }  
  
                    }  
                }  
                nexus.calc(count, xTotal, yTotal, xMax, yMax, xMin, yMin);  
                arr.push(nexus);  
                  
            }  
        }  
          
        arr.sort(function(a, b) {  
            if (a[1] < b[1]) return -1;  
            else if (a[1] > b[1]) {  
                return 1;  
            }  
            else {  
                if (a[0] < b[0]) return -1;  
                else if (a[0] > b[0]) return 1;  
                else return 0;  
            }  
            return 1;  
        });  
          
        var nexus = new Nexus();  
        for (var i = 0; i < arr.length; i++) {  
            nexus = arr[i];  
            if (nexus.percent > 0.01 && nexus.percent < 0.9) {  
                pointInfo += '[' + nexus.yth + ', ' + nexus.xth + ',' +  
                        nexus.xCenter.toFixed(0) + ', ' + nexus.yCenter.toFixed(0)+'], ';  
            }  
            if (nexus.percent > 0.01 && nexus.percent < 0.9) {  
                fillCircle(nexus.xCenter, nexus.yCenter, 4);  
            }  
            else {  
                fillCircle(nexus.xCenter, nexus.yCenter, 1);  
            }  
        }  
          
        pointInfo += '];';  
        var pointInfoNode = document.createTextNode(pointInfo);  
        document.body.appendChild(pointInfoNode);  
          
    }  
  
}  

//连线  
function step2(array) {  
    //array数组中的每个单元都是有4个参数的，分别是yth, xth, xCenter, yCenter  
    var len = array.length;  
    for (var i = 0; i < len; i++) {  
        fillCircle(array[i][2], array[i][3], 2);  
        for (var j = i; j < len; j++) {                    
            if (Math.abs(array[i][0] - array[j][0]) +    
                Math.abs(array[i][1] - array[j][1]) <= 1   
                ) {  
                plot.moveTo(array[i][2], array[i][3])  
                    .lineTo(array[j][2], array[j][3])  
                    .stroke();  
            }  
        }  
    }  
}  
  
function picData() {  
    // plot.init();  
    setPreference();  
      
    //step1();  
    step2($picDataArray);  
  
}  
picData() ;