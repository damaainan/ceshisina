# Memcache，一个Set命令的内部执行流程

作者  [简单方式][0] 已关注 2016.11.19 19:51*  字数 1697  

![][1]


memcached-version-1.4.25

### 介绍

在看一个 Set 命令内部流程时，最好已经对 Memcache (内存、网络、线程) 有一定了解，可以简单参考下之前写的那几篇源码解析。

* [Memcache-内存模型-源码分析][2]
* [Memcache-网络线程模型-源码分析][3]
* [Memcache-哈希表-源码分析][4]

### 介绍

由于 Memcache 采用 libevent 事件库来监听网络连接，只要有一个网络连接(文件描述符)有动作，都会马上回调 event_handler() 函数，这里我们就从这个 event_handler() 函数开始，追踪一个 Set 命令内部经历了哪些流程。

### 状态机

所谓状态机就是针对我们每个连接的不同状态做不同的处理，内部为 event_handler() 之后调用 drive_machine(conn*) 函数处理，因为每一个连接都对应一个 conn* 结构体指针，所以在该结构体内有一个 state 字段来记录当前的连接状态，状态机函数drive_machine(conn*) 就根据 conn->state 当前的状态来做对应的处理。

### 连接状态说明

    //一组枚举
    enum conn_states {
        /** 主线程 libevent 监听事件的回调状态,该连接状态只会赋给主
            线程 libevent 监听的文件描述符 (11211) 也就说 conn->state=conn_listening
            始终等于这个状态,由于主线程回调只会触发这一个状态,所以只要触发就代表有新的客户
            端来连接, 就需要分发该连接到 work 线程并创建, 因为主线程不负责处理连接 , 只负
            责分发连接 , 有点类似于负载均衡 */
    
        conn_listening,  /** 连接分发创建状态 */
    
        /** work线程 libevent 监听事件的回调状态 , 下面的这些状态全部都由 work 线程事件回调处理 */
    
        conn_new_cmd,    /** 连接开始状态 */
        conn_waiting,    /** 等待连接有活动 */
        conn_read,       /** 读取网路缓冲区数据到应用层buf缓冲区 */
        conn_parse_cmd,  /** 解析命令 */
        conn_write,      /** 添加一条response message*/
        conn_nread,      /** 从应用层缓存区读取数据 */
        conn_swallow,    /** swallowing unnecessary bytes w/o storing */
        conn_closing,    /** 关闭一个连接 */
        conn_mwrite,     /** 回写客户端数据 */
        conn_closed,     /** 异常进程关闭 */
        conn_max_state   /** Max state value (used for assertion) */
    };

### 网络连接-处理数据-状态转换

![][5]



Memcache-网络连接-状态转换

### 源码实现

> 网络连接事件回调函数，event_handler

    void event_handler(const int fd, const short which, void *arg) {
        conn *c;
    
        // 获取当前连接的 conn* 
        c = (conn *)arg;
        assert(c != NULL);
    
        // 保存一下当前触发 event 事件类型
        c->which = which;
    
        /* sanity */
        if (fd != c->sfd) {
            if (settings.verbose > 0)
                fprintf(stderr, "Catastrophic: event fd doesn't match conn fd!\n");
            conn_close(c);
            return;
        }
    
        // 调用状态机函数处理当前连接
        drive_machine(c);
    
        /* wait for next event */
        return;
    }

> 状态机函数，drive_machine 

    static void drive_machine(conn *c) {
        bool stop = false;
        int sfd;
        socklen_t addrlen;
        struct sockaddr_storage addr;
        int nreqs = settings.reqs_per_event;
        int res;
        const char *str;
    
        assert(c != NULL);
    
        while (!stop) {
    
             switch(c->state) {
    
                 case 'conn_listening' :
                    //....
                      stop = true;
                      break;
                 case 'conn_waiting':
                      conn_set_state(c, conn_read);
                      stop = true;
                      break;
                 case 'conn_read':
                    //.....
                        conn_set_state(c, conn_parse_cmd);
                        break;
                    //.....
                 case '.....':
                    //....
             } 
        }
    }

可以看到里面就是一些 switch case 状态判断, 外加一个 while 死循环 , 这个死循环何时结束取决于 stop = true , 因为有些情况一个连接状态处理完并赋值下一次的处理状态后, 就需要马上结束, 等待下次事件回调 , 那么就会 stop = true , 而有些情况一个连接状态处理完成后要求马上赋值新的连接状态并继续处理所以直接 break 。

###### memcache libevent 事件触发模式

memcache 的 libevent 库使用的触发模式为 水平触发 (level-triggered，也被称为条件触发) 只要满足条件，就触发一个事件 , 就是只要有数据没有被获取, 内核就不断通知你, 所以说状态机函数里面有的状态处理完了,只是更新一下下次的处理状态, 就直接退出, 这并没有什么问题，因为只要网络缓冲区还有数据, 就会不断的触发事件回调函数，然后继续处理状态。

###### 开始从一个新连接的初始化状态开始往下走流程.

> (1) > conn_new_cmd>  状态处理

    int nreqs = settings.reqs_per_event; // 默认 20 
    
          case conn_new_cmd:
    
                // 即使缓冲区buf有很多命令包, 但该连接一次最多循环执行 nreqs 个命令数据包 ,
                // 防止该连接事件一直占用当前函数 , 别的连接事件无法处理
                --nreqs;
                if (nreqs >= 0) {
                    // 重置当前连接状态
                    reset_cmd_handler(c);
                } else {
                    pthread_mutex_lock(&c->thread->stats.mutex);
                    c->thread->stats.conn_yields++;
                    pthread_mutex_unlock(&c->thread->stats.mutex);
    
                    //判断当前缓冲区buf是否还有数据
                    if (c->rbytes > 0) {
                        /* 
                            如果buf还有数据, 就不能马上退出, 需要处理, 但是已经达到 nreqs 限额了,
                            必须让出当前函数，交由其他的连接事件进行处理, 所以这里用了一个技巧, 
                            把当前连接事件变成 EV_WRITE , 因为当前连接的(写)网络缓冲区没有数据,
                            从而达到低于等于最低水位, 重新触发事件, 继续回调本函数, 进行处理buf剩
                            余的数据包
                        */
                        if (!update_event(c, EV_WRITE | EV_PERSIST)) {
                            if (settings.verbose > 0)
                                fprintf(stderr, "Couldn't update event\n");
                            conn_set_state(c, conn_closing);
                            break;
                        }
                    }
                    //退出
                    stop = true;
                }
                break;

###### 当达到 nreqs 最大处理范围, 为什么把当前连接事件变成 EV_WRITE

因为当前连接事件是 EV_READ , 当然我们可以不变成 EV_WRITE , 直接退出, 交由其他连接事件处理并等待下次回调即可,但是如果我们的(读)网络缓冲区一直没有数据，无法触发读回调，而应用缓冲区buf里面还有上次未处理的数据包，岂不是一直无法处理数据，对于客户端来说短时间内相当于丢失了一条命令,而变成 EV_WRITE 的话, 既可以保证直接退出，交由其他事件处理，又可以保证马上重新触发写事件，因为我们的写缓冲区没有数据，低于缓冲区最低水位(具体可以看一下网络缓冲区水位介绍), 所以保证马上又会触发事件回调 , 继续处理缓冲区buf未处理完的数据,说得简单点就相当于, 先让出队伍第一的位置, 重新到后面排队, 先让别的着急的人处理 , 一会排到我了在继续处理, 保证能把未处理完的给处理了.

