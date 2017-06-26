var floydWarshall =
require('../../src/graphs/shortest-path/floyd-warshall').floydWarshall;
var distMatrix =
   [[Infinity, 7,        9,       Infinity,  Infinity, 16],
    [7,        Infinity, 10,       15,       Infinity, Infinity],
    [9,        10,       Infinity, 11,       Infinity, 2],
    [Infinity, 15,       11,       Infinity, 6,        Infinity],
    [Infinity, Infinity, Infinity, 6,        Infinity, 9],
    [16,       Infinity, 2,        Infinity, 9,        Infinity]];

// [ [ 0, 7, 9, 20, 20, 11 ],
//   [ 7, 0, 10, 15, 21, 12 ],
//   [ 9, 10, 0, 11, 11, 2 ],
//   [ 20, 15, 11, 0, 6, 13 ],
//   [ 20, 21, 11, 6, 0, 9 ],
//   [ 11, 12, 2, 13, 9, 0 ] ]
var shortestDists = floydWarshall(distMatrix);