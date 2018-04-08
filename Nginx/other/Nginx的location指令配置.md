## Nginx的location指令配置

来源：[http://blog.7rule.com/2018/02/20/nginx-location.html](http://blog.7rule.com/2018/02/20/nginx-location.html)

时间 2018-02-20 22:16:54


在编写nginx配置中，正确的配置location指令是相当重要的。笔者在一次问题的排查中仔细研究了下这个指令的规则和使用方法，在这里和大家分享一下，如有不正确的地方还望指正。


## 指令语法

location [ 限定符 ] 规则uri { 指令集 }

在一个请求的处理流程中，nginx会启动一个location的查找过程，在这个过程中nginx会根据一定的规则用请求的request_uri有选择的去匹配这些定义好的规则uri，之后会找到要应用的唯一的location，并应用这个location定义的指令集中的指令。

这里面最关键的地方，就是location的查找过程是如何进行的。要明白这个过程，首先需要知道规则uri是如何定义的。


## 规则uri

规则uri分为三种类型，nginx对这几种类型执行的匹配方法是不同的：


#### 1. 对限定符为`=`的规则执行完全匹配  

完全匹配例：location = index.php {}，那么仅有请求：http://domain/index.php才能匹配，http://domain/index.phpp或http://domain/iindex.php这种均无法匹配。


#### 2. 对限定符为`^~`或没有限定符规则执行前缀匹配。  

前缀匹配例：location /abc {}或location ^~ /abc {}，那么请求：http://domain/abcd、http://domain/abc.php这种均能匹配。


#### 3. 对限定符为`~`或`~*`的规则执行正则匹配。  

正则匹配例：location ~ .php$ {}，那么请求：http://domain/abc.php、http://domain/abcd.php这种均可以匹配。


## location的查找过程

首先会对所有的`等号规则`和`前缀规则`进行一次匹配筛选。nginx会在所有的前缀匹配中找到一个`最长的`匹配，然后记住这个匹配的location。之后会根据这个匹配规则是否有`限定符`来决定之后的行为：

```
等号规则
^~
正则规则

```

通过上面的查找过程，如果成功找到匹配的location，则会执行执行这个location定义的指令集中的指令。


## 实例说明

下面举几个例子来看一下：假设用户均访问http://www.vmc7.com/abc


#### 1.`=`命中，跳过正则匹配  

```nginx
server {
    listen 80;

    server_name www.vmc7.com;

    location /abc {}

    location = /abc {}

    location ~ /abc {}
}
```


#### 2.`^~`命中，跳过正则匹配  

```nginx
server {

    listen 80;

    server_name www.vmc7.com;

    location /ab {}

    location ^~ /abc {}

    location ~ /abc {}
}
```


#### 3.`正则`命中1，正则匹配优先于前缀匹配  

```nginx
server {
    listen 80;

    server_name www.vmc7.com;

    location /abc {}

    location ~ /ab.* {}
}
```


#### 4.`正则`命中2，正则按顺序匹配  

```nginx
server {
    listen 80;

    server_name www.vmc7.com;

    location /ab {}

    location ~ /ab.* {}
    location ~ /abc {}
}
```


#### 5.`前缀`命中，正则匹配不到，使用之前命中的前缀匹配  

```nginx
server {
    listen 80;

    server_name www.vmc7.com;

    location /abc {}

    location ~ /ef.* {}
}
```


## 最后附上伪代码用以描述整个过程

```nginx
location = findLocation();
if (location != null) {
    runLocationCmdSet();
}
 
function findLocation()
{
    finalLocation  = null;
    prefixLocation = null;
    regexLocation  = null;
 
    prefixLocation = findByPrefix();
    if (prefixLocation != null) {
        if (preceding(prefixLocation) == '=') {
            return prefixLocation;
        }

        if (preceding(prefixLocation) == '^~') {
            return prefixLocation;
        }
    }
 
    regexLocation = findByRegex();
    finalLocation = (regexLocation == null) ? prefixLocation : regexLocation;
 
    return finalLocation;
}
 
function findByPrefix()
{
    result    = null;
    maxLength = 0;
 
    for (location in allPrefixLocation) {
        if ((matchLength = prefixMatch(location))!= 0) {
            if (matchLength > maxLength) {
                maxLength = matchLength;
                result    = location;
            }
        }
    }
 
    return result;
}
 
function findByRegex()
{
    for (location in allRegexLocation) {
        if (regexMatch(location)) {
            return location;
        }
    }
 
    return null;
}
```


