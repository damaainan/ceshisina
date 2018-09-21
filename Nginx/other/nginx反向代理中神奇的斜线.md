## nginx反向代理中神奇的斜线

来源：[http://www.cnblogs.com/songguo/p/9564111.html](http://www.cnblogs.com/songguo/p/9564111.html)

时间 2018-08-31 09:57:00



## nginx反向代理中神奇的斜线

在进行nginx反向代理配置的时候，location和proxy_pass中的斜线会造成各种困扰，有时候多一个或少一个斜线，就会造成完全不同的结果，所以特地将location和proxy_pass后有无斜线的情况进行了排列组合，进行了一次完整的测试，找出原理，以提高姿势水平~


### 〇. 环境信息

两台nginx服务器

nginx A: 192.168.1.48

nginx B: 192.168.1.56


### 一. 测试方法


* 在nginx A中配置不同的规则，然后请求nginx A：  [http://192.168.1.48/foo/api][0]
    
* 观察nginx B收到的请求，具体操作是查看日志中的$request字段
  


### 二. 测试过程及结果


#### 案例1

nginx A配置：

```nginx
location /foo/ {
   proxy_pass http://192.168.1.56/;
}
# nginx B收到的请求：/api
```


#### 案例2

nginx A配置：

```nginx
location /foo/ {
   proxy_pass http://192.168.1.56/;
}
# nginx B收到的请求：//api
```


#### 案例3

nginx A配置：

```nginx
location /foo/ {
   proxy_pass http://192.168.1.56/;
}
# nginx B收到的请求：/foo/api
```


#### 案例4

nginx A配置：

```nginx
location /foo/ {
   proxy_pass http://192.168.1.56/;
}
# nginx B收到的请求：/foo/api
```


#### 案例5

nginx A配置：

```nginx
location /foo/ {
   proxy_pass http://192.168.1.56/bar/;
}
# nginx B收到的请求：/bar/api
```


#### 案例6

nginx A配置：

```nginx
location /foo {
   proxy_pass http://192.168.1.56/bar/;
}
# nginx B收到的请求：/bar//api
```


#### 案例7

nginx A配置：

```nginx
location /foo/ {
   proxy_pass http://192.168.1.56/bar;
}
# nginx B收到的请求：/barapi
```


#### 案例8

nginx A配置：

```nginx
location /foo {
   proxy_pass http://192.168.1.56/bar;
}
# nginx B收到的请求：/bar/api
```

看到这里是不是都晕了呢，其实是有规律的

现在把这些案例按表格排列起来，结果表示nginx B收到的请求

表一

| 案例 | location | proxy_pass | 结果 |
| - | - | - | - |
| 1 | /foo/ | [http://192.168.1.48/][1] | /api |
| 2 | /foo | [http://192.168.1.48/][1] | //api |
| 3 | /foo/ | [http://192.168.1.48][3] | /foo/api |
| 4 | /foo | [http://192.168.1.48][3] | /foo/api |
  

表二

| 案例 | location | proxy_pass | 结果 |
| - | - | - | - |
| 5 | /foo/ | [http://192.168.1.48/bar/][5] | /bar/api |
| 6 | /foo | [http://192.168.1.48/bar/][5] | /bar//api |
| 7 | /foo/ | [http://192.168.1.48/bar][7] | /barapi |
| 8 | /foo | [http://192.168.1.48/bar][7] | /bar/api |
  


### 三. 解析

原请求路径：本文中统一为 "/foo/api"

`location`: 上面表格中的location列

`proxy_pass`：上面表格中的proxy_pass列

新请求路径：nginx将原请求路径处理过后的字符串

重点对`proxy_pass`进行分析，可以分为3种形式

然后按照`ip:port`后是否接了字符串归为2类，"/"也是字符串，因此1归为一类，2、3归为一类，下面对这两类情况进行说明

当 `proxy_pass` 的 `ip:port` 后未接字符串的时候，nginx 会将原请求路径原封不动地转交给下一站 nginx，如案例3和4

当 `proxy_pass` 的 `ip:port` 后接了字符串的时候，nginx 会将 location 从 原请求路径 中剔除，再将剩余的字符串拼接到 `proxy_pass` 后生成 新请求路径，然后将 新请求路径 转交给下一站nginx（上面一种情况实际上和这个是一样的，只不过剔除的字符串是空串~~）

举个最让人疑惑的例子：案例7。`proxy_pass` 的 `ip:port` 后接了字符串 "/bar"，因此将 location："/foo/" 从 原请求路径："/foo/api" 中剔除，变为"api"，再将"api"拼接到`proxy_pass`：[http://192.168.1.48/bar][7]
后生成了新请求url："[http://192.168.1.48/barapi][10]
"，因此下一站的nginx收到的请求就是 "/barapi"。

案例6：`proxy_pass` 的 `ip:port` 后接了字符串 "/bar/"，因此将 location："/foo" 从 原请求路径 "/foo/api" 中剔除，变为 "/api"，再将 "/api" 拼接到`proxy_pass`：[http://192.168.1.48/bar/][5]
后生成了 新请求路径："[http://192.168.1.48/bar//api][12]
"，因此下一站的nginx收到的请求就是 /bar//api。

其它的案例都可以以此类推，现在终于搞明白了，再也不用一头雾水。


[0]: http://192.168.1.48/foo/api
[1]: http://192.168.1.48/
[2]: http://192.168.1.48/
[3]: http://192.168.1.48/
[4]: http://192.168.1.48/
[5]: http://192.168.1.48/bar/
[6]: http://192.168.1.48/bar/
[7]: http://192.168.1.48/bar
[8]: http://192.168.1.48/bar
[9]: http://192.168.1.48/bar
[10]: http://192.168.1.48/barapi
[11]: http://192.168.1.48/bar/
[12]: http://192.168.1.48/bar//api