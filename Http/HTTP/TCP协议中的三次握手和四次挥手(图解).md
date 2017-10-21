# [TCP协议中的三次握手和四次挥手(图解)][0]



版权声明：本文为博主原创文章，未经博主允许不得转载。

建立TCP需要三次握手才能建立，而断开连接则需要四次握手。整个过程如下图所示：

![][9]

先来看看如何建立连接的。

**【更新于2017.01.04 】该部分内容配图有误，请大家见谅，正确的配图如下，错误配图也不删了，大家可以比较下，对比理解效果更好。这么久才来更新，抱歉！！**

![][10]

**错误配图如下：**

![][11]

首先Client端发送连接请求报文，Server段接受连接后回复ACK报文，并为这次连接分配资源。Client端接收到ACK报文后也向Server段发生ACK报文，并分配资源，这样TCP连接就建立了。

那如何断开连接呢？简单的过程如下：

![][12]

**【注意】中断连接端可以是Client端，也可以是Server端。**

假设Client端发起中断连接请求，也就是发送FIN报文。Server端接到FIN报文后，意思是说"我Client端没有数据要发给你了 "，但是如果你还有数据没有发送完成，则不必急着关闭Socket，可以继续发送数据。所以你先发送ACK，"告诉Client端，你的请求我收到了，但是我还没准备好，请继续你等我的消息"。这个时候Client端就进入FIN_WAIT状态，继续等待Server端的FIN报文。当Server端确定数据已发送完成，则向Client端发送FIN报文，"告诉Client端，好了，我这边数据发完了，准备好关闭连接了"。Client端收到FIN报文后，"就知道可以关闭连接了，但是他还是不相信网络，怕Server端不知道要关闭，所以发送ACK后进入TIME_WAIT状态，如果Server端没有收到ACK则可以重传。“，Server端收到ACK后，"就知道可以断开连接了"。Client端等待了2MSL后依然没有收到回复，则证明Server端已正常关闭，那好，我Client端也可以关闭连接了。Ok，TCP连接就这样关闭了！

整个过程Client端所经历的状态如下：

![][13]

而Server端所经历的过程如下： 转载请注明:[blog.csdn.net/whuslei][14]

![][15]

**【注意】**在TIME_WAIT状态中，如果TCP client端最后一次发送的ACK丢失了，它将重新发送。TIME_WAIT状态中所需要的时间是依赖于实现方法的。典型的值为30秒、1分钟和2分钟。等待之后连接正式关闭，并且所有的资源(包括端口号)都被释放。

**【问题1】为什么连接的时候是三次握手，关闭的时候却是四次握手？**  
答：因为当Server端收到Client端的SYN连接请求报文后，可以直接发送SYN+ACK报文。其中ACK报文是用来应答的，SYN报文是用来同步的。但是关闭连接时，当Server端收到FIN报文时，很可能并不会立即关闭SOCKET，所以只能先回复一个ACK报文，告诉Client端，"你发的FIN报文我收到了"。只有等到我Server端所有的报文都发送完了，我才能发送FIN报文，因此不能一起发送。故需要四步握手。

**【问题2】为什么TIME_WAIT状态需要经过2MSL(最大报文段生存时间)才能返回到CLOSE状态？**

答：虽然按道理，四个报文都发送完毕，我们可以直接进入CLOSE状态了，但是我们必须假象网络是不可靠的，有可以最后一个ACK丢失。所以TIME_WAIT状态就是用来重发可能丢失的ACK报文。

[0]: http://blog.csdn.net/whuslei/article/details/6667471

[9]: ./img/0_131271823564Rx.gif
[10]: http://img.blog.csdn.net/20170104214009596?watermark/2/text/aHR0cDovL2Jsb2cuY3Nkbi5uZXQvd2h1c2xlaQ==/font/5a6L5L2T/fontsize/400/fill/I0JBQkFCMA==/dissolve/70/gravity/Center
[11]: ./img/0_1312718352k8l6.gif
[12]: ./img/0_1312718564tZXD.gif
[13]: ./img/0_1312719804oSkK.gif
[14]: blog.csdn.net/whuslei
[15]: ./img/0_1312719833030b.gif