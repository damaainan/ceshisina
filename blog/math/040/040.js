function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    //图片  
    var image = new Image();  
    image.src = "./1.jpg";  
      
    var ruler = new Ruler();  
      
    image.onload = function() {  
        plot.save()  
            .scale(1.07, 1)  
            .drawImage(image)  
            .restore();  
  
        ruler.ruler(10, 128, 280, 0);  
    }  
      
}


/** 
* @usage   打印给定两个地点间所有路径 
* @author  mw 
* @date    2016年01月13日  星期三  09:50:23  
* @param 
* @return 
* 
*/  
function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    /* 
        求解以下问题： 
        （1）有六个地点，编号1-6，地点之间的连通情况为边数组 
    */  
    //顶点数量  
    var vertexNum = 6;  
    var edge = [[1,6],[1,2],[2,5],[2,3],[3,4],[4,5],[5,6]];  
    var edges = edge.length;  
      
    var pathMap = new Map();  
      
    for (var i = 0; i < edges; i++) {  
        pathMap.put(edge[i][0], edge[i][1]);  
        pathMap.put(edge[i][1], edge[i][0]);  
  
    }  
      
    pathMap.sort();  
      
    /* 
        （2）现在要求给出两点（6,3）， 
        求出所有能从地点6到达地点3的选择 
    */  
    var begin = 6;  
    var end = 3;  
      
    //可增长的路径  
    var pathArray = new Array();  
    //pathArray中的一个选择  
    var choice = new Array();  
    //choice前提下的下一步选择  
    var newChoice = new Array();  
    //有效路径  
    var effectivePath = new Array();  
    //临时变量  
    var tmp = 0, len = 0, len1 = 0;  
    var count = 0;  
      
    //把起点压入栈  
    pathArray.push([begin]);      
  
    while (pathArray.length > 0) {  
          
        //每次从选择集合中取出第一个选择  
  
        choice = pathArray.shift();  
        len = choice.length;  
        if (len >= vertexNum)   
            break;  
  
        //选择的最后一个元素  
        tmp = choice[len-1];  
        newChoice = pathMap.get(tmp);  
        //document.write(choice.join(',')+'------'+newChoice.join(',')+'<br/>');  
          
        len1 = newChoice.length;  
  
          
        for (var i = 0; i < len1; i++) {  
            for (var j = 0; j < len; j++) {  
                //元素重复了  
                if (newChoice[i] == choice[j]) {  
                    break;  
                }  
                //无重复元素  
                if (j >= len-1) {  
                    if (newChoice[i] == end) {  
                        effectivePath.push(choice.concat(newChoice[i]));  
                    }  
                    else {  
                        pathArray.push(choice.concat(newChoice[i]));  
                        //document.write(choice.concat(newChoice[i]).join(',')+'<br/>');  
                    }  
                }  
            }  
        }  
    }  
      
    var addressString = ['学校', '医院', '体育场', '公园', '小年宫', '小伟家'];  
    var s = '';   
    len = effectivePath.length;  
      
      
    for (var i = 0; i < len; i++) {  
        s = '';  
        choice = effectivePath[i];  
        len1 = choice.length;  
        for (var j = 0; j < len1; j++) {  
            s +=addressString[choice[j]-1];  
            if (j < len1-1) {  
                s += '-->';  
            }  
        }  
        document.write(s+'<br/>');  
    }  
      
}  