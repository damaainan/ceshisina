/*function Graph(v) {
    this.vertices = v;
    this.edges = 0;
    this.adj = [];
    for (var i = 0; i < this.vertices; ++i) {
        this.adj[i] = [];
        this.adj[i].push("");
    }
    this.addEdge = addEdge;
    this.showGraph = showGraph;
    this.dfs = dfs;
    this.bfs = bfs;
    this.marked = [];
    for (var i = 0; i < this.vertices; ++i) {
        this.marked[i] = false;
    }
}

function addEdge(v, w) {
    this.adj[v].push(w);
    this.adj[w].push(v);
    this.edges++;
}

// function showGraph() {
//     for (var i = 0; i < this.vertices; ++i) {
//         putstr(i + " -> ");
//         for (var j = 0; j < this.vertices; ++j) {
//             if (this.adj[i][j] != undefined)
//                 putstr(this.add[i][j] + ' ');
//         }
//         print();
//     }
// }
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

function dfs(v) {
    this.marked[v] = true;
    if (this.adj[v] != undefined) {
        console.log("Visited vertex: " + v);
    }
    for(var w in this.adj[v]) {
        // console.log(w);
        if (!this.marked[w]) {
            this.dfs(w);
        }
    }
}

function bfs(s) {
    var queue = [];
    this.marked[s] = true;
    queue.push(s); // 添加到队尾
    while (queue.length > 0) {
        var v = queue.shift(); // 从队首移除
        if (v == undefined) {
            console.log("Visisted vertex: " + v);
        }
        for(var w in this.adj[v]) {
            if (!this.marked[w]) {
                this.edgeTo[w] = v;
                this.marked[w] = true;
                queue.push(w);
            }
        }
    }
}*/



function Graph(v) {
   this.vertices = v;
   this.vertexList = [];
   this.edges = 0;
   this.adj = [];
   for (var i = 0; i < this.vertices; ++i) {
      this.adj[i] = [];
      this.adj[i].push("");
   }
   this.addEdge = addEdge;
   this.showGraph = showGraph;
   this.dfs = dfs;
   this.marked = [];
   for (var i = 0; i < this.vertices; ++i) {
      this.marked[i] = false;
   }
   this.bfs = bfs;
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
      if (stack[i] != undefined && stack[i] != false) {
         console.log(this.vertexList[stack[i]]);
      } 
   }
}

function topSortHelper(v, visited, stack) {
   visited[v] = true; 
   for each (var w in this.adj[v]) {
      if (!visited[w]) {
         this.topSortHelper(visited[w], visited, stack);
      }
   }
   stack.push(v);
}

function addEdge(v,w) {
   this.adj[v].push(w);
   this.adj[w].push(v);
   this.edges++;
}

/*function showGraph() {
   for (var i = 0; i < this.vertices; ++i) {
      putstr(i + " -> ");
      for (var j = 0; j < this.vertices; ++j) {
         if (this.adj[i][j] != undefined)
            putstr(this.adj[i][j] + ' ');
      }
      print();
   }
}*/

// a new function to display symbolic names instead of numbers
function showGraph() {
   var visited = [];
   for (var i = 0; i < this.vertices; ++i) {
      putstr(this.vertexList[i] + " -> ");
      visited.push(this.vertexList[i]);
      for (var j = 0; j < this.vertices; ++j) {
         if (this.adj[i][j] != undefined) {
            if (visited.indexOf(this.vertexList[j]) < 0) {
               putstr(this.vertexList[j] + ' ');
            }
         }
      }
      print();
      visited.pop();
   }
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




function dfs(v) {
   this.marked[v] = true;
   if (this.adj[v] != undefined) { 
      console.log("Visited vertex: " + v);
   }
   for each (var w in this.adj[v]) {
      if (!this.marked[w]) {
         this.dfs(w);
      }
   }
}

function bfs(s) {
   var queue = [];
   this.marked[s] = true;
   queue.unshift(s);
   while (queue.length > 0) {
      var v = queue.shift();
      if (typeof(v) != "string") {
         console.log("Visited vertex: " + v);
      }
      for each (var w in this.adj[v]) {
         if (!this.marked[w]) {
            this.edgeTo[w] = v;
            this.marked[w] = true;
            queue.unshift(w);
         }
      }
   }
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
   path.push(s);
   return path;
}




g = new Graph(5);
g.addEdge(0, 1);
g.addEdge(0, 2);
g.addEdge(1, 3);
g.addEdge(2, 4);
g.showGraph();
g.bfs(0);