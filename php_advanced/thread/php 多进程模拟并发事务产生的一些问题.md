## php 多进程模拟并发事务产生的一些问题

来源：[https://segmentfault.com/a/1190000017266969](https://segmentfault.com/a/1190000017266969)


## 表

```sql
drop table if exists `test`;
create table if not exists `test` (
    id int not null auto_increment , 
    count int default 0 , 
    primary key `id` (`id`)
) engine=innodb character set utf8mb4 collate = utf8mb4_bin comment '测试表';

insert into test (`count`) values (100);
```
## php 代码

```php
// 进程数量
$pro_count = 100;
$pids = [];
for ($i = 0; $i < $pro_count; ++$i)
{
    $pid = pcntl_fork();
    if ($pid < 0) {
        // 主进程
        throw new Exception('创建子进程失败: ' . $i);
    } else if ($pid > 0) {
        // 主进程
        $pids[] = $pid;
    } else {
        // 子进程
        try {
            $pdo = new PDO(...);
            $pdo->beginTransaction();
            $stmt = $pdo->query('select `count` from test');
            $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            $count = intval($count);
            if ($count > 0) {
                $count--;
                $pdo->query('update test set `count` = ' . $count . ' where id = 2');
            }
            $pdo->commit();
        } catch(Exception $e) {
            $pdo->rollBack();   
            throw $e;
        }
        // 退出子进程
        exit;
    }
}
```
## 期望的结果

期望`count`字段减少的量超过`100`，变成负数！也就是多减！
## 实际结果

并发 200 的情况下，运行多次后的结果分别如下：

```
1. count = 65
2. count = 75
3. count = 55
4. count = 84
...
```

与期望结果相差甚远！为什么会出现这样的现象呢？
## 解释

首先清楚下目前的程序运行环境，并发场景。何为并发，几乎同时执行，称之为并发。具体解释如下：

```
进程        过程            获取    更新
1-40        同时创建并运行  100     99
41-80       同时创建并运行  99      98
81 - 100    同时创建并运行  98      97
```

对上述第一行做解释，第`1-40`个子进程的创建几乎同时，运行也几乎同时：

```
进程 1 获取 count = 100，更新 99
进程 2 获取 count = 100，更新 99
...
进程 40 获取 count = 100，更新 99
```

所以，实际上这些进程都做了一致的操作，并没有按照预期的那样： **`进程1 获取 count=100，更新 99；进程 2 获取进程1更新后的结果 count=99，更新98；...；进程 99 获取进程 98更新后的结果count=1，更新0`** 
，产生的现象就是少减了！！
## 结论

采用上述做法实现的程序， **`库存总是 >= 0`** 。
## 疑问
 **`那要模拟超库存的场景该如何设计程序呢？`** 

仍然采用上述代码，将以下代码：

```php
if ($count > 0) {
    $count--;
    $pdo->query('update test set `count` = ' . $count . ' where id = 2');
}
```

修改成下面这样：

```php
if ($count > 0) {
    $pdo->query('update test set `count` = `count` - 1 where id = 2');
}
```

结果就会出现超库存！！

库存 100，并发 200，最终库存减少为`-63`。为什么会出现这样的情况呢？以下描述了程序运行的具体过程

```
进程 1 获取库存 100，更新 99
进程 2 获取库存 100，更新 98(99 - 1)
进程 3 获取库存 100，更新 97(98 - 1)
.... 
进程 168 获取库存 1 ，更新 0（1-1）
进程 169 获取库存 1 ，更新 -1（0 - 1）
进程 170 获取库存 1 ，更新 -2（-1 - 1）
....
进程 200 获取库存 1，更新 -63（-62 - 1）
```

现在看来很懵逼，实际就是下面这条语句导致的：

```
$pdo->query('update test set `count` = `count` - 1 where id = 2');
```

这边详细阐述`进程 1，简称 a；进程 2，简称 b`他们具体的执行顺序：

```
1. a 查询到库存 100
2. b 查询到库存 100
3. a 更新库存为 99（100 - 1），这个应该秒懂
4. b 更新库存为 98（99 - 1）
    - b 在执行更新操作的时候拿到的是 a 更新后的库存！
    - 为什么会这样？因为更新语句是 `update test set count = count - 1 where id = 2`
```
