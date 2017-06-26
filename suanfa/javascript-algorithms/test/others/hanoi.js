var hanoi = require('../../src/others/hanoi').hanoi;
var movements = hanoi(3, 'a', 'b', 'c');

// Move a to c
// Move a to b
// Move c to b
// Move a to c
// Move b to a
// Move b to c
// Move a to c
movements.forEach(function (move) {
  console.log('Move', move[0], 'to', move[1]);
});