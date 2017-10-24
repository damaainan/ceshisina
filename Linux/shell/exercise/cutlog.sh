#!/bin/bash
#function:cut nginx log files shell
#设置您的网站访问日志保存的目录，我的统一放在了/usr/local/nginx/logs目录下
log_files_path="/usr/local/nginx/logs/"
log_files_dir=${log_files_path}$(date -d "yesterday" +"%Y")/$(date -d "yesterday" +"%m")
#设置你想切割的nginx日志文件名称，比如设置的日志文件名是listen.com.log 的话，那这里直接填写listen.com 即可
log_files_name=(error access)
#设置nginx执行文件的路径。
nginx_sbin="/usr/local/nginx/sbin/nginx"
#设置你想保存的日志天数，我这里设置的是保存30天之前的日志
save_days=30
############################################
#Please do not modify the following script #
############################################
mkdir -p $log_files_dir
log_files_num=${#log_files_name[@]}
#cut nginx log files
for((i=0;i<$log_files_num;i++));do
mv ${log_files_path}${log_files_name[i]}.log ${log_files_dir}/${log_files_name[i]}_$(date -d "yesterday" +"%Y%m%d").log
done
#delete 30 days ago nginx log files
find $log_files_path -mtime +$save_days -exec rm -rf {} \;
$nginx_sbin -s reload