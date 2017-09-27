# Redis源码剖析--AOF持久化

 时间 2017-01-01 22:36:24  ZeeCoder

原文[http://zcheng.ren/2017/01/02/TheAnnotatedRedisSourceAof/][1]


在前一篇博客 [Redis源码剖析–RDB持久化][4] 中，我们分析了RDB持久化就是按照特定的格式将服务器中数据库里面的数据写入到RDB文件中，在服务器下一次开启的时候，再按照该格式读取上来，从而保证了数据的持久化。今天，我们来看看另一种持久化操作—-AOF持久化。 

## AOF概述 

AOF，其英文全称是Append Only File，就是只进行追加操作的文件。那么写进AOF文件中的到底是键值对数据还是什么呢？我先从客户端命令测试一下，Redis提供了 BGREWRITEAOF 命令用于执行AOF操作，接下来开启服务器和客户端，写几个数据进去看看效果。 

```
    ~ redis-cli 
    127.0.0.1:6379> set hello world
    OK
    127.0.0.1:6379> BGREWRITEAOF 
    Background append only file rewriting started
```

客户端执行完上述的命令之后，生成了一个 appendonly.aof 文件，我们用od命令打开。 

```
    od -c appendonly.aof
    0000000    *   2  \r  \n   $   6  \r  \n   S   E   L   E   C   T  \r  \n
    0000020    $   1  \r  \n   0  \r  \n   *   3  \r  \n   $   3  \r  \n   S
    0000040    E   T  \r  \n   $   5  \r  \n   h   e   l   l   o  \r  \n   $
    0000060    5  \r  \n   w   o   r   l   d  \r  \n
```

读取出来的文件看起来可能不那么直观，大致有 SELECT 、 SET 、 hello 和 world 这几个词，这么一来，感觉AOF文件中存放的是客户端操作数据的命令。没错，AOF文件中的确存放的是客户端对数据库执行了写操作命令，因为读操作不会修改到数据库，存下来也没有什么意义。 

有了上述简单的认识之后，我们在来一一分析一下AOF文件中的这些信息到底是什么。这次我们直接用文本编辑器打开，就能很直观的看到了。

```
    *2      // *代表一个操作命令，2代表这个命令由两个参数
    $6      // $代表后续命令或者数据的长度
    SELECT  // 操作名称，长度为6
    $1      // 长度
    0       // 表示切换到0号数据库
    *3      // 接下来的命令由三个参数
    $3      // 长度为3
    SET     // SET操作
    $5      // 长度为5
    hello   // 键
    $5      // 长度为5
    world   // 值
```

到这一步，大家基本上都能懂AOF持久化到底是存放什么了吧？接下来，我们深入到源码中去了解一下AOF持久化的具体执行过程。

## AOF写入步骤 

AOF持久化功能的实现可以分为以下三个步骤：命令追加、文件写入和文件同步。每一步主要完成以下功能：

* 命令追加：将命令数据写入aof_buf缓冲区
* 文件写入：将aof_buf缓冲区的数据写入系统IO缓冲区
* 文件同步：将系统IO缓冲区的数据同步到磁盘文件

## 命令追加 

在 redis.conf 文件中，有一个参数 appendonly 是用来控制服务器是否开启AOF持久化功能的。当AOF持久化处于开启状态时，服务器每执行一个写命令之后，都会按照协议格式将被执行的写命令追加到服务器状态的 aof_buf 缓冲区的末尾。其中 aof_buf 定义如下： 

```c
    /* 在server.h文件中的redisServer结构体中 */
    struct redisServer { 
      // ...
      sds aof_buf;  // aof缓冲区
      // ....
    }
```

例如，在上例中，我们运行 SET hello world 命令的时候，其就往这个缓冲区中写入一下数据： 

```
    *3\r\n$3\r\nSET\r\n$5\r\nhello\r\n$5\r\nworld\r\n
```

命令追加的函数由 feedAppendOnlyFile 函数执行，其源码如下： 

```c
    /* 追加命令到aof_buf缓冲区 */
    void feedAppendOnlyFile(struct redisCommand *cmd,int dictid, robj **argv,int argc){
        sds buf = sdsempty();
        robj *tmpargv[3];
    
        // 确保切换到了正确的数据库，如果没有则追加切换数据库命令
        if (dictid != server.aof_selected_db) {
            char seldb[64];
    
            snprintf(seldb,sizeof(seldb),"%d",dictid);
            // 格式化命令数据
            buf = sdscatprintf(buf,"*2\r\n$6\r\nSELECT\r\n$%lu\r\n%s\r\n",
                (unsigned long)strlen(seldb),seldb);
            // 保存当前aof_buf执行的数据库
            server.aof_selected_db = dictid;
        }
    
        if (cmd->proc == expireCommand || cmd->proc == pexpireCommand ||
            cmd->proc == expireatCommand) {
            // 追加过期键的命令
            buf = catAppendOnlyExpireAtCommand(buf,cmd,argv[1],argv[2]);
        } else if (cmd->proc == setexCommand || cmd->proc == psetexCommand) {
            // 追加setex或psetex这样带有过期时间设置的命令
            tmpargv[0] = createStringObject("SET",3);
            tmpargv[1] = argv[1];
            tmpargv[2] = argv[3];
            buf = catAppendOnlyGenericCommand(buf,3,tmpargv);
            decrRefCount(tmpargv[0]);
            buf = catAppendOnlyExpireAtCommand(buf,cmd,argv[1],argv[2]);
        } else {
            // 追加其他一般的修改数据库操作的命令
            buf = catAppendOnlyGenericCommand(buf,argc,argv);
        }
    
        // 将格式化的命令字符串追加到AOF缓冲区中，AOF缓冲区中的数据会在重新进入时间循环前写入到磁盘中，相应的客户端也会受到关于此次操作的回复
        if (server.aof_state == AOF_ON)
            server.aof_buf = sdscatlen(server.aof_buf,buf,sdslen(buf));
    
        if (server.aof_child_pid != -1)
            // 如果正在执行后台重写aof文件，将命令追加到新的AOF文件中，避免重写的AOF文件与当前数据库有差异
            aofRewriteBufferAppend((unsigned char*)buf,sdslen(buf));
        sdsfree(buf);
    }
```

追加格式化命令的底层实现由 catAppendOnlyGenericCommand 函数实现，其按照之前概述中的格式化要求将命令写入缓冲区。 

```c
    /* 通用格式化命令并写入到AOF缓冲区函数 */
    sds catAppendOnlyGenericCommand(sds dst,int argc, robj **argv){
        char buf[32];
        int len, j;
        robj *o;
        // 初始化命令参数个数
        buf[0] = '*';
        len = 1+ll2string(buf+1,sizeof(buf)-1,argc);
        buf[len++] = '\r';
        buf[len++] = '\n';
        dst = sdscatlen(dst,buf,len);
        // 遍历每一个参数，并追加到aof缓冲区中
        for (j = 0; j < argc; j++) {
            o = getDecodedObject(argv[j]);
            buf[0] = '$';
            len = 1+ll2string(buf+1,sizeof(buf)-1,sdslen(o->ptr));
            buf[len++] = '\r';
            buf[len++] = '\n';
            dst = sdscatlen(dst,buf,len);
            dst = sdscatlen(dst,o->ptr,sdslen(o->ptr));
            dst = sdscatlen(dst,"\r\n",2);
            decrRefCount(o);
        }
        return dst;
    }
```

## 文件写入 

Redis将格式化的命令存入缓冲区之后，等待服务器的指示，然后将这些命令写入到AOF文件中，写入文件的操作由 flushAppendOnlyFile 函数完成，这里简要的列出其源码： 

```c
    /* 将缓冲区的数据写入AOF文件中 */
    void flushAppendOnlyFile(int force){
        ssize_t nwritten;
        int sync_in_progress = 0;
        mstime_t latency;
        // 缓冲区为空
        if (sdslen(server.aof_buf) == 0) return;
      
        // ①...
        // 此处省略了一些同步方面的代码，将在下一节分析
      
        // 调用write命令将缓冲区的数据写入磁盘文件中，此处还监听了延迟
        latencyStartMonitor(latency);
        nwritten = write(server.aof_fd,server.aof_buf,sdslen(server.aof_buf));
        latencyEndMonitor(latency);
        // 如果同步正在进行
        if (sync_in_progress) {
            latencyAddSampleIfNeeded("aof-write-pending-fsync",latency);
        } else if (server.aof_child_pid != -1 || server.rdb_child_pid != -1) {
            latencyAddSampleIfNeeded("aof-write-active-child",latency);
        } else {
            latencyAddSampleIfNeeded("aof-write-alone",latency);
        }
        latencyAddSampleIfNeeded("aof-write",latency);
    
        // 重置aof写入的延迟时间
        server.aof_flush_postponed_start = 0;
        // 写操作出现错误，需要进行修复
        if (nwritten != (signed)sdslen(server.aof_buf)) {
                    static time_t last_write_error_log = 0;
            int can_log = 0;
            // 将日志的记录频率限制在每行AOF_WRITE_LOG_ERROR_RATE 秒
            if ((server.unixtime - last_write_error_log) > AOF_WRITE_LOG_ERROR_RATE) {
                can_log = 1;
                last_write_error_log = server.unixtime;
            }
            // 如果写入出错，那么尝试将出错情况写入日志
            if (nwritten == -1) {
                if (can_log) {
                    serverLog(LL_WARNING,"Error writing to the AOF file: %s",
                        strerror(errno));
                    server.aof_last_write_errno = errno;
                }
            } else {
                if (can_log) {
                    serverLog(LL_WARNING,"Short write while writing to "
                                           "the AOF file: (nwritten=%lld, "
                                           "expected=%lld)",
                                           (long long)nwritten,
                                           (long long)sdslen(server.aof_buf));
                }
                // 尝试移除新追加的不完整内容
                if (ftruncate(server.aof_fd, server.aof_current_size) == -1) {
                    if (can_log) {
                        serverLog(LL_WARNING, "Could not remove short write "
                                 "from the append-only file. Redis may refuse "
                                 "to load the AOF the next time it starts. "
                                 "ftruncate: %s", strerror(errno));
                    }
                } else {
                    /* If the ftruncate() succeeded we can set nwritten to
                     * -1 since there is no longer partial data into the AOF. */
                    nwritten = -1;
                }
                server.aof_last_write_errno = ENOSPC;
            }
    
            // 处理写入AOF文件时出现的错误
            if (server.aof_fsync == AOF_FSYNC_ALWAYS) {
                /* We can't recover when the fsync policy is ALWAYS since the
                 * reply for the client is already in the output buffers, and we
                 * have the contract with the user that on acknowledged write data
                 * is synced on disk. */
                serverLog(LL_WARNING,"Can't recover from AOF write error when the AOF fsync policy is 'always'. Exiting...");
                exit(1);
            } else {
                /* Recover from failed write leaving data into the buffer. However
                 * set an error to stop accepting writes as long as the error
                 * condition is not cleared. */
                server.aof_last_write_status = C_ERR;
    
                /* Trim the sds buffer if there was a partial write, and there
                 * was no way to undo it with ftruncate(2). */
                if (nwritten > 0) {
                    server.aof_current_size += nwritten;
                    sdsrange(server.aof_buf,nwritten,-1);
                }
                return; /* We'll try again on the next call... */
            }
        } else {
            // 写入成功，更新最后写入状态
            if (server.aof_last_write_status == C_ERR) {
                serverLog(LL_WARNING,
                    "AOF write error looks solved, Redis can write again.");
                server.aof_last_write_status = C_OK;
            }
        }
        // 更新aof文件的当前大小
        server.aof_current_size += nwritten;
    
        // 当缓冲区使用量很小时，可以考虑重用缓冲区
        if ((sdslen(server.aof_buf)+sdsavail(server.aof_buf)) < 4000) {
            sdsclear(server.aof_buf);
        } else {
            sdsfree(server.aof_buf);
            server.aof_buf = sdsempty();
        }
    
        // ②...
        // 后续为同步方面的代码，我们下一小节来分析
    }
```

## 文件同步 

上述文件写入操作采用write函数来执行，但是在操作系统中，当用户将数据写入一个文件中时，为了提高效率，操作系统往往会将待写入的数据存放在一个缓冲区，等到缓冲区满或者超过规定的时间后才真正将缓冲区的内容写入到磁盘文件中。用户可以调用 fsync() 函数强制让操作系统将缓冲区的数据写入到磁盘文件中，这就称为文件同步。Redis对AOF文件的同步提供了一下三种策略。 

* AOF_FSYNC_ALWAYS 每次事件循环都要将aof_buf缓冲区的所有内容都写入AOF文件，并且同步AOF文件
* AOF_FSYNC_EVERSEC 每个事件循环都要将aof_buf缓冲区的所有内容都写入AOF文件，并且每隔1秒就要在子进程中对AOF文件进行一次同步
* AOF_FSYNC_NO 每个事件循环都要将aof_buf缓冲区的所有内容都写入AOF文件，至于何时对AOF文件进行同步，则由操作系统控制。

```c
    /* 将缓冲区的数据写入AOF文件中 */
    void flushAppendOnlyFile(int force){
        // ...文件写入中已分析
        // 从标记为①开始
        if (server.aof_fsync == AOF_FSYNC_EVERYSEC)
            sync_in_progress = bioPendingJobsOfType(BIO_AOF_FSYNC) != 0;
    
        if (server.aof_fsync == AOF_FSYNC_EVERYSEC && !force) {
            /* With this append fsync policy we do background fsyncing.
             * If the fsync is still in progress we can try to delay
             * the write for a couple of seconds. */
            if (sync_in_progress) {
                if (server.aof_flush_postponed_start == 0) {
                    /* No previous write postponing, remember that we are
                     * postponing the flush and return. */
                    server.aof_flush_postponed_start = server.unixtime;
                    return;
                } else if (server.unixtime - server.aof_flush_postponed_start < 2) {
                    /* We were already waiting for fsync to finish, but for less
                     * than two seconds this is still ok. Postpone again. */
                    return;
                }
                /* Otherwise fall trough, and go write since we can't wait
                 * over two seconds. */
                server.aof_delayed_fsync++;
                serverLog(LL_NOTICE,"Asynchronous AOF fsync is taking too long (disk is busy?). Writing the AOF buffer without waiting for fsync to complete, this may slow down Redis.");
            }
        }
        // ....文件写入中已分析
      
        // 从标记为②处开始
        // 根据同步设定执行文件同步
        // 如果设定为NO，即不主动同步，由操作系统决定
        if (server.aof_no_fsync_on_rewrite &&
            (server.aof_child_pid != -1 || server.rdb_child_pid != -1))
                return;
        if (server.aof_fsync == AOF_FSYNC_ALWAYS) {
            // 如果设定为ALWAYS，则每次时间循环都要同步
            latencyStartMonitor(latency);
            aof_fsync(server.aof_fd); // 执行同步，写入到磁盘文件中
            latencyEndMonitor(latency);
            latencyAddSampleIfNeeded("aof-fsync-always",latency);
            server.aof_last_fsync = server.unixtime;
        } else if ((server.aof_fsync == AOF_FSYNC_EVERYSEC &&
                    server.unixtime > server.aof_last_fsync)) {
            // 如果设定为EVERYSEC，每秒执行一次同步，则判断时间然后执行同步
            if (!sync_in_progress) aof_background_fsync(server.aof_fd);
            server.aof_last_fsync = server.unixtime;  // 更新上一次同步时间
        }
    }
```

从源码中，可以看出根据设定的同步策略，Redis都做了相应的处理， aof_fsync 函数用来强制将缓冲区的数据写入到磁盘文件。分析这三种同步方式，其中， 

* ALWAYS 是最安全的，最多只会丢失一个事件循环的数据，但是其效率最低，缓冲区有一个数据都要同步，同步次数大大增多；
* EVERYSEC 最多丢失一秒钟的命令数据，其效率也足够高，属于折中方案；
* NO 的话，写入速度也是最快的，但累计起来的命令数据最多，单词同步时间较长，每次最多丢失上一次同步成功到现在的命令数据。

## AOF数据载入 

当数据存储在AOF文件中后，服务器在下一次重启需要载入数据，AOF数据载入比较有意思，其会开一个伪Redis客户端，然后模仿客户端对服务器执行命令的过程，将AOF中存储的命令一一执行，执行完毕后服务器数据库中的数据就和上次一样了。这里我简要的用伪码来表示一下整个过程。

```c
    # AOF数据载入 
    def loadAppendOnlyFile(char *filename):
      fakeClient = createRedisCli() # 创建伪客户端
         while True:
      command = getFromAof()  # 从AOF文件中取出命令
      flag = fakeClient(command) # 伪客户端执行命令
      if finish():  # 如果命令执行完，就退出
         return E_OK
      # 没有就继续执行
```

## AOF重写 

现在我们来分析一种情况，假设服务器执行了以下两个命令：

```
    SET key value
    DEL key
```

如果上述两个命令都执行成功了，AOF中必然会增加两条命令字符串，然而这对数据库根本没什么影响，如果服务器执行了大量这样的命令对，AOF是只能追加不能删除的，所以其文件体积会无限增大。考虑周全的Redis为客户提供了重写操作，用来重写AOF文件，剔除掉里面的无效命令对。

要执行AOF重写，最简单的步骤就是：遍历服务器每一个数据库中的数据，将每一个key对应的对象，都用一条命令来表达，并存储在AOF文件中。

有了这个思路，我们就去源码中看看，AOF重写的功能由 rewriteAppendOnlyFile 函数实现。 

```c
    /* AOF重写功能实现 */
    int rewriteAppendOnlyFile(char *filename){
        dictIterator *di = NULL;
        dictEntry *de;
        rio aof;
        FILE *fp;
        char tmpfile[256];
        int j;
        long long now = mstime();
        char byte;
        size_t processed = 0;
    
        /* Note that we have to use a different temp name here compared to the
         * one used by rewriteAppendOnlyFileBackground() function. */
        // 创建临时文件
        snprintf(tmpfile,256,"temp-rewriteaof-%d.aof", (int) getpid());
        fp = fopen(tmpfile,"w");
        if (!fp) {
            serverLog(LL_WARNING, "Opening the temp file for AOF rewrite in rewriteAppendOnlyFile(): %s", strerror(errno));
            return C_ERR;
        }
    
        server.aof_child_diff = sdsempty();
        // 获取文件描述符
        rioInitWithFile(&aof,fp);
        if (server.aof_rewrite_incremental_fsync)
            rioSetAutoSync(&aof,AOF_AUTOSYNC_BYTES);
        // 遍历服务器的每一个数据库
        for (j = 0; j < server.dbnum; j++) {
            char selectcmd[] = "*2\r\n$6\r\nSELECT\r\n";
            redisDb *db = server.db+j;
            dict *d = db->dict;
            if (dictSize(d) == 0) continue;
            di = dictGetSafeIterator(d);
            if (!di) {
                fclose(fp);
                return C_ERR;
            }
    
            // 写入SELECT语句
            if (rioWrite(&aof,selectcmd,sizeof(selectcmd)-1) == 0) goto werr;
            if (rioWriteBulkLongLong(&aof,j) == 0) goto werr;
    
            // 迭代当前数据库中的每一个键，并转换成命令字符串写入AOF文件
            while((de = dictNext(di)) != NULL) {
                sds keystr;
                robj key, *o;
                long long expiretime;
    
                keystr = dictGetKey(de);
                o = dictGetVal(de);
                initStaticStringObject(key,keystr);
    
                expiretime = getExpire(db,&key);
    
                // 如果此键过期，就跳过
                if (expiretime != -1 && expiretime < now) continue;
    
                // 保存该键以及对应的值
                if (o->type == OBJ_STRING) {
                    // 重写字符串对象
                    char cmd[]="*3\r\n$3\r\nSET\r\n";
                    if (rioWrite(&aof,cmd,sizeof(cmd)-1) == 0) goto werr;
                    // 写入键值
                    if (rioWriteBulkObject(&aof,&key) == 0) goto werr;
                    if (rioWriteBulkObject(&aof,o) == 0) goto werr;
                } else if (o->type == OBJ_LIST) {
                    // 重写列表对象
                    if (rewriteListObject(&aof,&key,o) == 0) goto werr;
                } else if (o->type == OBJ_SET) {
                    // 重写集合对象
                    if (rewriteSetObject(&aof,&key,o) == 0) goto werr;
                } else if (o->type == OBJ_ZSET) {
                    // 重写有序集合对象
                    if (rewriteSortedSetObject(&aof,&key,o) == 0) goto werr;
                } else if (o->type == OBJ_HASH) {
                    // 重写哈希对象
                    if (rewriteHashObject(&aof,&key,o) == 0) goto werr;
                } else {
                    serverPanic("Unknown object type");
                }
                // 保存过期时间信息
                if (expiretime != -1) {
                    char cmd[]="*3\r\n$9\r\nPEXPIREAT\r\n";
                    if (rioWrite(&aof,cmd,sizeof(cmd)-1) == 0) goto werr;
                    if (rioWriteBulkObject(&aof,&key) == 0) goto werr;
                    if (rioWriteBulkLongLong(&aof,expiretime) == 0) goto werr;
                }
                /* Read some diff from the parent process from time to time. */
                if (aof.processed_bytes > processed+1024*10) {
                    processed = aof.processed_bytes;
                    aofReadDiffFromParent();
                }
            }
            dictReleaseIterator(di);
            di = NULL;
        }
    
        /* Do an initial slow fsync here while the parent is still sending
         * data, in order to make the next final fsync faster. */
        if (fflush(fp) == EOF) goto werr;
        if (fsync(fileno(fp)) == -1) goto werr;
    
        /* Read again a few times to get more data from the parent.
         * We can't read forever (the server may receive data from clients
         * faster than it is able to send data to the child), so we try to read
         * some more data in a loop as soon as there is a good chance more data
         * will come. If it looks like we are wasting time, we abort (this
         * happens after 20 ms without new data). */
        int nodata = 0;
        mstime_t start = mstime();
        while(mstime()-start < 1000 && nodata < 20) {
            if (aeWait(server.aof_pipe_read_data_from_parent, AE_READABLE, 1) <= 0)
            {
                nodata++;
                continue;
            }
            nodata = 0; /* Start counting from zero, we stop on N *contiguous*
                           timeouts. */
            aofReadDiffFromParent();
        }
        // 以下是读取在重写AOF文件时，服务器新加入的数据
        // 告诉父进程停止增加数据
        if (write(server.aof_pipe_write_ack_to_parent,"!",1) != 1) goto werr;
        if (anetNonBlock(NULL,server.aof_pipe_read_ack_from_parent) != ANET_OK)
            goto werr;
        /* We read the ACK from the server using a 10 seconds timeout. Normally
         * it should reply ASAP, but just in case we lose its reply, we are sure
         * the child will eventually get terminated. */
        if (syncRead(server.aof_pipe_read_ack_from_parent,&byte,1,5000) != 1 ||
            byte != '!') goto werr;
        serverLog(LL_NOTICE,"Parent agreed to stop sending diffs. Finalizing AOF...");
    
        // 读取最终的差异数据
        aofReadDiffFromParent();
    
        /* Write the received diff to the file. */
        serverLog(LL_NOTICE,
            "Concatenating %.2f MB of AOF diff received from parent.",
            (double) sdslen(server.aof_child_diff) / (1024*1024));
        if (rioWrite(&aof,server.aof_child_diff,sdslen(server.aof_child_diff)) == 0)
            goto werr;
    
        // 保证系统不会残留在IO输出缓冲区
        if (fflush(fp) == EOF) goto werr;
        if (fsync(fileno(fp)) == -1) goto werr;
        if (fclose(fp) == EOF) goto werr;
    
        // 重命名AOF文件
        if (rename(tmpfile,filename) == -1) {
            serverLog(LL_WARNING,"Error moving temp append only file on the final destination: %s", strerror(errno));
            unlink(tmpfile);
            return C_ERR;
        }
        serverLog(LL_NOTICE,"SYNC append only file rewrite performed");
        return C_OK;
    // 出错处理
    werr:
        serverLog(LL_WARNING,"Write error writing append only file on disk: %s", strerror(errno));
        fclose(fp);
        unlink(tmpfile);
        if (di) dictReleaseIterator(di);
        return C_ERR;
    }
```

上述，具体到每一个键对应的值对象重写时，可能没有想象的那么简单，因为可能该值里面存放的数据较多，如果还是在一条命令中执行的话会造成缓冲区溢出。于是，Redis提供了如下参数：

```
    REDIS_AOF_REWRITE_ITEMS_PER_CMD  64
```

如果这些值对象中的数据超过64个（默认值），系统会将其拆分成多个命令来执行，即每个命令最多能操作64个元素。

## AOF后台重写 

上述的重写会阻塞服务器，如果数据量大的话，服务器会一直阻塞于此，所以和RDB一样，Redis也为AOF持久化提供了后台重写的函数。

很明显，提到后台重写就需要创建一个子进程，来执行AOF重写操作，这样就可以避免主线程被阻塞，服务器长时间无法工作。

但是，子进程在执行AOF重写的时候，服务器当前还在发生数据变化，为此，Redis提供了一个AOF后台重写缓冲区，用来存放子进程在执行AOF重写过程中插入的新数据。

```c
    #define AOF_RW_BUF_BLOCK_SIZE (1024*1024*10)// 每个块最多10M
    // 之所以规定每个块的大小是因为不知道新加入的字符串命令的个数
    typedef struct aofrwblock {
        unsigned long used, free;  // 已使用和空闲的
        char buf[AOF_RW_BUF_BLOCK_SIZE];  // 字符串命令
    } aofrwblock;
```

这样一来，在子进程执行AOF命令的时候，服务器如果有新数据到来，其字符串命令会添加到两个缓冲区，

* 一是AOF缓冲区，保证原AOF缓冲区的的内容会定期被写入和同步到现有的AOF文件中
* 二是AOF后台重写缓冲区，可以使得AOF后台重写不会错过新数据，相当于做了双重保险，命令不会丢失。

那么子进程在整个AOF后台重写命令时，会进行如下三个操作：

* 对现有数据库中的键值对转换成字符串命令，并写入和同步到临时AOF后台重写文件中
* 完成上述步骤后，将AOF后台重写缓冲区的数据存入临时AOF后台重写文件中
* 最后，执行更名操作，覆盖原有的AOF文件，完成新旧更替

AOF后台重写操作由如下两个函数完成，这里就不列出源码了。代码太多了而且都差不多。

```c
    // 后台执行AOF重写操作
    int rewriteAppendOnlyFileBackground(void);
    // 执行AOF后台重写缓冲区内数据的重写和更名操作，完成整个AOF后台重写功能
    void backgroundRewriteDoneHandler(int exitcode,int bysignal);
```

最后，只剩下最后一个问题了，除了显示运行命令执行，Redis还在什么时候执行后台重写操作，我一路追溯到这段代码：

```c
    /* 此段代码截取自server.c文件中的serverCron函数中 */
    // 如果后台没有执行rdb，aof，以及aof重写操作，而且aof文件的大于执行BGREWRITEAOF所需的最小大小
    if (server.rdb_child_pid == -1 &&
        server.aof_child_pid == -1 &&
        server.aof_rewrite_perc &&
        server.aof_current_size > server.aof_rewrite_min_size)
    {
      // 上一次完成AOF写入之后，AOF文件的大小
      long long base = server.aof_rewrite_base_size ?
            server.aof_rewrite_base_size : 1;
      // AOF文件当前的体积相对于base的体积百分比
      long long growth = (server.aof_current_size*100/base) - 100;
      // 如果增长百分比超过了规定的aof_rewrite_perc，那么执行BGREWRITEAOF
      if (growth >= server.aof_rewrite_perc) {
        serverLog(LL_NOTICE,"Starting automatic rewriting of AOF on %lld%% growth",growth);
        rewriteAppendOnlyFileBackground();
      }
    }
    
    /* 此段代码截取自server.c文件中的serverCron函数中 */
    // 接收子进程发来的信号，非阻塞
    if ((pid = wait3(&statloc,WNOHANG,NULL)) != 0) {
      int exitcode = WEXITSTATUS(statloc);
      int bysignal = 0;
    
      if (WIFSIGNALED(statloc)) bysignal = WTERMSIG(statloc);
    
      // BGSAVE 执行完毕
      if (pid == server.rdb_child_pid) {
        backgroundSaveDoneHandler(exitcode,bysignal);
    
        // BGREWRITEAOF 执行完毕
      } else if (pid == server.aof_child_pid) {
        backgroundRewriteDoneHandler(exitcode,bysignal);
    
      } else {
        redisLog(REDIS_WARNING,
                 "Warning, detected child with unmatched pid: %ld",
                 (long)pid);
      }
      updateDictResizePolicy();
    }
```

如源代码中显示的，先判断当前aof文件的大小是否大于执行BGREWRITEAOF所需的最小大小，如果大于，再判断其增长系数是否超过了规定，如超过，就执行AOF后台重写操作。并在服务器定期事件中判断AOF后台重写是否完成，如完成，将AOF后台重写缓冲区的数据写入临时AOF文件，最后覆盖原来的AOF文件，完美的完成替换操作！Over！

## AOF小结 

本篇博客分析了Redis的AOF持久化机制，源码较多，思路倒是有点混乱了，不过总算是弄清楚了AOF写入的过程，以及AOF重写，后台重写的执行步骤，Redis的各种缓存设计的真是巧妙，不得不佩服想问题这么全面。

感觉越分析到后面的代码越感觉有心无力，因为代码实在是太多太杂，耦合性比较高，要弄懂一个机制只从源码的角度出发会无从下手，所幸有网上分析Redis的人，以及黄建宏大神指路，一路上边看他们的博客和书籍，一路深入源码看看这个功能的实现步骤，最后能弄懂觉得很好了。坚持就是胜利，越到后面越是核心代码，越是整个Redis的精华所在！

各位元旦快乐，学习了一天，有点累和乏了……

欢迎转载本篇博客，不过请注明博客原地址： [http://zcheng.ren/2016/12/31/TheAnnotatedRedisSourceAof/][5]

[1]: http://zcheng.ren/2017/01/02/TheAnnotatedRedisSourceAof/
[4]: http://zcheng.ren/2016/12/30/TheAnnotatedRedisSourceRdb/
[5]: http://zcheng.ren/2016/12/31/TheAnnotatedRedisSourceAof/