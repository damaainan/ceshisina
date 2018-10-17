## 压测工具Locust

来源：[https://segmentfault.com/a/1190000009631468](https://segmentfault.com/a/1190000009631468)

发现一款很赞的压力测试软件`Locust`，开源的哦，用`python`编写测试脚本，定义用户行为，可以模拟数百万用户的访问，从而观测系统的性能与瓶颈！
 **`官方网站`** ：[http://locust.io/][2]
 **`特点`** ：

```
1、编写Python脚本，定义用户的行为

2、分布式，可扩展

3、安装使用简单

```
 **`需求`** ：

```
Python 2.7, 3.3, 3.4, 3.5, and 3.6

```
 **`安装`** ：

```
pip install locustio

```
 **`命令参数`** ：

```
locust --help

```
 **`编辑脚本`** ：`vim locustfile.py`

```
from locust import HttpLocust, TaskSet, task

class WebsiteTasks(TaskSet):
    @task
    def index(self):
        self.client.get("/index.html")

class WebsiteUser(HttpLocust):
    task_set = WebsiteTasks
    min_wait = 5000
    max_wait = 15000

```
 **`执行脚本`** ：

```
locust -f locustfile.py --host=http://127.0.0.1

```
 **`浏览器打开`** ：

```
http://127.0.0.1:8089/

出现一个界面，我们填写好参数值后，点击 Start swarming，压力测试就开始了。

```

![][0]
 **`测试结果`** ：

![][1]

[2]: http://locust.io/
[0]: ./image/bVOzIJ.png
[1]: ./image/bVOzJs.png