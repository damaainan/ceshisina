<?php 
// php 检测用户是否关闭浏览器
 

 echo str_repeat(" ",3000);
 ignore_user_abort(true); 
 mylog('online');
 while (true) {
             /*
              * 1、程序正常结束     connection_status 0
              * 2、点击浏览器“停止”按钮     connection_status 1
              * 3、超时    connection_status 2
              */
          echo "test<br>\n"; //注意程序一定要有输出，否则ABORTED状态是检测不到的
          flush();
          sleep(1);
          if (connection_status()!=0){
               mylog('offline');
               die('end the script');
          }
 }
 function mylog($str)
 {
     $fp = fopen('e:/abort.txt', 'a');
     $str = date('Y-m-d H:i:s').$str."\r\n";
     fwrite($fp, $str);
     fclose($fp);
 }



/*
// 例子2
 function foo() {
  $s = 'connection_status '. connection_status();
  mylog($s);
} 
register_shutdown_function('foo');//script processing is complete or when exit() is called
set_time_limit(10);
for($i=0; $i<10000000; $i++)
  echo $i;


function mylog($str)
{
    $fp = fopen('e:/abort.txt', 'a');
    $str = date('Y-m-d H:i:s').$str."\r\n";
    fwrite($fp, $str);
    fclose($fp);
}*/