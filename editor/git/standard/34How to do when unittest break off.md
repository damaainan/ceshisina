`单元测试突然中断怎么办？？？`
=====

### 场景描述
* 过程

 1. 切换到功能分支my_feature

    ```sh
    $ git checkout my_feature
    ```

 1. 保证功能分支工作区是干净的

    ```sh
    $ git status
    ```

    `必须`是以下提示

    > On branch my_feature

    > nothing to commit, working directory clean

 1. 执行./sync test MyTest，`得到`理论结果

 1. 执行./sync，`未得到`理论结果

* 理论结果

    > Syncing files to remote destination...

    > ...

    > Syncing is complete!

    > PHPUNIT start

    > ...

    > OK, but incomplete, skipped, or risky tests!


    > Tests: 172, Assertions: 1031, Skipped: 5, Incomplete: 10.


    > [2016-03-10 07:15:12] PHPUNIT is complete!

* 实际结果

 1. 在git终端结果

    > Syncing files to remote destination...

    > ...

    > Syncing is complete!

    > PHPUNIT start

    > ...

    > ~~OK, but incomplete, skipped, or risky tests!~~  ————没出现这一行

 1. 在服务器上的报错

    ```sh
    [~]$ cd /opt/logs/
    [logs]$ tail -f php_errors.log
    ```

    > [10-Mar-2016 15:21:07 Asia/Shanghai] PHP Fatal error:  Cannot redeclare class Item_model in my_dev/current/htdocs/www/application/models/item_model.php on line 301

### 解决办法

* 在新定义的每个function之前添加以下代码

```sh
/**
 * @runInSeparateProcess
 * @preserveGlobalState disabled
 */
```

* 继续执行./sync，得到理论结果

    > Syncing files to remote destination...

    > ...

    > Syncing is complete!

    > PHPUNIT start

    > ...

    > PHPUNIT is complete!

### 结论

由于定义了`相同名称`的类名，导致PHP错误，所以需要将当前单元测试运行在独立的PHP进程中