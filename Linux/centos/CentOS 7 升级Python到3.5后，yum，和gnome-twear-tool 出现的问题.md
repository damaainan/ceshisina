## CentOS 7 升级Python到3.5后，yum，和gnome-twear-tool 出现的问题

来源：[https://blog.csdn.net/hunyxv/article/details/51597852](https://blog.csdn.net/hunyxv/article/details/51597852)

时间：

CentOS 7升级Python到3.5后，我跟以前CentOS 6一样，在/usr/bin/python创建了一个指向Python 3的软连接，然后将/usr/bin/yum的顶部的：

    !/usr/bin/python 

改成了

    !/usr/bin/python2.7 

后，运行yum，还是出现了以下错误：

```
[root@vps ~]# yum -y install yum-priorities
Loaded plugins: fastestmirror, langpacks
Determining fastest mirrors
 * base: ftp.iij.ad.jp
 * epel: ftp.kddilabs.jp
 * epel-debuginfo: ftp.kddilabs.jp
 * epel-source: ftp.kddilabs.jp
 * extras: ftp.iij.ad.jp
 * updates: ftp.iij.ad.jp
  File "/usr/libexec/urlgrabber-ext-down", line 28
    except OSError, e:
                  ^
SyntaxError: invalid syntax
  File "/usr/libexec/urlgrabber-ext-down", line 28
    except OSError, e:
                  ^
SyntaxError: invalid syntax
  File "/usr/libexec/urlgrabber-ext-down", line 28
    except OSError, e:
                  ^
SyntaxError: invalid syntax
  File "/usr/libexec/urlgrabber-ext-down", line 28
    except OSError, e:
                  ^
SyntaxError: invalid syntax
  File "/usr/libexec/urlgrabber-ext-down", line 28
    except OSError, e:
                  ^
SyntaxError: invalid syntax
 ```
```


Exiting on user cancel 

以前CentOS 6没这个问题的。


开了/usr/libexec/urlgrabber-ext-down看了下，发下他也使用了/usr/bin/python，于是跟前面一样，改为2.7，完成。


升级后 还有一个问题，gnome-tweak-tool 也就是优化工具打不开

```
[root@localhost applications]# find / -name gnome-tweak-tool
/usr/bin/gnome-tweak-tool
/usr/share/gnome-tweak-tool
[root@localhost applications]# vim /usr/bin/gnome-tweak-tool 
```


解决办法：  gnome-tweak-tool 文件 #!/usr/bin/python换成 #!/usr/bin/python2.7 就可以了


yum-config-manager 文件同样出错

```
[root@localhost kwplayer-master]# whereis yum-config-manager
yum-config-manager: /usr/bin/yum-config-manager /usr/share/man/man1/yum-config-manager.1.gz
[root@localhost kwplayer-master]# vim /usr/bin/yum-config-manager 

```


解决办法同上： `#!/usr/bin/python`换成 `#!/usr/bin/python2.7` 就可以了