> 重置当前连接状态， reset_cmd_handler 函数

    static void reset_cmd_handler(conn *c) {
        c->cmd = -1;
        c->substate = bin_no_state;
    
        //如果c->item未释放,则重新释放下
        if(c->item != NULL) {
            item_remove(c->item);
            c->item = NULL;
        }
    
        //重置下各个buf缓冲区大小,因为有时候发送大量的数据过来就会导致buf特别大
        //当大到一个阀值,并且buf里面的数据处理的差不多了,就会收缩下内存
        conn_shrink(c);
    
        //判断当前buf缓冲区是否还有数据，如果有则 conn_parse_cmd 解析命令
        //如果没有则 conn_waiting 切换下状态, 重新监听事件等待下次回调
        if (c->rbytes > 0) {
           conn_set_state(c, conn_parse_cmd);
        } else {
           // 由于第一次调用 c->rbytes 是空的还没有读取进来所以直接
           conn_set_state(c, conn_waiting);
        }
    }

> (2) > conn_waiting>  状态处理

     conn_waiting 状态
         case conn_waiting:
                // 更新当前事件类型为 EV_READ , 当流转到当前状态时, 就代表需要重新监听可读事件
                // 主要是防止上面可能存在更新 EV_WRITE 情况, 所以就是在切换回来
                if (!update_event(c, EV_READ | EV_PERSIST)) {
                    if (settings.verbose > 0)
                        fprintf(stderr, "Couldn't update event\n");
                    conn_set_state(c, conn_closing);
                    break;
                }
                // 更新状态为 conn_read , 就是下次在回调本函数, 直接找 coon_read 状态处理 , 其实也就是
                // 开始读取网络缓冲区数据
                conn_set_state(c, conn_read);
                // 退出等待下次回调
                stop = true;
                break;

> (3) > conn_read>  状态处理

    case conn_read:
                //判断当前是UDP还是TCP,以TCP为例调用 try_read_network 读网络缓冲区数据
                res = IS_UDP(c->transport) ? try_read_udp(c) : try_read_network(c);
    
                switch (res) {
                //如果没有数据则返回此状态,但是几乎不会返回该状态
                //因为只要事件回调并调用conn_read状态就代表网络缓
                //冲区有数据
                case READ_NO_DATA_RECEIVED:
                    //置为conn_waiting重新监听该连接
                    conn_set_state(c, conn_waiting);
                    break;
                //成功读取
                case READ_DATA_RECEIVED:
                    //置为conn_parse_cmd状态解析命令
                    conn_set_state(c, conn_parse_cmd);
                    break;
                //读取失败
                case READ_ERROR:
                    //置为 conn_closing 关闭连接
                    conn_set_state(c, conn_closing);
                    break;
                //扩充当前连接的buf缓冲区失败，就是申请内存失败
                case READ_MEMORY_ERROR: 
                    //继续再读取, 因为刚才扩充失败, 所以导致新读取的数据会覆盖buf缓冲区已有的
                    break;
                }
                break;

> 读取网络缓冲区数据，try_read_network 函数

    static enum try_read_result try_read_network(conn *c) {
        enum try_read_result gotdata = READ_NO_DATA_RECEIVED;
        int res;
        int num_allocs = 0;
        assert(c != NULL);
    
        //判断当前连接buf缓冲区读取的位置在不在起始
        if (c->rcurr != c->rbuf) {
            if (c->rbytes != 0) /* otherwise there's nothing to copy */
                memmove(c->rbuf, c->rcurr, c->rbytes);
            c->rcurr = c->rbuf;
        }
    
        // 循环读取,两种情况
        // 第一种从网络缓冲区读取的数据小于当前连接buf缓冲区剩余的大小, 则代表读取完毕, 直接 brek
        // 第二种从网络缓冲区读取的数据等于当前连接buf缓冲区剩余的大小, 则代表网络缓冲区可能还有数据
        // 未读取完毕, 则扩充一下当前连接buf缓冲区的大小, 继续读取, 最大扩充 4 次
        while (1) {
            //判断读取的数据大小是否正好等于当前连接buf缓冲区大小
            if (c->rbytes >= c->rsize) {
                // 判断是否已经扩充了 4 次
                if (num_allocs == 4) {
                    return gotdata;
                }
                //+1
                ++num_allocs;
                //扩容
                char *new_rbuf = realloc(c->rbuf, c->rsize * 2);
                if (!new_rbuf) {
                    STATS_LOCK();
                    stats.malloc_fails++;
                    STATS_UNLOCK();
                    if (settings.verbose > 0) {
                        fprintf(stderr, "Couldn't realloc input buffer\n");
                    }
                    //当前读取的大小置0
                    c->rbytes = 0; /* ignore what we read */
                    out_of_memory(c, "SERVER_ERROR out of memory reading request");
                    c->write_and_go = conn_closing;
                    return READ_MEMORY_ERROR;
                }
                c->rcurr = c->rbuf = new_rbuf;
                //更新buf缓冲区大小
                c->rsize *= 2;
            }
            //计算当前buf缓冲区剩余大小avail
            int avail = c->rsize - c->rbytes;
            //读取 avail 个字节
            res = read(c->sfd, c->rbuf + c->rbytes, avail);
            if (res > 0) {
                pthread_mutex_lock(&c->thread->stats.mutex);
                c->thread->stats.bytes_read += res;
                pthread_mutex_unlock(&c->thread->stats.mutex);
                gotdata = READ_DATA_RECEIVED;
                //更新当前已读取大小
                c->rbytes += res;
                //判断读取的字节是否正好等于buf缓冲区剩余的大小
                if (res == avail) {
                    continue;
                } else {
                    break;
                }
            }
            if (res == 0) {
                return READ_ERROR;
            }
            if (res == -1) {
                if (errno == EAGAIN || errno == EWOULDBLOCK) {
                    break;
                }
                return READ_ERROR;
            }
        }
        return gotdata;
    }

> (4) > conn_parse_cmd>  状态处理

         case conn_parse_cmd :
                //对刚才读取到连接buf缓冲区里面的数据进行解析
                //如果是 二进制包 则解析包头
                //如果是 ASCII包  则解析命令
                if (try_read_command(c) == 0) {
                    /* wee need more data! */
                    conn_set_state(c, conn_waiting);
                }
        break;

###### memcache 数据包介绍:

memcache 的数据包分为 二进制 、 ASCII 两种类型，目前大部分使用的都是 ASCII 类型的数据包，拿PHP扩展来说一般 memcached 支持 ASCII/二进制协议，而memcache 只支持 ASCII协议.

> ASCII 协议：

    set <key> <flags> <exptime> <bytes> <cas unique> [noreply]\r\n<data block>\r\n

> 二进制协议头 header：

    typedef union {
            struct {
                uint8_t magic;    //二进制协议标示
                uint8_t opcode;   //命令标示(get|set)
                uint16_t keylen;  //key lenght
                uint8_t extlen;   //过期时间、flag 占用字节数, 固定 8 字节
                uint8_t datatype; //(保留未来使用)
                uint16_t reserved; //(保留未来使用)
                uint32_t bodylen; //value大小
                uint32_t opaque;  //目前没看有什么用途(看源码就是直接保存一下,回写客户端的时候又给带回去了)
                uint64_t cas;      // cas 
            } request;
            uint8_t bytes[24];
        } protocol_binary_request_header;

