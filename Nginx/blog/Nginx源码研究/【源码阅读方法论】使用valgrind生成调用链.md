## 【源码阅读方法论】使用valgrind生成调用链

来源：[https://segmentfault.com/a/1190000016995356](https://segmentfault.com/a/1190000016995356)

周生政
## valgrind查看调用关系

在学习开源代码时，我们希望有个工具能够给我们全局的视角而不过早的陷入细节的泥淖中。读书可以跳读，读代码也是可以跳读的。valgrind可以生成整个调用关系链。该关系链指导我们，迅速定位到我们关心的细节。
## 安装
## ubuntu系统

```LANG
1 apt-get install valgrind
2 apt-get install kcachegrind
```
## mac系统

```LANG
1 brew install qcachegrind --with-graphviz
```
## 使用
* 使用valgrind生成调用关系

```LANG
1 valgrind --tool=callgrind --trace-children=yes  --callgrind-out-file=/data/opt/callgrind.out.1111  ./nginx
```
* 使用qcachegrind查看调用关系

qcachegrind
[https://raw.githubusercontent...][1]

![][0]
## 可能遇到的问题
* Error: can not open cache simulation output file

保证写的目录有写的权限，可以创建一个目录，赋值为777，在该目录下启动命令valgrind --tool=callgrind --trace-children=yes /data/server/nginx/sbin/nginx, 使用kill终止程序kill SIGINT pid

[1]: ./img/qcachegrind.png
[0]: ./img/bVbjtqs.png