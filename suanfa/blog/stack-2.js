
/*
> 判断给定字符串是否是回文

**思路：**

从左往右把字符串的每一个字符，依次放入栈中，最后从栈的顶部往底部看去，就是一个反过来的字符串。我们只要依次将字符串弹出，组成一个新的字符串，与原来的字符串进行比较即可。相等的话，就是回文，反之，不是回文。

 */
var Stack = require("./Stack.js");
    function isPalindrome(word){
      var s = new Stack();
      for(var i=0;i<word.length;++i){
          s.push(word[i]);
      }
      var newWord = '';
      while(s.length()>0){
          newWord += s.pop();
      }
      if(newWord === word){
          return true;
      }else{
          return false;
      }
    }
    
    console.log(isPalindrome("racecar")); // true
    console.log(isPalindrome("hello")); // false