> 二进制 set 命令完整协议：

        // 实际上就是在header基础上多了一个body结构体, 记录一下 flags 和 过期时间
        // 这个结构体占用8个字节,也就是上面说的extlen, 也可以看到下
        // 面bytes的总大小是在header的基础上+8
        typedef union {
            struct {
                protocol_binary_request_header header;
                struct {
                    uint32_t flags; 
                    uint32_t expiration;
                } body;
            } message;
            uint8_t bytes[sizeof(protocol_binary_request_header) + 8];
        } protocol_binary_request_set;

> 解析buf缓冲区数据，try_read_command 函数 

    static int try_read_command(conn *c) {
        assert(c != NULL);
        assert(c->rcurr <= (c->rbuf + c->rsize));
        assert(c->rbytes > 0);
    
        if (c->protocol == negotiating_prot || c->transport == udp_transport)  {
            //判断当前buf缓冲区第一个字符是否等于 PROTOCOL_BINARY_REQ = 0x80
            if ((unsigned char)c->rbuf[0] == (unsigned char)PROTOCOL_BINARY_REQ) {
                //二进制包
                c->protocol = binary_prot;
            } else {
                //ASCII包
                c->protocol = ascii_prot;
            }
    
            if (settings.verbose > 1) {
                fprintf(stderr, "%d: Client using the %s protocol\n", c->sfd,
                        prot_text(c->protocol));
            }
        }
    
        //判断是否等于 二进制包
        if (c->protocol == binary_prot) {
            /* 判断当前buf缓冲区的数据大小是否小于默认包头的大小 */
            if (c->rbytes < sizeof(c->binary_header)) {
                //如果小于包头则不能处理当前数据
                //返回0 继续事件监听等待读取完整
                //的数据在进行处理
                return 0;
            } else {
                //把rcurr指向buf缓冲区位置的数据转换成 protocol_binary_request_header 包头结构体
                protocol_binary_request_header* req;
                req = (protocol_binary_request_header*)c->rcurr;
    
                //.....
    
                //整理数据
                c->binary_header = *req;
                c->binary_header.request.keylen = ntohs(req->request.keylen);
                c->binary_header.request.bodylen = ntohl(req->request.bodylen);
                c->binary_header.request.cas = ntohll(req->request.cas);
    
                //还是判断第一个字符是否不等于 0x80
                if (c->binary_header.request.magic != PROTOCOL_BINARY_REQ) {
                    if (settings.verbose) {
                        fprintf(stderr, "Invalid magic:  %x\n",
                                c->binary_header.request.magic);
                    }
                    conn_set_state(c, conn_closing);
                    return -1;
                }
    
                c->msgcurr = 0;
                c->msgused = 0;
                c->iovused = 0;
                if (add_msghdr(c) != 0) {
                    out_of_memory(c,
                            "SERVER_ERROR Out of memory allocating headers");
                    return 0;
                }
    
                //命令opcode获取
                c->cmd = c->binary_header.request.opcode;
                c->keylen = c->binary_header.request.keylen;
                c->opaque = c->binary_header.request.opaque;
                /* clear the returned cas value */
                c->cas = 0;
    
                //根据命令的类型设置读取命令指令
                dispatch_bin_command(c);
    
                //更新一下buf缓冲区剩余数据大小
                c->rbytes -= sizeof(c->binary_header);
                //更新一下buf缓冲区当前处理位置
                c->rcurr += sizeof(c->binary_header);
            }
        } else {
            // ASCII 处理 略过 .....
        }
    
        return 1;
    }

> 根据命令的类型-设置如何读取，dispatch_bin_command 函数

    static void dispatch_bin_command(conn *c) {
        int protocol_error = 0;
    
        int extlen = c->binary_header.request.extlen;
        int keylen = c->binary_header.request.keylen;
        uint32_t bodylen = c->binary_header.request.bodylen;
    
        if (settings.sasl && !authenticated(c)) {
            write_bin_error(c, PROTOCOL_BINARY_RESPONSE_AUTH_ERROR, NULL, 0);
            c->write_and_go = conn_closing;
            return;
        }
    
        MEMCACHED_PROCESS_COMMAND_START(c->sfd, c->rcurr, c->rbytes);
        c->noreply = true;
    
        /* 判断key的长度是否超过最大限制 */
        // KEY_MAX_LENGTH = 250
        if (keylen > KEY_MAX_LENGTH) {
            handle_binary_protocol_error(c);
            return;
        }
    
        switch (c->cmd) {
        //命中 PROTOCOL_BINARY_CMD_SETQ 类型
        case PROTOCOL_BINARY_CMD_SETQ:
            c->cmd = PROTOCOL_BINARY_CMD_SET; //更新cmd至memcache内部set类型
            break;
        case PROTOCOL_BINARY_CMD_ADDQ:
            c->cmd = PROTOCOL_BINARY_CMD_ADD;
            break;
        case PROTOCOL_BINARY_CMD_REPLACEQ:
            c->cmd = PROTOCOL_BINARY_CMD_REPLACE;
            break;
        //省略.......
        default:
            c->noreply = false;
        }
    
        switch (c->cmd) {
            //省略.......
    
            //命中 PROTOCOL_BINARY_CMD_SET 类型
            case PROTOCOL_BINARY_CMD_SET: /* FALLTHROUGH */
            case PROTOCOL_BINARY_CMD_ADD: /* FALLTHROUGH */
            case PROTOCOL_BINARY_CMD_REPLACE:
                if (extlen == 8 && keylen != 0 && bodylen >= (keylen + 8)) {
                    //设置读取key指令
                    bin_read_key(c, bin_reading_set_header, 8);
                } else {
                    protocol_error = 1;
                }
                break;
            case PROTOCOL_BINARY_CMD_GETQ:  /* FALLTHROUGH */
            case PROTOCOL_BINARY_CMD_GET:   /* FALLTHROUGH */
            case PROTOCOL_BINARY_CMD_GETKQ: /* FALLTHROUGH */
            case PROTOCOL_BINARY_CMD_GETK:
                if (extlen == 0 && bodylen == keylen && keylen > 0) {
                    bin_read_key(c, bin_reading_get_key, 0);
                } else {
                    protocol_error = 1;
                }
                break;
            //省略.......
            default:
                write_bin_error(c, PROTOCOL_BINARY_RESPONSE_UNKNOWN_COMMAND, NULL,
                                bodylen);
        }
    
        if (protocol_error)
            handle_binary_protocol_error(c);
    }

