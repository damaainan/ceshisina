function Graph(v) {
   this.vertices = v;
   this.edges = 0;
   this.adj = [];
   for (var i = 0; i < this.vertices; ++i) {
      this.adj[i] = [];
      // this.adj[i].push("");
   }
   this.addEdge = addEdge;
   this.showGraph = showGraph;
   this.dfs = dfs;
   this.bfs = bfs;
   this.marked = [];
   for (var i = 0; i < this.vertices; ++i) {
      this.marked[i] = false;
   }

   this.edgeTo = [];
   this.hasPathTo = hasPathTo;
   this.pathTo = pathTo;
   this.topSortHelper = topSortHelper;
   this.topSort = topSort;
}

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
      if (stack[i] != undefined && stack[i] !== false) {// 不全等 ！== 和 不等 ！=  结果有差异
         console.log(this.vertexList[stack[i]]);
      } 
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





function hasPathTo(v) {
   return this.marked[v];
}

function pathTo(v) {
   var source = 0;
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


function addEdge(v,w) {
   this.adj[v].push(w);
   this.adj[w].push(v);
   this.edges++;
}


function showGraph() {
    for (var i = 0; i < this.vertices; ++i) {
        var str="";
        for (var j = 0; j < this.vertices; ++j) {
            if (this.adj[i][j] != undefined)
                str+=this.adj[i][j] + ' ';
        }
        console.log(i + "->"+str);
    }
}

function bfs(s) {
    var queue = [];
    this.marked[s] = true;
    queue.push(s); // 添加到队尾
    while (queue.length > 0) {
        var v = queue.shift(); // 从队首移除
        if (v != undefined) {
            // console.log("Visisted vertex: " + v);
        }
        for (var w of this.adj[v]) {
            if (!this.marked[w]) {
                this.edgeTo[w] = v;
                this.marked[w] = true;
                queue.push(w);
            }
        }
    }
}

function dfs(v) {
    this.marked[v] = true;
    if (this.adj[v] != undefined) {
        // console.log("Visited vertex: " + v);
    }
    for(var w of this.adj[v]) {
      // console.log(w);
        if (!this.marked[w]) {
            this.dfs(w);
        }
    }
}




module.exports = Graph;

// g = new Graph(5);
// g.addEdge(0, 1);
// g.addEdge(0,2);
// g.addEdge(1,3);
// g.addEdge(2,4);
// g.showGraph();
// g.dfs(0);
// console.log('bfs');
// g1 = new Graph(5);
// g1.addEdge(0, 1);
// g1.addEdge(0,2);
// g1.addEdge(1,3);
// g1.addEdge(2,4);
// g1.showGraph();
// g1.bfs(0);