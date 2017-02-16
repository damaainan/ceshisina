function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    var vertExp = new VerticalExpression();  
    var r = 25;  
    var x = 200, y=20;  
      
    vertExp.add(271, 122, x, y, r);  
      
    x = 400;  
    vertExp.add(271, 31, x, y, r);  
      
    x = 600;  
    vertExp.add(271, 903, x, y, r);   
      
}


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);    
      
    var vertExp = new VerticalExpression();  
    var row = 1, col=2, width = 600, height = 400;  
    var r = 50;  
    var x = 0, y=20;  
      
    quest = [[445,298],[298,445]];  
    len = quest.length;  
      
    for (var i = 0; i < row;  i++) {  
        for (var j=0; j < col; j++) {  
            x = width/col*(j+1);  
            y = 20 + height/row*i;  
            vertExp.add(quest[i*col+j][0], quest[i*col+j][1], x, y, r);  
        }  
    }  
      
      
}  


function myDraw() {    
    var config = new PlotConfiguration();    
    config.init();    
    config.setPreference();   
    //config.setSector(1,1,1,1);  
   
    //config.axis2D(0, 0, 180);       
  
    /* 
        求解以下问题： 
        （1）有五个地点，编号1-5，地点之间的连通情况为边数组 
    */  
    //顶点数量  
    var vertexNum = 5;  
    var edge = [[1,2,218],[1,5,410],[1,4,510],[2,3,75],[3,4,440],[2,4,329],[4,5,125]];  
    var edges = edge.length;  
      
    var pathMap = new Map();  
      
    for (var i = 0; i < edges; i++) {  
        pathMap.put(edge[i][0], edge[i][1]);  
        pathMap.put(edge[i][1], edge[i][0]);  
  
    }  
  
      
    /* 
        （2）现在要求给出从地点1出发，回到地点1，中间经过（2, 3, 4)点， 
        求出所有满足要求的选择 
    */  
    var begin = 1;  
    var end = 1;  
    //要求经过的地点  
    var required = [1, 2, 3, 4];  
      
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
    var distance = 0;  
      
    //把起点压入栈  
    pathArray.push([begin]);      
  
    while (pathArray.length > 0) {  
          
        //每次从选择集合中取出第一个选择  
  
        choice = pathArray.shift();  
        len = choice.length;  
        if (len > vertexNum)   
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
                    if (newChoice[i] == end) {  
                        effectivePath.push(choice.concat(newChoice[i]));  
                    }   
                      
                    break;  
                }  
                //无重复元素  
                if (j >= len-1) {  
                    pathArray.push(choice.concat(newChoice[i]));  
                    //document.write(choice.concat(newChoice[i]).join(',')+'<br/>');  
                }  
            }  
        }  
    }  
      
    var addressString = ['小君家', '邮局', '书店', '超市', '学校'];  
    var s = '';   
    len = effectivePath.length;  
      
    for (var i = 0; i < len; i++) {  
        distance = 0;  
        choice = effectivePath[i];  
        len1 = choice.length;  
        for (var j = 0; j < len1-1; j++) {  
            for (var k = 0; k < edges; k++) {  
                if (edge[k][0] == choice[j] && edge[k][1] == choice[j+1]) {  
                    distance += edge[k][2];  
                }  
            }  
        }  
      
    }  
      
    for (var i = 0; i < len; i++) {  
        s = '';  
        choice = effectivePath[i];  
        len1 = choice.length;  
        distance = 0;         
          
        for (var j = 0; j < len1; j++) {  
            s +=addressString[choice[j]-1];  
            if (j < len1-1) {  
                s += '-->';  
            }  
        }  
          
        for (var j = 0; j < len1-1; j++) {  
            for (var k = 0; k < edges; k++) {  
                if ((edge[k][0] == choice[j] && edge[k][1] == choice[j+1]) ||  
                    (edge[k][1] == choice[j] && edge[k][0] == choice[j+1])) {  
                    distance += edge[k][2];  
                }  
            }  
        }  
        s += ' ，这条路总长：'+distance.toFixed(0)+'米。';  
          
        if (effective(choice, required)) {  
            s += '----有效。';  
        }  
          
        document.write(s+'<br/>');  
    }  
      
}  
  
function effective(arrayPath, arrayRequired) {  
    var array1 = new Array();  
    array1 = arrayPath;  
    var len1 = array1.length;  
      
    var array2 = new Array();  
    array2 = arrayRequired;  
    var len2 = array2.length;  
      
    for (var i = 0; i < len2; i++) {  
        for (var j = 0; j < len1; j++) {  
            if (array1[j] == array2[i])  
                break;  
                  
            if (j >= len1-1) {  
                return 0;  
            }  
        }  
    }  
      
    return 1;  
}