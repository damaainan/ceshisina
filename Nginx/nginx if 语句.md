## nginx if 语句

来源：[https://phpor.net/blog/post/9499](https://phpor.net/blog/post/9499)

时间 2018-08-16 10:35:57

* 不能多重判断，错误写法如：      

```nginx
if ($uri = "/abc" && $arg_var = "xyz") {
    return 200 "good";
}
```
    
* 不能嵌套，错误写法如：      

```nginx
if ($uri = "/abc") {
    if ($arg_var = "xyz" ) {
        return 200 "good";
    }
}
```

* 条件前件不能为字符串，只能为变量，错误写法如：      

```nginx
if ("$uri&&$arg_var" = "/abc&&xyz") {
    return 200 "good";
}
```

相对简单可行的办法：

```nginx
set $condition "$uri&&$arg_var";
if ($condition = "/abc&&xyz") {
    return 200 "good";
}
```

还有不太严谨的做法：

```nginx
if ($request_uri = "/abc?var=xyz") {
    return 200 "good";
}
```

因为对于多数程序来讲， `/abc?var=xyz`  与 `/abc?var=xyz&_=123456`   是一样的，但是后者就不能被不严谨的nginx写法匹配到

实际用例：

```nginx
set $changePassWordUrl "$uri&&$arg__fromURL";
if ($changePassWordUrl = "/hrm/HrmTab.jsp&&HrmResourcePassword") {
    return 302 "https://staff.beebank.com/password/forget";
}
```

