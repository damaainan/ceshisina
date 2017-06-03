# [SQL 经典回顾：JOIN 表连接操作不完全指南][0]

2017-01-16 分类：[数据库][1]、[编程开发][2]、[首页精华][3][0人评论][4]

**本文由[码农网][5] – 小峰原创翻译，转载请看清文末的转载要求，欢迎参与我们的[付费投稿计划][6]！**

也许最强大的SQL功能是JOIN操作。这让所有非关系数据库羡慕不已，因为当你想“合并”两个数据集时，这个概念是如此简单，并且又普遍适用。

简单地说，连接两个表，就是将一个表中的每一行与另一个表中的每一行结合起来。来自SQL Masterclass的插图展示了这个原理。

![venn-join1][7]

参见我们最近关于使用Venn图来说明JOIN的文章。上面的插图比较了INNER JOIN和不同的OUTER JOIN操作，但是这些并不是所有的可能性。让我们从更系统的角度来看问题。

请注意，每当本文中我说“X发生在Y之前”时，我的意思是“逻辑上，X发生在Y之前”。数据库优化器仍然可以选择在X之前执行Y，因为这更快，而不改变结果。有关操作的语法/逻辑顺序的更多信息[点击这里查看][8]。

好的，让我们一个个查看所有的join类型吧！

## CROSS JOIN（交叉连接）

最基本的JOIN操作是真正的笛卡尔乘积。它只是组合一个表中的每一行和另一个表中的每一行。维基百科通过一副卡片给出了笛卡尔乘积的最佳例子，交叉连接ranks表和suits表：

![venn-cross-product][9]

在现实世界的场景中，CROSS JOIN在执行报告时非常有用，例如，你可以生成一组日期（例如一个月的天数）并与数据库中的所有部门交叉连接，以创建完整的天/部门表。使用PostgreSQL语法：

    SELECT *
    
    -- This just generates all the days in January 2017
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    
    -- Here, we're combining all days with all departments
    CROSS JOIN departments

想象一下，我们有以下数据：

    +--------+   +------------+
    | day    |   | department |
    +--------+   +------------+
    | Jan 01 |   | Dept 1     |
    | Jan 02 |   | Dept 2     |
    | ...    |   | Dept 3     |
    | Jan 30 |   +------------+
    | Jan 31 |
    +--------+

现在结果将如下所示：

    +--------+------------+
    | day    | department |
    +--------+------------+
    | Jan 01 | Dept 1     |
    | Jan 01 | Dept 2     |
    | Jan 01 | Dept 3     |
    
    | Jan 02 | Dept 1     |
    | Jan 02 | Dept 2     |
    | Jan 02 | Dept 3     |
    | ...    | ...        |
    | Jan 31 | Dept 1     |
    | Jan 31 | Dept 2     |
    | Jan 31 | Dept 3     |
    +--------+------------+

现在，在每个天/部门组合中，你可以计算该部门的每日收入，或其他。

### 特点

CROSS JOIN是笛卡尔乘积，即“乘法”中的乘积。数学符号使用乘号表示此操作：A×B，或在本文例子中：days×departments。

与“普通”算术乘法一样，如果两个表中有一个为空（大小为零），则结果也将为空（大小为零）。这是完全有道理的。如果我们将前面的31天与0个部门组合，我们将获得0天/部门组合。同样的，如果我们将空日期范围与任何数量的部门组合，我们也会获得0天/部门组合。

换一种说法：

    size(result) = size(days) * size(departments)

### 替代语法

以前，在ANSI JOIN语法被引入到SQL之前，大家就会在FROM子句中写以逗号分隔的表格列表来编写CROSS JOIN。上面的查询等价于：

    SELECT *
    FROM
      generate_series(
        '2017-01-01'::TIMESTAMP,
        '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
        INTERVAL '1 day'
      ) AS days(day),
      departments

一般来说，我强烈建议使用CROSS JOIN关键字，而不是以逗号分隔的表格列表，因为如果你有意地想要执行CROSS JOIN，那么没有什么可以比使用实际的关键字能更好地传达这个意图（对下一个开发人员而言）。何况用逗号分隔的表格列表中有这么多地方都有可能会出错。你肯定不希望看到这样的事情！

## INNER JOIN（Theta-JOIN）

构建在先前的CROSS JOIN操作之上，INNER JOIN（或者只是简单的JOIN，有时也称为“THETA”JOIN）允许通过某些谓词过滤笛卡尔乘积的结果。大多数时候，我们把这个谓词放在ON子句中，它可能是这样的：

    SELECT *
    
    -- Same as before
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    
    -- Now, exclude all days/departments combinations for
    -- days before the department was created
    JOIN departments AS d ON day >= d.created_at

在大多数数据库中，INNER关键字是可选的，因此我在本文中略去了。

请注意INNER JOIN操作是如何允许在ON子句中放置任意谓词的，这在执行报告时也非常有用。就像在之前的CROSS JOIN示例中一样，我们将所有日期与所有部门结合在一起，但是我们只保留那些部门已经存在的天/部门组合，即部门创建在天之前。

再次，使用此数据：

    +--------+   +------------+------------+
    | day    |   | department | created_at |
    +--------+   +------------+------------+
    | Jan 01 |   | Dept 1     | Jan 10     |
    | Jan 02 |   | Dept 2     | Jan 11     |
    | ...    |   | Dept 3     | Jan 12     |
    | Jan 30 |   +------------+------------+
    | Jan 31 |
    +--------+

