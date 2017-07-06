/*

> 使用前面完成的 Deque 类来判断一个给定单词是否为回文。

**思路:**  
将字符串中的字符挨个推入双向队列中。然后每次弹出队列的首部元素和尾部元素进行比较。如果不相等，就说明不是回文。当双向队列中的元素只剩一个或一个不剩，循环结束。

 */


var Deque = require("./Deque.js");

    function isPalindrome(word){
        var deque = new Deque();
        var result =true; //是否是回文。默认为true，即是回文。
        for(var i=0;i<word.length;i++){
            deque.enterBackQueue(word[i]);
        }
        while(deque.count()>1){
            if(deque.delBackQueue() !== deque.delFrontQueue()){
                result = false;
                break;
            }
        }
       return result;
    }
    
    console.log(isPalindrome('word')); // false
    console.log(isPalindrome('pop')); // true
    console.log(isPalindrome('woow')); // true