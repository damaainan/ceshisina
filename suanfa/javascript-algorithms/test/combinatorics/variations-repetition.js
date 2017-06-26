var variations = require('../../src/combinatorics/' +
'variations-repetition').variationsWithRepetion;
var result = variations(['apple', 'orange', 'pear'], 2);

// [['apple', 'apple'],
//  ['apple', 'orange'],
//  ['apple', 'pear'],
//  ['orange', 'apple'],
//  ['orange', 'orange'],
//  ['orange', 'pear'],
//  ['pear', 'apple'],
//  ['pear', 'orange'],
//  ['pear', 'pear']]
console.log(result);