现在结果将如下所示：

    +--------+------------+
    | day    | department |
    +--------+------------+
    | Jan 10 | Dept 1     |
    
    | Jan 11 | Dept 1     |
    | Jan 11 | Dept 2     |
    
    | Jan 12 | Dept 1     |
    | Jan 12 | Dept 2     |
    | Jan 12 | Dept 3     |
    
    | Jan 13 | Dept 1     |
    | Jan 13 | Dept 2     |
    | Jan 13 | Dept 3     |
    | ...    | ...        |
    | Jan 31 | Dept 1     |
    | Jan 31 | Dept 2     |
    | Jan 31 | Dept 3     |
    +--------+------------+

因此，我们在1月10日之前没有任何结果，因为这些行被过滤掉了。

### 特点

INNER JOIN操作是过滤后的CROSS JOIN操作。这意味着如果两个表中有一个是空的，那么结果也保证为空。但是与CROSS JOIN不同的是，由于谓词的存在，我们总能获得比CROSS JOIN提供的更少的结果。

换一种说法：

    size(result) <= size(days) * size(departments)

### 替代语法

虽然ON子句对于INNER JOIN操作是强制的，但是你不需要在其中放置JOIN谓词（虽然从可读性角度强烈推荐）。大多数数据库将以同样的方式优化以下等价查询：

    SELECT *
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    
    -- You can always JOIN .. ON true (or 1 = 1 in other DBs)
    -- to turn an syntactic INNER JOIN into a semantic CROSS JOIN
    JOIN departments AS d ON true
    
    -- ... and then turn the CROSS JOIN back into an INNER JOIN
    -- by putting the JOIN predicate in the WHERE clause:
    WHERE day >= d.created_at

当然，再次，那只是为读者模糊了查询，但你可能有你的理由，对吧？如果我们进一步，那么下面的查询也是等效的，因为大多数优化器可以指出等价物并转而执行INNER JOIN：

    SELECT *
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    
    -- Now, this is really a syntactic CROSS JOIN
    CROSS JOIN departments AS d
    WHERE day >= d.created_at

…并且，如前所述，CROSS JOIN只是用逗号分隔的表格列表的语法糖。在这种情况下，我们保留WHERE子句以获得在引入ANSI JOIN语法之前人们经常做的事情：

    SELECT *
    FROM
      generate_series(
        '2017-01-01'::TIMESTAMP,
        '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
        INTERVAL '1 day'
      ) AS days(day),
      departments AS d
    WHERE day >= d.created_at

所有这些语法都了做同样的事情，通常没有性能损失，但显然，它们比原始的INNER JOIN语法更不可读。

## EQUI JOIN

有时，在著作中，你会听到EQUI JOIN这个术语，其中“EQUI”不是SQL关键字，而只是作为一种特殊的INNER JOIN写法。

事实上，令人奇怪的是“EQUI”JOIN是特殊情况，因为我们在SQL中EQUI JOIN做得最多，并且在OLTP应用程序中，我们只是通过主键/外键关系JOIN。例如：

    SELECT *
    FROM actor AS a
    JOIN film_actor AS fa ON a.actor_id = fa.actor_id
    JOIN film AS f ON f.film_id = fa.film_id

上述查询选择了所有演员及其电影。有两个INNER JOIN操作，一个将actors连接到film_actor关系表中的相应条目（因为演员可以演许多电影，而电影可以有许多演员出演），并且另一个连接每个film_actor与关于电影本身的附加信息的关系。

### 特点

该操作的特点与“一般的”INNER JOIN操作的特点相同。“EQUI”JOIN仍然结果集减少了的笛卡尔乘积（CROSS JOIN），即仅包含给定演员在给定电影中实际播放的那些演员/电影组合。

因此，换句话说：

    size(result) <= size(actor) * size(film)

结果大小等于演员大小乘以电影大小，但是每个演员在每部电影中都出演是不太可能的。

### 替代语法：USING

再次，和前面一样，我们可以写INNER JOIN操作，而不使用实际的INNER JOIN语法，而是使用CROSS JOIN或以逗号分隔的表格列表。这很无聊，但更有趣的是以下两种替代语法，其中之一是非常有用的：

    SELECT *
    FROM actor
    JOIN film_actor USING (actor_id)
    JOIN film USING (film_id)

USING子句替换ON子句，并允许列出必须在JOIN操作的两侧出现的一组列。如果你以与Sakila数据库相同的方式仔细设计数据库，即每个外键列具有与它们引用的主键列相同的名称（例如actor.actor_id = film_actor.actor_id），那么你至少可以在这些数据库中使用USING 用于“EQUI”JOIN：

* Derby
* Firebird
* HSQLDB
* Ingres
* MariaDB
* MySQL
* Oracle
* PostgreSQL
* SQLite
* Vertica

不幸的是，这些数据库不支持这个语法：

* Access
* Cubrid
* DB2
* H2
* HANA
* Informix
* SQL Server
* Sybase ASE
* Sybase SQL Anywhere

