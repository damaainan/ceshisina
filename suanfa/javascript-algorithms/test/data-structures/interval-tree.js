var IT = require('../../src/data-structures/interval-tree');
var intervalTree = new IT.IntervalTree();

intervalTree.add([0, 100]);
intervalTree.add([101, 200]);
intervalTree.add([10, 50]);
intervalTree.add([120, 220]);

console.log(intervalTree.contains(150)); // true
console.log(intervalTree.contains(250)); // false
console.log(intervalTree.intersects([210, 310])); // true
console.log(intervalTree.intersects([310, 320])); // false