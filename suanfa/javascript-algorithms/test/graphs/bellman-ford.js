var BellmanFord =
   require('../../src/graphs/shortest-path/bellman-ford');
var Edge = BellmanFord.Edge;
var bellmanFord = BellmanFord.bellmanFord;
var edges = [];
var vertexes = [
  new Vertex(0),
  new Vertex(1),
  new Vertex(2),
  new Vertex(3),
  new Vertex(4)
];

edges.push(new Edge(0, 1, -1));
edges.push(new Edge(0, 2, 4));
edges.push(new Edge(1, 2, 3));
edges.push(new Edge(1, 3, 2));
edges.push(new Edge(3, 1, 1));
edges.push(new Edge(4, 3, -3));
edges.push(new Edge(1, 4, 2));
edges.push(new Edge(3, 2, 5));

// {
//   parents:   { '0': null, '1':  0, '2': 1, '3':  4, '4': 1 },
//   distances: { '0': 0,    '1': -1, '2': 2, '3': -2, '4': 1 }
// }
var pathInfo = bellmanFord(vertexes, edges, 0);