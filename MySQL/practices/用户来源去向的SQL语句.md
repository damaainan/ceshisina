## 【SQL解题】用户来源去向的SQL语句你会写吗？ 

2017-09-20[数据分析师Nieson][0] 数据分析师Nieson **数据分析师Nieson** DataAnalystNieson

 玩转数据，挖掘数据，发现数据，利用数据！与数据分析爱好者一起，畅游数据的海洋！

最近遇到一道关于用户来源及去向的SQL题目，当时思考的时候存在考虑不周全的地方，故专门模拟数据从问题出发重新整理思路分享出来。

案例

 现有一批数据记录为某天多个用户app的使用情况，数据存放在record表中，其中有四个字段类型：

1. id：记录的id标识
1. user_id：用户的id标识
1. app_name：用户打开app的应用名称
1. create_time：用户打开app的时间

![][1]

 record表记录

 现要求得不同来源去向的用户数，即淘宝->京东、京东->唯品会等来源去向的用户数，并写出对应的SQL语句。

思路及解答

 首先，观察数据后发现用户可能有多次打开同一app和用户只打开一个app的情况。其次，打开app的时间都在某天所以这里不考虑按不同天来计算。这里我们需要将原问题拆解为小问题，需要思考的问题有（如有遗漏可在评论区留言）：

1. 如何表示用户使用app的来源及去向？
1. 怎么判断app的使用顺序？
1. 用户多次打开app会不会影响统计结果？
1. 用户只打开一个app并没有去向如何处理？
1. 如何计算不同来源去向的用户数？

 解决问题1和2需要查询到用打开app后下一个打开app是哪个。在SQL语言里面如果要得到两两交叉的结果，需要使用到交叉关联，同时需要是同个用户打开的app，具体实现代码如下：
```sql
SELECT
    a.user_id,
    a.app_name AS start_app,
    a.create_time AS start_time,
    b.app_name AS end_app,
    b.create_time AS end_time
FROM
    record a
JOIN record b
WHERE
    a.user_id = b.user_id
```
 

![][2]

 查询1的结果

 从上图的查询结果可以看到两个问题，交叉关联后来源与去向重合，来源app打开时间要大于去向打开时间。于是我们需要添加两个条件即start_app不等于end_app，start_time要小于end_time，修改后的代码如下：
```sql
SELECT
    a.user_id,
    a.app_name AS start_app,
    a.create_time AS start_time,
    b.app_name AS end_app,
    b.create_time AS end_time
FROM
    record a
JOIN record b
WHERE
    a.user_id = b.user_id
AND a.app_name != b.app_name
AND a.create_time < b.create_time
```
 

![][3]

 查询2的结果

 这样的处理同时问题4也解决，因为当用户只打开一个app的时候交叉关联后去向app还是自身，所以在上述操作中已经过滤。从上图可以发现1001用户在打开唯品会前打开过两次淘宝，故有两条来源去向的记录，这里就会造成重复统计，所以我们需要用户最后一次的来源去向记录即可，具体修改如下：
```sql
SELECT
    a.user_id,
    a.app_name AS start_app,
    MAX(a.create_time) AS start_time,
    b.app_name AS end_app,
    b.create_time AS end_time
FROM
    record a
JOIN record b
WHERE
    a.user_id = b.user_id
AND a.app_name != b.app_name
AND a.create_time < b.create_time
GROUP BY
    a.user_id,
    a.app_name,
    b.app_name
```
 

![][4]

 查询3的结果

 现在，我们需要计算不同来源去向的用户占比，即求得来源和去向分组后的user_id除重的结果，具体实现代码如下：
```sql
SELECT
    start_app,
    end_app,
    COUNT(DISTINCT user_id) AS user_num
FROM
    (
        SELECT
            a.user_id,
            a.app_name AS start_app,
            MAX(a.create_time) AS start_time,
            b.app_name AS end_app,
            b.create_time AS end_time
        FROM
            record a
        JOIN record b
        WHERE
            a.user_id = b.user_id
        AND a.app_name != b.app_name
        AND a.create_time < b.create_time
        GROUP BY
            a.user_id,
            a.app_name,
            b.app_name
    ) groups
GROUP BY
    start_app,
    end_app
```
 

![][5]

 最后汇总的结果

总结

 做SQL题目与写代码一样，重要的是能得到最终的结果，故查询效率最后考虑，需要优先考虑查询后的结果是否需要进行筛选以及重复记录的情况。将问题拆解为小问题有助于降低问题难度，同时也能对多方面有所考虑。总的来说这道SQL题目并不难，关键在于思路是否清晰，其次才是基础知识的考验。

[0]: ##
[1]: https://mmbiz.qpic.cn/mmbiz_png/yAyQKzCbAHbZic7hyaTAvibs093z12NN7yEbLUPw3JSTKia5scWiaZwTtCmQVhaialEnzqCRMyq6KZGkg83Hd4kh8CQ/
[2]: https://mmbiz.qpic.cn/mmbiz_png/yAyQKzCbAHbZic7hyaTAvibs093z12NN7yfHBfNLkAmsHhm283iabv6qq1WXXiaoEUvUkiaCU2HdlXwwzYdU6XqDPug/
[3]: https://mmbiz.qpic.cn/mmbiz_png/yAyQKzCbAHbZic7hyaTAvibs093z12NN7yI0Nmp5BCAnX4U80FVB76wicpz6rc9BNzAw4eBuYxcfw2Pe29bPNCAMA/
[4]: https://mmbiz.qpic.cn/mmbiz_png/yAyQKzCbAHbZic7hyaTAvibs093z12NN7y21fJ71F3siahYYx9OuHdar8ia4NYSoSMnmkgK7670oMypxP0e4ibVPOrw/
[5]: https://mmbiz.qpic.cn/mmbiz_png/yAyQKzCbAHbZic7hyaTAvibs093z12NN7yCcIHWib55K0MdDO7JwnDhLhuWGNvNvHZmtiaeSKKROQSgOUQ3hGvoXHA/