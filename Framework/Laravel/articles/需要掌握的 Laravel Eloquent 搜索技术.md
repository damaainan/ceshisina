## 需要掌握的 Laravel Eloquent 搜索技术

来源：[https://segmentfault.com/a/1190000014450604](https://segmentfault.com/a/1190000014450604)

本文同步至个人博客 [需要掌握的 Laravel Eloquent 搜索技术][0]，转载请注明出处。
当我们的应用程序访问较少时（例如在项目初期阶段），直接进行项目编码就可以解决大多数问题。项目中的搜索功能也是如此，没必要在一开始就引入完整的第三方类库进行搜索功能支持。大多数情况下使用 Eloquent 的查询功能就可以完成基本的搜索处理。
## 预热

搜索功能是应用的重要组成模块。优秀的设计，可以帮助我们的用户简单快速的检索想要的信息。因此，在项目中对搜索功能的设计，无论前端还是后端都需要提供良好的解决方案。
本文不会探讨搜索功能的前端及 UI 设计等内容。若需要学习前端在搜索设计方面的知识，可以阅读 [Instant AJAX Search with Laravel and Vue][1] 这篇文章。

本文将带领大家学习 MySQL 和 Eloquent 在搜索模块中设计的相关技术。
## 基本的 Eloquent Where 查询

作为首个要讲解的搜索功能，我们先不涉及新知识点。在 Laravel 中可以使用 **`where`**  方法实现对给定字段和给定值进行比较查询，就是这样简单。

```php
<?php

$results = Post::where('title', 'foo')->get();
```

甚至，你可以传入一个 **`array`**  到 **`where`**  方法里，对多个字段进行比较查询。它的工作原理，类似 **`&&（与查询）`**  运算符，当所有条件都为 **`true`**  时，返回结果集：

```php
<?php

$results = Post::where([
            ['title' => 'foo'],
            ['published' => true],
        ])->get();
```

如果需要实现类似 **`||（或查询）`**  查询，则可以使用 Eloquent 查询构造器提供的 **`orWhere`**  方法。

```php
<?php

$results = Post::where('title', 'foo')->orWhere('description', 'foo')->get();
```

有关 **`where`**  语句的使用方法，强烈建议阅读 Laravel 「[查询构造器 - Where 语句][2]」 文档。
## 使用 Like 关键字

如何实现模糊查询呢？即实现 MySQL 的 **`LIKE`**  查询。Eloquent 提供了比 **`where`**  语句更加灵活的模糊查询功能。通过在 **`where`**  方法中使用通配符，可以实现模糊查询功能。让我们看看 **`%`**  通配符：

```php
<?php

$keyword = 'foo';

// 获取以 foo 开始，以任何字符结尾的文章
$result = Post::where('title', 'like', '{$keyword}%')->get();

// 获取以任何字符开始，但以 foo 结尾的文章
$result = Post::where('title', 'like', '%{$keyword}')->get();

// 获取包含 foo 的文章
$result = Post::where('title', 'like', '%{$keyword}%')->get();
```

我们可以看到 Eloquent 的模糊查询功能十分灵活。即可以查询以指定字符开始或结尾的数据，也可以查询包含指定字符的数据。模糊查询在我们需要对依稀记得部分数据进行查询时非常实用。

提示：
A big note here: Probably you are using a collation that ends with _ci. That means it’s case-insensitive. Whether you type FOO, Foo, fOO, etc., you get the same result!

当然，上面的查询功能都可以在文档中找到。
## 在 JSON 列中搜索

JSON 类型让数据存储拥有灵活性，这个功能很赞。Laravel 中也可以轻松执行对 JSON 数据的查询，这得益于 Laravel 良好的 JSON 支持。
不过在深入研究之前需要注意的一点是：谨记 JSON 列的存储是 **`区分大小写`**  的。

而如果我们需要查询的数据不存在 **`区分大小写`**  的问题，可以执行类似下面的查询语句：

```php
<?php
$results = Post::where('meta->description', 'like', '%foo%')->get();
```

这条模糊查询语句和前面的 **`where`**  查询并无二致，对吧？但是如果我们的 JSON 数据存在 **`大小写字符`**  的情况，又该如何处理呢？这种场景最适合使用 **`whereRaw`**  方法，先来看看示例，再来讲解它工作原理：

```php
<?php
$keyword = 'foo';
$results = Post::whereRaw('lower(meta->"$.description") like lower(?)', ['%foo%']);
```

你会注意到这条的查询语句有些不同。

首先，除了 **`like`**  关键字外还多了些 SQL 语法，因为这里我们传入的是一条 **`原生 SQL 表达式`** 。
其次，在第 2 个 **`lower`**  函数内加入了 **`?`**  占位符，这种语法即为参数绑定，它的主要作用是用于防止 SQL 注入。

如你所见，我们将一个 **`array`**  给到 **`whereRaw`**  的第二个参数，数组内的第一个元素对应第一个参数绑定占位符，第二个元素对应第二个参数绑定占位符，以此类推。
这就是 **`whereRaw`**  的工作原理。

接下来将焦点集中到真正的关键处理：我们通过 MySQL 的 **`lower()`**  函数将待查询的 JSON 数据等数据转换成小写字符，实现 **`不区分大小写`**  的查询操作。解决方案虽然实现起来较为麻烦，但工作良好。
## 依据单词发音进行模糊匹配

继续探讨最后一个主题，当用户输入的查询表达式包含错误的单词拼写时，该如何进行搜索呢？查询与给定的表达式有类似发音的语句是个不错的主意。这种场景我们无法使用 **`like`**  关键字，但我们有 **`sound like`**  关键字。

先不必深究 **`sound like`**  的工作原理，但如果你真的对 **`sound like`**  功能感兴趣可以阅读 [MySQL SOUNDS LIKE][3] 这篇文章。所有你感兴趣的内容它都所涉及。但现在让我们看看 Laravel 如何使用这个功能。

```php
<?php
$results = Post::where('title', 'sound like', 'mistyped')->get();
```

提示：对 MySQL sound like 功能的支持，需要使用 **`5.6.8`**  以上的 Laravel 版本，可以查看 Laravel [changelog][4]

执行 **`sound like`**  操作，会进行一个发音相似性的算法，然后获取结果集。但是这并不是我们需要关注的，我们仅需将待查询的字符串传给 **`where`**  语句即可。返回的结果集即会包含完全匹配的数据，也会包含发音近似的数据。
## 总结

Laravel 为我们提供了简单实用的查询功能。我们可以在 Laravel 里使用 **`where`**  语句，可以使用原生 SQL 语句，甚至可以使用模糊查询和相似查询，所有这些查询功能都是 Laravel 内置提供的开箱即用，非常赞！

[0]: http://blog.phpzendo.com/?p=269
[1]: https://pineco.de/instant-ajax-search-laravel-vue/
[2]: https://laravel-china.org/docs/laravel/5.6/queries#ead379
[3]: https://www.w3resource.com/mysql/string-functions/mysql-sounds_like-function.php
[4]: https://github.com/laravel/framework/blob/5.6/CHANGELOG-5.6.md#v568-2018-03-06