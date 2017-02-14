//广度优先搜索对应的最短路径


var Graph = require("./G");

var g = new Graph(5);
g.addEdge(0,1);
g.addEdge(0,2);
g.addEdge(1,3);
g.addEdge(2,4);
g.bfs(0);  //需要先执行 广度优先 
var vertex = 4;
var paths = g.pathTo(vertex);
// console.log(paths);
var str="";
while (paths.length > 0) {
   if (paths.length > 1) {
      str+=paths.pop() + '-';
   }else {
      str+=paths.pop();
   }
}
console.log(str);