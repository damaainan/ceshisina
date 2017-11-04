#!/bin/bash
#Author:丁丁历险(Jacob)
#设置变量，url为你需要检测的目标网站的网址（IP或域名）
url=http://192.168.4.5/index.html
  
#定义函数check_http：
#使用curl命令检查http服务器的状态
#-m设置curl不管访问成功或失败，最大消耗的时间为5秒，5秒连接服务为相应则视为无法连接
#-s设置静默连接，不显示连接时的连接速度、时间消耗等信息
#-o将curl下载的页面内容导出到/dev/null(默认会在屏幕显示页面内容)
#-w设置curl命令需要显示的内容%{http_code}，指定curl返回服务器的状态码
check_http(){
status_code=$(curl -m 5 -s-o /dev/null -w %{http_code} $url)
}
  
while :
do
       check_http
       date=$(date +%Y%m%d-%H:%M:%S) 
#生成报警邮件的内容
       echo "当前时间为:$date
       $url服务器异常,状态码为${status_code}.
       请尽快排查异常." > /tmp/http$$.pid
        
#指定测试服务器状态的函数，并根据返回码决定是发送邮件报警还是将正常信息写入日志
       if [ $status_code -ne 200 ];then
              mail -s Warning root < /tmp/http$$.pid
       else
              echo "$url连接正常" >> /var/log/http.log
       fi
       sleep 5
done

# http://manual.blog.51cto.com/3300438/d-1

#定义函数check_http： 
#使用curl命令检查http服务器的状态 #-m设置curl不管访问成功或失败，最大消耗的时间为5秒，5秒连接服务为相应则视为无法连接
#-s设置静默连接，不显示连接时的连接速度、时间消耗等信息 
#-o将curl下载的页面内容导出到/dev/null(默认会在屏幕显示页面内容) 
#-w设置curl命令需要显示的内容%{http_code}，指定curl返回服务器的状态码