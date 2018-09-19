## 如何理解RESTful的幂等性

来源：[http://blog.720ui.com/2016/restful_idempotent/](http://blog.720ui.com/2016/restful_idempotent/)

时间 2016-10-12 19:50:00


理解RESTful的幂等性，并且设计符合幂等规范的高质量RESTful API。


## 怎么理解幂等性

HTTP幂等方法，是指无论调用多少次都不会有不同结果的 HTTP 方法。不管你调用一次，还是调用一百次，一千次，结果都是相同的。

还是以之前的博文的例子为例。

```
GET     /tickets       # 获取ticket列表
GET     /tickets/12    # 查看某个具体的ticket
POST    /tickets       # 新建一个ticket
PUT     /tickets/12    # 更新ticket 12
PATCH   /tickets/12    # 更新ticket 12
DELETE  /tickets/12    # 删除ticekt 12
```


### HTTP GET方法

HTTP GET方法，用于获取资源，不管调用多少次接口，结果都不会改变，所以是幂等的。

```
GET     /tickets       # 获取ticket列表
GET     /tickets/12    # 查看某个具体的ticket
```

只是查询数据，不会影响到资源的变化，因此我们认为它幂等。

值得注意，幂等性指的是作用于结果而非资源本身。怎么理解呢？例如，这个HTTP GET方法可能会每次得到不同的返回内容，但并不影响资源。

可能你会问有这种情况么？当然有咯。例如，我们有一个接口获取当前时间，我们就应该设计成

```
GET     /service_time # 获取服务器当前时间
```

它本身不会对资源本身产生影响，因此满足幂等性。


### HTTP POST方法

HTTP POST方法是一个非幂等方法，因为调用多次，都将产生新的资源。

```
POST    /tickets       # 新建一个ticket
```

因为它会对资源本身产生影响，每次调用都会有新的资源产生，因此不满足幂等性。


### HTTP PUT方法

HTTP PUT方法是不是幂等的呢？我们来看下

```
PUT     /tickets/12    # 更新ticket 12
```

因为它直接把实体部分的数据替换到服务器的资源，我们多次调用它，只会产生一次影响，但是有相同结果的 HTTP 方法，所以满足幂等性。


### HTTP PATCH方法

HTTP PATCH方法是非幂等的。HTTP POST方法和HTTP PUT方法可能比较好理解，但是HTTP PATCH方法只是更新部分资源，怎么是非幂等的呢?

因为，PATCH提供的实体则需要根据程序或其它协议的定义，解析后在服务器上执行，以此来修改服务器上的资源。换句话说，PATCH请求是会执行某个程序的，如果重复提交，程序可能执行多次，对服务器上的资源就可能造成额外的影响，这就可以解释它为什么是非幂等的了。

可能你还不能理解这点。我们举个例子

```
PATCH   /tickets/12    # 更新ticket 12
```

此时，我们服务端对方法的处理是，当调用一次方法，更新部分字段，将这条ticket记录的操作记录加一，这次，每次调用的资源是不是变了呢，所以它是有可能是非幂等的操作。


### HTTP DELETE方法

HTTP DELETE方法用于删除资源，会将资源删除。

```
DELETE  /tickets/12    # 删除ticekt 12
```

调用一次和多次对资源产生影响是相同的，所以也满足幂等性。


## 如何设计符合幂等性的高质量RESTful API


### HTTP GET方法 vs HTTP POST方法

也许，你会想起一个面试题。HTTP请求的GET与POST方式有什么区别？你可能会回答到：GET方式通过URL提交数据，数据在URL中可以看到；POST方式，数据放置在HTML HEADER内提交。但是，我们现在从RESTful的资源角度来看待问题，HTTP GET方法是幂等的，所以它适合作为查询操作，HTTP POST方法是非幂等的，所以用来表示新增操作。

但是，也有例外，我们有的时候可能需要把查询方法改造成HTTP POST方法。比如，超长（1k）的GET URL使用POST方法来替代，因为GET受到URL长度的限制。虽然，它不符合幂等性，但是它是一种折中的方案。


### HTTP POST方法 vs HTTP PUT方法

对于HTTP POST方法和TTP PUT方法，我们一般的理解是POST表示创建资源，PUT表示更新资源。当然，这个是正确的理解。

但是，实际上，两个方法都用于创建资源，更为本质的差别是在幂等性。HTTP POST方法是非幂等，所以用来表示创建资源，HTTP PUT方法是幂等的，因此表示更新资源更加贴切。


### HTTP PUT方法 vs HTTP PATCH方法

此时，你看会有另外一个问题。HTTP PUT方法和HTTP PATCH方法，都是用来表述更新资源，它们之间有什么区别呢？我们一般的理解是PUT表示更新全部资源，PATCH表示更新部分资源。首先，这个是我们遵守的第一准则。根据上面的描述，PATCH方法是非幂等的，因此我们在设计我们服务端的RESTful API的时候，也需要考虑。如果，我们想要明确的告诉调用者我们的资源是幂等的，我的设计更倾向于使用HTTP PUT方法。

