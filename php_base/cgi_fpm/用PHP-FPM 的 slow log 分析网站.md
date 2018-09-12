# 用PHP-FPM 的 slow log 分析网站

 时间 2013-05-13 17:01:52 

原文[http://extJS2.iteye.com/blog/1868017][1]


最近从GOOGLE ananlytics 发现网站速度变慢了很多， 于是想到了PHP中的FPM慢日志功能。

好在 PHP-FPM 提供了慢执行日志，可以将执行比较慢的脚本的调用过程 `dump` 到日志中。

    cd /etc/php5/fpm/pool.d/
    vi www.conf

修改配置

```cfg
    ; The timeout for serving a single request after which a PHP backtrace will be
    ; dumped to the 'slowlog' file. A value of '0s' means 'off'.
    ; Available units: s(econds)(default), m(inutes), h(ours), or d(ays)
    ; Default Value: 0
    request_slowlog_timeout = 1s
    
    ; The log file for slow requests
    ; Default Value: /usr/local/php/log/php-fpm.log.slow
    slowlog = /usr/local/php/log/php-fpm.log.slow
```
加上慢执行日志后，我们可以很容易从慢执行日志中看出问题所在，比如：

    [13-May-2013 05:17:12]  [pool www] pid 13557                                                                                                                    
    script_filename = /opt/www/inkebook/index.php                                                                                                                   
    [0x000000000292e0f0] commit() /opt/www/inkebook/includes/database/mysql/database.inc:166                                                                        
    [0x000000000292de88] popCommittableTransactions() /opt/www/inkebook/includes/database/database.inc:1128                                                         
    [0x000000000292dcf0] popTransaction() /opt/www/inkebook/includes/database/database.inc:1905                                                                     
    [0x00007fffe78cc460] __destruct() unknown:0                                                                                                                     
    [0x000000000292c690] execute() /opt/www/inkebook/modules/statistics/statistics.module:73                                                                        
    [0x00007fffe78cc900] statistics_exit() unknown:0                                                                                                                
    [0x000000000292c208] call_user_func_array() /opt/www/inkebook/includes/module.inc:857                                                                           
    [0x000000000292bf10] module_invoke_all() /opt/www/inkebook/includes/common.inc:2688                                                                             
    [0x000000000292ade0] drupal_page_footer() /opt/www/inkebook/includes/common.inc:2676                                                                            
    [0x000000000292aa28] drupal_deliver_html_page() /opt/www/inkebook/includes/common.inc:2560                                                                      
    [0x000000000292a378] drupal_deliver_page() /opt/www/inkebook/includes/menu.inc:532                                                                              
    [0x000000000292a198] menu_execute_active_handler() /opt/www/inkebook/index.php:21

再进行进一步的程序分析，就更具方向性了。


[1]: http://extJS2.iteye.com/blog/1868017
