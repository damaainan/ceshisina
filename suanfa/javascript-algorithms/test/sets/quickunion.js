var QuickUnion = require('../../' +
'src/sets/quickunion').QuickUnion;

var qunion = new QuickUnion(10);
qunion.union(0, 1);
qunion.union(2, 1);
qunion.union(3, 4);
qunion.union(8, 9);
qunion.union(4, 8);

console.log(qunion.connected(0, 9)); // false
console.log(qunion.connected(3, 9)); // true