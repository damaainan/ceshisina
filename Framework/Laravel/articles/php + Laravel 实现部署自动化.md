## [php + Laravel 实现部署自动化](https://segmentfault.com/a/1190000011489280)

所谓自动化部署， 我的理解就是在用户保证代码质量的前提下, 将代码能够快速的自动部署到目标服务器上的一种手段.

## 实现原理

本地推送代码 -> 代码库 -> webhook 通知服务端 -> 自动拉取代码库代码

![][0]

## 生成并且部署公钥

具体步骤参照 [配置SSH公钥][1]

### 1) 生成公钥

    # 使用给定的 email 生成 public/private rsa 密钥
    # 如果使用非默认地址需要配置 .ssh/config
    $ ssh-keygen -t rsa -b 4096 -C "your_email@example.com"

### 2) 在 coding 中添加公钥

输出部署公玥

    $ cat coding.pub

在git 管理端部分部署公钥

![][2]

### 3) 配置 config 文件

编辑 ~/.ssh/config 文件

    Host git.coding.net
    User xxxx@email.com
    PreferredAuthentications publickey
    IdentityFile ~/.ssh/coding_rsa  // 生成的非默认地址的公钥存放点

### 4) 测试是否可以链接到 git@git.coding.net 服务器

    # 注意 git.coding.net 接入到 CDN 上所以会解析多个不同的 host ip 
    $ ssh -T git@git.coding.net
    The authenticity of host 'git.coding.net (123.59.85.184)' can't be established.
    
    RSA key fingerprint is 98:ab:2b:30:60:00:82:86:bb:85:db:87:22:c4:4f:b1.
    
    Are you sure you want to continue connecting (yes/no)? 
    
    # 这里我们根据提示输入 yes
    Warning: Permanently added 'git.coding.net,123.59.85.184' (RSA) to the list of known hosts.
    
    Coding 提示: Hello duoli, You've connected to Coding.net via SSH. This is a deploy key.
    
    duoli，你好，你已经通过 SSH 协议认证 Coding.net 服务，这是一个部署公钥

## 设置 webhook

让代码库接收到通知的时候通知服务端接收代码更新. 

![][3]

这种 webhook 的方式来接收可以部署的请求, 这里的请求使用的是 post 方法

## php 接收部署

因为 php 脚本代码执行的时候会可能有服务的中断(例如执行时间), 不一定符合实际, 所以计划使用脚本来调用.

> 收到请求 -> 存入队列 -> 脚本监听处理队列

由于使用 laravel 框架, 收到通知之后, 存入队列, 因为队列使用的是命令行监听, 所以命令行执行的时候不会出现中断情况. 

在此之前需要配置运行代码的用户有权限能够访问到 git 的服务器. 也就是如果你的代码以 www-data 运行, 需要使用 www-data 的角色来访问 git@git.coding.net 服务器. 否则也不能实现部署, 原因是 密钥不符合而无权限获取内容.

### 1) 队列代码 设置 app/Jobs    <?php 
    namespace App\Jobs;
    
    use Illuminate\Contracts\Bus\SelfHandling;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Symfony\Component\Process\Process;
    
    class WebDeploy extends Job implements SelfHandling, ShouldQueue
    {
    
        private $shellPath;
    
        /**
         * Create a new job instance.
         */
        public function __construct()
        {
            $this->shellPath = dirname(dirname(__DIR__));
        }
    
        /**
         * Execute the job.
         * @return void
         */
        public function handle()
        {
            if (!env('LM_DEPLOY_BRANCH')) {
                echo 'ERR > ' . 'No branch Set'."\n";
            }
            $shell   = "/bin/bash " . base_path('resources/shell/deploy.sh') . ' ' . base_path() . ' ' . env('LM_DEPLOY_BRANCH', 'master');
            $process = new Process($shell);
            $process->start();
            $process->wait(function ($type, $buffer) {
                if (Process::ERR === $type) {
                    echo 'ERR > ' . $buffer;
                }
                else {
                    echo 'OUT > ' . $buffer;
                }
            });
        }
    }
    

### 2) 触发队列

    dispatch(new WebDeploy());

### 3) 部署 shell 脚本

    #!/bin/bash
    aim_path=$1
    branch=$2
    cd ${aim_path}
    echo $PWD
    /usr/bin/git pull origin ${branch} >/dev/null 2>&1
    if [ $? -eq 0 ];then
    echo "OK"
    else
       /usr/bin/git fetch -f
       /usr/bin/git reset --hard
       /usr/bin/git pull origin ${branch}
    fi
    

### 4) 使用supervisor 来监听队列执行, 监听队列任务

![][4]

文件位置 /etc/supervisord.d/project.ini

    [program:project_name]
    process_name=%(program_name)s_%(process_num)02d
    command=php /path/to/project/artisan queue:work  --sleep=3 --tries=3 --daemon
    autostart=true
    autorestart=true
    user=apache
    numprocs=1
    redirect_stderr=true
    stdout_logfile=/webdata/logs/project.log
    environment=QUEUE_DRIVER=database

## 注意要点

之前和同事研究自动化部署花费很长时间, 对于PHP能否胜任这个功能还是存在一点疑惑的, 之前在局域网进行部署的时候能够实现代码的部署, 但是在其余时间测试的时候则均是失败. 本次换了这种方式找到了一种方式来运行脚本. 理论上不会存在执行不成功的时候, 直到看到了如下的报错:

    OUT > /webdata/www/sour-lemon.com
    ERR > Could not create directory '/usr/share/httpd/.ssh'.
    ERR > Host key verification failed.
    ERR > fatal: Could not read from remote repository. Please make sure you have the correct access rights and the repository exists.

因为当前 shell 运行的用户是 apache , 所以在调用的时候会以 apache 的身份去调用这个请求, 故而出现了 **Could not create directory '/usr/share/httpd/.ssh'**, 所以就考虑用 apache 权限去设置 ssh 的自动化部署. 

由于 apache 用户是处于不允许登陆状态, 需要首先允许其登录, 然后再设置相应的 ssh key. 

更改文件 /etc/passwd 允许用户登录

    # 之前是 /sbin/nologin
    apache:x:48:48:Apache:/usr/share/httpd:/bin/bash

然后再切换到 apache 用户来进行 ssh key 设定, 这样经过测试, 通过. 

![][5]

## 参考文章

* [Github 访问时出现Permission denied (public key)][6]
* [配置SSH公钥][1]
* [https://gist.github.com/jexch...][7]
* [https://www.freebsd.org/cgi/m...][8]
* [https://help.github.com/artic...][9]
* [http://callmepeanut.blog.51ct...][10]
* [http://www.huamanshu.com/wall...][11]
* [http://walle-web.io/][12]
* [https://github.com/meolu/wall...][13]
* [https://www.phptesting.org/in...][14]

[0]: ../img/1460000011489285.png
[1]: https://coding.net/help/doc/git/ssh-key.html
[2]: ../img/1460000011489286.png
[3]: ../img/1460000011489287.png
[4]: ../img/1460000011489288.png
[5]: ../img/1460000011489289.png
[6]: http://www.cnblogs.com/gr-nick/p/3406235.html
[7]: https://gist.github.com/jexchan/2351996
[8]: https://www.freebsd.org/cgi/man.cgi?query=ssh_config&sektion=5&n=1
[9]: https://help.github.com/articles/error-permission-denied-publickey/#platform-linux
[10]: http://callmepeanut.blog.51cto.com/7756998/1304912
[11]: http://www.huamanshu.com/walle.html
[12]: http://walle-web.io/
[13]: https://github.com/meolu/walle-web
[14]: https://www.phptesting.org/install-phpci