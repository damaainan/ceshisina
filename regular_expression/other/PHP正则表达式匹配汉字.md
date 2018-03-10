

```php 
$str = "php编程";
if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$str)) {
    print("该字符串全部是中文");
} else {
    print("该字符串不全部是中文");
}

$ret = preg_match_all("/[\x{4e00}-\x{9fa5}]/u",$str,$match); //  匹配汉字内容并捕获存入 $match 
```

```
[\x{3002}\x{ff1b}\x{ff0c}\x{ff1a}\x{201c}\x{201d}\x{ff08}\x{ff09}\x{3001}\x{ff1f}\x{300a}\x{300b}]

匹配所有中文标点符号
。 ；  ， ： “ ”（ ） 、 ？ 《 》 


[\x{3002}] 。
[\x{ff1b}] ；
[\x{ff0c}] ，
[\x{ff1a}] ：
[\x{201c}] “
[\x{201d}] ”
[\x{ff08}] （
[\x{ff09}] ）
[\x{3001}] 、
[\x{ff1f}] ？
[\x{300a}] 《
[\x{300b}] 》
```