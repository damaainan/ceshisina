var SBTree = require('../src/data-structures/size-balanced-tree').SBTree;
var sbTree = new SBTree();

var treeNode = sbTree.push({
  name: 'John',
  surname: 'Smith'
});
sbTree.insert(0, {
  name: 'Pavlo',
  surname: 'Popov'
});
sbTree.insert(1, {
  name: 'Garry',
  surname: 'Fisher'
});
sbTree.insert(0, {
  name: 'Derek',
  surname: 'Anderson'
});

console.log(sbTree.get(0)); // { name: 'Derek', surname: 'Anderson' }