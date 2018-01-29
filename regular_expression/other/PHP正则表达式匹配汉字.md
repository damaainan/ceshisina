


```
$str = "php编程";
if (preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$str)) {
print("该字符串全部是中文");
} else {
print("该字符串不全部是中文");
}

```