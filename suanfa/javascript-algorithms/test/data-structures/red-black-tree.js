var RBTree = require('../src/data-structures/red-black-tree').RBTree;
var rbTree = new RBTree();

rbTree.put(1981, {
  name: 'John',
  surname: 'Smith'
});
rbTree.put(2000, {
  name: 'Pavlo',
  surname: 'Popov'
});
rbTree.put(1989, {
  name: 'Garry',
  surname: 'Fisher'
});
rbTree.put(1990, {
  name: 'Derek',
  surname: 'Anderson'
});

console.log(rbTree.get(1989)); // { name: 'Garry', surname: 'Fisher' }