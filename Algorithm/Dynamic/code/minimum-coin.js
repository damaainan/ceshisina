var MinCoinChange1 = require('./minimum-coin-dy.js');
var MinCoinChange2 = require('./minimum-coin-greedy.js');

var arr = [1, 3, 4];
var NUM = 10000;
var AMOUT = 6;

var mcc1 = new MinCoinChange1(arr);
var mcc2 = new MinCoinChange2(arr);


console.time('动态规划');
for (var i = 0; i < NUM; i++) {
  mcc1.makeChange(AMOUT);
}
console.timeEnd('动态规划');


console.time('贪心算法');
for (var i = 0; i < NUM; i++) {
  mcc2.makeChange(AMOUT);
}
console.timeEnd('贪心算法');