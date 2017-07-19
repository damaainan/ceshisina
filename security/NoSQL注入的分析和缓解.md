# NoSQL注入的分析和缓解

时间 2017-03-21 07:15:00  InfoQ

原文[http://www.infoq.com/cn/articles/nosql-injections-analysis][1]


## 本文要点：

* 了解针对NoSQL的新的安全漏洞
* 五类NoSQL攻击手段，比如重言式、联合查询、JavaScript 注入、背负式查询（Piggybacked queries），以及跨域违规
* OWASP组织针对检查NoSQL注入代码的建议
* 了解如何缓解安全风险
* 如何在整个软件开发周期中整合NoSQL数据库漏洞的管理

本篇文章已经在 [IEEE Software][5] 杂志上首发。 [IEEE Software][5] 就今天的战略性技术问题提供了可靠的、经专家评审过的信息。IT管理者和技术领导应依靠新先进解决方案的IT专业人员，以迎接运行可靠的、灵活的企业这一挑战。 

NoSQL（不仅仅是NoSQL）数据存储系统已经非常流行，因为它们易扩展且易于使用。尽管NoSQL数据存储的新的数据模型和查询格式令原来的攻击不再有效了，但攻击者却可以寻找新的契机插入恶意代码。

数据库安全是信息安全的一个重要内容。访问企业数据库授权攻击者能够充分控制关键性数据。例如，SQL注入攻击把恶意代码插入到应用向数据库层发送的语句中。这使攻击者几乎能对数据做任何操作，包括访问未授权的数据，以及修改、删除和插入数据。尽管由于框架更安全、人们意识更强，SQL注入这种手段的利用率近几年来已经稳步下降，但它仍然是个高危的系统漏洞。例如，Web应用每月受到四次或更多次Web攻击活动，而SQL注入仍然是攻击零售商最流行的方式1。此外，SQL注入漏洞对32%的Web应用都有影响。

NoSQL（不仅仅是SQL）是数据存储的一个流行趋势；它泛指依赖于不同存储机制的非关系型数据库，这些存储机制包括文档存储、键值对存储和图。这些数据库的广泛应用是由现代大型应用推动起来的，比如Facebook、Amazon和Twitter，它们需要把数据分布到许多的服务器上。传统关系型数据库不满足这种扩展性需求，它们需要一个单独的数据库节点去执行同一事务的所有操作。

于是，发展出一批分布式的、NoSQL键值对存储来满足这些大型应用的扩展性需求。这些数据存储包括像MongoDB和Cassandra之类的NoSQL数据库，也有像Redis和Memcached这样的内存和缓存存储。确实，NoSQL的受欢迎程度在过去几年来一直在稳定上升，其中MongoDB在10个最流行的数据库中排到了第四位，如图1所示。

![][6]

图1 [db-engines.com][7] 2015年8月流行度排名中前十个最受欢迎的数据库。其中NoSQL数据库有MongoDB、Cassandra和Redis。这三款的受欢迎程度仍在上升。 

在本文中，我们将分析NoSQL的威胁和技术，以及它们的缓解机制。

## NoSQL 漏洞

几乎就像每种新技术一样，NoSQL数据库在刚出现时还不够安全3–5。它们当初缺乏加密、适当的认证、角色管理和细粒度的授权等6。此外，它们还会出现危险的风险暴露和拒绝服务攻击3。如今，情况已经好转了，流行的数据库已经引入了内置的保护机制7。

NoSQL数据库使用不同的查询语言，这使传统的SQL注入技术已经无效了。但这是否意味着NoSQL系统对注入免疫呢？我们的研究表明，尽管这个查询语言及其驱动的安全性已经大型提升，但仍然存在着注入恶意查询的手段。已经有人整理出了NoSQL注入技术的列表1,3,4。有些初步应用扫描项目已经涌现出来了（例如 [nosqlproject.com][8] ），而且开放式Web应用程序安全项目（OWASP，Open Web Application Security Project）已经公布了检查NoSQL注入代码的建议。然而，这些还仅仅是初步成果，这些问题尚未得到充分的研究，并且未得到应有的关注。 

## NoSQL攻击途径

Web应用和服务通常使用NoSQL数据库去保存客户数据。图2展示了一个典型的架构，在此NoSQL用于保存通过Web应用来存取的数据。通过一个驱动程序来进行这个数据库的访问，即一个存取协议包装器，它为多种编程语言编写的数据库客户端提供类库。尽管该驱动程序自身可能不易受到攻击，但有时它们提供了不安全的API，当应用开发人员错误地使用它们时，就会给该应用引入漏洞了，这些漏洞会被人利用对数据库进行任意操作。如图2所示，攻击者可以伪造一个带有注入代码的Web访问请求，当数据库客户端或协议包装器进行处理时，将会执行预期的非法数据库操作。

![][9]

图2 典型Web应用架构。NoSQL用于保存通过Web应用来存取的数据。通过一个驱动程序来进行这个数据库的访问，即一个存取协议包装器，它为多种编程语言编写的数据库客户端提供类库。尽管该驱动程序自身可能不易受到攻击，但有时它们提供了不安全的API，当应用开发人员错误地使用它们时，就会给该应用引入漏洞了。

NoSQL相关的SQL攻击主要机制可以大致分为以下五类：

* **重言式** 。又称为永真式。此类攻击是在条件语句中注入代码，使生成的表达式判定结果永远为真，从而绕过认证或访问机制。例如，在本文中，我们将展示攻击者如何用$ne操作（不相等）的语法让他们无需相应的凭证即可非法进入系统。
* **联合查询** 。联合查询是一种众所周知的SQL注入技术，攻击者利用一个脆弱的参数去改变给定查询返回的数据集。联合查询最常用的用法是绕过认证页面获取数据。在本文中，我们将展示一个攻击示例，它将通过增加永真的表达式利用布尔OR运算符进行攻击，从而导致整个语句判定出错，进行非法的数据获取。
* **JavaScript注入** 。这是一种新的漏洞，由允许执行数据内容中JavaScript的NoSQL数据库引入的。JavaScript使在数据引擎进行复杂事务和查询成为可能。传递不干净的用户输入到这些查询中可以注入任意JavaScript代码，这会导致非法的数据获取或篡改。
* **背负式查询** 。在背负式查询中，攻击者通过利用转义特定字符（比如像回车和换行之类的结束符）插入由数据库额外执行的查询，这样就可以执行任意代码了。
* **跨域违规** 。HTTP REST APIs是NoSQL数据库中的一个流行模块，然而，它们引入了一类新的漏洞，它甚至能让攻击者从其他域攻击数据库。在跨域攻击中，攻击者利用合法用户和他们的网页浏览器执行有害的操作。在本文中，我们将展示此类跨站请求伪造（CSRF）攻击形式的违规行为，在此网站信任的用户浏览器将被利用在NoSQL数据库上执行非法操作。通过把HTML格式的代码注入到有漏洞的网站或者欺骗用户进入到攻击者自己的网站上，攻击者可以在目标数据库上执行post动作，从而破坏数据库。

## JSON查询以及数据格式

尽管相对安全，但流行的JSON表述格式仍可受到新类型的注入攻击。我们将举例说明MongoDB中的此类攻击，MongoDB是一个面向文档的数据库，已经有多个大型供应商予以采用，其中包括eBay、Foursquare和LinkedIn。

在MongoDB中，查询和数据以JSON格式描述，这在安全方面要优于SQL，因为它是更充分定义的，容易进行加密和解密，而且在每种编程语言中都有不错的原生实现。像SQL注入那样对查询结构的破坏，在JSON结构的查询中会更难实现。在MongoDB中常见的插入语句应该是这样的：

    db.books.insert({  title: ‘The Hobbit’,  author: ‘J.R.R. Tolkien’   })

这会插入一个新的文档到books的集合中，它具有title（标题）和author（作者）字段。常见的查询条件应该是这样的：

    db.books.find({ title: ‘The Hobbit’ })

除限制要查询的字段之外，查询中还可以包括正则表达式和条件。

### PHP重言式注入

让我们审视一下图3中所画的架构，一个使用PHP实现后端的Web应用，它将用于查询数据存储的请求编码为JSON格式。让我们使用一个MongoDB示例去演示数组注入漏洞吧，从技术和结果上来看这是一个与SQL注入有些类似的攻击手段。

![][10]

图3 使用MongoDB的PHP应用。一个使用PHP实现后端的Web应用，它把用于查询数据存储的请求编码为JSON格式。

PHP编码数组为原生JSON。嗯，数组示例如下：

    array(‘title’ => ‘The Hobbit’,   ‘author’ => ‘J.R.R. Tolkien’);

将由PHP编码为以下JSON格式：

    {“title”: “The Hobbit”, “author”:   “J.R.R. Tolkien”}

如果一个PHP具有登录机制，由用户浏览器通过HTTP POST（它像HTTP GET一样容易受到攻击）发送过来用户和密码，常见的POST URL编码应该是这样的：

    username=Tolkien&password=hobbit

后端PHP代码针对该用户对它进行处理并查询MongoDB，如下所示：

    db->logins->find(array(“username”=>$_   POST[“username”],   “password”=>$_POST[“password”]));

这本身合情合理没什么问题，直觉上开发人员可能喜欢用以下查询：

    db.logins.find({ username: ‘tolkien’,   password: ‘hobbit’})

然而，PHP针对关联数组有个内置的机制，这让攻击者有机可乘，可发送以下恶意的数据：

    username[$ne]=1&password[$ne]=1

PHP会把该输入解析为：

    array(“username” => array(“$[ne] “ =>   1), “password” =>   array(“$ne” => 1));,

它会编码为如下MongoDB查询：

    db.logins.find({ username: {$ne:1 },   password {$ne: 1 })

因为$ne是MongoDB用来判定条件是否不相等的，所以它会查询登录集合中的所有用户名称不等于1且密码也不等于1的记录。因此，本次查询将返回登录集合中的所有用户。换成SQL的表述法，就等同于以下查询语句：

    SELECT * FROM logins WHERE username <>   1 AND password <> 1

在这种情况下，漏洞就为攻击者提供了一个不必有效凭证即可登录应用的方式。在其他变体中，该漏洞可能会导致非法数据访问或由无特权的用户执行特权操作。为缓解这个问题，我们需要转换从需求中接收的参数为适当类型，在本例中，可使用字符串，如下所示：

    db->logins->find(  array(“username”=>(string)$_    POST[“username”],  “password”=>(string)$_    POST[“password”]));

## NoSQL联合查询注入

SQL注入漏洞经常是由于未对用户输入进行适当编码而直接拼接查询造成的。在MongoDB之类的流行数据存储中，JSON查询结构使攻击变得更难了。然而，这并不代表不可能。

让我们看一个通过HTTP POST发送用户名和密码参数到后端的登录表单，它通过拼接字符串的方式得到查询语句。例如，开发人员可能会这么做：

    string query = “{ username: ‘” + post_   username + “’, password:   ‘” + post_passport + ‘ “ }”

具有有效输入时，得到的查询语句是应该这样的：

    { username: ‘tolkien’, password:    ‘hobbit’ }

但具有恶意输入时，这个查询语句会被转换为忽略密码的，在无需密码的情况下登录用户账号。恶意输入示例如下：

    username=tolkien’, $or: [ {}, {‘a’:   ‘a&password=’ }],
    $comment: ‘successful MongoDB   injection’

该输入会被构建到该查询中：

    { username: ‘tolkien’, $or: [ {}, {   ‘a’: ‘a’, password ‘’ } ], $comment: ‘successful MongoDB   injection’ }

只要用户名是正确的，这个查询就可以成功。转换成SQL的表述，这个查询类似于以下语句：

    SELECT * FROM logins WHERE username =    ‘tolkien’ AND (TRUE OR (‘a’=’a’ AND password = ‘’))    #successful MongoDB injection

密码成为这个查询多余的一部分，因为（）内的条件总为真，所以不会影响到查询的最终结果。

这是怎么发生的呢？以下为拼接出的查询串，用户输入为加粗字体，剩余的文本串为无格式字体：

    { username: **‘tolkien’, $or: [ {}, {    ‘a’: ‘a’, password ‘’ } ], $comment: ‘successful MongoDB    injection’** }

这个攻击在任何只要用户名正确的情况下都将成功，一般得到个用户名并不是什么难事。

## NoSQL JavaScript注入

NoSQL数据库中有个共同特性，那就是可以在数据库引擎中运行JavaScript，从而可以执行复杂的查询或MapReduce之类的事务。包括MongoDB 和 CouchDB及其后续的 Cloudant 和 BigCouch等流行的数据库都允许这么做。如果不干净的用户输入发现这种查询方式的话，这么执行JavaScript就等于把薄弱面暴露给攻击者了。例如，设想一个需要JavaScript代码的复杂事务，包含有不干净的用户输入作为查询的参数。让我们看一下它的存储模型，它保存了一组条目，每个条目具有价格和数量属性。为得到这些属性的总数或平均值，开发人员编写了一个MapReduce函数，它从用户那里接收数量或价格作为参数，然后进行处理。在PHP中，看起来是如下代码（$param是用户输入）：

    $map = “function() { for (var i = 0; i < this.items.length;   i++) { emit(this.name, this.items[i].$param);    } }”; $reduce = “function(name, sum) {    return Array.sum(sum); }”; $opt = “{ out: ‘totals’ }”; $db->execute(“db.stores.   mapReduce($map, $reduce, $opt);”);

这段代码把每个条目按名称给定的$param合计起来。当时，$param预期是接收数量（amount）或价格（price）的，这段代码将按预期进行运转。但是，因为用户输入未被转义，所以恶意输入（它可能包含任意JavaScript）将被执行。

看一下如下输入：

    a);}},function(kv) { return 1; }, {    out: ‘x’ });**db.injection.** **insert({success:1});**return 1;db.stores.mapReduce(function() { {    emit(1,1

第一部分的数据会闭合最初的MapReduce函数，然后攻击者就可以在数据库上执行想要的JavaScript了（加粗部分）。最终，最后一部分调用一个新的MapReduce以保持被注入代码的原始语句的平衡。在把会被执行的用户输入合并到为字符串后，我们得到以下代码（注入的用户输入加粗显示）：

    db.stores.mapReduce(function() { for (var i = 0; i < this.items.length;   i++) { emit(this.name, this.items[i].**a); } },function(kv) { return 1; }, { out:   ‘x’ }); db.injection.insert({success:1}); return 1;db.stores.   mapReduce(function() { { emit(1,1);**   } }, function(name, sum) { return Array.   sum(sum); }, { out: ‘totals’ });”

这个注入看起来与经典的SQL注入非常相似。防御此类攻击的一种方式是在数据库配置中禁止执行JavaScript。如果JavaScript是必需的，那么最好的策略是不使用任何用户输入。

## 键值对数据存储

像Memcached、Redis和Tachyon之类的键值对存储是内存数据存储，旨在加快应用、云架构和平台以及大数量框架的执行速度。这些平台考虑的是反复频繁访问的数据的存储和检索。它们通常处于数据存储之前，如图4所示。缓存架构经常存储认证令牌及容器访问控制列表，对于每个后续的用户请求必须重新使其生效。

![][11]

图4 分布式内存数据存储架构。被攻击的Web服务器使用一个键值数据存储进行快速数据检索。对数据存储的查询是在该Web服务器上通过用户提供的数据构建出来的。如果处理不适当，用户数据可以导致一个非法查询注入，它将被该键值面目数据存储处理，导致应用逻辑中的错误，以此绕过认证或进行有害的数据检索。

尽管由于键值对查询很简单所以通常缓存API也非常简单，但我们发现一个Memcached（第二受欢迎的键值对面目全非）潜在的注入攻击手段，那就是基于特定PHP版本的Memcached驱动程序中的漏洞。达成以下条件即可进行攻击：

* 用作传递给缓存set/get 的属性（例如，value）是来自于用户请求的信息（例如，HTTP标头）
* 接收到的字符串未经过处理就发送了
* 缓存的属性包括将导致查询执行不同于预期的行为的敏感信息。

如果满足这些条件，攻击者就可以注入查询或操纵查询逻辑，比如背负式查询攻击。

## 背负式查询

把一个键及相应的值加到使用Memcached的数据库中的一组操作。当从命令行界面调用时，这组函数使用两行输入，第一行是：

    set <KEY> <FLAG> <EXPIRE_TIME>   <LENGTH>,

然后第二行由要保存的数据构成。

当PHP配置的函数被调用时，它接收的两个参数看起来是这样的：

    $memcached->set(‘key’, ‘value’);

研究人员表示，该驱动程序未能针对带有回车\r(0x0D)和换行的\n(0x0A)的ASCII码采取措施，导致攻击者有机会注入包含有键参数的新命令行和其他非计划内的命令到缓存中8。

看一下如下代码，其中的$param是用户输入并作为键来作用：

    $memcached=new Memcached(); $memcached ->addServer(‘localhost’,11211); $memcached->set($param, “some value”);

攻击者可以提供以下输入进行注入攻击：

    “key1 0 3600 4\r\nabcd\r\nset key2 0 3600 4\r\ninject\r\n”

在本例中，增加到数据库中的第一个键是具有“some value”值的key1。攻击者可以增加其他的、非计划内的键到数据库中，即带有“inject”值的key2。

这种注入也可以发生在get命令上。让我们看一下Memcached主页上的示例，它以这三行开头：

    Function get_foo(foo_id) foo = memcached_get(“foo: “ . foo_id) return foo if defined foo

这个示例展示了Memcached的典型用法，在处理输入之前首先检查在数据库中是不是已经存在了。假设用类似代码检查从用户那里接收的认证令牌，验证他们是不是登录过了，那么就可以通过传递以下作为令牌的字符串来利用它（注入部分已经加粗强调）：

    “random_token**\r\nset my_crafted_token 0 3600 4\r\nroot\r\n**”

当这个字符串作为令牌传递时，数据库将检查这个“random_token”是否存在，然后将添加一个具有“root”值的“my_crafted_token”。之后，攻击者就可以发送具有root身份的my_crafted_token令牌了。

可以被这项技术攻击的其他指令还有：

    incr <Key> <Amount>
    decr <Key> <Amount>
    delete <Key>

在此，incr用于增加一个键的值，decr用于缩减一个键的值，以及delete用于删除一个键。攻击者也可以用像set和get函数一样的手段来使用带来自己键参数的这三个函数。

攻击者可以使用多条目函数进行同样的注入：deleteMulti、getMulti和setMulti，其中每一个键字段都可以被注入。

回车换行注入可以被用于连接多个get请求。在一项我们进行的测试中，包括原始get在内最多可以连接17条。这样注入返回的结果是第一个键及其相应的值。

该驱动程序的漏洞已经在PHP 5.5 中修复，但不幸的是它已存在于之前所有的PHP版本中了。按照W3Techs.com对生产系统的PHP版本的统计来看，超过86%的PHP网站使用了比5.5要老的版本，这意味着如果他们使用了Memcached就很容易受到这种注入攻击。

## 跨域违规

NoSQL数据库的另一个常见特点是，他们能够常常暴露能够从客户端应用进行数据库查询的HTTP REST API。暴露REST API 的数据库包括MongoDB、CouchDB和HBase。暴露REST API 就直接把数据库暴露给应用了，甚至是仅基于HTML5的应用，因为它不再需要间接的驱动程序了，让任何编程语言都可以在数据库上执行HTTP查询。这么做的优势非常明显，但这一特点是否伴随着安全风险？我们的回答是肯定的：这种REST API给跨站点请求伪造（CSRF）暴露了数据库，让攻击者绕过了防火墙和其他外围防御。

只要数据库部署在诸如防火墙之类的安全设施之后的安全网络中，攻击者要危害这个数据库就必须找到能让他们进入这个安全网络的漏洞，或者完成能让他们执行任意查询的注入。当数据库在安全网络内暴露 REST API时，任何能够访问该安全网络的人都可以仅通过HTTP就能在这个数据库上执行查询，因此在浏览器上就可以发起此类查询了。如果攻击者可以在网站上输入HTML表单，或者欺骗用户到攻击者自己的网站上，就能够通过提交这个表单在数据库上执行任何post操作了。而post操作包括增加文件。

我们在调查研究审查了Sleepy Mongoose，这是一个针对MongoDB的全功能HTTP接口。 Sleepy Mongoose API是以http:// {host name}/{db name}/{collection name}/{action}这样的URL格式定义的。查找文件的参数可以作为查询参数包含在内，而新文件也可以作为请求数据予以添加。例如，如果我们想要在safe.internal.db主机上的数据库中名为hr的管理员集合中增加一个新文件{username:'attacker'} ，就可以发送一个post HTTP请求至 [http://safe.internal.db/hr/admins/_insert][12] ，加上URL编码过的数据username=attacker。 

现在让我们看看CSRF攻击是如何使用这个函数增加新文件到管理员集合中的，从而在hr数据库（它被认为处于安全的内部网络中）中增加了一个新的管理员用户，如图5所示。若想攻击成功，必须要满足几个条件。首先，攻击者必须能操作一个网站，要么是他们自己的网站，要么是利用不安全的网站。攻击在该网站放置一个HTML表单以及一段将自动提交该表单的脚本，比如：
```
<form action=” http://safe.internal. db/hr/admins/_insert” method=”POST” name=”csrf”> 
<input type=”text” name=”docs” value=” [{"username":attacker}]” /> 
</form> 
<script> document.forms[0].submit(); </script>
```

![][13]

图5 NoSQL HTTP REST API上的跨站请求背负式攻击示意图。藏在防火墙后的内部网络内的用户被欺骗访问一个恶意外部网页，这将导致在内部网络的NoSQL数据库的 REST API 上执行非预期的查询。

第二，攻击者必须通过网络诱骗或感染用户经常访问的网站欺骗用户进入被感染的网站。最后，用户必须许可访问Mongoose HTTP接口。

用这种方式，攻击者不必进入内部网络即可执行操作，在本例中，是插入新数据到位于内部网络中的数据库中。这种攻击执行很简单，但要求攻击者要提前侦察去识别主机、数据库名称，等等。

## 缓解

鉴于我们在本文中所提到的这些攻击手段，NoSQL部署中的安全问题的缓解是非常重要的。但不幸的是，应用层的代码分析不足以确保所有风险都能得以缓解。三个趋势使该问题将比之前面临更多的挑战。首先，云和大数据系统的形成，它们通常会执行多个复杂应用，这些应用使用异构的开源工具和平台。而这些应用通常由开源社区开发，大多数情况下，未经受过全面的安全性测试。另一个挑战是伴随DevOps方法论而形成的现代代码开发的速度，因为DevOps追求的是缩短开发和生产之间的时间。最后，大多数应用安全测试工具不能与新编程语言的应用保持同步，例如，大多数安全产品不支持Golang、Scala和 Haskel。

## 开发和测试

为充分解决由应用层引入的风险，我们需要考虑整个软件开发生命周期，如图6所示。

![][14]

图6 应用和部署安全的生命周期。为充分解决由应用层引入的风险，我们需要考虑整个软件开发生命周期。

**意识** 。很明显，构建阻止注入和其他潜在漏洞的安全软件是最好、最廉价的解决方案。建议在该软件生命周期中涉及到的人针对他们的职责接受适应的安全培训。例如，已经理解漏洞的开发人员就不太可能把它们引入到软件中。 

**设计** 。应用的安全方面必须在开发阶段早期予以考虑和定义。定义什么需要在应用内保护，如何确保这个功能已经转化为开发阶段中的任务并得到足够的重视。 

**针对代码的最佳实践** 。建议充分利用已经经受过安全验证处理的共享类库，从而减少犯安全性错误的机会。例如，使用针对认证充分验证过的类库，减少开发人员自己实现认证把漏洞引入到算法中的风险。再举一个使用“消毒后(sanitization)”类库的例子。所有注入攻击都是缺乏消毒造成的。使用一个充分测试消毒过的类库能很大程度上减少以自主研发消毒方法引入漏洞的风险。 

**特权隔离** 。在过去，NoSQL不支持适当的认证和角色管理9。现在，在大多数流行的NoSQL数据库上进行适当的认证管理和基于角色的访问控制认证已经成为可能。这些机制出于两个原因非常重要。第一，它们允许实施最少特权的原则，从而避免通过合法用户的特权升级攻击。第二，类似于SQL注入攻击10，当数据存储暴露在我们本文所说的注入攻击之下时，适当的特权隔离能将危害最小化。 

**安全扫描** 。建议在应用或源码上运行动态和静态应用安全测试（分别为DAST和SAST）以发现注入漏洞。问题是目前市场上的许多工具缺乏检测NoSQL注入的规则。DAST方法被认为比SAST更可靠11。特别是如果用户使用后端检查方法提升检测可靠性的话，这是一种作为交互式应用安全测试提出的方法9,12。它还建议，集成这些扫描工具到持续构建和发布系统中，如此它们就会在每个周期或检入时执行，缺陷就会及时发现并修复，而不只是在安全测试阶段。 

由于两个原因，这可能会减少修复安全性缺陷的工作量。第一个，在开发阶段修复缺陷的成本要远低于生命周期更后的阶段，特别是因为安全性测试往往都在功能性测试之后，而且修复安全性缺陷可能会需要重新做功能性测试。第二个，开发人员能在早期了解这些缺陷，在之后的代码开发中不会犯类似的错误。

**安全性测试** 。应该由专业的安全性测试人员测试应用。这些测试应该验证所有在设计阶段定义的安全需求都已经得到满足，并应该包括应用和主机基础设施之上（建议尽可能与生产环境类似）的浸透测试。 

## 安全的部署

保持应用一个很重要的部分是确保安全的部署。如果部署不够安全，所有在应用代码层的安全性努力可能也就付之东流了。而这一阶段有时会被忽视。

**网络隔离** 。Adobe、RSA Security和Sony等企业遭受了无数的攻击，在这些攻击中内网安全的概念已不再成立。内部网络在某种情况下是渗透的边界，我们应尽可能让攻击者很难从这一点上得到什么。对于某些相对较新缺乏基于角色授权的NoSQL数据库来讲这一点尤其如此。为此，建议严格配置网络，确保数据库只能由相关主机访问，比如应用服务器。 

**API的防护** 。为缓解REST API暴露和CSRF攻击的风险，需要对请求加以控制，限制它们的格式。例如，CouchDB已经采用了一些重要的安全措施去缓解因为暴露的REST API导致的风险。这些措施包括只接受JSON内容格式。HTML表单限制为URL编码的内容格式，所以攻击者不能使用HTML进行CSRF攻击。另一项举措是使用Ajax请求，得益于同源策略从浏览器发起的请求会被阻止。要确保在服务器的API中已经取消了JSONP和跨域资源共享，不能从浏览器直接发起操作，这一点也很重要。某些数据库，比如MongoDB，有很多第三方的REST API，其中许多都缺乏我们在此提到的安全措施。 

## 监控和攻击检测

人类容易犯错，即使遵循所有安全开发最佳实践，仍有可能在开发完后从软件中找到漏洞。此外，在开发测试时未知的新的攻击途径可能会被发掘出来。因此，建议在运行期进行应用的监控和防御。尽管这些系统可能擅于发现和阻止某些攻击，但是它们不了解应用逻辑和那些假定运行的应用下的规则，所以它们不能找出所有的漏洞。

**Web应用防火墙** 。Web应用防火墙（WAFs）是检查HTTP数据流和检测恶意HTTP事务的安全性工具。它们可以作为电器、网络嗅探器、代理或网站服务器模块来实现，具体目标是为Web应用提供一个独立的安全层，检测SQL注入之类的攻击。尽管已知攻击可以绕过防火墙13，但我们仍然提倡为这些系统增加检测NoSQL注入的规则。 

**侵入检测系统** 。与可以在网络层检测攻击的防火墙类似，基于主机的侵入检测系统（HIDSs）监控着应用的执行和服务器上的负载。HIDSs通常了解应用的正常行为，对与预期行为不符的行为给出预警，它们可能是攻击。此类工具可以检测在操作系统上传播的漏洞，但和SQL检测或CSRF无关。 

**数据活动监控** 。数据活动监控工具已成为组织数据防护的常规需求。它们控制数据的访问，以自定义安全预警监控活动，并创建数据访问和安全事件审计报告。虽然大多数解决方案定位的都是关系型数据库，但针对NoSQL数据存储的监控也已经开始涌现出来了10。我们希望这些将不断地改进成为NoSQL活动监控的常规实践。针对我们在本文演示过的注入攻击，这些工具是非常有用的监控和检测系统。 

**安全信息与事件管理（SIEM）系统和威胁警报** 。安全信息和事件管理系统整理日志并梳理日志的关联关系去帮助攻击的检测。 

此外，威胁警报工具可以帮助提供恶意IP地址和域上的数据，以及有危害的其他指标，这能有助于检测注入。

**运行期应用自我保护（** RASP **）** 。运行期应用自我保护引入了一种新的应用安全方式，在此防御机制是在运行期嵌入到应用中的，使其可以进行自我监控。运行期应用自我保护的优点超过其他安全技术，因为它能够检查内部的程序流转和数据处理。在应用中的关键位置设置检查点能更精确地检测更多的问题。而不利的一面是，运行期应用自我保护付出了性能的代价，它高度依赖于编程语言，而且可能导致应用在生产环境中中止运行。 

NoSQL数据库遭受像传统数据库一相的风险问题。一些低层技术和协议已经变了，但注入风险、不正确的访问控制管理以及不安全的网络暴露仍然居高不下。我们建议使用具有内置安全措施的成熟的数据库。然而，即使使用最安全的数据存储也无法阻止利用Web应用中的漏洞去访问数据存储的注入攻击。避免这些问题的其中一种方法是通过谨慎的代码检查和静态分析。然而，这些很难实施并且可能有很高的误报率。尽管动态分析工具已表明对检测SQL注入攻击很有作用，但它们需要做出调整去检测我们在本文中所说的那些特定于NoSQL数据库的漏洞。此外，与NoSQL风险相关的监控和防御系统也应该利用起来。

## 参考资料

* [Imperva Web Application Attack Report][15] , 4th ed., Imperva, 2013;
* [State of Software Security Report][16] , Varacode, 2013;
* A. Lane, ["No SQL and No Security"][17] , blog, 9 Aug. 2011;
* L. Okman et al. "Security Issues in NoSQL Databases", Proc. IEEE 10th Int’l Conf. Trust, Security and Privacy in Computing and Communications (TrustCom), 2011, pp. 541–547.
* E. Sahafizadeh and M.A. Nematbakhsh. "A Survey on Security Issues in Big Data and NoSQL", Int’l J. Advances in Computer Science, vol. 4, no. 4, 2015, pp. 2322–5157.
* M. Factor et al. "Secure Logical Isolation for Multi- tenancy in Cloud Storage", Proc. IEEE 29th Symp. Mass Storage Systems and Technologies (MSST), 2013, pp. 1–5.
* ["Security"][18] , MongoDB 3.2 Manual, 2016;
* I. Novikov, ["The New Page of Injections Book: Memcached Injections"][19] , Proc. Black Hat USA, 2014;
* J. Williams, ["7 Advantages of Interactive Application Security Testing (IAST) over Static (SAST) and Dynamic (DAST) Testing"][20] , blog, 30 June 2015;
* K. Zeidenstein, ["Organizations Ramp up on NoSQL Databases, but What about Security?"][21] , blog, 1 June 2015;
* V. Haldar, D. Chandra, and M. Franz, "Dynamic Taint Propagation for Java", Proc. IEEE 21st Computer Security Applications Conf., 2005, pp. 303–311.
* S.M. Kerner, ["Glass Box: The Next Phase of Web Application Security Testing?"][22] , blog, 3 Feb. 2012;
* I. Ristic, ["Protocol-Level Evasion of Web Application Firewalls"][23] , Proc. Black Hat USA, 2012.

## 关于作者

**Aviv Ron** 是IBM网络安全卓越中心的安全研究员。他的研究兴趣包括应用安全，特别是云环境的安全。Ron拥有Ben Gurion大学的计算机科学学士学位。 

[**Alexandra Shulman-Peleg**][24] 是花旗银行云安全领域的一名负责人。在准备这篇文章的期间，她是IBM网络安全卓越中心的高级研究员。她的研究兴趣包括云安全。Shulman-Peleg拥有Tel Aviv大学的计算机科学博士学位。她曾在顶级期刊、大会和书籍中发表了30多篇科学出版物。 

[**Anton Puzanov**][25] 是IBM网络安全卓越中心的安全研究员。他的研究兴趣包括应用安全测试产品。Puzanov拥有Ben Gurion大学的通信系统工程学士学位。 

本篇文章已经在 [IEEE Software][5] 杂志上首发。 [IEEE Software][5] 就今天的战略性技术问题提供了可靠的、经专家评审过的信息。IT管理者和技术领导应依靠新先进解决方案的IT专业人员，以迎接运行可靠的、灵活的企业这一挑战。 

查看英文原文： [Article: Analysis and Mitigation of NoSQL Injections][26]


[1]: http://www.infoq.com/cn/articles/nosql-injections-analysis
[5]: http://www.computer.org/software
[6]: ./img/IJz2Uji.jpg
[7]: http://db-engines.com/
[8]: http://nosqlproject.com/
[9]: ./img/RRrqMfZ.jpg
[10]: ./img/Nz6ZFvA.jpg
[11]: ./img/QbiUriu.jpg
[12]: http://safe.internal.db/hr/admins/_insert
[13]: ./img/6BNrqiA.jpg
[14]: ./img/reiuief.jpg
[15]: https://www.imperva.com/docs/HII_Web_Application_Attack_Report_Ed4.pdf
[16]: http://www.veracode.com/blog/2013/04/changing-the-future%20-state-of-software-security-report-2013
[17]: https://www.securosis.com/blog/nosql-and-no-security
[18]: https://docs.mongodb.com/manual/security/
[19]: http://www.blackhat.com/docs/us-14/materials/us-14-Novikov-The-New-Page-Of-Injections-Book-Memcached-Injections-WP.pdf
[20]: https://www.contrastsecurity.com/security-influencers/9-reasons-why-interactive-tools-are-better-than-static-or-dynamic-tools-regarding-application-security
[21]: https://securityintelligence.com/organizations-ramp-up-on-nosql-databases-but-what-about-security/
[22]: http://www.esecurityplanet.com/network-security/glass-box-the-next-phase-of-web-application-security-testing.html
[23]: https://media.blackhat.com/bh-us-12/Briefings/Ristic/BH_US_12_Ristic_Protocol_Level_Slides.pdf
[24]: mailto:shulman.peleg@gmail.com
[25]: mailto:antonp@il.ibm.com
[26]: http://www.infoq.com/articles/nosql-injections-analysis