虽然这产生的结果与ON子句完全相同（几乎相同），但读取和写入更快。我之所以“几乎”是因为一些数据库（以及SQL标准）指定，任何出现在USING子句中的列失去其限定。例如：

    SELECT
      f.title,   -- Ordinary column, can be qualified
      f.film_id, -- USING column, shouldn't be qualified
      film_id    -- USING column, correct / non-ambiguous here
    FROM actor AS a
    JOIN film_actor AS fa USING (actor_id)
    JOIN film AS f USING (film_id)

另外，当然，这种语法有点限制。有时，你的表中有多个外键，但不是所有键都具有主键列名称。例如：

    CREATE TABLE film (
      ..
      language_id          BIGINT REFERENCES language,
      original_language_id BIGINT REFERENCES language,
    )

如果你想通过ORIGINAL_LANGUAGE_ID连接，则必须诉诸ON子句。

### 备选语法：NATURAL JOIN

“EQUI”JOIN的一个更极端和更少有用的形式是NATURAL JOIN子句。前面的例子可以通过NATURAL JOIN替换USING来进一步“改进”，像这样：

    SELECT *
    FROM actor
    NATURAL JOIN film_actor
    NATURAL JOIN film

请注意，我们不再需要指定任何JOIN标准，因为NATURAL JOIN将自动从它加入的两个表中获取所有共享相同名称的列，并将它们放置在“隐藏”的USING子句中。正如我们前面所看到的，由于主键和外键具有相同的列名，这看起来很有用。

真相是：这是没用的。在Sakila数据库中，每个表还有一个LAST_UPDATE列，这是NATURAL JOIN会自动考虑的。因此NATURAL JOIN查询等价于：

    SELECT *
    FROM actor
    JOIN film_actor USING (actor_id, last_update)
    JOIN film USING (film_id, last_update)

…这当然完全没有任何意义。所以，马上将NATURAL JOIN抛之脑后吧（除了一些非常罕见的情况，例如当连接Oracle的诊断视图 ，如v$sql NATURAL JOIN v$sql_plan等，用于ad-hoc分析）

## OUTER JOIN

我们之前已经见识过INNER JOIN，它仅针对左/右表的组合返回结果，其中ON谓词产生true。

OUTER JOIN允许我们保留rowson的左/ 右侧，因此我们就找不到匹配的组合。让我们回到日期和部门的例子：

    SELECT *
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    LEFT JOIN departments AS d ON day >= d.created_at

同样，OUTER关键字是可选的，所以我在示例中省略了它。

此查询与INNER JOIN计数器部分有着非常微妙的不同，它每天总会返回至少一行，即使在给定的某一天没有在该天创建的部门。例如：所有部门都在1月10日创建。上述查询仍将返回1月1日至9日：

    +--------+   +------------+------------+
    | day    |   | department | created_at |
    +--------+   +------------+------------+
    | Jan 01 |   | Dept 1     | Jan 10     |
    | Jan 02 |   | Dept 2     | Jan 11     |
    | ...    |   | Dept 3     | Jan 12     |
    | Jan 30 |   +------------+------------+
    | Jan 31 |
    +--------+

除了我们之前在INNER JOIN示例中获得的行之外，我们现在还有从1月1日到9日的所有日期，其中包含NULL部门：

    +--------+------------+
    | day    | department |
    +--------+------------+
    | Jan 01 |            | -- Extra rows with no match here
    | Jan 02 |            | -- Extra rows with no match here
    | ...    |            | -- Extra rows with no match here
    | Jan 09 |            | -- Extra rows with no match here
    | Jan 10 | Dept 1     |
    | Jan 11 | Dept 1     |
    | Jan 11 | Dept 2     |
    | Jan 12 | Dept 1     |
    | Jan 12 | Dept 2     |
    | Jan 12 | Dept 3     |
    | Jan 13 | Dept 1     |
    | Jan 13 | Dept 2     |
    | Jan 13 | Dept 3     |
    | ...    | ...        |
    | Jan 31 | Dept 1     |
    | Jan 31 | Dept 2     |
    | Jan 31 | Dept 3     |
    +--------+------------+

换句话说，每一天在结果中至少出现一次。 LEFT JOIN对左表执行此操作，即它保留结果中来自左表的所有行。

正式地说，LEFT OUTER JOIN是一个像这样带有UNION的INNER JOIN：

    -- Convenient syntax:
    SELECT *
    FROM a LEFT JOIN b ON <predicate>
    
    -- Cumbersome, equivalent syntax:
    SELECT a.*, b.*
    FROM a JOIN b ON <predicate>
    UNION ALL
    SELECT a.*, NULL, NULL, ..., NULL
    FROM a
    WHERE NOT EXISTS (
      SELECT * FROM b WHERE <predicate>
    )

### RIGHT OUTER JOIN

RIGHT OUTER JOIN正好相反。它保留结果中来自右表的所有行。让我们添加更多部门

    +--------+   +------------+------------+
    | day    |   | department | created_at |
    +--------+   +------------+------------+
    | Jan 01 |   | Dept 1     | Jan 10     |
    | Jan 02 |   | Dept 2     | Jan 11     |
    | ...    |   | Dept 3     | Jan 12     |
    | Jan 30 |   | Dept 4     | Apr 01     |
    | Jan 31 |   | Dept 5     | Apr 02     |
    +--------+   +------------+------------+

新的部门4和5将不会在以前的结果中，因为它们是在1月31日之后的某一天创建的。但是它将显示在右连接结果中，因为部门是连接操作的右表，并且来自右表中的所有行都将被保留。

