var array = [1, 2, 3, 1, 4, 5, 6];
var merge =
   require('../../src/sorting/mergesort').merge;
merge(array, function (a, b) {  // [1, 1, 2, 3, 4, 5, 6]
 return a - b;
}, 0, 4, 7);