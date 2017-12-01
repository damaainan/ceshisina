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