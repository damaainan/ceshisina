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
  
/** 
* @usage   按区格压缩分块点，依赖Nexus节点类 
* @author  mw 
* @date    2015年12月23日  星期三  11:45:43  
* @param 
* @return 
* 
*/  
  
//按区格压缩分块点  
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
    var range = 50;  
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
            if (nexus.count > 10 && nexus.percent < 0.9) {  
                pointInfo += '[' + nexus.yth + ', ' + nexus.xth + ',' +  
                        nexus.xCenter.toFixed(0) + ', ' + nexus.yCenter.toFixed(0)+'], ';  
            }  
            if (nexus.count > 10 && nexus.percent < 0.9) {  
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

/** 
* @usage   压缩中心点数量 
* @author  mw 
* @date    2015年12月23日  星期三  11:45:43  
* @param 
* @return 
* 
*/  
  
//压缩中心点数量  
function centerPoint(arr, tolerance) {  
    tolerance = tolerance ? tolerance : 50;  
    var array = new Array();  
    var indexArray = new Array();  
  
      
    var len = arr.length;  
    var xTotal=0, yTotal=0, count=0;  
      
    for (var i =0 ; i < len; i++) {  
        if (indexArray[i]) continue;  
        //第i号已处理  
        indexArray[i]=1;  
        xTotal = arr[i][2];  
        yTotal = arr[i][3];  
        count=1;  
          
        for (var j =i+1; j < len; j++) {  
            if (indexArray[j]) continue;  
  
            if (Math.abs(arr[i][0] - arr[j][0])<=1 &&  
                Math.abs(arr[i][1] - arr[j][1])<=1) {  
                if (Math.abs(arr[i][2]-arr[j][2])+  
                    Math.abs(arr[i][3] - arr[j][3]) < tolerance) {  
                    indexArray[j]=1;  
                    xTotal += arr[j][2];  
                    yTotal += arr[j][3];  
                    count++;  
                }  
            }  
        }  
        array.push([arr[i][0], arr[i][1], Math.round(xTotal/count), Math.round(yTotal/count)]);   
    }  
      
      
    array.sort(function(a, b) {  
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
          
    var pointInfo = "$picDataArray = [";  
    len = array.length;  
    for (var i = 0; i < len; i++) {  
        pointInfo += "["+array[i][0]+", "+array[i][1]+", "+array[i][2]+", "  
            +array[i][3]+"], ";  
    }  
    pointInfo += "];";  
    document.body.appendChild(document.createTextNode(pointInfo));  
      
    return array;  
}  
  
//方便函数  
//绘点并产生正确的点个数  
function step2() {  
    var arr = new Array();  
    arr = centerPoint($picDataArray, 50);  
      
    var len = arr.length;  
    plot.save()  
        .setFillStyle('red');  
    for (var i = 0; i<len; i++) {  
        fillCircle(arr[i][2], arr[i][3], 5);  
    }  
    plot.restore();  
}  


/** 
* @usage   判断点是否在区域内，含范围线，区域线，点注明，目标点测试 
* @author  mw 
* @date    2015年12月23日  星期三  11:28:04  
* @param 
* @return 
* 
*/  
//暂时还是大杂烩的方便函数  
function step3() {  
    //存放轮廓点  
    var arr = new Array();  
    //存放路径中点在数据数组中的索引  
    var seqArr = new Array();  
    //获取数据  
    arr = $picDataArray;      
    var len = arr.length;  
      
    plot.save()  
        .setFillStyle('red')  
        .setStrokeStyle('black');  
      
    //原始数据点图  
    for (var i = 0; i<len; i++) {  
        fillCircle(arr[i][2], arr[i][3], 5);  
    }  
      
    //  
    //这一区块代码功能是：按一定序列连成区域  
    //  
    plot.setLineWidth(3)  
        .setStrokeStyle('red');  
          
    var rowBeg = arr[0][0], rowEnd = arr[len-1][0],   
        colBeg=arr[0][1], colEnd=arr[len-1][1];  
      
    if (rowBeg == rowEnd) {  
        for (var i=0; i < len; i++) {  
            if (arr[i][0] <= rowBeg) {  
                seqArr.push(i);  
            }  
        }  
          
        for (var i=len-1; i>0; i--) {  
            if (arr[i][0] > rowBeg) {  
                seqArr.push(i);  
            }  
        }  
    }  
    else {  
        var k = (colEnd - colBeg) / (rowEnd - rowBeg);  
          
        for (var i=0; i < len; i++) {  
            if (arr[i][1] >= colBeg +  k * (arr[i][0] - rowBeg)) {  
                seqArr.push(i);  
            }  
        }  
          
        for (var i=len-1; i>0; i--) {  
            if (arr[i][1] < colBeg +  k * (arr[i][0] - rowBeg)) {  
                seqArr.push(i);  
            }  
        }  
          
    }  
      
    seqArr.push(0);  
    //至此序列算法生成完毕  
    var len2 = seqArr.length;  
    var s = "["+ rowBeg.toFixed(0) + ", "+ rowEnd.toFixed(0)  
        + ", "+colBeg.toFixed(0) + ", "+colEnd.toFixed(0)+"] ***";  
    for (var i=0; i < len2; i++) {  
        s += seqArr[i].toFixed(0) + ', ';  
    }  
    document.body.appendChild(document.createTextNode(s));  
      
    //区域绘制  
    plot.moveTo(arr[0][2], arr[0][3]);  
    for (var i = 0; i<len2; i++) {  
        plot.lineTo(arr[seqArr[i]][2], arr[seqArr[i]][3]);  
    }  
    plot.stroke();  
      
    plot.setLineWidth(2);  
      
    //折线相连  
    /* 
    plot.moveTo(arr[0][2], arr[0][3]); 
    for (var i = 1; i<len; i++) { 
        plot.lineTo(arr[i][2], arr[i][3]); 
    } 
    plot.stroke(); 
    */  
      
    //  
    //这一区块代码功能是：求取边界点并绘制边界线  
    //  
    //待判定目标的像素坐标值  
    var xTarget=400, yTarget=200;     
    plot.setFillStyle('black');  
    fillCircle(xTarget, yTarget, 10);  
    plot.fillText('目标点', xTarget + 10, yTarget-10, 100);  
      
      
    //范围限定点算法开始  
    //上下左右边界每边都要有两个限制点  
    //如果这八个限制点都能取到，说明目标点在内部  
    //分两组，第一组为左上，左下，上左，上右  
    var leftIndex = -1, rightIndex = -1, upIndex = -1, downIndex=-1;  
    var left = -10000, right = 10000, up = -10000, down = 10000;  
    //第二组为下左， 下右，右上，右下  
    //区别在于，左上的‘上’最接近目标点，下左的‘左’最接近目标点，其它类推。  
    var leftIndex2 = -1, rightIndex2 = -1, upIndex2 = -1, downIndex2=-1;  
    var left2 = -10000, right2 = 10000, up2 = -10000, down2 = 10000;  
    //xdiff>0 说明在目标右边，ydiff>0说明在目标下边  
    var xdiff = 0, ydiff = 0;  
      
    //给目标点找最近的点把它包围起来  
    for (var i=0; i < len; i++) {          
        xdiff = arr[i][2] - xTarget;  
        ydiff = arr[i][3] - yTarget;  
          
        if (ydiff < 0) { //上边界的水平限制点  
            if (xdiff < 0 && left < xdiff) {  
                left = xdiff;  
                leftIndex = i;  
            }  
            if (xdiff > 0 && right > xdiff) {  
                right = xdiff;  
                rightIndex = i;  
            }  
        }  
        else {  
            if (xdiff < 0 && left2 < xdiff) {  
                left2 = xdiff;  
                leftIndex2 = i;  
            }  
            if (xdiff > 0 && right2 > xdiff) {  
                right2 = xdiff;  
                rightIndex2 = i;  
            }  
              
        }  
          
        if (xdiff < 0) { //左边界的上下限制点  
            if (ydiff < 0 && up < ydiff) {  
                up = ydiff;  
                upIndex = i;  
            }  
            if (ydiff > 0 && down > ydiff) {  
                down = ydiff;  
                downIndex = i;  
            }     
        }  
        else {  
            if (ydiff < 0 && up2 < ydiff) {  
                up2 = ydiff;  
                upIndex2 = i;  
            }  
            if (ydiff > 0 && down2 > ydiff) {  
                down2 = ydiff;  
                downIndex2 = i;  
            }     
          
        }  
          
    }  
      
    //效果查验  
    //左右夹击    
    plot.setStrokeStyle('blue');  
      
    if (leftIndex >= 0) {  
    strokeCircle(arr[leftIndex][2], arr[leftIndex][3], 10);   
    plot.fillText('上左', arr[leftIndex][2]-20, arr[leftIndex][3]+20, 20);  
    plot.moveTo(arr[leftIndex][2], arr[leftIndex][3]-600)  
        .lineTo(arr[leftIndex][2], arr[leftIndex][3]+600)  
        .stroke();  
    }  
      
      
    if (rightIndex >= 0) {  
    strokeCircle(arr[rightIndex][2], arr[rightIndex][3], 10);  
    plot.fillText('上右', arr[rightIndex][2]+20, arr[rightIndex][3]+20, 20);  
    plot.moveTo(arr[rightIndex][2], arr[rightIndex][3]-600)  
        .lineTo(arr[rightIndex][2], arr[rightIndex][3]+600)  
        .stroke();  
    }  
      
      
    plot.setStrokeStyle('#FF00FF');  
      
    if (leftIndex2 >= 0) {  
        strokeCircle(arr[leftIndex2][2], arr[leftIndex2][3], 10);  
        plot.fillText('下左', arr[leftIndex2][2]-20, arr[leftIndex2][3]-20, 20);  
        plot.moveTo(arr[leftIndex2][2], arr[leftIndex2][3]-600)  
            .lineTo(arr[leftIndex2][2], arr[leftIndex2][3]+600)  
            .stroke();  
    }  
      
    if (rightIndex2 >= 0) {  
        strokeCircle(arr[rightIndex2][2], arr[rightIndex2][3], 10);  
        plot.fillText('下右', arr[rightIndex2][2]+20, arr[rightIndex2][3]-20, 20);  
        plot.moveTo(arr[rightIndex2][2], arr[rightIndex2][3]-600)  
            .lineTo(arr[rightIndex2][2], arr[rightIndex2][3]+600)  
            .stroke();  
    }  
      
    //上下夹击  
    plot.setStrokeStyle('green');  
      
    if (upIndex >= 0) {  
        strokeCircle(arr[upIndex][2], arr[upIndex][3], 10);  
        plot.fillText('左上', arr[upIndex][2]-30, arr[upIndex][3]-10, 20);  
        plot.moveTo(arr[upIndex][2]+600, arr[upIndex][3])  
            .lineTo(arr[upIndex][2]-600, arr[upIndex][3])  
            .stroke();  
    }  
      
    if (downIndex >= 0) {  
        strokeCircle(arr[downIndex][2], arr[downIndex][3], 10);  
        plot.fillText('左下', arr[downIndex][2]-30, arr[downIndex][3]+10, 20);  
        plot.moveTo(arr[downIndex][2]+600, arr[downIndex][3])  
            .lineTo(arr[downIndex][2]-600, arr[downIndex][3])  
            .stroke();  
    }  
      
    plot.setStrokeStyle('cyan');      
      
    if (upIndex2 >= 0) {  
        strokeCircle(arr[upIndex2][2], arr[upIndex2][3], 10);  
        plot.fillText('右上', arr[upIndex2][2]+10, arr[upIndex2][3]-10, 20);  
        plot.moveTo(arr[upIndex2][2]+600, arr[upIndex2][3])  
            .lineTo(arr[upIndex2][2]-600, arr[upIndex2][3])  
            .stroke();  
    }  
      
    if (downIndex2 >= 0) {  
        strokeCircle(arr[downIndex2][2], arr[downIndex2][3], 10);  
        plot.fillText('右下', arr[downIndex2][2]+10, arr[downIndex2][3]+10, 20);  
        plot.moveTo(arr[downIndex2][2]+600, arr[downIndex2][3])  
            .lineTo(arr[downIndex2][2]-600, arr[downIndex2][3])  
            .stroke();  
    }  
      
    plot.setStrokeStyle('black');  
      
      
    //  
    //这一区块代码功能是：严格判断点是否在区域中  
    //  
      
    //左上left  
    //右上right  
    //左下 left2   
    //右下right2   
      
    //上左 up  
    //下左 down   
    //上右 up2  
    //下右 down2   
      
    //如果左边的上下都确定好，那么左边界就确定好了  
    //反之，则要另行判断  
    //两个点和一个斜率作临时变量。  
    var isIn = false;  
    var x1, y1, x2, y2, k;  
    var xRef, yRef;  
      
    //小于0为没取到合适值，说明这个点缺失      
    if (leftIndex < 0 && upIndex < 0) {//左上角需判断  
        //左上角不确定，从左下边和上右边连线求  
        if (downIndex >= 0 && upIndex2 >= 0) {  
            x1 = arr[downIndex][2];  
            y1 = arr[downIndex][3];  
            x2 = arr[upIndex2][2];  
            y2 = arr[upIndex2][3];  
              
            plot.moveTo(x1, y1)  
                .lineTo(x2, y2)  
                .stroke();  
                  
            if (x1 == x2) {}  
            else {  
                k = (y2 - y1) / (x2 - x1);  
                  
                yRef = y1 + (xTarget - x1) * k;  
                if (yRef < yTarget) {  
                    isIn = true;  
                  
                }  
            }  
        } else {  
            //如果两层判断所需的四个点至少缺失三个，可以肯定这个方向边界是越界的  
            isIn = false;  
        }  
    }  
    else if (leftIndex2 < 0 &&  downIndex < 0) { //左下角需判断  
        //左下角不确定，从左上边和下右边连线求  
        if (upIndex >= 0 && downIndex2 >= 0) {  
            x1 = arr[upIndex][2];  
            y1 = arr[upIndex][3];  
            x2 = arr[downIndex2][2];  
            y2 = arr[downIndex2][3];  
              
            plot.moveTo(x1, y1)  
                .lineTo(x2, y2)  
                .stroke();  
                  
            if (x1 == x2) {}  
            else {  
                k = (y2 - y1) / (x2 - x1);  
                  
                yRef = y1 + (xTarget - x1) * k;  
                if (yRef > yTarget) {  
                    isIn = true;  
                  
                }  
            }  
        } else {  
            //如果两层判断所需的四个点至少缺失三个，可以肯定这个方向边界是越界的  
            isIn = false;  
        }  
    }  
    else if (rightIndex < 0 &&  upIndex2 < 0) { //右上角需判断  
        //右上角不确定，从右下边和上左边连线求  
        if (rightIndex2 >= 0 && leftIndex >= 0) {  
            x1 = arr[rightIndex2][2];  
            y1 = arr[rightIndex2][3];  
            x2 = arr[leftIndex][2];  
            y2 = arr[leftIndex][3];  
              
            plot.moveTo(x1, y1)  
                .lineTo(x2, y2)  
                .stroke();  
                  
            if (x1 == x2) {}  
            else {  
                k = (y2 - y1) / (x2 - x1);  
                  
                yRef = y1 + (xTarget - x1) * k;  
                if (yRef < yTarget) {  
                    isIn = true;  
                  
                }  
            }  
        } else {  
            //如果两层判断所需的四个点至少缺失三个，可以肯定这个方向边界是越界的  
            isIn = false;  
        }  
    }  
    else if (rightIndex2 < 0 &&  downIndex2 < 0) { //右下角需判断  
        //右下角不确定，从右上边和下左边连线求  
        if (rightIndex >= 0 && leftIndex2 >= 0) {  
            x1 = arr[rightIndex][2];  
            y1 = arr[rightIndex][3];  
            x2 = arr[leftIndex2][2];  
            y2 = arr[leftIndex2][3];              
              
            plot.moveTo(x1, y1)  
                .lineTo(x2, y2)  
                .stroke();  
              
            if (x1 == x2) {}  
            else {  
                k = (y2 - y1) / (x2 - x1);  
                  
                yRef = y1 + (xTarget - x1) * k;  
                if (yRef > yTarget) {  
                    isIn = true;  
                  
                }  
            }  
        } else {  
            //如果两层判断所需的四个点至少缺失三个，可以肯定这个方向边界是越界的  
            isIn = false;  
        }  
    }  
    else {  
        isIn = true;  
    }  
      
    //最终审判  
    if (isIn) {  
        plot.fillText('目标点在区域内', 20, 30, 100);  
    }  
    else {  
        plot.fillText('目标点在区域外面', 20, 30, 100);  
    }  
      
    plot.restore();  
  
}  