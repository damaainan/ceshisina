var combinations = require('../../src/' +
'combinatorics/combinations').combinations;
var result = combinations(['apple', 'orange', 'pear'], 2);
// [['apple', 'orange'],
//  ['apple', 'pear'],
//  ['orange', 'pear']]
console.log(result);