> 设置如何去读取key命令，bin_read_key 函数

    static void bin_read_key(conn *c, enum bin_substates next_substate, int extra) {
        assert(c);
    
        //当前key处理类型
        c->substate = next_substate;
        //计算从buf缓冲区或网络缓冲区待读取的字节数(也就是读取 key + flags + expiration)
        //因为有时候buf缓冲区数据不够则从网络缓冲区读取
        c->rlbytes = c->keylen + extra;
    
        //判断buf缓冲区空间中是否已经可以容纳下待读取的rlbytes字节数
        //大部分不会存在rlbytes容纳不下的情况, 因为每次发送过
        //来都是一个完整的二进制包而rlbytes也都在这个包里面所
        //以读取的时候也是完整的读取到buf缓冲区里面,也就是说buf缓冲
        //区空间是根据发送数据多少动态调整的,最大调整(扩容)4次
    
        //除非极端情况比如:网络延迟,先到了一部分数据,还有一部分
        //未到,这可能会导致我们的buf空间大小计算错误
        //如 默认buf等于2048/Bytes,正常完整发送过来完整的包等于2050/Bytes
        //   但是由于各种原因先发送过来 2046/Bytes, 如果按完整的包大小我们
        //   的buf需要(调整扩容1次)以完全容纳下,但这不是一个完整的包只是部分
        //   所以 2048 > 2046 没考虑扩容,但是当我们处理完包头2046/Bytes
        //     需要在处理 c->rlbytes 字节的时候就会导致空间不足了,因为已经处理2046/Bytes
        //   还需要处理4/Bytes, 但是buf只有2048-2046=2/Bytes, 但是我们需要4/Bytes, 所以就还要在
        //     网络缓冲区中读取2/Bytes放到buf里面,但是buf空间不足,这就会导致命中下面这个条件 4 > 2048 - 2046
    
        ptrdiff_t offset = c->rcurr + sizeof(protocol_binary_request_header) - c->rbuf;
        if (c->rlbytes > c->rsize - offset) {
            size_t nsize = c->rsize;
            size_t size = c->rlbytes + sizeof(protocol_binary_request_header);
    
            while (size > nsize) {
                nsize *= 2;
            }
            if (nsize != c->rsize) {
                if (settings.verbose > 1) {
                    fprintf(stderr, "%d: Need to grow buffer from %lu to %lu\n",
                            c->sfd, (unsigned long)c->rsize, (unsigned long)nsize);
                }
                char *newm = realloc(c->rbuf, nsize);
                if (newm == NULL) {
                    STATS_LOCK();
                    stats.malloc_fails++;
                    STATS_UNLOCK();
                    if (settings.verbose) {
                        fprintf(stderr, "%d: Failed to grow buffer.. closing connection\n",
                                c->sfd);
                    }
                    conn_set_state(c, conn_closing);
                    return;
                }
    
                c->rbuf= newm;
                /* rcurr should point to the same offset in the packet */
                c->rcurr = c->rbuf + offset - sizeof(protocol_binary_request_header);
                c->rsize = nsize;
            }
            if (c->rbuf != c->rcurr) {
                memmove(c->rbuf, c->rcurr, c->rbytes);
                c->rcurr = c->rbuf;
                if (settings.verbose > 1) {
                    fprintf(stderr, "%d: Repack input buffer\n", c->sfd);
                }
            }
        }
    
        //把c->ritem指向buf中待读取的数据区
        c->ritem = c->rcurr + sizeof(protocol_binary_request_header);
        //切换当前连接状态为conn_nread
        conn_set_state(c, conn_nread);
    }

> (5) > conn_nread>  状态处理

    case conn_nread:
                //是否已经把 rlbytes 读取完
                if (c->rlbytes == 0) {
                    //处理key或value
                    complete_nread(c);
                    break;
                }
    
                /* Check if rbytes < 0, to prevent crash */
                if (c->rlbytes < 0) {
                    if (settings.verbose) {
                        fprintf(stderr, "Invalid rlbytes to read: len %d\n", c->rlbytes);
                    }
                    conn_set_state(c, conn_closing);
                    break;
                }
    
                /* first check if we have leftovers in the conn_read buffer */
                if (c->rbytes > 0) {
    
                    //判断buf缓冲区剩余的rbytes是否大于或小于rlbytes然后计算出tocpoy
                    //举个例子：
                    //如果 rbytes=2、rlbytes=4 那么判断条件 tocopy = 2 > 4 ? 4 : 2;
                    //所以 tocopy=2 也就是说buf缓冲区中已经存在 2/Bytes , 先把buf缓冲区中
                    //这2/Bytes更新掉,再去网络缓冲区把剩下的2/Bytes读取进来,相反如果
                    //rbytes > rlbytes 那就不需要再去网络缓冲区读取了,因为我们当前的
                    //buf中已经存在了.
                    //tocopy就是rlbytes在buf缓冲区已经存在的字节数
                    int tocopy = c->rbytes > c->rlbytes ? c->rlbytes : c->rbytes;
    
                    //如果 ritem != rcurr 就会把 rcurr 指向的区域数据copy到 ritem 地址里面
                    //正常 ritem == rcurr 都是指向buf区域
                    //出现这种情况一般是处理key的时候已经申请完毕item内存块,然后准备读取最终的
                    //value这个时候会把ritem改成指向已申请的item数据区,然后执行到这步,直接把buf中
                    //rcurr指向的数据copy到ritem指向的item数据区.
                    if (c->ritem != c->rcurr) {
                        memmove(c->ritem, c->rcurr, tocopy);
                    }
    
                    //按topcopy更新
                    c->ritem += tocopy;
                    c->rlbytes -= tocopy;
                    c->rcurr += tocopy;
                    c->rbytes -= tocopy;
                    //判断更新完rlbytes是否等于0,如果等于0就代表 tocopy 等于 rlbytes
                    //也就是说rlbytes已经完整的存在buf缓冲区里面了
                    if (c->rlbytes == 0) {
                        break; /* 读取完毕break 执行调用上面的 complete_nread() 函数 */
                    }
                }
                //上面条件如果rlbytes不等于0,就代表 tocopy 小于 rlbytes
                //也就说buf缓冲区只存在一部分,还剩一部分需要去网络缓冲区里
                //面在把 c->rbytes -= tocopy 剩余读取进来
                res = read(c->sfd, c->ritem, c->rlbytes);
                if (res > 0) {
                    pthread_mutex_lock(&c->thread->stats.mutex);
                    c->thread->stats.bytes_read += res;
                    pthread_mutex_unlock(&c->thread->stats.mutex);
                    if (c->rcurr == c->ritem) {
                        c->rcurr += res;
                    }
                    c->ritem += res;
                    c->rlbytes -= res;
                    break; /* 读取完毕break 执行调用上面的 complete_nread() 函数 */
                }
                //下面就是一些读取失败的判断条件
                if (res == 0) { /* end of stream */
                    conn_set_state(c, conn_closing);
                    break;
                }
                if (res == -1 && (errno == EAGAIN || errno == EWOULDBLOCK)) {
                    if (!update_event(c, EV_READ | EV_PERSIST)) {
                        if (settings.verbose > 0)
                            fprintf(stderr, "Couldn't update event\n");
                        conn_set_state(c, conn_closing);
                        break;
                    }
                    stop = true;
                    break;
                }
                /* otherwise we have a real error, on which we close the connection */
                if (settings.verbose > 0) {
                    fprintf(stderr, "Failed to read, and not due to blocking:\n"
                            "errno: %d %s \n"
                            "rcurr=%lx ritem=%lx rbuf=%lx rlbytes=%d rsize=%d\n",
                            errno, strerror(errno),
                            (long)c->rcurr, (long)c->ritem, (long)c->rbuf,
                            (int)c->rlbytes, (int)c->rsize);
                }
                conn_set_state(c, conn_closing);
                break;

###### 说明

下面的函数处理都是针对 key 和 value 的了，因为上面包头信息已经处理完毕了，并且上面 c->rlbytes = (key + flags + expiration) 也读取完毕，同时也能看出来 memcache 在处理一个set命令的时候 ,如果是二进制包处理步骤为  
(1) 包头信息处理  
(2) 读取key信息处理  
(3) 读取value处理

