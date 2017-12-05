# 提升网站访问速度的 SQL 查询优化技巧

 时间 2017-12-04 19:06:16  

原文[https://www.oschina.net/translate/sql-query-optimization][1]


![][3]

 你一定知道，一个快速访问的网站能让用户喜欢，可以帮助网站从Google 上提高排名，可以帮助网站增加转化率。如果你看过网站性能优化方面的文章，例如 [设置服务器的最佳实现][4] 、到 [干掉慢速代码][5] 以及   [使用CDN 加载图片][6] ，就认为你的 WordPress 网站已经足够快了。但是事实果真如此吗？

使用动态数据库驱动的网站，例如WordPress，你的网站可能依然有一个问题亟待解决：数据库查询拖慢了网站访问速度。

在这篇文章中，我将介绍如何识别导致性能出现问题的查询，如何找出它们的问题所在，以及快速修复这些问题和其他加快查询速度的方法。我会把门户网站 deliciousbrains.com 出现的 拖慢查询速度的情况作为实际的案例。 

定位

处理慢SQL查询的第一步是找到慢查询。Ashley已经在之前的 [博客][7] 里面赞扬了调试插件 [Query Monitor][8] ，而且这个插件的数据库查询特性使其成为定位慢SQL查询的宝贵工具。该插件会报告所有页面请求过程中的数据库请求，并且可以通过调用这些查询代码或者原件(插件，主题，WordPress核)过滤这些查询，高亮重复查询和慢查询。 

![][9]

要是不愿意在生产安环境装调试插件(性能开销原因)，也可以打开 [MySQL Slow Query Log][10] ，这样在特定时间执行的所有查询都会被记录下来。这种方法配置和设置存放查询位置相对简单。由于这是一个服务级别的调整，性能影响会小于使用调试插件，但当不用的时候也应该关闭。 

## 理解

一旦你找到了一个你要花很大代价找到的查询，那么接下来就是尝试去理解它并找到是什么让查询变慢。最近，在我们开发我们网站的时候，我们找到了一个要执行8秒的查询。

我们使用WooCommerce和定制版的WooCommerce软件插件来运行我们的插件商店。此查询的目的是获取那些我们知道客户号的客户的所有订阅。 WooCommerce是一个稍微复杂的数据模型， 即使订单以自定义的类型存储，用户的ID（商店为每一个用户创建的WordPress）也没有存储在 post_author， 而是作为后期数据的一部分。订阅软件插件给自义定表创建了一对链接。让我们深入了解查询的更多信息。 

## MySQL是你的朋友

MySQL有一个很方便的语句 [DESCRIBE][11] ，它可以输出表结构的信息，比如字段名，数据类型等等。所以，当你执行 DESCRIBE wp_postmeta; 你将会看到如下的结果：

Field Type Null Key Default Extra 
meta_id bigint(20) unsigned NO PRI NULL auto_increment 
post_id bigint(20) unsigned NO MUL 0 
meta_key varchar(255) YES MUL NULL 
meta_value longtext YES NULL 

你可能已经知道了这个语句。但是你知道 DESCRIBE语句可以放在SELECT, INSERT, UPDATE, REPLACE 和 DELETE语句前边使用吗 ？更为人们所熟知的是他的同义词  [EXPLAIN][12]  ，并将提供有关该语句如何执行的详细信息。 

这是我们查询到的结果：

id select_type table type possible_keys key key_len ref rows Extra 
1 SIMPLE pm2 ref meta_key meta_key 576 const 28 Using where; Using temporary; Using filesort 
1 SIMPLE pm ref post_id,meta_key meta_key 576 const 37456 Using where 
1 SIMPLE p eq_ref PRIMARY,type_status_date PRIMARY 8 deliciousbrainsdev.pm.post_id 1 Using where 
1 SIMPLE l ref PRIMARY,order_id order_id 8 deliciousbrainsdev.pm.post_id 1 Using index condition; Using where 
1 SIMPLE s eq_ref PRIMARY PRIMARY 8 deliciousbrainsdev.l.key_id 1 NULL 

乍一看，这很难解释。幸运的是，人们通过SitePoint总结了一个 [理解语句的全面指南][13] 。 

最重要的字段是 type ，它描述了一张表是怎么构成的。如果你想看全部的内容，那就意味着MySQL要从内存读取整张表，增加I/O的速度并在CPU上加载。这种被称为“全表浏览”—稍后将对此进行详细介绍。 

 rows 字段也是一个好的标识，标识着MySQL将要不得不做的事情，它显示了结果中查找了多少行。 

Explain 也给了我们很多可以优化的信息。例如，pm2表（ (wp_postmeta ），告诉我们是 Using filesort， 因为我们使用了  ORDER BY语句对结果进行了排序。如果我们要对查询结果进行分组，这将会给执行增加开销。

### 可视化研究

对于这种类型的研究， [MySQL Workbench][14] 是另外一个方便，免费的工具。将数据库用MySQL5.6及其以上的版本打开， EXPLAIN  的结果可以用JSON格式输出，同时 MySQL Workbench将JSON转换成可视化执行语句：

  ![][15]

它自动将查询的问题用颜色着重表示提醒用户去注意。我们可以马上看到，连接 wp_woocommerce_software_licences（别名l） 的表有严重的问题 。

###  解决 

你 [应该避免][16] 这种全部表浏览的查询，因为他使用非索引字段 order_id 去连接 wp_woocommerce_software_licences 表和 wp_posts 表  。这对于查询慢是常见的问题，而且也是比较容易解决的问题。 

### 索引

order_id在表中是一个相当重要的标志性数据，如果想像这种方式查询，我们需要在列上建立一个 [索引][17] ，除此之外，MySQL将逐字扫描表的每一行，直到找到我们想要的行为止。让我们添加一个索引并看看它是怎么样工作的： 

    CREATE INDEX order_id ON wp_woocommerce_software_licences(order_id)

![][18]

哇，干的漂亮！我们成功的添加了索引并将查询的时间缩短了5s.

### 了解你的查询语句

检查下查询语句——看看每一个join，每一个子查询。它们做了它们不该做的事了吗？这里能做什么优化吗？

这个例子中，我们把licenses 表和posts 表通过order_id 连接起来同时限制post type 为shop_order。这是为了通过保持数据的完整性来保证我们只使用正确的订单记录，但是事实上这在查询中是多余的。我们知道这是一个关于安全的赌注，在posts 表中software license 行是通过order_id 来跟 WooCommerce order 相关联的，这在PHP 插件代码中是强制的。让我们移除join 来看看有什么提升没有：

![][19]

提升并不算很大但 现在 查询时间低于3 秒了。 

### 缓存一切数据

如果你的服务器默认情况下没有使用MySQL查询缓存，那么你应该开启缓存。开启缓存意味着MySQL 会把所有的语句和语句执行的结果保存下来，如果随后有一条与缓存中完全相同的语句需要执行，那么MySQL 就会返回缓存的结果。缓存不会过时，因为MySQL 会在表数据更新后刷新缓存。

查询监视器发现在加载一个页面时我们的查询语句执行了四次，尽管有MySQL查询缓存很好，但是在一个请求中重复读取数据库的数据是应该完全避免的。你的PHP 代码中的静态缓存很简单并且可以很高效的解决这个问题。基本上，首次请求时从数据库中获取查询结果，并将其存储在类的静态属性中，然后后续的查询语句调用将从静态属性中返回结果：

    class WC_Software_Subscription {
    
        protected static $subscriptions = array();
    
        public static function get_user_subscriptions( $user_id ) {
            if ( isset( static::$subscriptions[ $user_id ] ) ) {
                return static::$subscriptions[ $user_id ];
            }
    
            global $wpdb;
    
            $sql = '...';
    
            $results = $wpdb->get_results( $sql, ARRAY_A );
    
            static::$subscriptions[ $user_id ] = $results;
    
            return $results;
        }
    }

缓存有一个生命周期，具体地说是实例化对象有一个生命周期。如果你正在查看跨请求的查询结果，那么你需要实现一个持久对象缓存。然而不管怎样，你的代码应该 负责 设置缓存，并且当基础数据变更时让缓存失效。 

### 跳出箱子外思考 

 不仅仅是调整查询或添加索引， 还有其他方法可以加快查询的执行速度。 我们查询的最慢的部分是从客户ID到产品ID再到 加入表格 所做的工作，我们必须为每个客户做到。我们是不是可以在需要的时候抓取客户的数据？如果是那样，那 我们就只需要加入一次。

您可以通过创建数据表来存储许可数据，以及所有许可用户标识和产品标识符来对数据进行非规范化（反规范化）处理，并针对特定客户进行查询。 您需要使用INSERT / UPDATE / DELETE上的 [MySQL触发器][20] 来重建表格（不过这要取决于数据来更改的表格），这会显着提高查询数据的性能。 

类似地，如果一些连接在MySQL中减慢了查询速度，那么将查询分解为两个或更多语句并在PHP中单独执行它们可能会更快，然后可以在代码中收集和过滤结果。 Laravel 通过 [预加载][21] 在 Eloquent 中就做了类似的事情。 

如果您有大量数据和许多不同的自定义帖子类型，WordPress可能会在wp_posts表上减慢查询速度。 如果您发现查询的帖子类型较慢，那么可以考虑从自定义帖子类型的存储模型移动到 [自定义表格][22] 中 - 更多内容将在后面的文章中介绍


[1]: https://www.oschina.net/translate/sql-query-optimization
[3]: https://img0.tuicool.com/bYv6vaU.jpg
[4]: https://deliciousbrains.com/hosting-wordpress-setup-secure-virtual-server/
[5]: https://deliciousbrains.com/finding-bottlenecks-wordpress-code/
[6]: https://deliciousbrains.com/wp-offload-s3/doc/why-use-a-cdn/
[7]: https://deliciousbrains.com/finding-bottlenecks-wordpress-code/#query-monitor
[8]: https://wordpress.org/plugins/query-monitor/
[9]: https://img0.tuicool.com/QjANB3V.png
[10]: https://dev.mysql.com/doc/refman/5.7/en/slow-query-log.html
[11]: https://dev.mysql.com/doc/refman/5.7/en/describe.html
[12]: https://dev.mysql.com/doc/refman/5.7/en/explain.html
[13]: https://www.sitepoint.com/using-explain-to-write-better-mysql-queries/
[14]: https://www.mysql.com/products/workbench/
[15]: https://img1.tuicool.com/3MVjyqi.png
[16]: https://dev.mysql.com/doc/refman/5.7/en/table-scan-avoidance.html
[17]: https://dev.mysql.com/doc/refman/5.7/en/mysql-indexes.html
[18]: https://img1.tuicool.com/Qvmi6nV.png
[19]: https://img0.tuicool.com/uIzyamF.png
[20]: https://dev.mysql.com/doc/refman/5.7/en/create-trigger.html
[21]: https://laravel.com/docs/5.5/eloquent-relationships#eager-loading
[22]: https://deliciousbrains.com/creating-custom-table-php-wordpress/