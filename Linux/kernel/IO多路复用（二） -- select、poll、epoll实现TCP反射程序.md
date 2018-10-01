## IO多路复用（二） -- select、poll、epoll实现TCP反射程序

来源：[https://segmentfault.com/a/1190000016400430](https://segmentfault.com/a/1190000016400430)

接着上文[IO多路复用（一）-- Select、Poll、Epoll][2]，接下来将演示一个TCP回射程序，源代码来自于该博文[https://www.cnblogs.com/Anker...][3]，在这里将其进行了整合，突出select、poll和epoll不同方法之间的比较，但是代码的结构相同，为了突出方法之间的差别，可能有的代码改动的并不合理，实际中使用并非这么写。
## 程序逻辑

该程序的主要逻辑如下：


服务器：
    1. 开启服务器套接字
    2. 将服务器套接字加入要监听的集合中（select的fd_set、poll的pollfd、epoll调用epoll_ctl）
    3. 进入循环，调用IO多路复用的API函数（select/poll/epoll_create），如果有事件产生：
        3.1. 服务器套接字产生的事件，添加新的客户端到监听集合中
        3.2. 客户端套接字产生的事件，读取数据，并立马回传给客户端
        
客户端：
    1. 开启客户端套接字
    2. 将客户端套接字和标准输入文件描述符加入要监听的集合中（select的fd_set、poll的pollfd、epoll调用epoll_ctl）
    3. 进入循环，调用IO多路复用的API函数（select/poll/epoll_create），如果有事件产生：
        3.1. 客户端套接字产生的事件，则读取数据，将其输出到控制台
        3.2. 标准输入文件描述符产生的事件，则读取数据，将其通过客户端套接字传给服务器

## multiplexing.h

具体代码如下，首先是头文件

```c
//multiplexing.h

#ifndef MULTIPLEXING_H
#define MULTIPLEXING_H

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <errno.h>
#include <netinet/in.h>
#include <sys/socket.h>
#include#include <unistd.h>
#include <sys/types.h>
#include <arpa/inet.h>
#include <sys/select.h>
#include <sys/epoll.h>
#include <unistd.h>
using namespace std;

#define MAXLINE     1024
class Multiplexing {
protected:
    static const int DEFAULT_IO_MAX = 10; //默认的最大文件描述符
    static const int INFTIM = -1;
    int io_max; //记录最大文件描述符
    int listenfd; //监听句柄
public:
    Multiplexing() { this->io_max = DEFAULT_IO_MAX; }
    Multiplexing(int max, int listenfd) { this->io_max = max; this->listenfd = listenfd; }
    ~Multiplexing() {}

    virtual void server_do_multiplexing() = 0; //服务端io多路复用
    virtual void client_do_multiplexing() = 0; //客户端io多路复用
    virtual void handle_client_msg() = 0; //处理客户端消息
    virtual bool accept_client_proc() = 0; //接收客户端连接
    virtual bool add_event(int confd, int event) = 0;
    virtual int wait_event() = 0; // 等待事件
};

//-----------------select-------------------------
class MySelect : public Multiplexing {
private:
    fd_set* allfds;      //句柄集合
    int* clifds;   //客户端集合
    int maxfd; //记录句柄的最大值
    int cli_cnt; //客户端个数

public:
    MySelect() : Multiplexing() { allfds = NULL; clifds = NULL; maxfd = 0; cli_cnt = 0; }
    MySelect(int max, int listenfd);
    ~MySelect() {
        if (allfds) {
            delete allfds;
            allfds = NULL;
        }
        if (clifds) {
            delete clifds;
            clifds = NULL;
        }
    }

    void server_do_multiplexing();
    void client_do_multiplexing();
    void handle_client_msg();
    bool accept_client_proc();
    bool add_event(int confd, int event);
    bool init_event(); //每次调用select前都要重新设置文件描述符
    int wait_event(); // 等待事件

};

//-----------------poll-------------------------
typedef struct pollfd Pollfd;
class MyPoll : public Multiplexing {
private:
    Pollfd* clientfds; //poll中使用pollfd结构体指定一个被监视的文件描述符
    int max_index; //记录当前clientfds数组中使用的最大下标

public:
    MyPoll() : Multiplexing() { clientfds = NULL; max_index = -1; }
    MyPoll(int max, int listenfd);
    ~MyPoll() {
        if (clientfds) {
            delete clientfds;
            clientfds = NULL;
        }
    }

    void server_do_multiplexing();
    void client_do_multiplexing();
    void handle_client_msg();
    bool accept_client_proc();
    bool add_event(int confd, int event);
    int wait_event(); // 等待事件
};

//-----------------epoll-------------------------
typedef struct epoll_event Epoll_event;
class MyEpoll : public Multiplexing {
private:
    int epollfd; //epoll的句柄，用来管理多个文件描述符
    Epoll_event *events; //事件数组
    int nready; //在handle_client_msg函数中用到，传给handle_client_msg函数的当前事件的个数
public:
    MyEpoll() : Multiplexing() { events = NULL; epollfd = -1; }
    MyEpoll(int max, int listenfd);
    ~MyEpoll() {
        if (events) {
            delete events;
            events = NULL;
        }
    }

    void server_do_multiplexing();
    void client_do_multiplexing();
    void handle_client_msg();
    bool accept_client_proc();
    bool add_event(int confd, int event);
    bool delete_event(int confd, int event);
    int wait_event(); // 等待事件
};

#endif // !MULTIPLEXING_H
```
## multiplexing.cpp

然后是函数的实现，从各个函数的实现可以看到select、poll、epoll在使用过程中的区别，具体看代码注释

```c
//multiplexing.cpp

#include "multiplexing.h"

//--------------------------select------------------
MySelect::MySelect(int max, int listenfd) : Multiplexing(max, listenfd) {
    this->allfds = new fd_set[this->io_max];
    this->clifds = new int[this->io_max];
    if (NULL == this->allfds || NULL == this->clifds) {
        perror("initialization failed!");
        exit(-1);
    }
    this->cli_cnt = 0;
    this->maxfd = 0;

    //初始化客户连接描述符
    int i;
    for (i = 0; i < io_max; i++) {
        this->clifds[i] = -1;
    }
}

void MySelect::server_do_multiplexing() {
    int  nready = 0;
    int i = 0;

    while (1) {
        //重新初始化fd_set集合 -- 这里与poll不同
        init_event();

        /*开始轮询接收处理服务端和客户端套接字*/
        nready = wait_event();
        if (-1 == nready) return;
        if (0 == nready) continue;

        if (FD_ISSET(this->listenfd, this->allfds)) {
            /*监听客户端请求*/
            if (!accept_client_proc())  //处理连接请求
                continue;
            if (--nready <= 0) //说明此时产生的事件个数小于等于1，所以不必再处理下面的客户连接信息
                continue;
        }

        /*接受处理客户端消息*/
        handle_client_msg();
    }
}

void MySelect::client_do_multiplexing() {
    char sendline[MAXLINE], recvline[MAXLINE];
    int n;
    this->maxfd = -1;
    int nready = -1;
    if (this->io_max < 2) {
        perror("please increase the max number of io!");
        exit(1);
    }

    //添加连接描述符
    if (!add_event(this->listenfd, -1)) {
        perror("add event error!");
        exit(1);
    }

    //添加标准输入描述符
    if (!add_event(STDIN_FILENO, -1)) {
        perror("add event error!");
        exit(1);
    }

    while (1) {
        //重新初始化fd_set集合 -- 这里与poll不同
        init_event();

        //等待事件产生
        nready = wait_event();
        if (-1 == nready) return;
        if (0 == nready) continue;

        //是否有客户信息准备好
        if (FD_ISSET(this->listenfd, this->allfds)) {
            n = read(this->listenfd, recvline, MAXLINE);
            if (n <= 0) {
                fprintf(stderr, "client: server is closed.\n");
                close(this->listenfd);
                return;
            }
            write(STDOUT_FILENO, recvline, n);
        }
        //测试标准输入是否准备好
        if (FD_ISSET(STDIN_FILENO, this->allfds)) {
            n = read(STDIN_FILENO, sendline, MAXLINE);
            if (n <= 0) {
                shutdown(this->listenfd, SHUT_WR);
                continue;
            }
            write(this->listenfd, sendline, n);
        }
    }
}

bool MySelect::init_event() {
    FD_ZERO(this->allfds); //重新设置文件描述符

                           /*添加监听套接字*/
    FD_SET(this->listenfd, this->allfds);
    this->maxfd = this->listenfd;

    int i;
    int  clifd = -1;
    /*添加客户端套接字*/
    for (i = 0; i < this->cli_cnt; i++) {
        clifd = this->clifds[i];
        /*去除无效的客户端句柄*/
        if (clifd != -1) {
            FD_SET(clifd, this->allfds);
        }
        this->maxfd = (clifd > this->maxfd ? clifd : this->maxfd);
    }
}

bool MySelect::accept_client_proc() {
    struct sockaddr_in cliaddr;
    socklen_t cliaddrlen;
    cliaddrlen = sizeof(cliaddr);
    int connfd;

    //接受新的连接
    if ((connfd = accept(this->listenfd, 
                         (struct sockaddr*)&cliaddr, &cliaddrlen)) == -1) {
        if (errno == EINTR)
            return false;
        else {
            perror("accept error:");
            exit(1);
        }
    }
    fprintf(stdout, "accept a new client: %s:%d\n", 
            inet_ntoa(cliaddr.sin_addr), cliaddr.sin_port);
    return add_event(connfd, -1); //添加新的描述符
}

bool MySelect::add_event(int connfd, int event) { //在select中event并没有作用
                                                  //将新的连接描述符添加到数组中
    int i = 0;
    for (i = 0; i < io_max; i++) {
        if (this->clifds[i] < 0) {
            this->clifds[i] = connfd;
            this->cli_cnt++;
            break;
        }
    }

    if (i == io_max) {
        fprintf(stderr, "too many clients.\n");
        return false;
    }

    //将新的描述符添加到读描述符集合中
    FD_SET(connfd, this->allfds);
    if (connfd > this->maxfd) this->maxfd = connfd;
    return true;
}

void MySelect::handle_client_msg() {
    int i = 0, n = 0;
    int clifd;
    char buf[MAXLINE];
    memset(buf, 0, MAXLINE);

    //处理信息
    for (i = 0; i <= this->cli_cnt; i++) {
        clifd = this->clifds[i];
        if (clifd < 0) {
            continue;
        }

        /*判断客户端套接字是否有数据*/
        if (FD_ISSET(clifd, this->allfds)) {
            //接收客户端发送的信息
            n = read(clifd, buf, MAXLINE);

            if (n <= 0) {
                /*n==0表示读取完成，客户都关闭套接字*/
                FD_CLR(clifd, this->allfds);
                close(clifd);
                this->clifds[i] = -1;
                continue;
            }

            //回写数据
            printf("recv buf is :%s\n", buf);
            write(clifd, buf, n);
            return;
        }
    }
}

int MySelect::wait_event() {
    struct timeval tv;

    /*每次调用select前都要重新设置文件描述符和时间，因为事件发生后，文件描述符和时间都被内核修改啦*/
    tv.tv_sec = 30;
    tv.tv_usec = 0;

    /*开始轮询接收处理服务端和客户端套接字*/
    int nready = select(this->maxfd + 1, this->allfds, NULL, NULL, &tv);
    if (nready == -1) {
        fprintf(stderr, "select error:%s.\n", strerror(errno));
    }
    if (nready == 0) {
        fprintf(stdout, "select is timeout.\n");
    }
    return nready;
}

//-----------------poll-------------------------
MyPoll::MyPoll(int max, int listenfd) : Multiplexing(max, listenfd) {
    this->clientfds = new Pollfd[this->io_max];
    //初始化客户连接描述符
    int i;
    for (i = 0; i < io_max; i++) {
        this->clientfds[i].fd = -1;
    }
    this->max_index = -1;
}

void MyPoll::server_do_multiplexing() {
    int sockfd;
    int i;
    int nready;
    this->max_index = -1;

    //注意：需要将监听描述符添加在第一个位置
    if (!add_event(this->listenfd, POLLIN)) {
        perror("add listen event error!");
        return;
    }

    //循环处理
    while (1) {
        //等待事件，获取可用描述符的个数
        nready = wait_event();
        if (nready == -1) {
            return;
        }
        if (nready == 0) {
            continue;
        }

        //测试监听描述符是否准备好
        if (this->clientfds[0].revents & POLLIN) {
            if (!accept_client_proc())  //处理连接请求
                continue;
            if (--nready <= 0) //说明此时产生的事件个数小于等于1，所以不必再处理下面的客户连接信息
                continue;
        }

        //处理客户连接
        handle_client_msg();
    }
}

void MyPoll::client_do_multiplexing() {
    char    sendline[MAXLINE], recvline[MAXLINE];
    int n;
    this->max_index = -1;
    int nready = -1;
    if (this->io_max < 2) {
        perror("please increase the max number of io!");
        exit(1);
    }

    //添加连接描述符
    if (!add_event(this->listenfd, POLLIN)) {
        perror("add event error!");
        exit(1);
    }

    //添加标准输入描述符
    if (!add_event(STDIN_FILENO, POLLIN)) {
        perror("add event error!");
        exit(1);
    }

    while (1) {
        //等待事件产生
        nready = wait_event();
        if (-1 == nready) return;
        if (0 == nready) continue;

        //是否有客户信息准备好
        if (this->clientfds[0].revents & POLLIN) {
            n = read(this->listenfd, recvline, MAXLINE);
            if (n <= 0) {
                fprintf(stderr, "client: server is closed.\n");
                close(this->listenfd);
                return;
            }
            write(STDOUT_FILENO, recvline, n);
        }
        //测试标准输入是否准备好
        if (this->clientfds[1].revents & POLLIN) {
            n = read(STDIN_FILENO, sendline, MAXLINE);
            if (n <= 0) {
                shutdown(this->listenfd, SHUT_WR);
                continue;
            }
            write(this->listenfd, sendline, n);
        }
    }
}

bool MyPoll::accept_client_proc() {
    struct sockaddr_in cliaddr;
    socklen_t cliaddrlen;
    cliaddrlen = sizeof(cliaddr);
    int connfd;

    //接受新的连接
    if ((connfd = accept(this->listenfd, 
                         (struct sockaddr*)&cliaddr, &cliaddrlen)) == -1) {
        if (errno == EINTR)
            return false;
        else {
            perror("accept error:");
            exit(1);
        }
    }
    fprintf(stdout, "accept a new client: %s:%d\n", 
            inet_ntoa(cliaddr.sin_addr), cliaddr.sin_port);
    return add_event(connfd, POLLIN); //添加新的描述符
}

bool MyPoll::add_event(int connfd, int event) {
    //将新的连接描述符添加到数组中
    int i;
    for (i = 0; i < io_max; i++) {
        if (this->clientfds[i].fd < 0) {
            this->clientfds[i].fd = connfd;
            break;
        }
    }

    if (i == io_max) {
        fprintf(stderr, "too many clients.\n");
        return false;
    }

    //将新的描述符添加到读描述符集合中
    this->clientfds[i].events = event;
    if (i > this->max_index) this->max_index = i;
    return true;
}

void MyPoll::handle_client_msg() {
    int i, n;
    char buf[MAXLINE];
    memset(buf, 0, MAXLINE);

    //处理信息
    for (i = 1; i <= this->max_index; i++) {
        if (this->clientfds[i].fd < 0)
            continue;

        //测试客户描述符是否准备好
        if (this->clientfds[i].revents & POLLIN) {
            //接收客户端发送的信息
            n = read(this->clientfds[i].fd, buf, MAXLINE);
            if (n <= 0) {
                close(this->clientfds[i].fd);
                this->clientfds[i].fd = -1;
                continue;
            }

            write(STDOUT_FILENO, buf, n);
            //向客户端发送buf
            write(this->clientfds[i].fd, buf, n);
        }
    }
}

int MyPoll::wait_event() {
    /*开始轮询接收处理服务端和客户端套接字*/
    int nready = nready = poll(this->clientfds, this->max_index + 1, INFTIM);
    if (nready == -1) {
        fprintf(stderr, "poll error:%s.\n", strerror(errno));
    }
    if (nready == 0) {
        fprintf(stdout, "poll is timeout.\n");
    }
    return nready;
}

//------------------------epoll---------------------------
MyEpoll::MyEpoll(int max, int listenfd) : Multiplexing(max, listenfd) {
    this->events = new Epoll_event[this->io_max];
    //创建一个描述符
    this->epollfd = epoll_create(this->io_max);
}

void MyEpoll::server_do_multiplexing() {
    int i, fd;
    int nready;
    char buf[MAXLINE];
    memset(buf, 0, MAXLINE);

    //添加监听描述符事件
    if (!add_event(this->listenfd, EPOLLIN)) {
        perror("add event error!");
        exit(1);
    }
    while (1) {
        //获取已经准备好的描述符事件
        nready = wait_event();
        this->nready = nready;
        if (-1 == nready) return;
        if (0 == nready) continue;

        //进行遍历
        /**这里和poll、select都不同，因为并不能直接判断监听的事件是否产生，
        所以需要一个for循环遍历，这个for循环+判断类似于poll中 
        if (FD_ISSET(this->listenfd, this->allfds))、
        select中的if (this->clientfds[0].revents & POLLIN)
        这里只是尽量写的跟poll、select中的结构类似，
        但是实际代码中，不应该这么写，这么写多加了一个for循环**/
        for (i = 0; i < nready; i++) {
            fd = events[i].data.fd;
            //根据描述符的类型和事件类型进行处理
            if ((fd == this->listenfd) && (events[i].events & EPOLLIN)) {  //监听事件
                /*监听客户端请求*/
                if (!accept_client_proc())  //处理连接请求
                    continue;
                //说明此时产生的事件个数小于等于1，所以不必再处理下面的客户连接信息
                if (--nready <= 0) 
                    continue;
            }
        }

        //处理客户端事件
        handle_client_msg();
    }
    close(epollfd);
}

bool MyEpoll::accept_client_proc() {
    struct sockaddr_in cliaddr;
    socklen_t cliaddrlen;
    cliaddrlen = sizeof(cliaddr);
    int connfd;

    //接受新的连接
    if ((connfd = accept(this->listenfd, 
                         (struct sockaddr*)&cliaddr, &cliaddrlen)) == -1) {
        if (errno == EINTR)
            return false;
        else {
            perror("accept error:");
            exit(1);
        }
    }
    fprintf(stdout, "accept a new client: %s:%d\n", 
            inet_ntoa(cliaddr.sin_addr), cliaddr.sin_port);
    return add_event(connfd, EPOLLIN); //添加新的描述符
}

void MyEpoll::client_do_multiplexing() { 
    char    sendline[MAXLINE], recvline[MAXLINE];
    int n;
    int nready = -1;
    int i, fd;
    if (this->io_max < 2) {
        perror("please increase the max number of io!");
        exit(1);
    }

    //添加连接描述符
    if (!add_event(this->listenfd, POLLIN)) {
        perror("add event error!");
        exit(1);
    }

    //添加标准输入描述符
    if (!add_event(STDIN_FILENO, POLLIN)) {
        perror("add event error!");
        exit(1);
    }

    while (1) {
        //等待事件产生
        nready = wait_event();
        if (-1 == nready) return;
        if (0 == nready) continue;

        for (i = 0; i < nready; i++) {
            fd = events[i].data.fd;
            //根据描述符的类型和事件类型进行处理
            if ((fd == this->listenfd) && (events[i].events & EPOLLIN)) {  //监听事件
                n = read(this->listenfd, recvline, MAXLINE);
                if (n <= 0) {
                    fprintf(stderr, "client: server is closed.\n");
                    close(this->listenfd);
                    return;
                }
                write(STDOUT_FILENO, recvline, n);
            }
            else {
                n = read(STDIN_FILENO, sendline, MAXLINE);
                if (n <= 0) {
                    shutdown(this->listenfd, SHUT_WR);
                    continue;
                }
                write(this->listenfd, sendline, n);
            }
        }
    }
}

bool MyEpoll::add_event(int connfd, int event) {
    //将新的描述符添加到读描述符集合中
    Epoll_event ev;
    ev.events = event;
    ev.data.fd = connfd;
    return epoll_ctl(this->epollfd, EPOLL_CTL_ADD, connfd, &ev) == 0;
}

void MyEpoll::handle_client_msg() {
    int i, fd;
    char buf[MAXLINE];
    memset(buf, 0, MAXLINE);

    //处理信息
    for (i = 0; i <= this->nready; i++) {
        fd = this->events[i].data.fd;
        if (fd == this->listenfd)
            continue;

        if (events[i].events & EPOLLIN) {
            int n = read(fd, buf, MAXLINE);
            if (n <= 0) {
                perror("read error:");
                close(fd);
                delete_event(fd, EPOLLIN);
            }
            else {
                write(STDOUT_FILENO, buf, n);
                //向客户端发送buf
                write(fd, buf, strlen(buf));
            }
        }
    }
}

int MyEpoll::wait_event() {
    /*开始轮询接收处理服务端和客户端套接字*/
    int nready = epoll_wait(this->epollfd, this->events, this->io_max, INFTIM);;
    if (nready == -1) {
        fprintf(stderr, "poll error:%s.\n", strerror(errno));
    }
    if (nready == 0) {
        fprintf(stdout, "poll is timeout.\n");
    }
    return nready;
}

bool MyEpoll::delete_event(int fd, int state) {
    Epoll_event ev;
    ev.events = state;
    ev.data.fd = fd;
    return epoll_ctl(this->epollfd, EPOLL_CTL_DEL, fd, &ev) == 0;
}
```
## 服务器代码

```c
#include "multiplexing.h"

#define IPADDRESS   "127.0.0.1"
#define PORT        8787
#define LISTENQ     5
#define OPEN_MAX    1000

//函数声明
//创建套接字并进行绑定
static int socket_bind(const char* ip, int port);

int main(int argc, char *argv[]) {
    int listenfd = socket_bind(IPADDRESS, PORT);
    if (listenfd < 0) {
        perror("socket bind error");
        return 0;
    }

    listen(listenfd, LISTENQ);
    
    // 改动此处，调用不同的IO复用函数
    MySelect mltp(OPEN_MAX, listenfd);
    //MyPoll mltp(OPEN_MAX, listenfd);
    //MyEpoll mltp(OPEN_MAX, listenfd);
    
    mltp.server_do_multiplexing(); //处理服务端
    return 0;
}

static int socket_bind(const char* ip, int port) {
    int  listenfd;
    struct sockaddr_in servaddr;
    listenfd = socket(AF_INET, SOCK_STREAM, 0);
    if (listenfd == -1) {
        perror("socket error:");
        exit(1);
    }
    bzero(&servaddr, sizeof(servaddr));
    servaddr.sin_family = AF_INET;
    inet_pton(AF_INET, ip, &servaddr.sin_addr);
    servaddr.sin_port = htons(port);
    if (bind(listenfd, (struct sockaddr*)&servaddr, sizeof(servaddr)) == -1) {
        perror("bind error: ");
        exit(1);
    }
    return listenfd;
}
```
## 客户端代码

```c
#include "multiplexing.h"
#define MAXLINE     1024
#define IPADDRESS   "127.0.0.1"
#define SERV_PORT   8787

static void handle_connection(int sockfd);

int main(int argc, char *argv[]) {
    int                 sockfd;
    struct sockaddr_in  servaddr;
    sockfd = socket(AF_INET, SOCK_STREAM, 0);
    bzero(&servaddr, sizeof(servaddr));
    servaddr.sin_family = AF_INET;
    servaddr.sin_port = htons(SERV_PORT);
    inet_pton(AF_INET, IPADDRESS, &servaddr.sin_addr);
    connect(sockfd, (struct sockaddr*)&servaddr, sizeof(servaddr));

    // 改动此处，调用不同的IO复用函数
    MySelect mltp(2, sockfd);
    //MyPoll mltp(2, sockfd);
    //MyEpoll mltp(2, sockfd);
    
    poll.client_do_multiplexing(); // 处理客户端
    return 0;
}
```
## 运行结果

服务端：

![][0]

客户端：

![][1]

完整代码可以访问笔者github：[https://github.com/yearsj/Cli...][4]
## 参考资料

[IO多路复用之select总结][5]

[IO多路复用之poll总结][6]

[IO多路复用之epoll总结][7]

作者：[yearsj][8]转载请注明出处：[https://segmentfault.com/a/11...][9]

[2]: https://segmentfault.com/a/1190000016400053
[3]: https://www.cnblogs.com/Anker/p/3258674.html%E5%8D%9A%E4%B8%BB%E7%9A%84%E5%87%A0%E7%AF%87%E7%9B%B8%E5%85%B3%E7%9A%84%E6%96%87%E7%AB%A0
[4]: https://github.com/yearsj/ClientServerProject.git
[5]: https://www.cnblogs.com/Anker/p/3258674.html
[6]: https://www.cnblogs.com/Anker/p/3261006.html
[7]: https://www.cnblogs.com/Anker/p/3263780.html
[8]: https://segmentfault.com/u/yearsj
[9]: https://segmentfault.com/a/1190000016400053
[0]: ./img/bVbgYCs.png
[1]: ./img/bVbgYCu.png