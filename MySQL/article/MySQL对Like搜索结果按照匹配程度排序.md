# MySQL对Like搜索结果按照匹配程度排序

 时间 2018-01-09 10:52:30  郭宇翔的博客

原文[https://blog.yourtion.com/mysql-like-search-ordering.html][1]


## MySQL对Like搜索结果按照匹配程度排序  [解决问题][3]  January 09 2018  [MySql][4]

最近项目上遇到一个需求，在原来项目的管理后台上，有一个通过用户昵称进行模糊搜索的功能，但是用户反映说有时候搜索关键字的结果比较多的话，准确匹配的结果没有排在前面。

检查了一下后端的代码，发现 like 的语句是 LIKE %keyword% ，然后排序的就是按照默认的方式，结果如下： 

![][5]

可以发现确实完整匹配 “阳光” 关键字的结果是分散的，找了一下解决方案，结果在 stackoverflow 找到这样的一个答案： [MySQL order by “best match”][6] ，里面提出了几个解决方案，经过测试，在其中一个的基础上做了一些修改，得到比较好的结果。 

更新后的 SQL 如下：

    SELECT nickname
    FROM customer
    WHERE nickname LIKE '%阳光%'
    ORDER BY
      CASE
        WHEN nickname LIKE '阳光' THEN 0
        WHEN nickname LIKE '阳光%' THEN 1
        WHEN nickname LIKE '%阳光' THEN 3
        ELSE 2
      END

结果如下：

![][7]

使用 ORDER BY 并通过 CASE 进行判断，来返回排序结果，这样的方法从性能上可能存在问题，但是本身通过 %keyword% 查找就没有办法使用索引，而且管理后台的查询量就相对较少，通过上述方法可以很好的解决问题，最重要的是知道了 MySQL 上 ORDER 语句的一个新特性。 

## 参考

* [MySQL order by “best match”][6]

[1]: https://blog.yourtion.com/mysql-like-search-ordering.html?utm_source=tuicool&utm_medium=referral
[3]: https://blog.yourtion.com/categories.html#-ref
[4]: https://blog.yourtion.com/tags.html#MySql-ref
[5]: https://img1.tuicool.com/JRBnuaa.jpg
[6]: https://stackoverflow.com/questions/18725941/mysql-order-by-best-match
[7]: https://img1.tuicool.com/6Jj2YjM.jpg