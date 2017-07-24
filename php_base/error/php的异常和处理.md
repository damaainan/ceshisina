# [php的异常和处理][0]

* [php][1]

[**甄城**][2] 2015年11月03日发布 


### 常见错误处理类型

* 语法错误
* 环境错误
* 逻辑错误

### 常见错误级别

* Deprecated 最低级别的错误
    * 不推荐，不建议，使用一些过期函数的时候会出现，程序继续执行
* Notice 通知级别的错误
    * 使用一些未定义变量、常量或者数组key没有加引号的时候会出现，程序继续执行
* Waning 警告级别的错误
    * 程序出问题了，需要修改代码！！！程序继续执行
* Fatal Error 错误级别的错误
    * 程序直接报错，需要修改代码！！！中断程序执行
* parse error 语法解析错误
    * 语法检查阶段报错，需要修改代码！！！中断程序执行
* E_USER_相关的错误
    * 用户定义的错误，用户手动抛出错误，进行自定义错误处理

### PHP配置文件和错误相关选项

_设置错误级别_  
1、通过修改php.ini文件设置错误级别，静态设置，需要重启apache  

    // error_reporting = E_ALL&~E_NOTICE; //显示所有错误，除了E_NOTICE级别  
    // display_errors = 1; //线下开启，先上关闭

2、通过error_reporting()函数设置，动态设置  
    
    // error_reporting(E_ALL&~E_NOTICE); //显示所有错误，除了E_NOTICE级别  
    // error_reporting(0); //屏蔽所有错误，只会显示语法解析错误  
    // erorr_reporting(-1); //显示所有错误

3、通过ini_set()函数进行运行时设置，动态设置  

    // ini_set('error_reporting',0);  
    // ini_set('error_reporting',-1);  
    // ini_set('display_errors',0);

### 使用triggerr_error进行错误抛出

```php
    <?php
    header('content-type:text/html;charset=utf-8');
    
    $num1=1;
    $num2='xxx';
    if ( (!is_numeric($num1) || !is_numeric($num2)) ) {
    
        //通知级别，代码继续执行
        //echo trigger_error('数值必须为整型！',E_USER_NOTICE); 
        
        //警告级别，代码继续执行
        //echo trigger_error('数值必须为整型！',E_USER_WARNING); 
        
        //错误级别，代码中断
        echo trigger_error('数值必须为整型！',E_USER_ERROR); 
    
    }else{
        echo $num1+$num2;
    }
    
    echo '<br />代码继续执行';
```

### 记录错误

#### 配置php.ini脚本设置记录错误  

    log_errors = On //是否将产生错误信息记录到日志或者error_log中  
    ;error_log = syslog //设置脚本错误将记录到系统日志中  
    log_errors_max_len = 1024 //设置错误报错最大值，单位字节  
    ignore_repeated_errors = Off //是否忽略重复的错误信息  
    ignore_repeated_source = Off //是否忽略重复错误消息的来源  
    track_errors = On //如果开启，最后一个错误将永远保存在$php_errormsg中

#### 将错误记录到指定的文件中

```php
    <?php
    //运行时设置错误处理
    ini_set('display_errors','off');
    ini_set('error_log','D:\logs\error.log');
    //设置错误输出
    error_reporting(-1);
    
    echo $test; //NOTICE
    echo '<hr />';
    
    settype($var,'king'); //Warning
    echo '<hr />';
    
    test(); //Fatal error
```

##### 将日志文件保存到系统日志中

```php
    <?php
    error_reporting(-1);
    ini_set('display_errors',0);
    ini_set('log_errors',1);
    ini_set('error_log','syslog');
    //该记录方式，在windows中需要去，计算机管理-》事件查看器-》自定义视图中查找php5.4.0的log日志
    openlog('PHP5.4.0',LOG_PID,LOG_SYSLOG);
    syslog(LOG_ERR,'this is syslog !!!daye:'.date('Y/m/d H:i:s'));
    closelog();
```

##### 将错误以邮件形式发送 
1、首先需要配置邮件服务器！  
2、去php.ini中配置邮件参数  
3、写代码

    error_log('当前系统被人攻击！产生错误！',1,'87399497@qq.com');

### error_log函数使用

    error_log($msg); //传入错误记录，需要与error_log配置使用

### 如何使用Set_error_handler()

