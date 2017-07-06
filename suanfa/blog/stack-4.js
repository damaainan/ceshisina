

/*


用栈来判断一个表达式中的括号（仅有一种括号,小、中或大括号）是否配对.编写并实现它的算法.

 */
var Stack = require("./Stack.js");
    function isMatch(str){
    var s = new Stack;
    var bracket = '';
    for(var i=0;i<str.length;i++){
        bracket = str[i];
        if(bracket === "(" || bracket === "[" || bracket === "{"){
            s.push(bracket)
        }else if(bracket === ")" || bracket === "]" || bracket === "}") {
            if(s.length()>0){
                s.pop()
            }else {
                return '括号不匹配';
            }
        }
      }
      if(s.length()>0){
          return '括号不匹配'
      }
        return '匹配';
    }
    console.log(isMatch('1+2*(2+1')); // 括号不匹配
    console.log(isMatch('1+2*(2+1)')); // 匹配
    console.log(isMatch('1+2*(2+1)+(2*2+1')); // 括号不匹配
    console.log(isMatch('1+2*(2+1)+(2*2+1)')); // 匹配