// var readline = require('readline');

// var rl = readline.createInterface({
//   input: process.stdin,
//   output: process.stdout
// });
// console.log(rl);
// rl.question("Enter a name to search for: ", function(name) {
//     var names = ["David", "Cynthia", "Raymond", "Clayton", "Jennifer"];
//     var position = names.indexOf(name);
//     if (position >= 0) {
//         console.log("Found " + name + " at position " + position);
//     } else {
//         console.log(name + " not found in array.");
//     }
//     rl.close();
// });

function Graph(v) {
    this.vertices = v;
    this.edges = 0;
    this.adj = [];
    for (var i = 0; i < this.vertices; ++i) {
        this.adj[i] = [];
        this.adj[i].push("");
    }
    this.addEdge = addEdge;
    this.showGraph = showGraph;
}

function addEdge(v, w) {
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

g = new Graph(5);
g.addEdge(0,1);
g.addEdge(0,2);
g.addEdge(1,3);
g.addEdge(2,4);
g.showGraph();