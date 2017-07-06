/*

> 修改 Queue 类，形成一个 Deque 类。这是一个和队列类似的数据结构，允许从队列两端 添加和删除元素，因此也叫双向队列。写一段测试程序测试该类

 */


var Deque = require("./Deque.js");


    var d = new Deque();
    d.enterBackQueue('a');
    console.log(d.dataStore);
    d.enterBackQueue('b');
    console.log(d.dataStore);
    d.enterFrontQueue('c');
    console.log(d.dataStore);
    d.enterFrontQueue('d');
    console.log(d.dataStore);
    d.enterFrontQueue('e');
    console.log(d.dataStore);
    d.enterBackQueue('f');
    
    console.log(d.dataStore);
    
    d.delBackQueue();
    console.log(d.dataStore);
    d.delFrontQueue();
    console.log(d.dataStore);