> complete_nread 函数

    static void complete_nread(conn *c) {
        assert(c != NULL);
        assert(c->protocol == ascii_prot
               || c->protocol == binary_prot);
    
        //还是判断当前的数据包是 ASCII 还是 binary_prot
        //我们是二进制包
        if (c->protocol == ascii_prot) {
            complete_nread_ascii(c);
        } else if (c->protocol == binary_prot) {
            complete_nread_binary(c);
        }
    }

> complete_nread_binary 函数

    static void complete_nread_binary(conn *c) {
        assert(c != NULL);
        assert(c->cmd >= 0);
    
        //根据 c->substate 状态选择对应的函数处理
        //之前在调用 bin_read_key(c, bin_reading_set_header, 8)
        //这个函数的时候已经赋值了 c->substate = bin_reading_set_header
        switch(c->substate) {
        case bin_reading_set_header:
            if (c->cmd == PROTOCOL_BINARY_CMD_APPEND ||
                    c->cmd == PROTOCOL_BINARY_CMD_PREPEND) {
                process_bin_append_prepend(c);
            } else {
                //(1) 执行 key 处理
                process_bin_update(c);
            }
            break;
        case bin_read_set_value:
            //(2) 执行 value 处理
            complete_update_bin(c);
            break;
        case bin_reading_get_key:
        case bin_reading_touch_key:
            process_bin_get_or_touch(c);
            break;
        case bin_reading_stat:
            process_bin_stat(c);
            break;
        case bin_reading_del_header:
            process_bin_delete(c);
            break;
        case bin_reading_incr_header:
            complete_incr_bin(c);
            break;
        case bin_read_flush_exptime:
            process_bin_flush(c);
            break;
        case bin_reading_sasl_auth:
            process_bin_sasl_auth(c);
            break;
        case bin_reading_sasl_auth_data:
            process_bin_complete_sasl_auth(c);
            break;
        default:
            fprintf(stderr, "Not handling substate %d\n", c->substate);
            assert(0);
        }
    }

###### 处理到目前 c->rcurr 指向 buf 中的什么位置 ?

c->rcurr 目前指向缓冲区[buf]的位置为 header + extra(8) + key + (rcurr)value 可以看到目前指向value的位置，记住当前位置，下面会根据 c->rcurr 计算出 header、key、等位置.

> process_bin_update 函数

    static void process_bin_update(conn *c) {
        char *key;
        int nkey;
        int vlen;
        item *it;
        //获取当前包头指针并转成set包类型,因为set包里面有flags和过期时间expiration
        //就是之前在header包后面跟着那8个字节
        protocol_binary_request_set* req = binary_get_request(c);
        /*
            static void* binary_get_request(conn *c) {
                char *ret = c->rcurr;
                ret -= (sizeof(c->binary_header) + c->binary_header.request.keylen +
                    c->binary_header.request.extlen);
                assert(ret >= c->rbuf);
                return ret;
            }   
         */
    
        assert(c != NULL);
    
        //获取key
        key = binary_get_key(c);
        /*
            static char* binary_get_key(conn *c) {
                return c->rcurr - (c->binary_header.request.keylen);
            }
        */
        //key的长度
        nkey = c->binary_header.request.keylen;
    
        /* fix byteorder in the request */
        req->message.body.flags = ntohl(req->message.body.flags);
        req->message.body.expiration = ntohl(req->message.body.expiration);
    
        //value的长度
        vlen = c->binary_header.request.bodylen - (nkey + c->binary_header.request.extlen);
    
        if (settings.verbose > 1) {
            int ii;
            if (c->cmd == PROTOCOL_BINARY_CMD_ADD) {
                fprintf(stderr, "<%d ADD ", c->sfd);
            } else if (c->cmd == PROTOCOL_BINARY_CMD_SET) {
                fprintf(stderr, "<%d SET ", c->sfd);
            } else {
                fprintf(stderr, "<%d REPLACE ", c->sfd);
            }
            for (ii = 0; ii < nkey; ++ii) {
                fprintf(stderr, "%c", key[ii]);
            }
    
            fprintf(stderr, " Value len is %d", vlen);
            fprintf(stderr, "\n");
        }
    
        if (settings.detail_enabled) {
            stats_prefix_record_set(key, nkey);
        }
    
        //申请一块item内存块存放数据
        it = item_alloc(key, nkey, req->message.body.flags,
                realtime(req->message.body.expiration), vlen+2);
        //上面的realtime函数,该函数负责将客户端传递的过期时间(sec)
        //生成一个相对服务器的过期时间
    
        if (it == 0) {
            if (! item_size_ok(nkey, req->message.body.flags, vlen + 2)) {
                write_bin_error(c, PROTOCOL_BINARY_RESPONSE_E2BIG, NULL, vlen);
            } else {
                out_of_memory(c, "SERVER_ERROR Out of memory allocating item");
            }
    
            /* Avoid stale data persisting in cache because we failed alloc.
             * Unacceptable for SET. Anywhere else too? */
            if (c->cmd == PROTOCOL_BINARY_CMD_SET) {
                it = item_get(key, nkey);
                if (it) {
                    item_unlink(it);
                    item_remove(it);
                }
            }
    
            /* swallow the data line */
            c->write_and_go = conn_swallow;
            return;
        }
    
        //设置cas,如果客户端传递的话.
        ITEM_set_cas(it, c->binary_header.request.cas);
    
        switch (c->cmd) {
            case PROTOCOL_BINARY_CMD_ADD:
                c->cmd = NREAD_ADD;
                break;
            case PROTOCOL_BINARY_CMD_SET:
                c->cmd = NREAD_SET;    //命中NREAD_SET
                break;
            case PROTOCOL_BINARY_CMD_REPLACE:
                c->cmd = NREAD_REPLACE;
                break;
            default:
                assert(0);
        }
    
        //判断当前的item否已经有cas了,如果有则 cmd = NREAD_CAS
        //在处理value的时候会进行cas判断,判断当前item->data->cas
        //是否等于之前的item->data->cas,这也是memcache在处理并发操作
        //同一个item的解决方案.
        if (ITEM_get_cas(it) != 0) {
            c->cmd = NREAD_CAS;
        }
    
        c->item = it;
        //c->ritem改成指向item数据区,上面在状态 conn_nread 里面也有提到这个点.
        c->ritem = ITEM_data(it);
        //待读取value的总长度
        c->rlbytes = vlen;
        //更新当前连接处理下一步状态为 conn_nread
        conn_set_state(c, conn_nread);
        //处理数据下一步状态为 bin_read_set_value
        c->substate = bin_read_set_value;
    }

