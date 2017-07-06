/*
> 使用栈来实现阶乘函数

**思路：**

首先将数字从 num 到 1 压入栈，然后使用一个循环，将数字挨  
个弹出连乘，就得到了正确的答案

 */
var Stack = require("./Stack.js");
    function factorial(num){
      var s = new Stack();
      while(num>1){
          s.push(num--);
      }
      var product = 1;
      while(s.length()>0){
          product *= s.pop();
      }
      return product;
    }
    console.log(factorial(5));   //120