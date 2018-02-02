## 正则匹配 css 属性名

```php
$str=file_get_contents("1.css");

preg_match_all('/[\{|;]\s{0,1}([a-z-]*)[^:\};\s]/', $str, $matches);

// var_dump($matches[0]);
$s=$matches[0];
foreach ($s as &$value) {
    $value = str_replace(array(' ',';','{'),array('','',''),$value);
}
$stt=implode($s,',');
var_dump($stt);
```

```php

$str = "123456789";
$str = preg_replace('/(?!^)(?=(\d{3})+$)/', ',',$str);
echo $str;
echo "\n*****\n";

$str = "12345678 123456789";
$str = preg_replace('/(?!\b)(?=(\d{3})+\b)/', ',',$str);
echo $str;
echo "\n*****\n";

$str = "12345678 123456789";
$str = preg_replace('/\B(?=(\d{3})+\b)/', ',',$str);
echo $str;
```

