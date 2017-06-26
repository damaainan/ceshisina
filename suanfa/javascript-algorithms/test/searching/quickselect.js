var quickselect = require('../../src/searching/'+
'quickselect').quickselect;
var result = quickselect([5, 1, 2, 2, 0, 3], 1, 0, 5);
console.log(result); // 1