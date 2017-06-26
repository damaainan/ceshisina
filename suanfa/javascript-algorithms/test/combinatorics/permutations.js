var permutations = require('../../src/' +
'combinatorics/permutations').permutations;
var result = permutations(['apple', 'orange', 'pear']);

// [ [ 'apple', 'orange', 'pear' ],
//   [ 'apple', 'pear', 'orange' ],
//   [ 'orange', 'apple', 'pear' ],
//   [ 'orange', 'pear', 'apple' ],
//   [ 'pear', 'orange', 'apple' ],
//   [ 'pear', 'apple', 'orange' ] ]
console.log(result);