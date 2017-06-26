var QuickFind = require('../../src/sets/quickfind').QuickFind;

var qfind = new QuickFind(10);
qfind.union(0, 1);
qfind.union(2, 1);
qfind.union(3, 4);
qfind.union(8, 9);
qfind.union(4, 8);

console.log(qfind.connected(0, 9)); // false
console.log(qfind.connected(3, 9)); // true