运行此查询：

    SELECT *
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    RIGHT JOIN departments AS d ON day >= d.created_at

将产生：

    +--------+------------+
    | day    | department |
    +--------+------------+
    | Jan 10 | Dept 1     |
    | Jan 11 | Dept 1     |
    | Jan 11 | Dept 2     |
    | Jan 12 | Dept 1     |
    | Jan 12 | Dept 2     |
    | Jan 12 | Dept 3     |
    | Jan 13 | Dept 1     |
    | Jan 13 | Dept 2     |
    | Jan 13 | Dept 3     |
    | ...    | ...        |
    | Jan 31 | Dept 1     |
    | Jan 31 | Dept 2     |
    | Jan 31 | Dept 3     |
    |        | Dept 4     | -- Extra rows with no match here
    |        | Dept 5     | -- Extra rows with no match here
    +--------+------------+

在大多数情况下，每个LEFT OUTER JOIN表达式都可以转换为等效的RIGHT OUTER JOIN表达式，反之亦然。因为RIGHT OUTER JOIN通常不太可读，大多数人只使用LEFT OUTER JOIN。

### FULL OUTER JOIN

最后，还有FULL OUTER JOIN，它保留JOIN操作两侧的所有行。在我们的示例中，这意味着每一天在结果中至少出现一次，就像每个部门在结果中至少出现一次一样。

让我们再来看一下这个数据：

    +--------+   +------------+------------+
    | day    |   | department | created_at |
    +--------+   +------------+------------+
    | Jan 01 |   | Dept 1     | Jan 10     |
    | Jan 02 |   | Dept 2     | Jan 11     |
    | ...    |   | Dept 3     | Jan 12     |
    | Jan 30 |   | Dept 4     | Apr 01     |
    | Jan 31 |   | Dept 5     | Apr 02     |
    +--------+   +------------+------------+

现在，让我们运行这个查询：

    SELECT *
    FROM generate_series(
      '2017-01-01'::TIMESTAMP,
      '2017-01-01'::TIMESTAMP + INTERVAL '1 month -1 day',
      INTERVAL '1 day'
    ) AS days(day)
    FULL JOIN departments AS d ON day >= d.created_at

现在结果看起来像这样：

    +--------+------------+
    | day    | department |
    +--------+------------+
    | Jan 01 |            | -- row from the left table
    | Jan 02 |            | -- row from the left table
    | ...    |            | -- row from the left table
    | Jan 09 |            | -- row from the left table
    | Jan 10 | Dept 1     |
    | Jan 11 | Dept 1     |
    | Jan 11 | Dept 2     |
    | Jan 12 | Dept 1     |
    | Jan 12 | Dept 2     |
    | Jan 12 | Dept 3     |
    | Jan 13 | Dept 1     |
    | Jan 13 | Dept 2     |
    | Jan 13 | Dept 3     |
    | ...    | ...        |
    | Jan 31 | Dept 1     |
    | Jan 31 | Dept 2     |
    | Jan 31 | Dept 3     |
    |        | Dept 4     | -- row from the right table
    |        | Dept 5     | -- row from the right table 
    +--------+------------+

如果你坚持，正式地说来，LEFT OUTER JOIN是一个像这样带有UNION的INNER JION：

    -- Convenient syntax:
    SELECT *
    FROM a LEFT JOIN b ON <predicate>
    
    -- Cumbersome, equivalent syntax:
    SELECT a.*, b.*
    FROM a JOIN b ON <predicate>
    -- LEFT JOIN part
    UNION ALL
    SELECT a.*, NULL, NULL, ..., NULL
    FROM a
    WHERE NOT EXISTS (
      SELECT * FROM b WHERE <predicate>
    )
    -- RIGHT JOIN part
    UNION ALL
    SELECT NULL, NULL, ..., NULL, b.*
    FROM b
    WHERE NOT EXISTS (
      SELECT * FROM a WHERE <predicate>
    )

### 备选语法：“EQUI”OUTER JOIN

上面的例子再次使用了某种“带过滤器的笛卡尔积”JOIN。然而，更常见的是“EQUI”OUTER JOIN方法，其中我们连接了主键/外键关系。让我们回到Sakila数据库示例。一些演员没有在任何电影中出演，那么我们可能希望像这样查询：

    SELECT *
    FROM actor
    LEFT JOIN film_actor USING (actor_id)
    LEFT JOIN film USING (film_id)

此查询将返回所有actors至少一次，无论他们是否在电影中出演。如果我们还想要所有没有演员的电影，那么我们可以使用FULL OUTER JOIN来组合结果：

    SELECT *
    FROM actor
    FULL JOIN film_actor USING (actor_id)
    FULL JOIN film USING (film_id)

当然，这也适用于NATURAL LEFT JOIN，NATURAL RIGHT JOIN，NATURAL FULL JOIN，但同样的，这些都没有用，因为我们将再次使用USING（…，LAST_UPDATE），这使之完全没有任何意义。

### 备选语法：Oracle和SQL Server style OUTER JOIN

这两个数据库在ANSI语法建立之前有OUTER JOIN。它看起来像这样：

    -- Oracle
    SELECT *
    FROM actor a, film_actor fa, film f
    WHERE a.actor_id = fa.actor_id(+)
    AND fa.film_id = f.film_id(+)
    
    -- SQL Server
    SELECT *
    FROM actor a, film_actor fa, film f
    WHERE a.actor_id *= fa.actor_id
    AND fa.film_id *= f.film_id