> 计算过期时间，realtime 函数

    static rel_time_t realtime(const time_t exptime) {
        /* no. of seconds in 30 days - largest possible delta exptime */
    
        if (exptime == 0) return 0; /* 0 means never expire */
    
        //如果想保存30天以上则需要传递绝对时间戳，如果不传递绝对时间戳而是30天的秒数则会导致马上过期
        //如果不保存30天以上则直接传递过期秒数即可
    
        /** #define REALTIME_MAXDELTA 60*60*24*30 */
        if (exptime > REALTIME_MAXDELTA) {
            //如果小于服务器启动时间戳则马上过期
            if (exptime <= process_started) 
                return (rel_time_t)1;
    
            //客户端传递的绝对时间戳减去服务器的启动时间戳计算出过期时间
            return (rel_time_t)(exptime - process_started);
        } else {
            //客户端传递的过期秒数加上服务器的相对时间秒数计算出过期时间
            return (rel_time_t)(exptime + current_time);
        }
    
        //举个例子:
        // process_started : 服务器启动时间戳 1477929600
        // current_time ：服务器相对时间,就是相对于启动到现在共过了多少秒,每秒+1.
    
        //（1） 保存30天以下的过期时间计算:
        //      current_time = 10000  服务器当前相对时间
        //      exptime = 3600        客户端要求3600秒之后过期
        //      过期时间 = current_time +　exptime = 13600 
    
        //（2） 保存30天以上的过期时间计算:
        //      current_time = 10000  服务器当前相对时间
        //      process_started = 1477929600 服务器启动时间
        //      当前服务器的绝对时间 = process_started + current_time  启动时间 + 相对时间
        //      当前客户端的绝对时间 === 当前服务器的绝对时间
    
        //      exptime = time() + 2678400 = 1480618000  客户端按当前的绝对时间向后加上30天
    
        //      过期时间 = exptime - process_started = 2688400
        //               = (process_started + current_time + 2678400) - process_started
        //               = current_time + 2678400    
    
        //      可以看出来实际上还是相对于 current_time 加上了 2678400 秒,所以就是通过绝对时间计算出相对时间
        //      但是这种情况要保证客户端的时间和服务器的时间一致,不然的话会导致计算错误情况。
    
    
        //一般采用相对时间比较准,因为永远都是相对于服务器当前时间往后加sec
    
    }

> item_alloc 函数

    item *item_alloc(char *key, size_t nkey, int flags, rel_time_t exptime, int nbytes) {
        item *it;
        /* do_item_alloc handles its own locks */
        it = do_item_alloc(key, nkey, flags, exptime, nbytes, 0);
        return it;
    }

> do_item_alloc 函数

    item *do_item_alloc(char *key, const size_t nkey, const int flags,
                        const rel_time_t exptime, const int nbytes,
                        const uint32_t cur_hv) {
        int i;
        uint8_t nsuffix;
        item *it = NULL;
        char suffix[40];
        unsigned int total_chunks;
    
        //计算需要申请内存总大小
        size_t ntotal = item_make_header(nkey + 1, flags, nbytes, suffix, &nsuffix);
        //判断是否开启cas如果开启cas那么就在ntotal基础上再加上
        //存放cas信息的大小,一般是8/Bytes
        if (settings.use_cas) {
            ntotal += sizeof(uint64_t);
        }
    
        //根据要申请的内存块大小找到对应的区域id,之前也说过memcache内存是按不同大小
        //的区域进行划分的.
        unsigned int id = slabs_clsid(ntotal);
        if (id == 0)
            return 0;
    
        //循环申请内存块,如果申请失败,继续循环申请最多5次
        //如果在id对应的区域没有可用的内存块item，会去LRU
        //队列逐出一个.
        for (i = 0; i < 5; i++) {
            /* Try to reclaim memory first */
            if (!settings.lru_maintainer_thread) {
                lru_pull_tail(id, COLD_LRU, 0, false, cur_hv);
            }
            //去id指定的区域获取一个空闲的item内存块
            it = slabs_alloc(ntotal, id, &total_chunks, 0);
            if (settings.expirezero_does_not_evict)
                total_chunks -= noexp_lru_size(id);
            //如果等于NULL代表没有空闲的item则去LRU队列逐出一个
            //这里的 LRU 队列分为两种情况
    
            //第一种情况开启 lru_maintainer_thread 线程
            //会维护4个队列:
            /*  id  => NOEXP_LRU 永不淘汰LRU队列,过期时间等于0会放在该队列
                id  => HOT_LRU   新添加数据LRU队列,如果大于指定的数量则挪到COLD_LRU队列
                id  => WARM_LRU  冷数据变为热数据LRU队列,如果大于指定的数量则挪到COLD_LRU队列
                id  => COLD_LRU  冷数据LRU队列,如get访问的是此队列中最后一个元素，在lru线程
                                 维护此队列的时候会挪到WARM_LRU队列中,因为是最后一个元素,不
                                 挪走的话,有可能最先被逐出 
                                 而 HOT_LRU 和 WARM_LRU 队列中如果访问的是最后一个元素
                                 则会被挪到各自的队列头*/
    
            //可以看到每个区域id都对应一条LRU队列 ,那么这个LRU队列也就代表只保存该区域id的item指针.
            //HOT_LRU|WARM_LRU : 队列逐出的时候会去队列尾部看看是否有过期的数据如果有则淘汰,没有则不淘汰
            //COLD_LRU : 队列逐出的时候会去尾部看看是否有过期的数据如果有则淘汰,如果没有也会淘汰.
    
            //第二种情况不开启 lru_maintainer_thread 线程
            //id    =>  COLD_LRU  按照使用已使用item顺序先后加在该队列里面,头部最新使用的item,尾部最旧使用的item
            //COLD_LRU : 队列逐出原则,就是从尾部依次淘汰跟上面原则一样.
    
            if (it == NULL) {
                if (settings.lru_maintainer_thread) {
                    lru_pull_tail(id, HOT_LRU, total_chunks, false, cur_hv);
                    lru_pull_tail(id, WARM_LRU, total_chunks, false, cur_hv);
                    lru_pull_tail(id, COLD_LRU, total_chunks, true, cur_hv);
                } else {
                    lru_pull_tail(id, COLD_LRU, 0, true, cur_hv);
                }
            } else {
                break;
            }
        }
    
        if (i > 0) {
            pthread_mutex_lock(&lru_locks[id]);
            itemstats[id].direct_reclaims += i;
            pthread_mutex_unlock(&lru_locks[id]);
        }
    
        if (it == NULL) {
            pthread_mutex_lock(&lru_locks[id]);
            itemstats[id].outofmemory++;
            pthread_mutex_unlock(&lru_locks[id]);
            return NULL;
        }
    
        assert(it->slabs_clsid == 0);
        //assert(it != heads[id]);
    
        /* Refcount is seeded to 1 by slabs_alloc() */
        it->next = it->prev = it->h_next = 0;
    
        //判断当前获取item加在那条LRU队列上
        if (settings.lru_maintainer_thread) {
            //这里可以看到如果过期时间设置为0则放到永不过期队列
            if (exptime == 0 && settings.expirezero_does_not_evict) {
                id |= NOEXP_LRU;
            } else {
                id |= HOT_LRU; //默认放到最新的队列
            }
        } else {
            /* There is only COLD in compat-mode */
            id |= COLD_LRU;
        }
        //保存一下id，这个id是对应LRU队列的索引位置.
        it->slabs_clsid = id;
    
        DEBUG_REFCNT(it, '*');
        it->it_flags = settings.use_cas ? ITEM_CAS : 0;
        it->nkey = nkey;
        it->nbytes = nbytes;
        //把key copy到item->data里面
        memcpy(ITEM_key(it), key, nkey);
        /*
        #define ITEM_key(item) (((char*)&((item)->data)) \
                + (((item)->it_flags & ITEM_CAS) ? sizeof(uint64_t) : 0))
        */
        //保存下过期时间
        it->exptime = exptime;
        //后缀copy到item->data里面,这个后缀suffix就是 flags + vlen 的字符串
        memcpy(ITEM_suffix(it), suffix, (size_t)nsuffix);
        //后缀长度
        it->nsuffix = nsuffix;
        return it;
    }

