#### 安装增强工具的必要步骤

1.你需要安装编译内核的相关组件。

    yum install kernel-devel kernel-headers gcc gcc-c++ make perl  bzip2 -y  ( perl  bzip2 不必要)

    yum update kernel (可以不必要)

2.在安装完成后，做一个连接  顺序问题很严重

    ln -s /usr/src/kernels/2.6.18-164.15.1.el5-i686 /usr/src/linux




#### Vagrant命令

Vagrant安装完成之后，我们就可以从命令行通过vagrant命令来进行操作。vagrant 常用命令如下：

       vagrant box add <name> <url>
       vagrant box list
       vagrant box remove <name>
       vagrant box repackage <name> 
       vagrant init [box-name] [box-url]
       vagrant up [vm-name] [--[no-]provision] [-h]
       vagrant destroy [vm-name]
       vagrant suspend [vm-name]
       vagrant reload [vm-name]
       vagrant resume [vm-name]
       vagrant halt [vm-name]
       vagrant status [vm-name] 
       vagrant package [vm-name] [--base name] [--output name.box][--include one,two,three] [--vagrantfile file]
       vagrant provision [vm-name]
       vagrant ssh [vm-name] [-c command] [-- extra ssh args]
       vagrant ssh-config [vm-name] [--host name]