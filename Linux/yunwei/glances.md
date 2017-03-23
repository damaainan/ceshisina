# 系统监控工具Glances

## 安装glances

Redhat族系（Redhat，Fedora，等等）：

    sudo yum install -y glances

Debian族系（Debian，Ubuntu，等等）：

    sudo apt-add-repository ppa:arnaud-hartmann/glances-stable
    sudo apt-get update
    sudo apt-get install glances

也可以这么安装：

    curl -L http://bit.ly/glances | /bin/bash

或

    wget -O- http://bit.ly/glances | /bin/bash

当然，也可以用pip来安装

    pip install glances

## 使用glances

glances的使用非常方便（应该说Linux中的软件使用都很方便，只需要一个命令，回车即可，就是这么潇洒），只需要在终端输入glances，回车：

    glances

### windows下命令

    glances  --disable-diskio  --disable-irq --disable-folder   --disable-sensors  --disable-raid --disable-sensors --disable- process  --disable-history   --disable-log

> --disable- process 输出较多

