//别人写的  能完整运行
    //定义类  
function Graph(v){  
    this.vertices = v; //顶点  
    this.vertexList = [];  
    this.edges = 0;  
    this.adj = [];  
    for(var i=0;i<this.vertices;++i){  
        this.adj[i] = [];  
    };  
    //方法  
    this.addEdge = addEdge;  
    this.showGraph = showGraph;  
    this.marked = [];  
    this.dfs = dfs;    //深度优先  
    for(var i=0;i<this.vertices;++i){  
        this.marked[i] = false;  
    };  
    this.bfs = bfs;    //广度优先  
    this.edgeTo = [];   //最短距离，保存一个顶点到下一个顶点的所有边  
    this.pathTo = pathTo;  
    this.hasPathTo = hasPathTo;  
    this.topSortHelper = topSortHelper;  
    this.topSort = topSort;  
}  
  
//类对应的方法  
function addEdge(v,w){  
    this.adj[v].push(w);  
    this.adj[w].push(v);  
    this.edges++;  
}  
  
  
// 用于显示符号名字而非数字的新函数,打印所有顶点及其相邻顶点列表  
function showGraph() {           
    var visited = [];  
    for ( var i = 0; i < this.vertices; ++i) {  
        var str = '';  
        visited.push(this.vertexList[i+1]);  
        for ( var j = 0; j < this.vertices; ++j ) {  
            if (this.adj[i][j] != undefined) {  
                if (visited.indexOf(this.vertexList[j]) < 0) {  
                    str += this.adj[i][j] + ' ';  
                }  
            }  
  
        }  
        console.log(i + '->' + str);  
        visited.pop();  
    }  
}  
//深度优先  
function dfs(v) {  
    this.marked[v] = true;  
    if (this.adj[v] != undefined) {  
        console.log("Visited vertex: " + v);  
    }  
    for(var w of this.adj[v]) {  
        if (!this.marked[w]) {  
            this.dfs(w);  
        }  
    }  
}  
//广度优先  
function bfs(s){  
    var queue = [];    //队列  
    this.marked[s] = true;  
    queue.push(s);    //添加到队尾,如果用unshift则会由右往左遍历，显示0 2 1 3 4   
    while(queue.length > 0){  
        var v = queue.shift();//从队首移除  
        if(typeof(v) != 'string'){  
            console.log("Visited vertex:" + v);  
        };  
        for(var w of this.adj[v]){  
            if(!this.marked[w]){  
                this.edgeTo[w] = v;     
                this.marked[w] = true;  
                queue.push(w);  
            }  
        }  
    }  
}  
  
  
function pathTo(startVertices,v) {  
    console.log("v"+v);
    console.log("startVertices"+startVertices);
    var source = startVertices;             //bfs遍历的开始的点，根据调用bfs传入的参数修改  
    console.log(this.hasPathTo(v));
    if (!this.hasPathTo(v)) {  
        return undefined;  
    }  
    var path = [];  
    for (var i = v; i != source; i = this.edgeTo[i]) {  
        path.push(i);  
    }  
    path.push(source);  
    return path;  
}  
function hasPathTo(v) {  
    return this.marked[v];  
}  
//显示最短距离路径显示的函数  
function showShortDiatance(paths){  
    var str = '';                 //以下都为输出顺序的显示  
    while (paths.length > 0) {  
        if (paths.length > 1) {  
            str += paths.pop() + '-';  
        }  
        else {  
            str += paths.pop();  
        }  
    }  
    console.log(str);  
}  
//拓扑排序  
function topSort() {
    var stack = [];
    var visited = [];
    for (var i = 0; i < this.vertices; i++) {
        visited[i] = false;
    }
    for (var i = 0; i < this.vertices; i++) {
        if (visited[i] == false) {
            this.topSortHelper(i, visited, stack);
        }
    }
    for (var i = 0; i < stack.length; i++) {
        if (stack[i] != undefined && stack[i] !== false)      //stack[i] = 0，但是0 != false 是true，所有应该用严等于  
        console.log(this.vertexList[stack[i]]);
    }
}


    function topSortHelper(v, visited, stack) {  
        visited[v] = true;  
        for(var w in this.adj[v]) {  
            if (!visited[w]) {  
                this.topSortHelper(visited[w], visited, stack);  
            }  
        }  
        stack.push(v);  
    }  
// module.exports = Graph;


// var g = new Graph(5);
// g.addEdge(0,1);
// g.addEdge(0,2);
// g.addEdge(1,3);
// g.addEdge(2,4);
// g.bfs(0);
// var vertex = 4;
// var paths = g.pathTo(0,vertex);
// console.log(paths);
  
//测试拓扑结构  
g = new Graph(6); 
g.addEdge(1, 2); 
g.addEdge(2, 5); 
g.addEdge(1, 3); 
g.addEdge(1, 4); 
g.addEdge(0, 1); 
g.vertexList = ["CS1", "CS2", "Data Structures", 
"Assembly Language", "Operating Systems", 
"Algorithms"]; 
g.showGraph(); 
g.topSort();  
  
//测试其他函数  
// g = new Graph(5); 
// g.addEdge(0,1); 
// g.addEdge(0,2); 
// g.addEdge(1,3); 
// g.addEdge(2,4); 
// var startVertices = 0;  
// console.time('dfs');  
// g.dfs(startVertices);            //用时4ms  
// console.timeEnd('dfs');  
// console.time('bfs');  
// g.bfs(startVertices);              //用时16ms  
// console.timeEnd('bfs');  
//var endVertices = 2;   //从bfs的起点到vertex的最短路径  
//var paths = g.pathTo(startVertices,endVertices);  
//showShortDiatance(paths);  