很好，假定某个时间点（在80年代？？），ANSI没有指定OUTER JOIN。但80年代是在30多年前，所以，可以安全地说这个东西已经过时了。

SQL Server做了正确的事情，很久以前就弃用（以及后面删除）了语法。因为向后兼容性的原因，Oracle仍然支持。

但是关于这种语法没有什么是合理或可读的。所以不要使用它。用ANSI JOIN替换。

### PARTITIONED OUTER JOIN 

这是Oracle特定的，但我必须说，这是一个真正的耻辱，因为没有其他数据库偷窃该功能。还记住我们用来将每一天与每个部门组合的CROSS JOIN操作？因为，有时，这是我们想要的结果：所有的组合，并且如果有一个匹配的话也匹配行中的值。

这很难用文字描述，用例子讲就容易多了。下面是使用Oracle语法的查询：

    WITH
    
      -- Using CONNECT BY to generate all dates in January
      days(day) AS (
        SELECT DATE '2017-01-01' + LEVEL - 1
        FROM dual
        CONNECT BY LEVEL <= 31
      ),
    
      -- Our departments
      departments(department, created_at) AS (
        SELECT 'Dept 1', DATE '2017-01-10' FROM dual UNION ALL
        SELECT 'Dept 2', DATE '2017-01-11' FROM dual UNION ALL
        SELECT 'Dept 3', DATE '2017-01-12' FROM dual UNION ALL
        SELECT 'Dept 4', DATE '2017-04-01' FROM dual UNION ALL
        SELECT 'Dept 5', DATE '2017-04-02' FROM dual
      )
    SELECT *
    FROM days 
    LEFT JOIN departments 
      PARTITION BY (department) -- This is where the magic happens
      ON day >= created_at

不幸的是，PARTITION BY用在具有不同含义的各种上下文中（例如针对窗口函数）。在这种情况下，这意味着我们通过departments.department 列“partition” 我们的数据，为每个部门创建一 个“partition”。现在 ，每个（partition）分区 将获得 每一天的副本，无论在我们的谓词中是否有匹配（不像在普通的LEFT JOIN情况下，我们有一堆“缺少部门”的日期）。上面的查询结果现在是这样的：

    +--------+------------+------------+
    | day    | department | created_at |
    +--------+------------+------------+
    | Jan 01 | Dept 1     |            | -- Didn't match, but still get row
    | Jan 02 | Dept 1     |            | -- Didn't match, but still get row
    | ...    | Dept 1     |            | -- Didn't match, but still get row
    | Jan 09 | Dept 1     |            | -- Didn't match, but still get row
    | Jan 10 | Dept 1     | Jan 10     | -- Matches, so get join result
    | Jan 11 | Dept 1     | Jan 10     | -- Matches, so get join result
    | Jan 12 | Dept 1     | Jan 10     | -- Matches, so get join result
    | ...    | Dept 1     | Jan 10     | -- Matches, so get join result
    | Jan 31 | Dept 1     | Jan 10     | -- Matches, so get join result
    
    | Jan 01 | Dept 2     |            | -- Didn't match, but still get row
    | Jan 02 | Dept 2     |            | -- Didn't match, but still get row
    | ...    | Dept 2     |            | -- Didn't match, but still get row
    | Jan 09 | Dept 2     |            | -- Didn't match, but still get row
    | Jan 10 | Dept 2     |            | -- Didn't match, but still get row
    | Jan 11 | Dept 2     | Jan 11     | -- Matches, so get join result
    | Jan 12 | Dept 2     | Jan 11     | -- Matches, so get join result
    | ...    | Dept 2     | Jan 11     | -- Matches, so get join result
    | Jan 31 | Dept 2     | Jan 11     | -- Matches, so get join result
    
    | Jan 01 | Dept 3     |            | -- Didn't match, but still get row
    | Jan 02 | Dept 3     |            | -- Didn't match, but still get row
    | ...    | Dept 3     |            | -- Didn't match, but still get row
    | Jan 09 | Dept 3     |            | -- Didn't match, but still get row
    | Jan 10 | Dept 3     |            | -- Didn't match, but still get row
    | Jan 11 | Dept 3     |            | -- Didn't match, but still get row
    | Jan 12 | Dept 3     | Jan 12     | -- Matches, so get join result
    | ...    | Dept 3     | Jan 12     | -- Matches, so get join result
    | Jan 31 | Dept 3     | Jan 12     | -- Matches, so get join result
    
    | Jan 01 | Dept 4     |            | -- Didn't match, but still get row
    | Jan 02 | Dept 4     |            | -- Didn't match, but still get row
    | ...    | Dept 4     |            | -- Didn't match, but still get row
    | Jan 31 | Dept 4     |            | -- Didn't match, but still get row
    
    | Jan 01 | Dept 5     |            | -- Didn't match, but still get row
    | Jan 02 | Dept 5     |            | -- Didn't match, but still get row
    | ...    | Dept 5     |            | -- Didn't match, but still get row
    | Jan 31 | Dept 5     |            | -- Didn't match, but still get row
    +--------+------------+

