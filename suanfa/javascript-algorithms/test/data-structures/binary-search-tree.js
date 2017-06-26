var BST = require('../../src/data-structures/binary-search-tree');
var bst = new BST.BinaryTree();

bst.insert(2000);
bst.insert(1989);
bst.insert(1991);
bst.insert(2001);
bst.insert(1966);

var node = bst.find(1989);
console.log(node.value); // 1989

var minNode = bst.findMin();
console.log(minNode.value); // 1966

var maxNode = bst.findMax();
console.log(maxNode.value); //2001