* UDP(User Datagram Protocol), 无连接, 不可靠, 全双工, UDP套接字是一种数据报套接字
* TCP(Transmission Control Protocol), 客户端服务器建立连接, 全双工, 可靠, 包含动态估算RTT算法, 流量控制, 超时重传
* SCTP(Stream Control Transmission Protocol), 面向消息, 面向连接, 单个SCTP断点支持多个IP地址(多宿性)

## **TCP三次握手**

1. 客户端主动连接服务器(connect()), 并发送一个SYN(告诉服务器未来发送数据的初始序列号)
1. 服务器一直等待连接(accept()), 收到SYN后, 发出新的SYN+ACK进行确认
1. 客户端向服务器发出ACK确认, accept返回套接字描述符

## TCP四次终止

1. 客户端主动关闭(close()), 向服务器端发送FIN表示数据发送完毕
1. 服务器端收到FIN后, 向客户端发出ACK确认, FIN表明服务器端不需要再接收数据
1. 一段时间后, 服务器端接收到进程关闭套接字描述符(close()), 并想客户端发送一个FIN
1. 客户端收到FIN后, 向服务器发出确认


> (**> 注意:** )三阶段到四阶段, 主动关闭的一方进入TIME_WAIT状态, 为了可靠的实现TCP全双工连接的终止, 允许老的重复分解在网络中消失(P37)

**套接字表面上可以认为由IP和端口号组成, IP用来表示不同的主机(也就是端到端), 端口号用来表示一台主机上不同的进程(不同进程使用不同的端口号)**

## TCP输出过程:

* 进程调用write(), 内核从应用进程的缓冲区复制所有数据到套接字的发送缓冲区(SO_SNDBUF)
* 如果套接字发送缓冲区小于应用发送缓冲区(或者套接字发送缓冲已有数据), 则进程睡眠(阻塞).
* 直到进程缓冲区所有数据都复制的发送缓冲区.
* TCP从套接字发送缓冲区提取并发送数据(数据未确认前, TCP保留数据副本)


> write()返回仅表示可以重新使用应用缓冲区, 并不标明另一端的TCP接收到数据

对于UDP, 如果进程写一个大于套接字缓冲区大小的数据报, 内核将返回EMSGSIZE错误, UDP不保留数据副本

> write调用成功返回表示所写数据报已被加入数据链路层的传输队列

# 套接字

* bind, connect, sendto, sendmsg是从进程到内核传递套接字
* accept, recvfrom, recvmsg, getpeername, getsockname是从内核到进程传递套接字
```
//所有类型的套接字都至少是16字节  
struct  sockaddr_in {  
    uint8_t sin_len;   /* length of struct */  
    sa_family_t sin_family;  /* Address family */  
in_port_t  int  sin_port;  /* Port number */  
    struct  in_addr  sin_addr;  /* Internet address */  
    unsigned  char  sin_zero[8];  /* Same size as struct sockaddr */  
};  
struct  in_addr {  
    in_addr_t  s_addr; /* 32-bit network byte ordered IPv4 address */  
};  
//网络编程函数中的struct sockaddr参数  
struct sockaddr {  
    uint8_t sa_len;      
    sa_family sa_family;     /* address family, AF_xxx */  
    char sa_data[14];                 /* 14 bytes of protocol address */  
};  
//网络编程需要将指向特定协议的套接字地址结构的指针进行强制转换, 编程通用套接字地址结构指针  
struct sockaddr_in serv;  //IPv4套接字结构  
(struct sockaddr *)&serv  //强制类型转换
```
* 内存中存储数据的方法分为小端字节序和大端字节序
* 网络协议指定网络字节序规则各字节传送顺序(使用大端字节序)

## ASCII字符串与网络字节序的二进制值进行转换
```
#include <arpa/inet.h>  
/* 下面两个函数仅适用于IPv4 */  
//将字符串转换为网络字节序二进制数, 字符串有效返回1, 否则返回0  
int inet_aton(const char *cp, struct in_addr *pin);  
//将网络字节序二进制转换为点十进制字符换  
char *inet_ntoa(struct in_addr in);  
/* 适用于IPv4和IPv6 */  
//第一个参数可以是AF_INET或者AF_INET6, 将表达格式(点十进制IP地址, presentation format address)转换为网络格式(network format), 成功返回1, 表达格式无效返回0, 出错返回-1  
int inet_pton(int family, const char * restrict src, void * restrict dst);  
//将网络格式转换为表达格式(presentation format address), 成功返回指向结果的指针, 出错则为NULL  
const char *inet_ntop(int af, const void * restrict src, char * restrict dst, socklen_t size);
```
## Rio

