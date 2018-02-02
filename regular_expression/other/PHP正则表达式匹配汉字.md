

```php 
$str = "php编程";
if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$str)) {
    print("该字符串全部是中文");
} else {
    print("该字符串不全部是中文");
}

$ret = preg_match_all("/[\x{4e00}-\x{9fa5}]/u",$str,$match); //  匹配汉字内容并捕获存入 $match 
```

