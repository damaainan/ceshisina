“约瑟夫环”是一个数学的应用问题：一群猴子排成一圈，按1,2,…,n依次编号。然后从第1只开始数，数到第m只,把它踢出圈，从它后面再开始数， 再数到第m只，在把它踢出去…，如此不停的进行下去， 直到最后只剩下一只猴子为止，那只猴子就叫做大王。要求编程模拟此过程，输入m、n, 输出最后那个大王的编号。

下面列出了三种用PHP来解决此问题的方法：

1. 按逻辑依次去除
1. 递归算法
1. 线性表应用

## 方法一：按逻辑依次去除

    
```php
<?php
function getKingMokey($n, $m)
{
    $monkey[0] = 0;
    //将1-n只猴子顺序编号 入数组中
    for($i= 1; $i<= $n; $i++) 
    { 
        $monkey[$i] = $i;
    }
    $len = count($monkey);
    //循环遍历数组元素(猴子编号)
    for($i= 0; $i< $len; $i= $i)
    {
        $num = 0;
        /*
         * 遍历$monkey数组，计算数组中值不为0的元素个数（剩余猴子的个数）
         * 赋值为$num，并获取值不为0的元素的元素值
        */
        foreach($monkey as $key => $value) 
        { 
           if($value == 0) continue; 
           $num++; 
           $values = $value; 
        }
        //若只剩一只猴子 则输出该猴子编号(数组元素值) 并退出循环 
        if($num == 1) 
        { 
            return $values;
            exit; 
        }
        /* 
         * 若剩余猴子数大于1（$num > 1） 
         * 继续程序 
        */
        //将第$i只猴子踢出队伍(相应数组位置元素值设为0) 
        $monkey[$i] = 0;
        /*
         * 获取下一只需要踢出队伍的猴子编号
         * 在$m值范围内遍历猴子 并设置$m的计数器
         * 依次取下一猴子编号
         * 若元素值为0，则该位置的猴子已被踢出队伍
         * 若不为0，继续获取下一猴子编号，且计数器加1
         * 若取得的猴子编号大于数组个数
         * 则从第0只猴子开始遍历(数组指针归零) 步骤同上
         * 直到计数器到达$m值 * 最后获取的$i值即为下一只需要踢出队伍的猴子编号
         */
        //设置计数器 
        for($j= 1; $j<= $m; $j++) 
        { 
            //猴子编号加一，遍历下一只猴子 
            $i++;
            //若该猴子未被踢出队伍,获取下一只猴子编号 
            if($monkey[$i] > 0) continue;
            //若元素值为0，则猴子已被踢出队伍，进而循环取下一只猴子编号 
            if($monkey[$i] == 0) 
            { 
                //取下一只猴子编号 
                for($k= $i; $k< $len; $k++)
                { 
                    //值为0，编号加1 
                    if($monkey[$k] == 0) $i++;
                    //否则，编号已取得，退出 
                    if($monkey[$k] > 0) break;
                } 
             }
            //若编号大于猴子个数，则从第0只猴子开始遍历(数组指针归零) 步骤同上 
            if($i == $len) $i = 0;
            //同上步骤，获取下一只猴子编号
            if($monkey[$i] == 0) 
            { 
                for($k= $i; $k< $len; $k++) 
                {
                    if($monkey[$k] == 0) $i++;
                    if($monkey[$k] > 0) break;
                } 
            } 
        }
    }
}
//猴子个数 
$n = 10;
//踢出队伍的编号间隔值 
$m = 3;
//调用猴王获取函数
echo getKingMokey($n, $m)."是猴王";
```

## 方法二：递归算法

    
```php
<?php
function killMonkey($monkeys , $m , $current = 0){
    $number = count($monkeys);
    $num = 1;
    if(count($monkeys) == 1){
        echo $monkeys[0]."成为猴王了";
        return;
    }
    else{
        while($num++ < $m){
            $current++ ;
            $current = $current%$number;
        }
        echo $monkeys[$current]."的猴子被踢掉了<br/>";
        array_splice($monkeys , $current , 1);
        killMonkey($monkeys , $m , $current);
    }
}
$monkeys = array(1 , 2 , 3 , 4 , 5 , 6 , 7, 8 , 9 , 10); //monkeys的编号
$m = 3; //数到第几只猴子被踢出
killMonkey($monkeys , $m);
```
## 方法三：线性表应用

最后这个算法最牛，有网友给了解释：  
哦，是这样的，每个猴子出列后，剩下的猴子又组成了另一个子问题。只是他们的编号变化了。第一个出列的猴子肯定是a[1]=m(mod)n(m/n的余数)，他除去后剩下的猴子是a[1]+1,a[1]+2,…,n,1,2,…a[1]-2,a[1]-1，对应的新编号是1,2,3…n-1。设此时某个猴子的新编号是i，他原来的编号就是(i+a[1])%n。于是，这便形成了一个递归问题。假如知道了这个子问题(n-1个猴子)的解是x，那么原问题(n个猴子)的解便是：(x+m%n)%n=(x+m)%n。问题的起始条件：如果n=1,那么结果就是1。

    
```php
<?php
function yuesefu($n,$m) {
    $r=0;
    for($i=2; $i<=$n; $i++) {
        $r=($r+$m)%$i;
    }
    return $r+1;
}
echo yuesefu(10,3)."是猴王";
```

## 方法四：
```php
<?php
function get_king_mokey($n, $m) 
{
    $arr = range(1, $n);
    $i = 0;
    while (count($arr) > 1) {
        $i++;
        $survice = array_shift($arr);
        if ($i % $m != 0) {
            array_push($arr, $survice);
        }
    }
    return $arr[0];
}
```