> 计算需要申请内存大小 ，item_make_header 函数

    static size_t item_make_header(const uint8_t nkey, const int flags, const int nbytes,
             char *suffix, uint8_t *nsuffix) {
            //后缀大小计算
            *nsuffix = (uint8_t) snprintf(suffix, 40, " %d %d\r\n", flags, nbytes - 2);
            //这里看到每次申请内存大小并不是按照key+value,还会有一些额外的信息
            return sizeof(item) + nkey + *nsuffix + nbytes;
    }

> 根据要申请的内存大小定位对应的存放区域 id， slabs_clsid 函数

    unsigned int slabs_clsid(const size_t size) {
        int res = POWER_SMALLEST;
    
        if (size == 0)
            return 0;
        //如果size不大于当前区域,就代表当前区域可以存放size大小
        //返回区域id
        while (size > slabclass[res].size)
            if (res++ == power_largest)     /* won't fit in the biggest slab */
                return 0;
        return res;
    }

> slabs_alloc 函数

    void *slabs_alloc(size_t size, unsigned int id, unsigned int *total_chunks,
            unsigned int flags) {
        void *ret;
        //这里可以看到每次要去指定的slabclass[id]区域获取item都会
        //锁住整个slabclass,理论上应该去哪个区域获取item就锁住哪个
        //区域应该类似于采用(行锁)不应该采用(表锁),操作哪个id就
        //锁住哪个id
        pthread_mutex_lock(&slabs_lock);
        ret = do_slabs_alloc(size, id, total_chunks, flags);
        pthread_mutex_unlock(&slabs_lock);
        return ret;
    }

> do_slabs_alloc 函数

    static void *do_slabs_alloc(const size_t size, unsigned int id, unsigned int *total_chunks,
            unsigned int flags) {
        slabclass_t *p;
        void *ret = NULL;
        item *it = NULL;
    
        if (id < POWER_SMALLEST || id > power_largest) {
            MEMCACHED_SLABS_ALLOCATE_FAILED(size, 0);
            return NULL;
        }
        //通过id获取slabclass指向区域指针
        p = &slabclass[id];
        assert(p->sl_curr == 0 || ((item *)p->slots)->slabs_clsid == 0);
    
        //获取目前区域最大存放item量
        if (total_chunks != NULL) {
            *total_chunks = p->slabs * p->perslab;
        }
    
        //判断当前区域是否还有剩余的item,如果没有那么就在去申请1M内存
        //同时再划分若干item，之前内存模型有说明.
        if (p->sl_curr == 0 && flags != SLABS_ALLOC_NO_NEWPAGE) {
            do_slabs_newslab(id);
        }
    
        if (p->sl_curr != 0) {
            /* 获取一个item */
            it = (item *)p->slots;
            //更新剩余的item
            p->slots = it->next;
            if (it->next) it->next->prev = 0;
            /* Kill flag and initialize refcount here for lock safety in slab
             * mover's freeness detection. */
            it->it_flags &= ~ITEM_SLABBED;
    
            //默认item引用是1,之后只要有新的请求操作该item都会加1,处理完之后在减1
            //如果等于0则系统会销毁该item
    
            //之所以用这个引用有一个好处就是延时删除item,比如有一个A请求正在操作该item
            //这个时候一个B请求发送过来删除该item命令,如果在A请求没处理完成之前被B请求删除了
            //那么A请求在操作这个item就是非法操作内存了,会造成一些问题,但是如果有refcount引用
            //就不会有问题了, A请求 refcount+1 = 2 B请求 refcount-1 = 1 不等于0,所以暂时先不删除
            //等A请求处理完毕之后再解除引用 refcount-1 = 0 整好等于0 删除之.
            it->refcount = 1;
            //空闲item数量更新
            p->sl_curr--;
            ret = (void *)it;
        } else {
            ret = NULL;
        }
    
        if (ret) {
            p->requested += size;
            MEMCACHED_SLABS_ALLOCATE(size, id, p->size, ret);
        } else {
            MEMCACHED_SLABS_ALLOCATE_FAILED(size, id);
        }
    
        return ret;
    }

> 经过上述步骤处理, 已经根据发送过来的协议包, 申请完item内存块了, 并做了一些处理, 剩下的部分就是  
> (1) buf缓冲区或者网络缓冲区里面的value部分复制到item数据区  
> (2) item地址通过当前key添加到hash表里  
> (3) item加入LRU队列,就是一个链表记录已使用的item

