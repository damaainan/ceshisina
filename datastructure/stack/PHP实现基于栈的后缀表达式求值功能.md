# PHP实现基于栈的后缀表达式求值功能

 时间 2017-11-10 12:32:56 

原文[http://www.linuxsight.com/blog/95663][1]


本文实例讲述了PHP实现基于栈的后缀表达式求值功能。分享给大家供大家参考，具体如下：

后缀表达式概述

后缀表达式，指的是不包含括号，运算符放在两个运算对象的后面，所有的计算按运算符出现的顺序，严格从左向右进行（不再考虑运算符的优先规则）。

实现代码：

```php
    <?php
    class Stack{
      public $stack;
      public $stack_top;
      public function __construct(){
        $this->stack=array();
        $this->stack_top=-1;
      }
      public function push($data){
        $this->stack[]=$data;
        $this->stack_top++;
      }
      public function pop(){
        if(!$this->is_empty())
        {
          $this->stack_top--;
          return array_pop($this->stack);
        }else
        {
          echo "stack is empty";
        }
      }
      public function is_empty(){
        if($this->stack_top==-1)
        return true;
      }
    }
    $string="1243-*+63/-";
    $arrs=str_split($string);
    echo var_export($arrs);
    $stack=new Stack();
    foreach($arrs as $arr){
      switch($arr){
        case "+":$one=$stack->pop();$two=$stack->pop();$temp=$two + $one;$stack->push($temp);break;
        case "-":$one=$stack->pop();$two=$stack->pop();$temp=$two - $one;$stack->push($temp);break;
        case "*":$one=$stack->pop();$two=$stack->pop();$temp=$two * $one;$stack->push($temp);break;
        case "/":$one=$stack->pop();$two=$stack->pop();$temp=$two / $one;$stack->push($temp);break;
        default:$stack->push($arr);
      }
    }
    echo $stack->pop();
    ?>
```
运行结果：

    array (
     0 => '1',
     1 => '2',
     2 => '4',
     3 => '3',
     4 => '-',
     5 => '*',
     6 => '+',
     7 => '6',
     8 => '3',
     9 => '/',
     10 => '-',
    )1


[1]: http://www.linuxsight.com/blog/95663
