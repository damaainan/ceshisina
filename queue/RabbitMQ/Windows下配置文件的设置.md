##  Windows 下配置文件的设置

#### 配置文件位置

安装目录 `sbin` 下 `rabbitmq-env.bat` 


#### 主要配置项

    ::需要使用的MNESIA数据库的路径
    RABBITMQ_MNESIA_BASE=G:\data\rabbit\db
    ::log的路径
    RABBITMQ_LOG_BASE=G:\data\rabbit\log       
    ::插件的路径
    RABBITMQ_PLUGINS_DIR=/usr/local/rabbitmq-server/plugins   


#### 配置环境变量 

> 系统变量

**给path变量添加内容** ，在其后面增加： `;%RABBITMQ_SERVER%\sbin` （注意前面的分号），然后确定即可  

**添加环境变量： RABBITMQ_SERVER**

> 用户变量

**还需要配置用户变量中的path**