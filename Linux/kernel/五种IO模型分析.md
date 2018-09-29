## 五种IO模型分析

来源：[http://www.cnblogs.com/f-ck-need-u/p/7624733.html](http://www.cnblogs.com/f-ck-need-u/p/7624733.html)

时间 2017-10-03 22:41:00

 
## 1. 基础
 
在引入IO模型前，先对io等待时某一段数据的"经历"做一番解释。如图：
 
![][0]
 
当某个程序或已存在的进程/线程(后文将不加区分的只认为是进程)需要某段数据时，它只能在用户空间中属于它自己的内存中访问、修改，这段内存暂且称之为app buffer。假设需要的数据在磁盘上，那么进程首先得发起相关系统调用，通知内核去加载磁盘上的文件。但正常情况下，数据只能加载到内核的缓冲区，暂且称之为kernel buffer。数据加载到kernel buffer之后，还需将数据复制到app buffer。到了这里，进程就可以对数据进行访问、修改了。
 
现在有几个需要说明的问题。
 
(1).为什么不能直接将数据加载到app buffer呢？
 
实际上是可以的，有些程序或者硬件为了提高效率和性能，可以实现内核旁路的功能，避过内核的参与，直接在存储设备和app buffer之间进行数据传输，例如RDMA技术就需要实现这样的内核旁路功能。
 
但是，最普通也是绝大多数的情况下，为了安全和稳定性，数据必须先拷入内核空间的kernel buffer，再复制到app buffer，以防止进程串进内核空间进行破坏。
 
(2).上面提到的数据几次拷贝过程，拷贝方式是一样的吗？
 
不一样。现在的存储设备(包括网卡)基本上都支持DMA操作。什么是DMA(direct memory access，直接内存访问)？简单地说，就是内存和设备之间的数据交互可以直接传输，不再需要计算机的CPU参与，而是通过硬件上的芯片(可以简单地认为是一个小cpu)进行控制。
 
假设，存储设备不支持DMA，那么数据在内存和存储设备之间的传输，必须通过计算机的CPU计算从哪个地址中获取数据、拷入到对方的哪些地址、拷入多少数据(多少个数据块、数据块在哪里)等等，仅仅完成一次数据传输，CPU都要做很多事情。而DMA就释放了计算机的CPU，让它可以去处理其他任务。
 
再说kernel buffer和app buffer之间的复制方式，这是两段内存空间的数据传输，只能由CPU来控制。
 
所以，在加载硬盘数据到kernel buffer的过程是DMA拷贝方式，而从kernel buffer到app buffer的过程是CPU参与的拷贝方式。
 
(3).如果数据要通过TCP连接传输出去要怎么办？
 
例如，web服务对客户端的响应数据，需要通过TCP连接传输给客户端。
 
TCP/IP协议栈维护着两个缓冲区：send buffer和recv buffer，它们合称为socket buffer。需要通过TCP连接传输出去的数据，需要先复制到send buffer，再复制给网卡通过网络传输出去。如果通过TCP连接接收到数据，数据首先通过网卡进入recv buffer，再被复制到用户空间的app buffer。
 
同样，在数据复制到send buffer或从recv buffer复制到app buffer时，是CPU参与的拷贝。从send buffer复制到网卡或从网卡复制到recv buffer时，是DMA操作方式的拷贝。
 
如下图所示，是通过TCP连接传输数据时的过程。
 
![][1]
 
(4).网络数据一定要从kernel buffer复制到app buffer再复制到send buffer吗？
 
不是。如果进程不需要修改数据，就直接发送给TCP连接的另一端，可以不用从kernel buffer复制到app buffer，而是直接复制到send buffer。这就是 **`零复制`**  技术。
 
例如httpd不需要访问和修改任何信息时，将数据原原本本地复制到app buffer再原原本本地复制到send buffer然后传输出去，但实际上复制到app buffer的过程是可以省略的。使用零复制技术，就可以减少一次拷贝过程，提升效率。
 
当然，实现零复制技术的方法有多种，见我的另一篇结束零复制的文章： [ 零复制(zero copy)技术 ][19]   。
 
以下是以httpd进程处理文件类请求时比较完整的数据操作流程。
 
![][2]
 
大致解释下：客户端发起对某个文件的请求，通过TCP连接，请求数据进入TCP 的recv buffer，再通过recv()函数将数据读入到app buffer，此时httpd工作进程对数据进行一番解析，知道请求的是某个文件，于是发起某个系统调用(例如要读取这个文件，发起read())，于是内核加载该文件，数据从磁盘复制到kernel buffer再复制到app buffer，此时httpd就要开始构建响应数据了，可能会对数据进行一番修改，例如在响应首部中加一个字段，最后将修改或未修改的数据复制(例如send()函数)到send buffer中，再通过TCP连接传输给客户端。
 
## 2. I/O模型
 
所谓的IO模型，描述的是出现I/O等待时进程的状态以及处理数据的方式。围绕着进程的状态、数据准备到kernel buffer再到app buffer的两个阶段展开。其中数据复制到kernel buffer的过程称为 **`数据准备`**  阶段，数据从kernel buffer复制到app buffer的过程称为 **`数据复制`**  阶段。请记住这两个概念，后面描述I/O模型时会一直用这两个概念。
 
本文以httpd进程的TCP连接方式处理本地文件为例，请无视httpd是否真的实现了如此、那般的功能，也请无视TCP连接处理数据的细节，这里仅仅只是作为方便解释的示例而已。另外，本文用本地文件作为I/O模型的对象不是很适合，它的重头戏是在套接字上，如果想要看处理TCP/UDP过程中套接字的I/O模型，请看完此文后，再结合我的另一篇文章" [ 不可不知的socket和TCP连接过程 ][20]   "以重新认识I/O模型。
 
再次说明，从硬件设备到内存的数据传输过程是不需要CPU参与的，而内存间传输数据是需要CPU参与的。
 
## 2.1 Blocking I/O模型
 
如图：
 
![][3]
 
假设客户端发起index.html的文件请求，httpd需要将index.html的数据从磁盘中加载到自己的httpd app buffer中，然后复制到send buffer中发送出去。
 
但是在httpd想要加载index.html时，它首先检查自己的app buffer中是否有index.html对应的数据，没有就发起系统调用让内核去加载数据，例如read()，内核会先检查自己的kernel buffer中是否有index.html对应的数据，如果没有，则从磁盘中加载，然后将数据准备到kernel buffer，再复制到app buffer中，最后被httpd进程处理。
 
如果使用Blocking I/O模型：
 
 
   (1).当设置为blocking i/o模型，httpd从 
  
![][4]
 到 
  
![][5]
 
都是被阻塞的。
 
(2).只有当数据复制到app buffer完成后，或者发生了错误，httpd才被唤醒处理它app buffer中的数据。
 
(3).cpu会经过两次上下文切换：用户空间到内核空间再到用户空间。
 (4).由于 
  
![][6]
 阶段的拷贝是不需要CPU参与的，所以在 
  
![][6]
 
阶段准备数据的过程中，cpu可以去处理其它进程的任务。
 (5). 
  
![][5]
 
阶段的数据复制需要CPU参与，将httpd阻塞，在某种程度上来说，有助于提升它的拷贝速度。
 
(6).这是最省事、最简单的IO模式。

 
如下图：
 
![][9]
 
## 2.1 Non-Blocking I/O模型
 
(1).当设置为non-blocking时，httpd第一次发起系统调用(如read())后，立即返回一个错误值EWOULDBLOCK(至于read()读取一个普通文件时能否返回EWOULDBLOCK请无视，毕竟I/O模型主要是针对套接字文件的，就当read()是recv()好了  )，而不是让httpd进入睡眠状态。UNP中也正是这么描述的。

```
When we set a socket to be nonblocking, we are telling the kernel "when an I/O operation that I request cannot be completed without putting the process to sleep, do not put the process to sleep, but return an error instead.
```
 
  
(2).虽然read()立即返回了，但httpd还要不断地去发送read()检查内核：数据是否已经成功拷贝到kernel buffer了？这称为轮询(polling)。每次轮询时，只要内核没有把数据准备好，read()就返回错误信息EWOULDBLOCK。
 
(3).直到kernel buffer中数据准备完成，再去轮询时不再返回EWOULDBLOCK，而是将httpd阻塞，以等待数据复制到app buffer。
 (4).httpd在 
  
![][4]
 到 
  
![][6]
 阶段不被阻塞，但是会不断去发送read()轮询。在 
  
![][5]
 
被阻塞，将cpu交给内核把数据copy到app buffer。

 
如下图：
 
![][13]
 
## 2.3 I/O Multiplexing模型
 
称为多路IO模型或IO复用，意思是可以检查多个IO等待的状态。有三种IO复用模型：select、poll和epoll。其实它们都是一种函数，用于监控指定文件描述符的数据是否就绪，就绪指的是对某个系统调用不再阻塞了，例如对于read()来说，就是数据准备好了就是就绪状态。就绪种类包括是否可读、是否可写以及是否异常，其中可读条件中就包括了数据是否准备好。当就绪之后，将通知进程，进程再发送对数据操作的系统调用，如read()。所以，这三个函数仅仅只是处理了数据是否准备好以及如何通知进程的问题。可以将这几个函数结合阻塞和非阻塞IO模式使用，例如设置为非阻塞时，select()/poll()/epoll将不会阻塞在对应的描述符上，调用函数的进程/线程也就不会被阻塞。
 
select()和poll()差不多，它们的监控和通知手段是一样的，只不过poll()要更聪明一点，所以此处仅以select()监控单个文件请求为例简单介绍IO复用，至于更具体的、监控多个文件以及epoll的方式，在本文的最后专门解释。
 
(1).当想要加载某个文件时，假如httpd要发起read()系统调用，如果是阻塞或者非阻塞情形，那么read()会根据数据是否准备好而决定是否返回，是否可以主动去监控这个数据是否准备到了kernel buffer中呢，亦或者是否可以监控send buffer中是否有新数据进入呢？这就是select()/poll()/epoll的作用。
 
(2).当使用select()时，httpd发起一个select调用，然后httpd进程被select()"阻塞"。由于此处假设只监控了一个请求文件，所以select()会在数据准备到kernel buffer中时直接唤醒httpd进程。之所以阻塞要加上双引号，是因为select()有时间间隔选项可用控制阻塞时长，如果该选项设置为0，则select不阻塞，此时表示立即返回但一直轮询检查是否就绪，还可以设置为永久阻塞。
 
(3).当select()的监控对象就绪时，将通知(轮询情况)或唤醒(阻塞情况)httpd进程，httpd再发起read()系统调用，此时数据会从kernel buffer复制到app buffer中并read()成功。
 
(4).httpd发起第二个系统调用(即read())后被阻塞，CPU全部交给内核用来复制数据到app buffer。 (5).对于httpd只处理一个连接的情况下，IO复用模型还不如blocking I/O模型，因为它前后发起了两个系统调用(即select()和read())，甚至在轮询的情况下会不断消耗CPU。但是IO复用的优势就在于能同时监控多个文件描述符。
 
如图：
 
![][14]
 
更详细的说明，见本文末。
 
## 2.4 Signal-driven I/O模型
 
即信号驱动IO模型。当开启了信号驱动功能时，首先发起一个信号处理的系统调用，如sigaction()，这个系统调用会立即返回。但数据在准备好时，会发送SIGIO信号，进程收到这个信号就知道数据准备好了，于是发起操作数据的系统调用，如read()。
 
在发起信号处理的系统调用后，进程不会被阻塞，但是在read()将数据从kernel buffer复制到app buffer时，进程是被阻塞的。如图：
 
![][15]
 
## 2.5 Asynchronous I/O模型
 
即异步IO模型。当设置为异步IO模型时，httpd首先发起异步系统调用(如aio_read()，aio_write()等)，并立即返回。这个异步系统调用告诉内核，不仅要准备好数据，还要把数据复制到app buffer中。
 
httpd从返回开始，直到数据复制到app buffer结束都不会被阻塞。当数据复制到app buffer结束，将发送一个信号通知httpd进程。
 
如图：
 
![][16]
 
看上去异步很好，但是注意，在复制kernel buffer数据到app buffer中时是需要CPU参与的，这意味着不受阻的httpd会和异步调用函数争用CPU。如果并发量比较大，httpd接入的连接数可能就越多，CPU争用情况就越严重，异步函数返回成功信号的速度就越慢。如果不能很好地处理这个问题，异步IO模型也不一定就好。
 
## 2.6 同步IO和异步IO、阻塞和非阻塞的区分
 
阻塞、非阻塞、IO复用、信号驱动都是同步IO模型。因为在发起操作数据的系统调用(如本文的read())过程中是被阻塞的。这里要注意，虽然在加载数据到kernel buffer的数据准备过程中可能阻塞、可能不阻塞，但kernel buffer才是read()函数的操作对象，同步的意思是让kernel buffer和app buffer数据同步。显然，在保持kernel buffer和app buffer同步的过程中，进程必须被阻塞，否则read()就变成异步的read()。
 
只有异步IO模型才是异步的，因为发起的异步类的系统调用(如aio_read())已经不管kernel buffer何时准备好数据了，就像后台一样read一样，aio_read()可以一直等待kernel buffer中的数据，在准备好了之后，aio_read()自然就可以将其复制到app buffer。
 
如图：
 
![][17]
 
## 3 select()、poll()和epoll
 
前面说了，这三个函数是文件描述符状态监控的函数，它们可以监控一系列文件的一系列事件，当出现满足条件的事件后，就认为是就绪或者错误。事件大致分为3类：可读事件、可写事件和异常事件。它们通常都放在循环结构中进行循环监控。
 
select()和poll()函数处理方式的本质类似，只不过poll()稍微先进一点，而epoll处理方式就比这两个函数先进多了。当然，就算是先进分子，在某些情况下性能也不一定就比老家伙们强。
 
## 3.1 select() & poll()
 
首先，通过FD_SET宏函数创建待监控的描述符集合，并将此描述符集合作为select()函数的参数，可以在指定select()函数阻塞时间间隔，于是select()就创建了一个监控对象。
 
除了普通文件描述符，还可以监控套接字，因为套接字也是文件，所以select()也可以监控套接字文件描述符，例如recv buffer中是否收到了数据，也即监控套接字的可读性，send buffer中是否满了，也即监控套接字的可写性。select()默认最大可监控1024个文件描述符。而poll()则没有此限制。
 
select()的时间间隔参数分3种：
 
(1).设置为指定时间间隔内阻塞，除非之前有就绪事件发生。
 
(2).设置为永久阻塞，除非有就绪事件发生。
 
(3).设置为完全不阻塞，即立即返回。但因为select()通常在循环结构中，所以这是轮询监控的方式。
 
当创建了监控对象后，由内核监控这些描述符集合，于此同时调用select()的进程被阻塞(或轮询)。当监控到满足就绪条件时(监控事件发生)，select()将被唤醒(或暂停轮询)，于是select()返回 **`满足就绪条件的描述符数量`**  ，之所以是数量而不仅仅是一个，是因为多个文件描述符可能在同一时间满足就绪条件。由于只是返回数量，并没有返回哪一个或哪几个文件描述符，所以通常在使用select()之后，还会在循环结构中的if语句中使用宏函数FD_ISSET进行遍历，直到找出所有的满足就绪条件的描述符。最后将描述符集合通过指定函数拷贝回用户空间，以便被进程处理。
 
监听描述符集合的大致过程如下图所示，其中select()只是其中的一个环节：
 
![][18]
 
大概描述下这个循环监控的过程：
 
(1).首先通过FD_ZERO宏函数初始化描述符集合。图中每个小方格表示一个文件描述符。
 
(2).通过FD_SET宏函数创建描述符集合，此时集合中的文件描述符都被打开，也就是稍后要被select()监控的对象。
 
(3).使用select()函数监控描述符集合。当某个文件描述符满足就绪条件时，select()函数返回集合中满足条件的数量。图中标黄色的小方块表示满足就绪条件的描述符。
 
(4).通过FD_ISSET宏函数遍历整个描述符集合，并将满足就绪条件的描述符发送给进程。同时，使用FD_CLR宏函数将满足就绪条件的描述符从集合中移除。
 
(5).进入下一个循环，继续使用FD_SET宏函数向描述符集合中添加新的待监控描述符。然后重复(3)、(4)两个步骤。
 
如果使用简单的伪代码来描述：

```
FD_ZERO
for() {
    FD_SET()
    select()
    if(){
        FD_ISSET()
        FD_CLR()
    }
    writen()
}
```
 
以上所说只是一种需要循环监控的示例，具体如何做却是不一定的。不过从中也能看出这一系列的流程。
 
## 3.2 epoll
 
epoll比poll()、select()先进，考虑以下几点，自然能看出它的优势所在：
 
(1).epoll_create()创建的epoll实例可以随时通过epoll_ctl()来新增和删除感兴趣的文件描述符，不用再和select()每个循环后都要使用FD_SET更新描述符集合的数据结构。
 
(2).在epoll_create()创建epoll实例时，还创建了一个epoll就绪链表list。而epoll_ctl()每次向epoll实例添加描述符时，还会注册该描述符的回调函数。当epoll实例中的描述符满足就绪条件时将触发回调函数，被移入到就绪链表list中。
 
(3).当调用epoll_wait()进行监控时，它只需确定就绪链表中是否有数据即可，如果有，将复制到用户空间以被进程处理，如果没有，它将被阻塞。当然，如果监控的对象设置为非阻塞模式，它将不会被阻塞，而是不断地去检查。
 
也就是说，epoll的处理方式中，根本就无需遍历描述符集合。
 
##### [ 回到Linux系列文章大纲：http://www.cnblogs.com/f-ck-need-u/p/7048359.html ][21] 
 
##### [ 回到网站架构系列文章大纲：http://www.cnblogs.com/f-ck-need-u/p/7576137.html ][22] 
 
##### [ 回到数据库系列文章大纲：http://www.cnblogs.com/f-ck-need-u/p/7586194.html ][23] 


[19]: http://www.cnblogs.com/f-ck-need-u/p/7615914.html
[20]: http://www.cnblogs.com/f-ck-need-u/p/7623252.html
[21]: http://www.cnblogs.com/f-ck-need-u/p/7048359.html
[22]: http://www.cnblogs.com/f-ck-need-u/p/7576137.html
[23]: http://www.cnblogs.com/f-ck-need-u/p/7586194.html
[0]: ./img/niY3Qz.png
[1]: ./img/RFv6beJ.png
[2]: ./img/A32uIzQ.png
[3]: ./img/2ErAR3V.png
[4]: ./img/jYbQruv.png
[5]: ./img/m2iMnub.png
[6]: ./img/uMfUzya.png
[7]: ./img/uMfUzya.png
[8]: ./img/m2iMnub.png
[9]: ./img/2Aje2yE.png
[10]: ./img/jYbQruv.png
[11]: ./img/uMfUzya.png
[12]: ./img/m2iMnub.png
[13]: ./img/aauAnu7.png
[14]: ./img/e6JnuiU.png
[15]: ./img/nAFJjq7.png
[16]: ./img/nUBVNzv.png
[17]: ./img/zUzAnyE.png
[18]: ./img/yma6biB.png