**字节流套接字条用调用read或write输入和输出的字节数可以比请求的数量少, 原因在于内核中用于套接字的缓冲区可能已达到极限**, 这一点在CSAPP中Rio章节同样有深入的讲解.
```
/*********************************************************************  
 * The Rio package - robust I/O functions  
 **********************************************************************/  
/*  
 * rio_readn - robustly read n bytes (unbuffered)  
 */  
/* $begin rio_readn */  
ssize_t rio_readn(int fd, void *usrbuf, size_t n) { //等价与UNP中的readn函数  
    size_t nleft = n;  
    ssize_t nread;  
    char *bufp = usrbuf;  
    while (nleft > 0) {  
    if ((nread = read(fd, bufp, nleft)) < 0) {         if (errno == EINTR) /* interrupted by sig handler return */         nread = 0;      /* and call read() again */         else         return -1;      /* errno set by read() */      }      else if (nread == 0)         break;              /* EOF */     nleft -= nread;  //nleft保存剩余需要读取的字节数， nread表示已读字节数     bufp += nread;  // bufp始终指向要读取的字符串起始位置     }     return (n - nleft);         /* return >= 0 */  
}  
/* $end rio_readn */  
/*  
 * rio_writen - robustly write n bytes (unbuffered)  
 */  
/* $begin rio_writen */  
ssize_t rio_writen(int fd, void *usrbuf, size_t n) {  //等价于UNP中的writen函数  
    size_t nleft = n;  
    ssize_t nwritten;  
    char *bufp = usrbuf;  
    while (nleft > 0) {  
    if ((nwritten = write(fd, bufp, nleft)) <= 0) {         if (errno == EINTR)  /* interrupted by sig handler return */         nwritten = 0;    /* and call write() again */         else         return -1;       /* errno set by write() */     }     nleft -= nwritten;     bufp += nwritten;     }     return n; } /* $end rio_writen */ /*   * rio_read - This is a wrapper for the Unix read() function that  *    transfers min(n, rio_cnt) bytes from an internal buffer to a user  *    buffer, where n is the number of bytes requested by the user and  *    rio_cnt is the number of unread bytes in the internal buffer. On  *    entry, rio_read() refills the internal buffer via a call to  *    read() if the internal buffer is empty.  */ /* $begin rio_read */ //当调用rio_read读取n个字节时，读缓冲区内有rp->rio_cnt个未读字节 //如果缓冲区为空，则调用read填满  
//缓冲区非空， rio_read从读缓冲区拷贝n和rp->rio_cnt中较小的字节到用户缓冲区，并返回拷贝字节  
static ssize_t rio_read(rio_t *rp, char *usrbuf, size_t n)  
{  
    int cnt;  
    while (rp->rio_cnt <= 0) {  /* refill if buf is empty */         rp->rio_cnt = read(rp->rio_fd, rp->rio_buf,   
               sizeof(rp->rio_buf));  //调用的是系统read函数，预先读取到内部缓冲区中  
    if (rp->rio_cnt < 0) {         if (errno != EINTR) /* interrupted by sig handler return */             return -1;     } else if (rp->rio_cnt == 0)  /* EOF */  
        return 0;  
    else   
        rp->rio_bufptr = rp->rio_buf; /* reset buffer ptr */  
    }  
    /* Copy min(n, rp->rio_cnt) bytes from internal buf to user buf */  
    cnt = n;            
    if (rp->rio_cnt < n)            cnt = rp->rio_cnt;  
    memcpy(usrbuf, rp->rio_bufptr, cnt); //rio_bufptr始终指向字符串初始复制位置，复制cnt个倡导到usrbuf  
    rp->rio_bufptr += cnt; //复制初始位置指针后移  
    rp->rio_cnt -= cnt;  //缓冲区大小剪掉已读取字符数  
    return cnt;  //返回已读取字符数， 返回值与系统read含义类似  
}  
/* $end rio_read */  
/*  
 * rio_readinitb - Associate a descriptor with a read buffer and reset buffer*（缓冲区初始化）  
 */  
/* $begin rio_readinitb */  
void rio_readinitb(rio_t *rp, int fd)   
{  
    rp->rio_fd = fd;    
    rp->rio_cnt = 0;    
    rp->rio_bufptr = rp->rio_buf;  
}  
/* $end rio_readinitb */  
/*  
 * rio_readnb - Robustly read n bytes (buffered)  
 */  
/* $begin rio_readnb */  
ssize_t rio_readnb(rio_t *rp, void *usrbuf, size_t n)   
{  
    size_t nleft = n;  
    ssize_t nread;  
    char *bufp = usrbuf;  
      
    while (nleft > 0) {  
        if ((nread = rio_read(rp, bufp, nleft)) < 0) {             if (errno == EINTR) /* interrupted by sig handler return */                 nread = 0;      /* call read() again */             else                 return -1;      /* errno set by read() */      } else if (nread == 0)         break;              /* EOF */     nleft -= nread;     bufp += nread;     }     return (n - nleft);         /* return >= 0 */  
}  
/* $end rio_readnb */  
/*   
 * rio_readlineb - robustly read a text line (buffered) 线程安全  
 */  
/* $begin rio_readlineb */  
ssize_t rio_readlineb(rio_t *rp, void *usrbuf, size_t maxlen)   
{  
    int n, rc;  
    char c, *bufp = usrbuf;  
    for (n = 1; n < maxlen; n++) {   
        if ((rc = rio_read(rp, &c, 1)) == 1) {  
            *bufp++ = c;  
        if (c == '\n')  
            break;  
    } else if (rc == 0) {  
        if (n == 1)  
            return 0; /* EOF, no data read */  
        else  
            break;    /* EOF, some data was read */  
    } else  
        return -1;    /* error */  
    }  
    *bufp = 0;  
    return n;  
}  
/* $end rio_readlineb */  
/**********************************  
 * Wrappers for robust I/O routines  
 **********************************/  
ssize_t Rio_readn(int fd, void *ptr, size_t nbytes)   
{  
    ssize_t n;  
    
    if ((n = rio_readn(fd, ptr, nbytes)) < 0)  
        unix_error("Rio_readn error");  
    return n;  
}  
void Rio_writen(int fd, void *usrbuf, size_t n)   
{  
    if (rio_writen(fd, usrbuf, n) != n)  
        unix_error("Rio_writen error");  
}  
void Rio_readinitb(rio_t *rp, int fd)  
{  
    rio_readinitb(rp, fd);  
}   
ssize_t Rio_readnb(rio_t *rp, void *usrbuf, size_t n)   
{  
    ssize_t rc;  
    if ((rc = rio_readnb(rp, usrbuf, n)) < 0)  
        unix_error("Rio_readnb error");  
    return rc;  
}  
ssize_t Rio_readlineb(rio_t *rp, void *usrbuf, size_t maxlen)   
{  
    ssize_t rc;  
    if ((rc = rio_readlineb(rp, usrbuf, maxlen)) < 0)  
        unix_error("Rio_readlineb error");  
    return rc;  
}
```
> Stevens的UNP中使用static不是线程安全的，在CSapp中rio_readlineb和rio_readnb修改了两个缺陷，都是缓冲区读，并且线程安全， 其中核心为rio_t读缓冲区struct

```
/* Persistent state for the robust I/O (Rio) package */  
/* $begin rio_t */  
#define RIO_BUFSIZE 8192  
typedef struct {  
    int rio_fd;                /* descriptor for this internal buf */  
    int rio_cnt;               /* unread bytes in internal buf */  
    char *rio_bufptr;          /* next unread byte in internal buf */  
    char rio_buf[RIO_BUFSIZE]; /* internal buffer */  
} rio_t;  
/* $end rio_t */
```
## 参考

* UNIX网络编程第三版
* 深入理解计算机系统（系统I/O章节）

作者: [Andrew Liu][0]

[0]: http://andrewliu.tk/2015/07/05/UNIX%E7%BD%91%E7%BB%9C%E7%BC%96%E7%A8%8B%E5%9F%BA%E7%A1%80/