正如你所看到的，我已经为5个部门创建了5个分区。每个分区通过每一天来组合部门，但不像CROSS JOIN时做的那样，我们现在实际得到的是LEFT JOIN .. ON ..结果，万一谓词有匹配的话。这在Oracle报告中是一个非常好的功能！

## SEMI JOIN

在关系代数中，存在半连接操作的概念，遗憾的是这在SQL中没有语法表示。如果有语法的话，可能会是LEFT SEMI JOIN和RIGHT SEMI JOIN，就像Cloudera Impala语法扩展提供的那样。

### 什么是“SEMI” JOIN？

当写下如下虚构查询时：

    SELECT *
    FROM actor
    LEFT SEMI JOIN film_actor USING (actor_id)

我们真正的意思是，我们想要电影中出演的所有演员。但我们不想在结果中出现任何电影，只要演员。更具体地说，我们不想让每个演员出现多次，即一部电影出现一次。我们希望每个演员在结果中只出现一次（或零次）。

Semi在拉丁语中为“半”的意思，即我们只实现“半连接”，在这种情况下，即左半部分。

在SQL中，有两个可以模拟“SEMI”JOIN的替代语法

### 备选语法：EXISTS

这是更强大和更冗长的语法

    SELECT *
    FROM actor a
    WHERE EXISTS (
      SELECT * FROM film_actor fa
      WHERE a.actor_id = fa.actor_id
    )

我们正在寻找存在于一部电影的所有演员，即在电影中演出的演员。使用这种语法（即，“SEMI”JOIN被放置在WHERE子句中），很明显我们可以在结果中最多得到每个演员一次。语法中没有实际的JOIN。

尽管如此，大多数数据库能够识别这里真正发生的是“SEMI”JOIN，而不仅仅是一个普通的EXISTS()谓词。例如，对上述查询考虑Oracle执行计划：

![hash-join-semi][10]

注意Oracle如何调用操作“HASH JOIN（SEMI）” ——此处存在SEMI关键字。 PostgreSQL也是这样：

![nested-loop-semi-join][11]

或SQL Server：

![nested-loops-left-semi-join][12]

除了是正确的最佳解决方案，使用“SEMI”JOIN而不是INNER JOIN也有一些性能优势，因为数据库可以在找到第一个匹配后立即停止查找匹配项！

### 替代语法：IN

IN和EXISTS完全等同于“SEMI”JOIN模拟。以下查询将在大多数数据库（不是MySQL）中生成与先前EXISTS查询相同的计划：

    SELECT *
    FROM actor
    WHERE actor_id IN (
      SELECT actor_id FROM film_actor
    )

如果你的数据库支持“SEMI”JOIN操作的两种语法，你或许可以从文体的角度选择你喜欢的。

这与下面的JOIN是不一样的。

## ANTI JOIN

原则上，“ANTI”JOIN正好与“SEMI”JOIN相反。当写下如下虚构查询时：

    SELECT *
    FROM actor
    LEFT ANTI JOIN film_actor USING (actor_id)

…我们正在做的是找出所有没有在任何电影中出演的演员。不幸的是，再次，SQL并没有这个操作的内置语法，但我们可以用EXISTS来模拟它：

### 替代语法：NOT EXISTS

以下查询正好有预期的语义：

    SELECT *
    FROM actor a
    WHERE NOT EXISTS (
      SELECT * FROM film_actor fa
      WHERE a.actor_id = fa.actor_id
    )

### （危险）替代语法：NOT IN

小心！虽然EXISTS和IN是等效的，但NOT EXISTS和NOT IN是不等效的。因为NULL值！

在这种特殊情况下，下面的NOT IN查询等同于先前的NOT EXISTS查询，因为我们的film_actor表在film_actor.actor_id上有一个NOT NULL约束

    SELECT *
    FROM actor
    WHERE actor_id NOT IN (
      SELECT actor_id FROM film_actor
    )

然而，如果actor_id变为可空，那么查询将是错误的。不相信吗？尝试运行：

    SELECT *
    FROM actor
    WHERE actor_id NOT IN (1, 2, NULL)

它不会返回任何记录。为什么？因为NULL在SQL中是UNKNOWN值。所以，上面的谓词如下是一样的：

    SELECT *
    FROM actor
    WHERE actor_id NOT IN (1, 2, UNKNOWN)

并且因为我们不能确定actor_id是否在一个值为UNKNOWN（是4？还是5？抑或-1？）的一组值中，因此整个谓词变为UNKNOWN

    SELECT *
    FROM actor
    WHERE UNKNOWN

如果你想了解更多，这里有一篇Joe Celko写的[关于三值逻辑的好文章][13]。

当然，这样还不够：

> 不要在SQL中使用NOT IN谓词，除非你添加常量，非空值。  
 > ——Lukas Eder。现 > 在。

甚至不要在存在NOT NULL约束时进行冒险。也许，一些DBA可能暂时关闭约束来加载一些数据，但是你的查询当下却会是错的。只使用NOT EXISTS。或者，在某些情况下…

### （危险）替代语法：LEFT JOIN / IS NULL

奇怪的是，有些人喜欢以下语法：

    SELECT *
    FROM actor a
    LEFT JOIN film_actor fa
    USING (actor_id)
    WHERE film_id IS NULL

这是正确的，因为我们：

* 连接电影加到演员
* 保留所有演员而不保留电影（LEFT JOIN）
* 保留没有出演电影的演员（film_id IS NULL）

