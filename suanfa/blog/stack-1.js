 
 /*
> 利用栈将一个数字从一种数制转换成另一种数制。假设想将数字 n 转换为以 b 为基数  
> 的数字，实现转换的算法如下。  
> (1) 最高位为 n % b，将此位压入栈。  
> (2) 使用 n/b 代替 n。  
> (3) 重复步骤 1 和 2，直到 n 等于 0，且没有余数。  
> (4) 持续将栈内元素弹出，直到栈为空，依次将这些元素排列，就得到转换后数字的字符  
> 串形式。  
> 此算法只针对基数为2～9的情况

  */
var Stack = require("./Stack.js");
    function mulBase(num, base) {
    var s = new Stack();
    do {
        s.push(num % base);
        num = Math.floor(num / base);
    } while (num > 0)
    var converted = "";
    while (s.length() > 0) {
        converted += s.pop();
    }
      return converted;
    }
    
    console.log(mulBase(32, 2)); // 100000