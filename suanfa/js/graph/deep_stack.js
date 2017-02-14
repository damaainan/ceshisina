

//深度优先搜索(栈实现)
//
function  DfsStack(){
  var  M= 8;//图有八个顶点,如下所示
  //图及图的邻接矩阵表示
  //1------5
  //|      |
  //2--4---6----8
  //|      |   
  //3------7
  
  var matrix =[
    [0, 1, 0, 0, 1, 0, 0, 0],
    [1, 0, 1, 1, 0, 0, 0, 0],
    [0, 1, 0, 0, 0, 0, 1, 0],
    [0, 1, 0, 0, 1, 1, 0, 0], 
    [1, 0, 0, 1, 0, 0, 0, 0],
    [0, 0, 0, 1, 0, 0, 1, 1],
    [0, 0, 1, 0, 0, 1, 0, 0],
    [0, 0, 0, 0, 0, 1, 0, 0]
  ];
   //访问标记, visited[i]=true表示顶点i已访问
   var visited=[];
   
   this.GT_DFS=function(){
     visited[1] = true;//从顶点1开始访问
     var s=new Stack();
     console.log("1 ");
     s.push(1);
     while(!s.isEmpty()){
       var top = s.peek();//不出栈
       for(var i = 1; i <= M; ++i){
          if(!visited[i] && matrix[top - 1][i - 1 ] == 1)
          {
           visited[i] = true;
           s.push(i);
           console.log(i);
            break;
           }
       }
       if( i == M + 1){
         s.pop();//出栈
       }
     }
   }
  }
 
   new DfsStack().GT_DFS();
 function Stack() {
    var items = [];
    this.push = function(element){
        items.push(element);
    };
    this.pop = function(){
        return items.pop();
    };
    this.peek = function(){
        return items[items.length-1];
    };
    this.isEmpty = function(){
        return items.length == 0;
    };
    this.size = function(){
        return items.length;
    };
    this.clear = function(){
        items = [];
    };
    this.print = function(){
        console.log(items.toString());
    };
    this.toString = function(){
        return items.toString();
    };
}