好吧，我个人不怎么喜欢这种语法，因为它一点也没有传达“ANTI”JOIN的意图。而且有可能会很慢，因为你的优化器不认为这是一个“ANTI”JOIN操作（或者事实上，它不能正式证明它可能是）。所以，再次，使用NOT EXISTS代替。

一个有趣的（但有点过时）博客文章比较了这三个语法：

[https://explainextended.com/2009/09/15/not-in-vs-not-exists-vs-left-join-is-null-sql-server][14]

## LATERAL JOIN

LATERAL是SQL标准中相对较新的关键字，并且它得到了PostgreSQL和Oracle的支持。SQL Server人员有一个特定于供应商的替代语法，总是使用APPLY关键字（这个我个人更喜欢）。让我们看一个使用PostgreSQL / Oracle LATERAL关键字的例子：

    WITH
      departments(department, created_at) AS (
        VALUES ('Dept 1', DATE '2017-01-10'),
               ('Dept 2', DATE '2017-01-11'),
               ('Dept 3', DATE '2017-01-12'),
               ('Dept 4', DATE '2017-04-01'),
               ('Dept 5', DATE '2017-04-02')
      )
    SELECT *
    FROM departments AS d
    CROSS JOIN LATERAL generate_series(
      d.created_at, -- We can dereference a column from department!
      '2017-01-31'::TIMESTAMP, 
      INTERVAL '1 day'
    ) AS days(day)

事实上，与其在所有部门和所有日子之间进行CROSS JOIN，为什么不直接为每个部门生成必要的日期？这就是LATERAL的作用。它是任何JOIN操作（包括INNER JOIN，LEFT OUTER JOIN等）右侧的前缀，允许右侧从左侧访问列。

这当然与关系代数不再有关，因为它强加了一个JOIN顺序（从左到右）。有时，这是OK的，有时，你的表值函数（或子查询）是如此复杂，于是那通常是你可以实际使用它的唯一方法。

另一个非常受欢迎的用例是将“TOP-N”查询连接到常规表中。 如果你想找到每个演员，以及他们最畅销的TOP 5电影：

    SELECT a.first_name, a.last_name, f.*
    FROM actor AS a
    LEFT OUTER JOIN LATERAL (
      SELECT f.title, SUM(amount) AS revenue
      FROM film AS f
      JOIN film_actor AS fa USING (film_id)
      JOIN inventory AS i USING (film_id)
      JOIN rental AS r USING (inventory_id)
      JOIN payment AS p USING (rental_id)
      WHERE fa.actor_id = a.actor_id
      GROUP BY f.film_id
      ORDER BY revenue DESC
      LIMIT 5
    ) AS f
    ON true

结果可能会是：

![outer-join-lateral-result][15]

不要担心派生表中一长串的连接，这就是我们如何在Sakila数据库中从FILM表到PAYMENT表获取的原理：

![sakila1][16]

基本上，子查询计算每个演员最畅销的TOP 5电影。 因此，它不是“经典的”派生表，而是返回多个行和一列的相关子查询。 我们都习惯于写这样的相关子查询：

    SELECT
      a.first_name, 
      a.last_name, 
      (SELECT count(*) 
       FROM film_actor AS fa 
       WHERE fa.actor_id = a.actor_id) AS films
    FROM actor AS a

特点：

LATERAL关键字并没有真正改变被应用的JOIN类型的语义。如果你运行CROSS JOIN LATERAL，结果大小仍然是

    size(result) = size(left table) * size(right table)

即使右表是在左列表每行的基础上产生的。

你也可以使用OUTER JOIN来使用LATERAL，即使你的表函数不返回右侧的任何行，这样的情况下，左侧的行也将被保留。

### 替代语法：APPLY

SQL Server没有选择混乱的LATERAL关键字，它们很久以前就引入了APPLY关键字（更具体地说：CROSS APPLY和OUTER APPLY），这更有意义，因为我们对表的每一行应用一个函数。让我们假设我们在SQL Server中有一个generate_series()函数：

    -- Use with care, this is quite inefficient!
    CREATE FUNCTION generate_series(@d1 DATE, @d2 DATE)
    RETURNS TABLE AS
    RETURN
      WITH t(d) AS (
        SELECT @d1 
        UNION ALL
        SELECT DATEADD(day, 1, d) 
        FROM t
        WHERE d < @d2
      ) 
      SELECT * FROM t;

然后，我们可以使用CROSS APPLY为每个部门调用函数：

    WITH
      departments AS (
        SELECT * FROM (
          VALUES ('Dept 1', CAST('2017-01-10' AS DATE)),
                 ('Dept 2', CAST('2017-01-11' AS DATE)),
                 ('Dept 3', CAST('2017-01-12' AS DATE)),
                 ('Dept 4', CAST('2017-04-01' AS DATE)),
                 ('Dept 5', CAST('2017-04-02' AS DATE))
        ) d(department, created_at)
      )
    SELECT *
    FROM departments AS d
    CROSS APPLY dbo.generate_series(
      d.created_at, -- We can dereference a column from department!
      CAST('2017-01-31' AS DATE)
    )

这个语法的好处是——再次——我们对表的每一行应用一个函数，并且该函数产生行。听起来耳熟？在Java 8中，我们将对此使用Stream.flatMap()！考虑以下流的使用：

    departments.stream()
    .flatMap(department -> generateSeries(
                department.createdAt, 
                 LocalDate.parse("2017-01-31"))
                 .map(day -> tuple(department, day))
    );

这里发生了什么？

* DEPARTMENTS表只是一个Java部门流
* 我们使用一个为每个部门生成元组的函数来映射department流
* 这些元组包括部门本身，以及从部门CreatedAt日期开始的一系列日期中生成的一天

同样的故事！ SQL CROSS APPLY / CROSS JOIN LATERAL与Java的Stream.flatMap()是一样的。事实上，SQL和流并不是太不同。有关更多信息，请阅读此博客文章。

注意：就像我们可以编写LEFT OUTER JOIN LATERAL一样，我们还可以编写OUTER APPLY，以便保留JOIN表达式的左侧。

## MULTISET

很少数据库实现这个（实际上，只有Oracle），但如果你思考它的话，它真的是一个超棒的JOIN类型。创建了嵌套集合。如果所有数据库都实现它的话，那么我们就不需要ORM！

来一个假设的例子（使用SQL标准语法，而不是Oracle的），像这样：

    SELECT a.*, MULTISET (
      SELECT f.*
      FROM film AS f
      JOIN film_actor AS fa USING (film_id)
      WHERE a.actor_id = fa.actor_id
    ) AS films
    FROM actor

MULTISET运算符使用相关子查询参数，并在嵌套集合中聚合其所有生成的行。这和LEFT OUTER JOIN（我们得到了所有的演员，并且如果他们参演电影的话，我们也得到了他们的所有电影）的工作方式类似，但不是复制结果集中的所有演员，而是将它们收集到嵌套集合中。

就像我们在ORM中所做的那样，当获取事物到这个结构中时：

    @Entity
    class Actor {
      @ManyToMany
      List<Film> films;
    }
    
    @Entity
    class Film {
    }

忽略使用的JPA注释的不完整性，我只想展示嵌套集合的强度。与在ORM中不同，SQL MULTISET运算符允许将相关子查询的任意结果收集到嵌套集合中——而不仅仅是实际实体。这比ORM强上百万倍。

### 备代语法：Oracle

正如我所说，Oracle实际上支持MULTISET，但是你不能创建ad-hoc嵌套集合。由于某种原因，Oracle选择为这些嵌套集合实现名义类型化，而不是通常的SQL样式结构类型化。所以你必须提前声明你的类型：

    CREATE TYPE film_t AS OBJECT ( ... );
    CREATE TYPE film_tt AS TABLE OF FILM;
    
    SELECT
      a.*, 
      CAST (
        MULTISET (
          SELECT f.*
          FROM film AS f
          JOIN film_actor AS fa USING (film_id)
          WHERE a.actor_id = fa.actor_id
        ) AS film_tt
      ) AS films
    FROM actor

有点更冗长，但仍然取得了成功！真赞！

### 替代语法：PostgreSQL

超棒的PostgreSQL缺少了一个优秀的SQL标准功能，但有一个解决方法：数组！这次，我们可以使用结构类型，哇哦！所以下面的查询将在PostgreSQL中返回一个嵌套的行数组：

    SELECT
      a AS actor,
      array_agg(
        f
      ) AS films
    FROM actor AS a
    JOIN film_actor AS fa USING (actor_id)
    JOIN film AS f USING (film_id)
    GROUP BY a

结果是每个人的ORDBMS梦想！嵌套记录和集合无处不在（只有两列）：

    actor                  films
    --------------------   ----------------------------------------------------
    (1,PENELOPE,GUINESS)   {(1,ACADEMY DINOSAUR),(23,ANACONDA CONFESSIONS),...}
    (2,NICK,WAHLBERG)      {(3,ADAPTATION HOLES),(31,APACHE DIVINE),...}
    (3,ED,CHASE)           {(17,ALONE TRIP),(40,ARMY FLINTSTONES),...}

如果你说对此你并不觉得令人兴奋，好吧，那我也无能为力了。

## 结论

再次声明，本文对SQL中JOIN表的许多不同方法可能并不完整。我希望你在这篇文章中能发现1-2个新的技巧。JOIN只是许多非常有趣的SQL操作中的其中一个。

[0]: http://www.codeceo.com/article/sql-join-guide.html
[1]: http://www.codeceo.com/article/category/develop/database
[2]: http://www.codeceo.com/article/category/develop
[3]: http://www.codeceo.com/article/category/pick
[4]: http://www.codeceo.com/article/sql-join-guide.html#comments
[5]: http://www.codeceo.com/
[6]: http://www.codeceo.com/article/2016-codeceo-post-plan.html
[7]: ./img/venn-join1.png
[8]: https://blog.jooq.org/2016/12/09/a-beginners-guide-to-the-true-order-of-sql-operations/
[9]: ./img/venn-cross-product.png
[10]: ./img/hash-join-semi.png
[11]: ./img/nested-loop-semi-join.png
[12]: ./img/nested-loops-left-semi-join.png
[13]: https://www.simple-talk.com/sql/learn-sql-server/sql-and-the-snare-of-three-valued-logic/
[14]: https://explainextended.com/2009/09/15/not-in-vs-not-exists-vs-left-join-is-null-sql-server
[15]: ./img/outer-join-lateral-result.png
[16]: ./img/sakila1.png