```php
    <?php
    
    header('content-type:text/html;charset=utf-8');
    
    //-1代表显示所有的错误警告
    error_reporting(-1);
    
    /**
     * 自定义一个错误处理
     * @param  [type] $errno  [错误级别]
     * @param  [type] $errmsg [错误信息]
     * @param  [type] $file   [错误文件]
     * @param  [type] $line   [错误行号]
     * @return [type]         [description]
     */
    function customer_error($errno,$errmsg,$file,$line){
        echo "<b>错误代码：</b>[{$errno}] {$errmsg} <br/>".PHP_EOL;
        echo "<b>错误行号：</b>{$file}文件中的第 {$line} <br/>".PHP_EOL;
        echo "<b>PHP版本：</b>".PHP_VERSION."(".PHP_OS.") <br/>".PHP_EOL;
        //注意：如果自定义错误处理捕获了，代码还是会执行，如果不想被执行，需要die掉！！！
        //die;
    }
    
    //设置自定义错误处理
    set_error_handler('customer_error');
    
    //输出一个未定义变量的警告
    echo $test;
    echo '<hr/>';
    //原生出错
    //Notice: Undefined variable: test in D:\phpStudy\WWW\example\index.php on line 26
    
    //自定义出错
    //错误代码：[8] Undefined variable: test 
    //错误行号：D:\phpStudy\WWW\example\index.php文件中的第 26 
    //PHP版本：5.3.29(WINNT) 
    
    
    //无法捕获一个致命错误Fatal error，会切换到原生出错
    //test();
    
    //手动抛出一个错误，被自定义的错误处理捕获
    trigger_error('this is a test of error',E_USER_ERROR);
    echo 'contiune';
    echo '<hr/>';
    
    //错误代码：[256] this is a test of error 
    //错误行号：D:\phpStudy\WWW\example\index.php文件中的第 43 
    //PHP版本：5.3.29(WINNT) 
    //contiune
    
    
    //取消自定义错误处理，将会重新适应PHP原生的错误处理
    restore_error_handler();
    echo $tt;
    echo '<hr />';
    
    //Notice: Undefined variable: tt in D:\phpStudy\WWW\example\index.php on line 49
    
    
    //重新挂载自定义错误处理
    //除了NOTICE级别的交给系统处理，剩下的全部使用customer_error自定义的错误处理
    set_error_handler('customer_error',E_ALL&~E_NOTICE);
    
    echo $asc; //E_NOTICE级别，系统的错误处理
    settype($var,'king'); //E_WARNING级别，使用自定义的错误处理
    
    //Notice: Undefined variable: asc in D:\phpStudy\WWW\example\index.php on line 65
    
    //错误代码：[2] settype() [function.settype]: Invalid type 
    //错误行号：D:\phpStudy\WWW\example\index.php文件中的第 66 
    //PHP版本：5.3.29(WINNT) 
```


### 自定义一个错误处理器

```php
    <?php
    
    class MyErrorHandler{
        
        public $msg='';
        public $filename='';
        public $line=0;
        public $vars=array();
    
        public function __construct($msg,$filename,$line,$vars){
            $this->msg = $msg;
            $this->filename = $filename;
            $this->line = $line;
            $this->vars = $vars;
        }
    
        public static function deal($errno,$errmsg,$filename,$line,$vars){
            
            $self = new self($errmsg,$filename,$line,$vars);
            switch ($errno) {
                case E_USER_ERROR :
                    return $self->dealError();
                    break;
                case E_USER_WARNING :
                case E_WARNING :
                    return $self->dealWarning();
                    break;
                case E_NOTICE :
                case E_USER_NOTICE :
                    return $self->dealNotice();
                    break;
                default:
                    return false;
                    break;
            }
        }
    
        /**
         * 处理致命错误
         * @return [type] [description]
         */
        public function dealError(){
            ob_start();
            debug_print_backtrace();
            $backtrace = ob_get_flush();
            $errmsg = <<<EOF
    出现了致命错误，如下：
    产生错误的文件：{$this->filename}
    产生错误的信息：{$this->msg}
    产生错误的行号：{$this->line}
    追踪信息：{$backtrace}
EOF;
            //发送邮件的错误日志
            //error_log($errmsg,1,'87399497@qq.com');
            //记录到错误日志
            error_log($errmsg,3,'D:/logs/customer_error.log');
            exit(1);
        }
    
        /**
         * 处理警告错误
         * @return [type] [description]
         */
        public function dealWarning(){
            $errmsg = <<<EOF
    出现了警告错误，如下：
    产生警告的文件：{$this->filename}
    产生警告的信息：{$this->msg}
    产生警告的行号：{$this->line}
EOF;
            error_log($errmsg,3,'D:/logs/customer_warning.log');
        }
    
        /**
         * 处理通知级别的错误
         * @return [type] [description]
         */
        public function dealNotice(){
            $date = date('Y-m-d H:i:s',time());
            $errmsg = <<<EOF
    出现了通知错误，如下：
    产生错误的文件：{$this->filename}
    产生错误的信息：{$this->msg}
    产生错误的行号：{$this->line}
    产生通知的时间：{$date}
EOF;
            error_log($errmsg,3,'D:/logs/customer_notice.log');
        }
    }
    
    
    
    //显示所有错误
    error_reporting(-1);
    
    //设置自定义错误，使用传入类和方法的方式
    set_error_handler(array('MyErrorHandler','deal'));
    
    //触发NOTICE级别错误，会保存到log日志中
    echo $tt;
    
    //手动触发一个错误
    trigger_error('手动抛出一个错误',E_USER_ERROR);
```
    

### register_shutdown_function()函数

```php
    <?php 
    //register_shutdown_function该函数将会在PHP执行关闭时调用
    //使用场景
    //1、页面强制停止
    //2、代码意外终止
    
    class Showdown{
        public static function endScript(){
            if (error_get_last()){
                echo '<pre>';
                error_get_last();
                echo '</pre>';
                
                //因为register_shutdown_function调用该函数的时候，是代码终止，脱离当前PHP上下文环境了
                //所以$filename的路径要写决定路径！！！
                file_put_contents('D:\logs\register_shutdown_function.log', error_get_last());
                
                die('endScript');
            }
        }
    }
    
    //特别声明！如果有die或exit在注册错误处理之前，那么将不会注册错误处理
    
    register_shutdown_function(array('Showdown','endScript'));
    
    echo md6();
```

### 错误抑制符

    @settype($var,'longjq'); //无变量$var，使用@符号进行抑制错误输出

### 错误级别

[http://www.w3school.com.cn/php/php_ref_error.asp][3]

[0]: https://segmentfault.com/a/1190000003946479
[1]: /t/php/blogs
[2]: /u/zhencheng
[3]: http://www.w3school.com.cn/php/php_ref_error.asp