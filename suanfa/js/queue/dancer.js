var Queue = require("./queue");
var fs = require("fs");

function Dancer(name, sex) {
    this.name = name;
    this.sex = sex;
}

function getDancers(males, females) {
    var names = fs.readFileSync("dancers.txt", "utf-8").split("\n");
    for (var i = 0; i < names.length; ++i) {
        names[i] = names[i].trim();
    }
    for (var j = 0; j < names.length; ++j) {
        var dancer = names[j].split(" ");
        var sex = dancer[0];
        var name = dancer[1];
        if (sex == "F") {
            females.enqueue(new Dancer(name, sex));
        } else {
            males.enqueue(new Dancer(name, sex));
        }
    }
}

function dance(males, females) {
    console.log("The dance partners are: \n");
    while (!females.empty() && !males.empty()) {
        person = females.dequeue();
        console.log("Female dancer is: " + person.name);
        person = males.dequeue();
        console.log(" and the male dancer is: " + person.name);
    }
    console.log();
}

// 测试程序
var maleDancers = new Queue();
var femaleDancers = new Queue();
getDancers(maleDancers, femaleDancers);
dance(maleDancers, femaleDancers);
if (maleDancers.count() > 0) {
    console.log("There are " + maleDancers.count() +
        " male dancers waiting to dance.");
}
if (femaleDancers.count() > 0) {
    console.log("There are " + femaleDancers.count() +
        " female dancers waiting to dance.");
}



//
