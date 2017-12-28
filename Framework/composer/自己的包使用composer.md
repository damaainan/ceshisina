`composer.json` 加入如下代码 

```
"type": "project",
    "autoload": {  
        "psr-4": {  
            "命名空间\\":"文件夹名",
            "Tools\\": "lib/",
            "Tools\\lib\\": "lib/library/"
        }  
    }
```

对应的文件中加入 `namespace Tools;` 

文件名和类名需要对应，大小写不敏感

文件名 为 `name.php` 形式 `name.class.php` 报错，暂未解决

### 使用

**最前 `\` 不带也可以**

两种使用方式  
use 方式（需带类名）  

    use \Tools\replaceElement;
    $re = new replaceElement();


或者 直接使用

    $re = new \Tools\replaceElement();
    var_dump($re);