## php多进程编程实现与优化

来源：[http://www.cnblogs.com/jingliming/p/9100309.html](http://www.cnblogs.com/jingliming/p/9100309.html)

时间 2018-05-28 15:16:00



## PHP多进程API

创建子进程

``` 
@params void
@returns int
int pcntl_fork(void)
成功时，在父进程执行线程内返回产生的子进程PID，在子进程执行线程内返回0，失败时，在父进程上下文返回-1，不会创建子进程，并且会引发一个php错误
```

获取当前进程id

``` 
@params void
@returns int
int posix_getpid(void)
返回进程id，类型为整型
```

父进程等待子进程退出

``` 
@params $status
@params $option
@return bool
int pcntl_wait(int &$status[,int $options=0])
该函数等同于以-1作为参数pid的值并且没有options参数来调用pcntl_waitpid()的函数
```

进程退出状态

``` 
@params $status
@return bool
bool pcntl_wifexited(int $status)
```

进程退出码

``` 
@params $status
@return int
int pcntl_wexitstatus(int $status)
```


## 简单PHP多进程示例

```php
function process_execute($input) {
        $pid = pcntl_fork(); //创建子进程
        if ($pid == 0) {//子进程
                $pid = posix_getpid();
                echo "* Process {$pid} was created, and Executed:\n\n";
                eval($input); //解析命令
                exit;
        } else {//主进程
                $pid = pcntl_wait($status, WUNTRACED); //取得子进程结束状态
                if (pcntl_wifexited($status)) {
                        echo "\n\n* Sub process: {$pid} exited with {$status}";
                }
        }
    }
```

通过调用php创建子进程接口完成一个子进程的创建，pcntl_fork返回值为0证明进入到子进程内，非0则进入到父进程内部，-1则父进程创建子进程失败。


## 多个子进程初级版本示例

```php
foreach ($clusterList as $key=>$value) {
            $pid = pcntl_fork();//创建子进程
            if($pid == 0) {//子进程
                //do something
            } else if($pid == -1) {
                //fork error occured
            } else {
                pcntl_wait($status);
            }

        }
```


该实现方式主要逻辑为循环创建一个子进程，并且父进程等待子进程完成退出后，再继续创建下一个子进程

缺点：无法真正体现多进程，实际上时串行的创建子进程

  
## 多个子进程优化版本示例

```php
foreach ($clusterList as $key=>$value) {
            $pid = pcntl_fork();//创建子进程
            if($pid == 0) {//子进程
                //do something
            } else if($pid == -1) {
                return false;
            }
        }
        for (;;) {
            $ret = pcntl_waitpid(-1,$status,WNOHANG);
            if ($ret == -1) {
                // error occured 
            } else if ($ret == 0) {
                //all child are existed
                break;
            } else {
                //check sub process exit status
                $extFlag = pcntl_wifexited($status);
                if(!$extFlag){
                    //exited unnormally
                }else {
                    $extCode = pcntl_wexitstatus($status);
                    //exited normally
                }
            }
        }
```

该逻辑通过for循环不断获取子进程的退出状态，直到所有的子进程都退出，真正实现多进程处理。


