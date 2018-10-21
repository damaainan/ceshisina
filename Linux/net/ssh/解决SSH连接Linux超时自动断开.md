## 如何解决SSH连接Linux超时自动断开？

来源：[https://www.cnblogs.com/enjoycode/p/5022607.html](https://www.cnblogs.com/enjoycode/p/5022607.html)

2015-12-05 23:45

最近项目开发中用到云服务器，部署了MySQL，开发团队总是反映MySQL过一会儿就断开连接了，必须手动重连才可以。反映越来越强烈，已经到了影响开发进度的高度了，必须解决！

查了资料，这个可能和SSH超时自动断开是一样的，可以修改以下2个配置参数来解决。

1、$TMOUT 系统环境变量

```
# 用以下命令判断是否是否设置了该参数
echo $TMOUT
# 如果输出空或0表示不超时，大于0的数字n表示n秒没有收入则超时
# 修改方法
vi /etc/profile
# ----------------------------
export TMOUT=900
# ----------------------------
# 将以上900修改为0就是设置不超时
source /etc/profile
# 让配置立即生效
```

2、sshd 服务配置

```
cd /etc/ssh
# 查看sshd_config中关于客户端活动状态的配置
grep ClientAlive sshd_config
# 默认配置如下
# ----------------------------
#ClientAliveInterval 0
#ClientAliveCountMax 3
# ----------------------------
# ClientAliveInterval指定了服务器端向客户端请求消息的时间间隔, 默认是0, 不发送。设置60表示每分钟发送一次, 然后客户端响应, 这样就保持长连接了。
# ClientAliveCountMax表示服务器发出请求后客户端没有响应的次数达到一定值, 就自动断开。正常情况下, 客户端不会不响应，使用默认值3即可。
# 备份原配置文件
cp sshd_config sshd_config.bak
# 启用客户端活动检查，每60秒检查一次，3次不活动断开连接
sed -i "s/#ClientAliveInterval 0/ClientAliveInterval 60/g" sshd_config
sed -i "s/#ClientAliveCountMax 3/ClientAliveCountMax 3/g" sshd_config
# 确认修改
grep ClientAlive sshd_config
# 比较配置文件差异
diff sshd_config sshd_config.bak
# 重新加载ssd配置，让配置生效
service sshd reload
```

修改配置截图：

![][0]

现在无论空闲多久，SSH客户端都不会自动断开了。

【参考资料】

<http://www.linuxidc.com/Linux/2013-02/79940.htm>

<http://www.linuxidc.com/Linux/2013-02/79942.htm>

[0]: https://images2015.cnblogs.com/blog/89852/201512/89852-20151205233557658-478102201.png