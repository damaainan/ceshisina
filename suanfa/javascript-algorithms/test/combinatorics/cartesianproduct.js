var product = require('../../src/combinatorics/' +
'cartesianproduct').cartesianProduct;
var result = product([[1, 2, 3], [3, 2, 1]]);
// [ [ 1, 3 ],
//   [ 1, 2 ],
//   [ 1, 1 ],
//   [ 2, 3 ],
//   [ 2, 2 ],
//   [ 2, 1 ],
//   [ 3, 3 ],
//   [ 3, 2 ],
//   [ 3, 1 ] ]
console.log(result);