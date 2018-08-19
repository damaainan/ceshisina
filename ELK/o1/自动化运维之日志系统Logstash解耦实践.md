# [自动化运维之日志系统Logstash解耦实践(八)][0]


### 6.5消息队列解耦综合实践

1.将所有需要收集的日志写入一个配置文件,发送至node4的Redis服务(以下配置文件在各个节点上)。

```shell
[root@linux-node3~]#cat/etc/logstash/conf.d/input_file_output_redis.conf
input{

#system
syslog{
type=> "system_rsyslog"
host=> "192.168.90.203"
port=> "514"
 }

#java
file{
path=> "/var/log/elasticsearch/xuliangwei.log"
type=> "error_es"
start_position=> "beginning"
codec=>multiline{
pattern=> "^\["
negate=> true
what=> "previous"
 }
 }

#nginx
file{
path=> "/var/log/nginx/access_json.log"
type=> "access_nginx"
codec=> "json"
start_position=> "beginning"
 }
}


output{
#多行文件判断
 if [type] == "system_rsyslog" {
redis{
host=> "192.168.90.204"
port=> "6379"
db=> "6"
data_type=> "list"
key=> "system_rsyslog"
 }
 }


 if [type] == "error_es" {
redis{
host=> "192.168.90.204"
port=> "6379"
db=> "6"
data_type=> "list"
key=> "error_es"
 }
 }


 if [type] == "access_nginx" {
redis{
host=> "192.168.90.204"
port=> "6379"
db=> "6"
data_type=> "list"
key=> "access_nginx"
 }
 }
}

```

2.将Redis消息队列收集的所有日志，写入Elasticsearch集群。

```
[root@linux-node3~]#cat/etc/logstash/conf.d/input_redis_output_es.conf
input{
redis{
type=> "system_rsyslog"
host=> "192.168.90.204"
port=> "6379"
db=> "6"
data_type=> "list"
key=> "system_rsyslog"
 }

redis{
type=> "error_es"
host=> "192.168.90.204"
port=> "6379"
db=> "6"
data_type=> "list"
key=> "error_es"
 }


redis{
type=> "access_nginx"
host=> "192.168.90.204"
port=> "6379"
db=> "6"
data_type=> "list"
key=> "access_nginx"
 }
}


output{
#多行文件判断
 if [type] == "system_rsyslog" {
elasticsearch{
hosts=> ["192.168.90.201:9200","192.168.90.202:9200"]
index=> "system_rsyslog_%{+YYYY.MM}"
 }
 }

 if [type] == "error_es" {
elasticsearch{
hosts=> ["192.168.90.201:9200","192.168.90.202:9200"]
index=> "error_es_%{+YYYY.MM.dd}"
 }
 }
 if [type] == "access_nginx" {
elasticsearch{
hosts=> ["192.168.90.201:9200","192.168.90.202:9200"]
index=> "access_nginx_%{+YYYY.MM.dd}"
 }
 }
}

```

3.查看Elasticsearch情况

![es-05][3]



[0]: http://www.cloudstack.top/archives/135.html

[3]: ./img/es-05.jpg