/*

> 现实生活中栈的一个例子是佩兹糖果盒。想象一下你有一盒佩兹糖果，里面塞满了红 色、黄色和白色的糖果，但是你不喜欢黄色的糖果。使用栈(有可能用到多个栈)写一 段程序，在不改变盒内其他糖果叠放顺序的基础上，将黄色糖果移出。

**思路**

除了糖果盒，还需要俩个栈。一个用来存放黄色糖果，一个用来存放其他糖果。首先将糖果盒中的糖一个个弹出，压入目标栈中。当糖果盒中的糖全都弹出后，把存放其他糖果的栈中的糖一个个弹出，压入糖果盒中，这样就得到想要的结果了。


 */
var Stack = require("./Stack.js");

    // 假设盒子中放着这些糖果
    var sweetBox = new Stack();
    
    sweetBox.push('yellow');
    sweetBox.push('white');
    sweetBox.push('yellow');
    sweetBox.push('white');
    sweetBox.push('red');
    sweetBox.push('red');
    sweetBox.push('yellow');
    sweetBox.push('red');
    sweetBox.push('yellow');
    sweetBox.push('white');
    
    console.log(sweetBox.dataStore);
    
    function selectYellow(){
        var yellow  = new Stack(); //放置黄色糖果
        var other = new Stack(); //放置其他糖果
        var one = '';
        while(sweetBox.length()>0){
        var one = sweetBox.pop();
        if(one === 'yellow'){
            yellow.push(one);
        }else{
            other.push(one);
        }
    }
    while(other.length()>0){
        sweetBox.push(other.pop());
    }
        return sweetBox;
    }
    console.log(selectYellow().dataStore);