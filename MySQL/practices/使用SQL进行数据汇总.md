## 使用SQL进行数据汇总

来源：[http://datartisan.com/article/detail/295.html](http://datartisan.com/article/detail/295.html)

时间 2018-03-28 19:19:07

## 使用SQL进行数据汇总


![][0]
作者简介：Matt DeLand，Wagon联合创始人与数据科学家，毕业于哥伦比亚大学，曾任教于密歇根大学，其团队开发了一套SQL协同编辑器。

文章地址： [http://blog.yhat.com/posts/summarizing-data-in-SQL.html][14] 有删改


许多电脑使用Excel在面对上千行数据时已力不从心，而R较难部署在集群上运行，人眼显然不可能直接从大量数据中总结出规律。如何才能快速理解你的数据集？SQL可以帮助你！

对数据进行统计汇总是能最快了解数据的方法。面对一个新数据集时，人们往往会关心数据中的异常值、数据的分布形式、行列之间的关系等。SQL是一种专为数据计算设计的语言，其中已经内置了许多数据汇总函数，也支持用户编写SQL命令实现更为复杂的汇总需求。本文以香蕉销售相关数据为例，从4个方面介绍如何用SQL进行数据汇总。

![][1]

一、基础汇总

我们可以通过一段很短的SQL命令实现如计算个数（count）、去重（distinct）、求和（sum）、求平均（average）、求方差（variance）等汇总需求。假设我们有一个关于香蕉交易的数据表格，需要计算每天的顾客总数（num_customers）、去重顾客数（distinct_customers）、香蕉销量（total_bananas）、总收入（total_revenue）和每笔平均收入（revenue_per_sale），可以通过以下命令实现：

```sql
SELECT 
date,
count(*) as num_customers,
count(distinct user_id) as distinct_customers,
sum(bananas_sold) as total_bananas,
sum(revenue) as total_revenue,
avg(revenue) as revenue_per_sale
FROM banana_sales
GROUP BY date
ORDER BY date;
```

得到的结果如下：

![][2]


仅通过一次命令请求，我们就可以在非常大的数据集上计算出这些重要的汇总结果。如果再加上where或join命令，我们还可以高效地对数据进行切分。当然，有些需求并不能完全由一般的SQL函数实现。

![][3]

二、计算分位数

如果数据的分布存在较大的偏斜，平均值并不能告诉我们平均等待时间的分布情况。因此我们往往需要知道数据的25%、50%、75%分位数是多少。

许多数据库已经内建了分位数函数（包括Postgres 9.4、Redshift、SQL Server）。下面的例子使用percentile_cont函数计算等待时间的分位数。该函数是一个窗口函数，可以按天进行分组计算。

```sql
SELECT
date,
percentile_cont (0.25) WITHIN GROUP
(ORDER BY wait_time ASC) OVER(PARTITION BY date) as percentile_25,
percentile_cont (0.50) WITHIN GROUP
(ORDER BY wait_time ASC) OVER(PARTITION BY date) as percentile_50,
percentile_cont (0.75) WITHIN GROUP
(ORDER BY wait_time ASC) OVER(PARTITION BY date) as percentile_75,
avg(wait_time) as avg --用于比较
FROM banana_sales
GROUP BY date
ORDER BY date;
```

计算结果如下：

![][4]

其他窗口函数的结构和percentile_cont函数类似，我们可以指定对数据如何排序、如何分组。如果我们想要增加更多分组维度（如具体时间段），只需要将它们添加到partition和group by子句中。对于不支持percentile_cont的数据库，命令会更复杂一些，但仍然可以实现。主要问题是如何将每天的订单各自按等待时间递增的顺序排序，然后取出其中位数值。在MySQL中我们可以使用局部变量来跟踪订单，在Postgres中，我们可以使用row_number函数：

```sql
SELECT
t1.date,
t1.wait_time as median
FROM ( 
SELECT
date,
wait_time,
ROW_NUMBER() OVER(ORDER BY wait_time PARTITION BY date) as row_num
FROM banana_sales
) t
JOIN (
SELECT
date,
count(*) as total
FROM banana_sales
GROUP BY date
) t2
ON
t1.date = t2.date
WHERE t1.row_num =
  CASE when t2.total % 2 = 0 
THEN t2.total / 2
ELSE (t2.total + 1) / 2
END;
```

计算结果如下：

![][5]


![][6]
三、直方图

直方图是大致了解数据分布的好方法。我们可以用以下命令来计算每笔交易收入的分布：


```sql
select
revenue,
count(*)
from banana_sales
group by revenue
order by revenue;
```

由于每个不同的收入都会占用一行，以上命令的结果行数将会非常多。我们需要将收入值分组以方便我们得到数据分布的大致印象，比如分为 
![][7]
 5、 
![][8]
 10等组。如何分组并没有一个标准的做法，需要我们自己根据需要，进行实验来选择。组别过多和过少都不合理，一般使用20个左右的组即可，也可以指定分组的宽度，分组越宽，分组数就越少。以下是指定分组宽度的例子：

```sql
select 
floor(revenue/5.00)*5 as bucket_floor,
count(*) as count
from banana_sales
group by 1
order by 1;
```

计算结果如下：

![][9]
 这个命令将每个收入数据值向下取整到5的倍数并以此分组，即分组宽度为5。这种方法有个缺点，当某个区间内没有记录（比如在55-60美元之间没有人购买），那么结果中将不会有这个组别，这也可以通过编写更复杂的SQL语句来解决。如果我们想要自行选择区间的大小，首先需要计算数据的最大值和最小值，以便我们了解需要设定多少个区间。我们还可以用以下命令来使得每个区间有一个好看的标签：

```sql
select
    bucket_floor,
    CONCAT(bucket_floor, ' to ', bucket_ceiling) as bucket_name,
    count(*) as count
from (
select 
floor(revenue/5.00)*5 as bucket_floor,
floor(revenue/5.00)*5 + 5 as bucket_ceiling
from web_sessions_table
) a
group by 1, 2
order by 1;
```

得到的结果如下：

![][10]

![][11]

四、联合分布

比较两个不同的指标也是总结数据时的重要步骤。比如我们可能关心等待时间太久的人，最终是否会花费较少的钱。为了得到等待时间和收入之间的大致关系，我们可以使用以下命令：

```sql
select 
floor(wait_time/10.00)*10 as wait_time_bucket,
avg(revenue) as avg_revenue
from banana_sales
group by 1
order by 1;
```

得到的结果如下：

![][12]

我们可能也关心诸如协方差、方差这类统计指标。大多数SQL实现已经内建了这些统计函数，比如在Postgres或Redshift中我们可以使用以下命令：

```sql
select
corr(wait_time, revenue) as correlation,
covar_samp(wait_time, revenue) as covariance
from banana_sales;
```

Postgres中内建了诸多汇总函数，甚至包括线性回归。


[14]: http://blog.yhat.com/posts/summarizing-data-in-SQL.html
[0]: ./img/26JrAjQ.png 
[1]: ./img/yA7veme.png 
[2]: ./img/rQjYneF.png 
[3]: ./img/7JZZJbR.png 
[4]: ./img/z6bmEzJ.png 
[5]: ./img/mIVVJbF.png 
[6]: ./img/3qUZZze.png 
[7]: ./img/7faeEvJ.png 
[8]: ./img/JRjEFrR.png 
[9]: ./img/7ba2E33.png 
[10]: ./img/vqi2QnB.png 
[11]: ./img/aaqQBrI.png 
[12]: ./img/YbuYrai.png 