上面 process_bin_update（） 函数处理完 key , 会把连接状态又更新成 conn_nread 然后还是调用 complete_nread() -> complete_nread_binary() -> case:bin_read_set_value(状态) => complete_update_bin()> complete_update_bin 函数

    static void complete_update_bin(conn *c) {
        protocol_binary_response_status eno = PROTOCOL_BINARY_RESPONSE_EINVAL;
        enum store_item_type ret = NOT_STORED;
        assert(c != NULL);
    
        //c->item 就是我们申请item地址
        item *it = c->item;
    
        pthread_mutex_lock(&c->thread->stats.mutex);
        c->thread->stats.slab_stats[ITEM_clsid(it)].set_cmds++;
        pthread_mutex_unlock(&c->thread->stats.mutex);
    
        //注意: value在刚才调用 conn_nread 状态里面的时候已经读取到 ITEM_data(it) 里面了
        //memmove(c->ritem, c->rcurr, tocopy);
    
        //给item->data里面的 value 部分结尾加上\r\n
        //在上面item_alloc()函数参数vlen+2已经预留出来2个字节空间了
        *(ITEM_data(it) + it->nbytes - 2) = '\r';
        *(ITEM_data(it) + it->nbytes - 1) = '\n';
    
    
        //处理 value
        ret = store_item(it, c->cmd, c);
        switch (ret) {
        case STORED:
            /* Stored */
            write_bin_response(c, NULL, 0, 0, 0);
            break;
        case EXISTS:
            write_bin_error(c, PROTOCOL_BINARY_RESPONSE_KEY_EEXISTS, NULL, 0);
            break;
        case NOT_FOUND:
            write_bin_error(c, PROTOCOL_BINARY_RESPONSE_KEY_ENOENT, NULL, 0);
            break;
        case NOT_STORED:
            if (c->cmd == NREAD_ADD) {
                eno = PROTOCOL_BINARY_RESPONSE_KEY_EEXISTS;
            } else if(c->cmd == NREAD_REPLACE) {
                eno = PROTOCOL_BINARY_RESPONSE_KEY_ENOENT;
            } else {
                eno = PROTOCOL_BINARY_RESPONSE_NOT_STORED;
            }
            write_bin_error(c, eno, NULL, 0);
        }
        //解除item引用
        //因为无论是get还是set都会对 item->refcount++，这里会在 item->refcount--
        item_remove(c->item);       /* release the c->item reference */
        c->item = 0;

#### 说明

memcache 在每次 set 的时候都会新获取一个item，然后再把原来的item删除，不会直接在原来的基础上修改，因为有可能这次发送的数据size要比第一次的大，如果直接在原来的基础上肯定装不下，所以每次都根据数据包size重新选择一块item。

> store_item 函数

    enum store_item_type store_item(item *item, int comm, conn* c) {
        enum store_item_type ret;
        uint32_t hv;
        //获取key对应的hash值
        hv = hash(ITEM_key(item), item->nkey);
    
        //只要有对key操作的地方都要加锁，防止多个请求同时操作一个key
    
        //加锁
        item_lock(hv);
        ret = do_store_item(item, comm, c, hv);
        //解锁
        item_unlock(hv);
    
        return ret;
    }

> do_store_item 函数

    enum store_item_type do_store_item(item *it, int comm, conn *c, const uint32_t hv) {
        //获取key
        char *key = ITEM_key(it);
        //通过key获取原来的item 如果存在的话
        item *old_it = do_item_get(key, it->nkey, hv);
        //默认返回类型
        enum store_item_type stored = NOT_STORED;
    
        item *new_it = NULL;
        int flags;
    
        //这个是add命令类型,可以看到如果是add命令，则不会更新到新的item
        //只是更新一下原来item的时间而已
        if (old_it != NULL && comm == NREAD_ADD) {
            /* add only adds a nonexistent item, but promote to head of LRU */
            do_item_update(old_it);
        } else if (!old_it && (comm == NREAD_REPLACE
            || comm == NREAD_APPEND || comm == NREAD_PREPEND))
        {
            /* replace only replaces an existing value; don't store */
    
        //这个是CAS如果开启了cas则会走这步代码
        } else if (comm == NREAD_CAS) {
            //如果old_it等于null，则不需要cas对比
            if(old_it == NULL) {
                // LRU expired
                stored = NOT_FOUND;
                pthread_mutex_lock(&c->thread->stats.mutex);
                c->thread->stats.cas_misses++;
                pthread_mutex_unlock(&c->thread->stats.mutex);
            }
            //判断当前item的cas是否等于之前的cas，为了保证数据的一致性
            else if (ITEM_get_cas(it) == ITEM_get_cas(old_it)) {
                // cas validates
                // it and old_it may belong to different classes.
                // I'm updating the stats for the one that's getting pushed out
                pthread_mutex_lock(&c->thread->stats.mutex);
                c->thread->stats.slab_stats[ITEM_clsid(old_it)].cas_hits++;
                pthread_mutex_unlock(&c->thread->stats.mutex);
                //如果cas对比一样则更新到新的item
                item_replace(old_it, it, hv);
                stored = STORED;
            } else {
                //如果cas对比不一样则返回错误
                pthread_mutex_lock(&c->thread->stats.mutex);
                c->thread->stats.slab_stats[ITEM_clsid(old_it)].cas_badval++;
                pthread_mutex_unlock(&c->thread->stats.mutex);
    
                if(settings.verbose > 1) {
                    fprintf(stderr, "CAS:  failure: expected %llu, got %llu\n",
                            (unsigned long long)ITEM_get_cas(old_it),
                            (unsigned long long)ITEM_get_cas(it));
                }
                stored = EXISTS;
            }
        } else {
            /*
             * Append - combine new and old record into single one. Here it's
             * atomic and thread-safe.
             */
            if (comm == NREAD_APPEND || comm == NREAD_PREPEND) {
                /*
                 * Validate CAS
                 */
                if (ITEM_get_cas(it) != 0) {
                    // CAS much be equal
                    if (ITEM_get_cas(it) != ITEM_get_cas(old_it)) {
                        stored = EXISTS;
                    }
                }
    
                if (stored == NOT_STORED) {
                    /* we have it and old_it here - alloc memory to hold both */
                    /* flags was already lost - so recover them from ITEM_suffix(it) */
    
                    flags = (int) strtol(ITEM_suffix(old_it), (char **) NULL, 10);
    
                    new_it = do_item_alloc(key, it->nkey, flags, old_it->exptime, it->nbytes + old_it->nbytes - 2 /* CRLF */, hv);
    
                    if (new_it == NULL) {
                        /* SERVER_ERROR out of memory */
                        if (old_it != NULL)
                            do_item_remove(old_it);
    
                        return NOT_STORED;
                    }
    
                    /* copy data from it and old_it to new_it */
    
                    if (comm == NREAD_APPEND) {
                        memcpy(ITEM_data(new_it), ITEM_data(old_it), old_it->nbytes);
                        memcpy(ITEM_data(new_it) + old_it->nbytes - 2 /* CRLF */, ITEM_data(it), it->nbytes);
                    } else {
                        /* NREAD_PREPEND */
                        memcpy(ITEM_data(new_it), ITEM_data(it), it->nbytes);
                        memcpy(ITEM_data(new_it) + it->nbytes - 2 /* CRLF */, ITEM_data(old_it), old_it->nbytes);
                    }
    
                    it = new_it;
                }
            }
    
            //如果是Set命令,并且没有开启cas则会走下面这块代码
            if (stored == NOT_STORED) {
                if (old_it != NULL)
                    item_replace(old_it, it, hv);
                else
                    //执行
                    do_item_link(it, hv);
    
                c->cas = ITEM_get_cas(it);
    
                stored = STORED;
            }
        }
    
        if (old_it != NULL)
            do_item_remove(old_it);         /* release our reference */
        if (new_it != NULL)
            do_item_remove(new_it);
    
        if (stored == STORED) {
            c->cas = ITEM_get_cas(it);
        }
    
        return stored;
    }

> do_item_link 函数

    int do_item_link(item *it, const uint32_t hv) {
        MEMCACHED_ITEM_LINK(ITEM_key(it), it->nkey, it->nbytes);
        assert((it->it_flags & (ITEM_LINKED|ITEM_SLABBED)) == 0);
        it->it_flags |= ITEM_LINKED;
        //it->time 记录添加时间
        it->time = current_time;
    
        //统计更新
        STATS_LOCK();
        stats.curr_bytes += ITEM_ntotal(it);
        stats.curr_items += 1;
        stats.total_items += 1;
        STATS_UNLOCK();
    
        /* 如果开启cas则设置一个新的cas版本号 */
        ITEM_set_cas(it, (settings.use_cas) ? get_cas_id() : 0);
        //添加到hash表
        assoc_insert(it, hv);
        //添加到LRU队列
        item_link_q(it);
    
        //为什了这里要引用+1   
    
        //(一)
        //因为我们这个item是新的,不是通过 item_get() hash表获取的,所以没有默认+1
        //如果是通过item_get() hash表取出来的都会默认+1,不需要像下面还手动+1
    
        //(二)
        //因为memcache在每个请求结束之后都会把当前的item引用-1,如果等于0则会被free掉.
        //这里为了保证不被free掉则引用+1
    
        //(三) 
        //或者我们这里把这个key刚添加到hash，就被另一个线程给del了，但是我们本次set操作
        //还没做完，这就可能导致本次非法操作这个item，所以这里把引用+1，保证我们这个item不会
        //被马上del,也是做一个延时删除的作用大概.
    
        refcount_incr(&it->refcount);
    
        return 1;
    }

### 结束

以上大概就是一个 Set 命令在 Memcache 内部所经历的流程，同理别的命令也可以按照上述流程去追踪一下源码，但以上介绍可能有理解错误或不准确的地方，如有发现请及时纠正.

[0]: /u/9642a0c8db39
[1]: ../img/2416964-6fab1585488960e3.jpg
[2]: http://www.jianshu.com/p/a824ae00d9bb
[3]: http://www.jianshu.com/p/16295a1f1cd2
[4]: http://www.jianshu.com/p/fcf92469ca52
[5]: ../img/2416964-218600ea96eb9f14.png