/*
> 使用队列来模拟跳方块舞的人。当 男男女女来到舞池，他们按照自己的性别排成两队。当舞池中有地方空出来时，选两个队 列中的第一个人组成舞伴。他们身后的人各自向前移动一位，变成新的队首。当一对舞伴 迈入舞池时，主持人会大声喊出他们的名字。当一对舞伴走出舞池，且两排队伍中有任意 一队没人时，主持人也会把这个情况告诉大家。

 */

var Queue = require("./Queue.js");

      // 模拟舞会成员
      var dancers = [
        'F Allison McMillan',
        'M Frank Opitz',
        'M Mason McMillan',
        'M Clayton Ruff',
        'F Cheryl Ferenback',
        'M Raymond Williams',
        'F Jennifer Ingram',
        'M Bryan Frazer',
        'M David Durr',
        'M Danny Martin',
        'F Aurora Adney'
    ];
    
     //舞者
    function Dancer(name, sex) {
      this.name = name;
      this.sex = sex;
    }
    
    // 对跳舞的成员按性别分组
    function getDancers(males, females) {
        var dancer,
            sex,
            name;
          for (var i = 0; i < dancers.length - 1; i++) {
            dancer = dancers[i].split(' ');
            sex = dancer[0];
            name = dancer[1];
            if (sex === 'F') {
                females.enqueue(new Dancer(name, sex));
            } else {
                males.enqueue(new Dancer(name, sex));
            }
        }
    }
    // 进入舞池跳舞
    function dance(males, females) {
        console.log('The dance partners are: \n');
        var person;
        while (!females.empty() && !males.empty()) {
            person = females.dequeue();
            console.log("Female dancer is: " + person.name);
            person = males.dequeue();
            console.log(" and the male dancer is: " + person.name);
        }
    }
    
    var maleDancers = new Queue();
    var femaleDancers = new Queue();
    getDancers(maleDancers, femaleDancers);
    dance(maleDancers, femaleDancers);
    
    if (!femaleDancers.empty()) {
        console.log(femaleDancers.front().name + " is waiting to dance.");
    }
    if (!maleDancers.empty()) {
        console.log(maleDancers.front().name + " is waiting to dance.");
    }