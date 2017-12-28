# 使用 Python 工具 Locust 进行负载测试

 时间 2017-12-08 14:11:00  

原文[http://www.ttlsa.com/python/使用-python-工具-locust-进行负载测试/][1]


Locust 是一个用Python 编写的开源的负载测试工具。 它允许您针对模拟用户行为的 Web 应用程序编写测试，然后按规模运行测试以帮助查找瓶颈或其他性能问题。 

## 安装

安装是使用 Python 常用的工具 pip 完成的：

    $pip install locustio
    

## 配置

Locust 最好的功能之一是通过”Plain Old Python”1 完成配置。您只需创建一个名为 locustfile.py 的文件，为您的负载测试任务进行所有配置，并在其中进行测试。

下面是 locustfile.py 的一个例子，它定义了一个简单的用户行为，它由一个获取特定网页的“任务”组成：

    from locust import HttpLocust, TaskSet, task
    class UserBehavior(TaskSet):
       @task
       def get_something(self):
           self.client.get("/something")
    class WebsiteUser(HttpLocust):
       task_set = UserBehavior
    

我们再来添加第二个任务：

    class UserBehavior(TaskSet):
       @task
       def get_something(self):
           self.client.get("/something")
       @task
       def get_something_else(self):
           self.client.get("/something-else")
    

当上面的 UserBehavior 运行时，Locust 将在每个任务之间随机选择并运行它们。 如果你想为不同的任务定义权重，那么你可以按照下面的方法来加权：

    class UserBehavior(TaskSet):
       @task(2)
       def get_something(self):
           self.client.get("/something")
       @task(1)
       def get_something_else(self):
           self.client.get("/something-else")
    

权重定义了所有任务执行的比例，所以这里 get_something 在负载测试中的频率会是 get_something_else 的两倍。

您也可以编写嵌套的任务，以执行一系列连续的或有特殊顺序的任务。 这使您可以通过多个请求来定义用户操作流。 例如：

    class UserBehavior(TaskSet):
       @task
       def get_something(self):
           self.client.get("/something")
       @task
       def get_something_else(self):
           self.client.get("/something-else")
       @task
       def get_two_things(self):
           self.get_something()
           self.get_something_else()
    

TaskSet 类可以有选择地声明一个 on_start 函数，当模拟用户开始执行该 TaskSet 类时会调用该函数。 在开始负载测试之前，可以使用它来登录：

    class UserBehavior(TaskSet):
       def on_start(self):
           self.client.post("/login", {
               'username': 'foo', 'password': 'bar'
           })
       @task
       def get_something(self):
           self.client.get("/something")
    

## 在本地运行

要运行 Locust，可以在与 locustfile.py 相同的目录下运行 locust 命令：

    $ locust --host=http://localhost:5000
    

一旦命令运行，Locust 启动一个本地 Web 服务器，您可以在浏览器中访问：

![][4]

选择用户数量和用户产生速率后，您可以开始测试，这将显示正在运行的测试的实时视图：

![][5]

## 分布式运行

在本地运行对于开始使用 Locust 和基本的测试来说是好的，但是如果您只是从本地机器运行它，大多数应用程序将不会收到很大的负载。在分布式模式下运行它几乎是不可避免的。用户可以轻松使用几个云节点来增加负载。

安装 Locust 并将 locustfile.py 移动到所有节点后，可以启动“主”节点：

    $ locust --host=http://localhost:5000 --master
    

然后启动任何 slave 节点，给他们对主节点的引用：

    $ locust --host=http://localhost:5000 --slave\
     --master-host=192.168.10.100
    

## 不足

尽管 Locust 很好用，但是仍有有一些缺点。 首先，对于测试结果来说，统计信息相当糟糕(gen ben bu cun zai)，或者说完全应该做得更好（例如，没有图表，并且不能在没有运行多个测试的情况下将增加的故障率与较高的负载相关联）。其次，有时候除了错误的状态外，很难获得错误响应的细节。 最后，做非 HTTP 或非 RESTful 请求的测试可能是会有一定复杂度的（尽管这很少见）。

## 优点

总的来说，Locust 是一个非常有用的负载测试工具，特别是作为一个开源项目。 如果您的代码库是基于 Python 的，由于有机会从现有的代码库中获取数据，模型或业务逻辑，所以这自然是您可以使用的最舒服的工具，但即使您不使用 Python，也可以轻松整合它。

[1]: http://www.ttlsa.com/python/使用-python-工具-locust-进行负载测试/
[4]: https://img1.tuicool.com/mUVzyuF.jpg
[5]: https://img2.tuicool.com/JNveA3M.jpg