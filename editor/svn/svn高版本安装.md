# [CentOS安装Subversion 1.9.*版本客户端][0]

# 安装yum仓库

以下以CentOS6为例，其他类似

    # vim /etc/yum.repos.d/wandisco-svn.rep
    
    [WandiscoSVN]
    name=Wandisco SVN Repo
    baseurl=http://opensource.wandisco.com/centos/6/svn-1.8/RPMS/$basearch/
    enabled=1
    gpgcheck=0

# 安装Subversion

    # yum clean all
    # yum install subversion

# 检测是否安装成功

    # svn --version

# 参考

[How to Install Subversion 1.8.13 (SVN Client) on CentOS/RHEL 7/6/5][1]

[0]: http://www.cnblogs.com/shockerli/p/centos-install-svn.html
[1]: http://tecadmin.net/install-subversion-1-8-on-centos-rhel/