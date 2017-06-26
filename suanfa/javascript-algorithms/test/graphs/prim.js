var Prim = require('../../src/graphs/spanning-trees/prim');
var Graph = Prim.Graph;
var Edge = Prim.Edge;
var Vertex = Prim.Vertex;

var graph, edges = [];
edges.push(new Edge(new Vertex(0), new Vertex(1), 4));
edges.push(new Edge(new Vertex(0), new Vertex(7), 8));
edges.push(new Edge(new Vertex(1), new Vertex(7), 11));
edges.push(new Edge(new Vertex(1), new Vertex(2), 8));
edges.push(new Edge(new Vertex(2), new Vertex(8), 2));
edges.push(new Edge(new Vertex(2), new Vertex(3), 7));
edges.push(new Edge(new Vertex(2), new Vertex(5), 4));
edges.push(new Edge(new Vertex(2), new Vertex(3), 7));
edges.push(new Edge(new Vertex(3), new Vertex(4), 9));
edges.push(new Edge(new Vertex(3), new Vertex(5), 14));
edges.push(new Edge(new Vertex(4), new Vertex(5), 10));
edges.push(new Edge(new Vertex(5), new Vertex(6), 2));
edges.push(new Edge(new Vertex(6), new Vertex(8), 6));
edges.push(new Edge(new Vertex(8), new Vertex(7), 7));
graph = new Graph(edges, edges.length);

// { edges:
//    [ { e: '1', v: 0, distance: 4 },
//      { e: '2', v: 8, distance: 2 },
//      { e: '3', v: 2, distance: 7 },
//      { e: '4', v: 3, distance: 9 },
//      { e: '5', v: 2, distance: 4 },
//      { e: '6', v: 5, distance: 2 },
//      { e: '7', v: 0, distance: 8 },
//      { e: '8', v: 7, distance: 7 } ],
//   nodesCount: 0 }
var spanningTree = graph.prim();