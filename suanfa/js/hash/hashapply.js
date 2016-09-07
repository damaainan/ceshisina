var Hash1 = require("./hash1");
var Hash2 = require("./hash2");
// var Hash3 = require("./hash3");


var someNames = ["David", "Jennifer", "Donnie", "Raymond",
    "Cynthia", "Mike", "Clayton", "Danny", "Jonathan"
];
var hTable1 = new Hash1();
for (var i = 0; i < someNames.length; ++i) {
    hTable1.put(someNames[i]);
}
hTable1.showDistro();

console.log("***********************************");
var someNames1 = ["David", "Jennifer", "Donnie", "Raymond",
    "Cynthia", "Mike", "Clayton", "Danny", "Jonathan"
];
var hTable2 = new Hash2();
for (var j = 0; j < someNames1.length; ++j) {
    hTable2.put(someNames1[j]);
}
hTable2.showDistro();
