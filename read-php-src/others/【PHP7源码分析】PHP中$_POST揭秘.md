## 【PHP7源码分析】PHP中$_POST揭秘

来源：[https://segmentfault.com/a/1190000016868502](https://segmentfault.com/a/1190000016868502)

运营研发团队  季伟滨
## 一、前言

前几天的工作中，需要通过curl做一次接口测试。让我意外的是，通过$_POST竟然无法获取到Content-Type是application/json的http请求的body参数。
  查了下php官网（[http://php.net/manual/zh/rese...][14]）对$_POST的描述，的确是这样。后来通过file_get_contents("php://input")获取到了原始的http请求body，然后对参数进行json_decode解决了接口测试的问题。事后，脑子里面冒出了挺多问题：


* php-fpm是怎么读取并解析FastCGI协议的？http请求的header和body分别都存储在哪里？
* 对于Content-Type是application/x-www-form-urlencoded的请求，为什么通过$_POST可以拿到解析后的参数数组？
* 对于Content-Type是application/json的请求，为什么通过$_POST拿不到解析后的参数数组？


基于这几个问题，对php代码进行了一次新的学习， 有一定的收获，在这里记录一下。
最后，编写了一个叫postjson的php扩展，它在源代码层面实现了feature：对于Content-Type是application/json的请求，可以通过$_POST拿到请求参数。
## 二、fpm整体流程

在分析之前，有必要对php-fpm整体流程有所了解。包括你可能想知道的fpm进程启动过程、ini配置文件何时读取，扩展在哪里被加载，请求数据在哪里被读取等等，这里都会稍微提及一下，这样看后面的时候，我们会比较清楚，某一个函数调用发生在整个流程的哪一个环节，做到可识庐山真面目，哪怕身在此山中。

![][0]

和Nginx进程的启动过程类似，fpm启动过程有3种进程角色：启动shell进程、fpm master进程和fpm worker进程。上图列出了各个进程在生命周期中执行的主要函数，其中标有颜色的表示和上面的问题答案有关联的函数。下面概况的说明一下：
## 启动shell进程


* 1.sapi_startup：SAPI启动。将传入的cgi_sapi_module的地址赋值给全局变量sapi_module，初始化全局变量SG，最后执行php_setup_sapi_content_types函数。【这个函数后面会详细说明】
* 2.php_module_startup ：模块初始化。php.ini文件的解析，php动态扩展.so的加载、php扩展、zend扩展的启动都是在这里完成的。


* zend_startup：启动zend引擎，设置编译器、执行器的函数指针，初始化相关HashTable结构的符号表CG(function_table)、CG(class_table)以及CG(auto_globals)，注册Zend核心扩展zend_builtin_module（该过程会注册Zend引擎提供的函数：func_get_args、strlen、class_exists等），注册标准常量如E_ALL、TRUE、FALSE等。
* php_init_config：读取php.ini配置文件并解析，将解析的key-value对存储到configuration_hash这个hashtable中，并且将所有的php扩展（extension=xx.so)的扩展名称保存到extension_lists.functions结构中，将所有的zend扩展（zend_extension=xx.so)的扩展名称保存到extension_lists.engine结构中。
* php_startup_auto_globals：向CG(auth_globals)中注册_GET、_POST、_COOKIE、_SERVER等超全局变量钩子，在后面合适的时机（实际上是php_hash_environment）会回调相应的handler。
* php_startup_sapi_content_types：设置sapi_module的default_post_reader和treat_data。【这2个函数后面会详细说明】
* php_ini_register_extensions：遍历extension_lists.functions，使用dlopen函数打开xx.so扩展文件，将所有的php扩展注册到全局变量module_registry中，同时如果php扩展有实现函数的话，将实现的函数注册到CG(function_table)。遍历extension_lists.engine，使用dlopen函数打开xx.so扩展文件，将所有的zend扩展注册到全局变量zend_extensions中。
* zend_startup_modules：遍历module_registry，调用所有php扩展的MINIT函数。
* zend_startup_extensions：遍历zend_extensions，调用所有zend扩展的startup函数。


* 3.fpm_init：fpm进程相关初始化。这个函数也比较重要。解析php-fpm.conf、fork master进程、安装信号处理器、打开监听socket（默认9000端口）都是在这里完成的。启动shell进程在fork之后不久就退出了。而master进程则通过setsid调用脱离了原来启动shell的终端所在会话，成为了daemon进程。限于篇幅，这里不再展开。

## master进程

* fpm_run：根据php-fpm.conf的配置fork worker进程（一个监听端口对应一个worker pool即进程池，worker进程从属于worker pool，只处理该监听端口的请求）。然后进入fpm_event_loop函数，无限等待事件的到来。

* fpm_event_loop：事件循环。一直等待着信号事件或者定时器事件的发生。区别于Nginx的master进程使用suspend系统调用挂起进程，fpm master通过循环的调用epoll_wait（timeout为1s）来等待事件。

## worker进程


* fpm_init_request：初始化request对象。设置request的listen_socket为从父进程复制过来的相应worker pool对应的监听socket。
* fcgi_accept_request：监听请求连接，读取请求的头信息。

* * 1.accept系统调用：如果没有请求到来，worker进程会阻塞在这里。直到请求到来，将连接fd赋值给request对象的fd字段。
* 2.select/poll系统调用：循环的调用select或者poll（timeout为5s)，等待着连接fd上有可读事件。如果连接fd一直不可读，worker进程将一直在这里阻塞着。


* 3.fcgi_read_request：一旦连接fd上有可读事件之后，会调用该函数对FastCGI协议进行解析，解析出http请求header以及fastcgi_param变量存储到request的env字段中。


* php_request_startup：请求初始化


* 1.zend_activate：重置垃圾回收器，初始化编译器、执行器、词法扫描器。
* 2.sapi_activate：激活SAPI，读取http请求body数据。
* 3.php_hash_environment：回调在php_startup_auto_globals函数中注册的_GET，_POST，_COOKIE等超全局变量的钩子，完成超全局变量的生成。
* 4.zend_activate_modules：调用所有php扩展的RINIT函数。



* php_execute_script：使用Zend VM对php脚本文件进行编译（词法分析+语法分析）生成虚拟机可识别的opcodes，然后执行这些指令。这块很复杂，也是php语言的精华所在，限于篇幅这里不展开。
* php_request_shutdown：请求关闭。调用注册的register_shutdown_function回调，调用__destruct析构函数，调用所有php扩展的RSHUTDOWN函数，flush输出内容，发送http响应header，清理全局变量，关闭编译器、执行器，关闭连接fd等。


```c
        注：当worker进程执行完php_request_shutdown后会再次调用fcgi_accept_request函数，准备监听新的请求。这里可以看到一个worker进程只能顺序的处理请求，在处理当前请求的过程中，该worker进程不会接受新的请求连接，这和Nginx worker进程的事件处理机制是不一样的。
```
## 三、FastCGI协议的处理

言归正传，让我们回到本文的主题，一步步接开$_POST的面纱。

大家都知道$_POST存储的是对http请求body数据解析后的数组，但php-fpm并不是一个web server，它并不支持http协议，一般它通过FastCGI协议来和web server如Apache、Nginx进行数据通信。关于这个协议，已经有其他同学写的好几篇很棒的文章来讲述，如果对FastCGI不了解的，可以先读一下这些文章。

一个FastCGI请求由三部分的数据包组成：FCGI_BEGIN_REQUEST数据包、FCGI_PARAMS数据包、FCGI_STDIN数据包。

![][1]


* FCGI_BEGIN_REQUEST表示请求的开始，它包括：


* header
* data：数据部分，承载着web server期望fpm扮演的角色role字段



* FCGI_PARAMS主要用来传输http请求的header以及fastcgi_param变量数据，它包括：


* 首header：表示FCGI_PARAMS的开始
* data：承载着http请求header和fastcgi_params信息的key-value对组成的字符串
* padding：填充字段
* 尾header：表示FCGI_PARAMS的结束



* FCGI_STDIN用来传输http请求的body数据，它包括：


* 首header：表示FCGI_STDIN的开始
* data：承载着原始的http请求body数据
* padding：填充字段
* 尾header：表示FCGI_STDIN的结束


php对FastCGI协议本身的处理上，可以分为了3个阶段：头信息读取、body信息读取、数据后置处理。下面一一介绍各个阶段都做了些什么。

![][2]
## 头信息读取

头信息读取阶段只读取FCGI_BEGIN_REQUEST和FCGI_PARAMS数据包。因此在这个阶段只能拿到http请求的header以及fastcgi_param变量。在main/fastcgi.c中fcgi_read_request负责完成这个阶段的读取工作。从第二节可以看到，它在worker进程发现请求连接fd可读之后被调用。

```c
static int fcgi_read_request(fcgi_request *req)
{
    fcgi_header hdr;
    int len, padding;
    unsigned char buf[FCGI_MAX_LENGTH+8];
    ...

    //读取到了FCGI_BEGIN_REQUEST的header
    if (hdr.type == FCGI_BEGIN_REQUEST && len == sizeof(fcgi_begin_request)) { 
        
        //读取FCGI_BEGIN_REQUEST的data，存储到buf里
        if (safe_read(req, buf, len+padding) != len+padding) { 
            return 0;
        }

        ...
        //分析buf里FCGI_BEGIN_REQUEST的data中FCGI_ROLE，一般是RESPONDER
        switch ((((fcgi_begin_request*)buf)->roleB1 << 8) + ((fcgi_begin_request*)buf)->roleB0) { 
            case FCGI_RESPONDER:
                fcgi_hash_set(&req->env, FCGI_HASH_FUNC("FCGI_ROLE", sizeof("FCGI_ROLE")-1), "FCGI_ROLE", sizeof("FCGI_ROLE")-1, "RESPONDER", sizeof("RESPONDER")-1);
                break;
            case FCGI_AUTHORIZER:
                fcgi_hash_set(&req->env, FCGI_HASH_FUNC("FCGI_ROLE", sizeof("FCGI_ROLE")-1), "FCGI_ROLE", sizeof("FCGI_ROLE")-1, "AUTHORIZER", sizeof("AUTHORIZER")-1);
                break;
            case FCGI_FILTER:
                fcgi_hash_set(&req->env, FCGI_HASH_FUNC("FCGI_ROLE", sizeof("FCGI_ROLE")-1), "FCGI_ROLE", sizeof("FCGI_ROLE")-1, "FILTER", sizeof("FILTER")-1);
                break;
            default:
                return 0;
        }

        //继续读下一个header
        if (safe_read(req, &hdr, sizeof(fcgi_header)) != sizeof(fcgi_header) ||
            hdr.version < FCGI_VERSION_1) {
            return 0;
        }

        len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;
        padding = hdr.paddingLength;

        while (hdr.type == FCGI_PARAMS && len > 0) {
            //读取到了FCGI_PARAMS的首header（header中len大于0，表示FCGI_PARAMS数据包的开始）
            if (len + padding > FCGI_MAX_LENGTH) {
                return 0;
            }

            //读取FCGI_PARAMS的data
            if (safe_read(req, buf, len+padding) != len+padding) {
                req->keep = 0;
                return 0;
            }

            //解析FCGI_PARAMS的data，将key-value对存储到req.env中
            if (!fcgi_get_params(req, buf, buf+len)) {
                req->keep = 0;
                return 0;
            }

            //继续读取下一个header，下一个header有可能仍然是FCGI_PARAMS的首header，也有可能是FCGI_PARAMS的尾header
            if (safe_read(req, &hdr, sizeof(fcgi_header)) != sizeof(fcgi_header) ||
                hdr.version < FCGI_VERSION_1) {
                req->keep = 0;
                return 0;
            }
            len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;
            padding = hdr.paddingLength;
        }
    }
    ...
    return 1;
}
```

上面的代码可以和FastCGI协议对照着去看，这会加深我们对FastCGI协议的理解。

总的来讲，对于FastCGI协议，总是需要先读取header，根据header中带的类型以及长度继续做不同的处理。

当读取到FCGI_PARAMS的data时，会调用fcgi_get_params函数对data进行解析，将data中的http header以及fastcgi_params存储到req.env结构体中。FCGI_PARAMS的data格式是什么样的呢？它是由一个个的key-value对组成的字符串，对于key-value对，通过keyLength+valueLength+key+value的形式来描述，因此FCGI_PARAMS的data的格式一般是这样：

![][3]

这里有一个细节需要注意，为了节省空间，在Length字段长度制定上，采取了长短2种表示法。如果key或者value的Length不超过127，那么相应的Length字段用一个char来表示。最高位是0，如果相应的Length字段大于127，那么相应的Length字段用4个char来表示，第一个char的最高位是1。大部分http中的header以及fastcgi_params变量的key-value的长度其实都是不超过127的。

举个栗子，在我的vm环境下，执行如下curl命令：curl -H "Content-Type: application/json" -d '{"a":1}' [http://10.179.195.72][15]:8585/test/jiweibin，下面是我gdb时FCGI_PARAMS的data的结果：

```c
\017?SCRIPT_FILENAME/home/weibin/codedir/mis_deploy/mis/src/index.php/test/jiweibin\f\000QUERY_STRING\016\004REQUEST_METHODPOST\f\020CONTENT_TYPEapplication/json\016\001CO
NTENT_LENGTH7\v SCRIPT_NAME/mis/src/index.php/test/ji...
```

可以看到第一个key-value对是"017?SCRIPT_FILENAME/home/weibin/codedir/mis_deploy/mis/src/index.php/test/jiweibin"，keyLength是'017'，它是8进制，转成十进制是15，valueLength是字符'?'，字符'?'对应的数值是63，也就是valueLength是63，因此按keyLength往后读取15个长度的字符，取到了key是："SCRIPT_FILENAME"，继续前移读取63个字符，取到value是："/home/weibin/codedir/mis_deploy/mis/src/index.php/test/jiweibin"。以此类推，我们解析出一个个key-value对，可以看到CONTENT_TYPE=application/json也在其中。

在fcgi_get_params里面解析了某一个key-value对之后，会调用fcgi_hash_set函数将key-value存储到req.env结构体中。req.env结构体的类型是fcgi_hash：

```c
typedef struct _fcgi_hash {
    fcgi_hash_bucket  *hash_table[FCGI_HASH_TABLE_SIZE]; //hashtable，共128个slot，每一个slot存储着指向bucket的指针
    fcgi_hash_bucket  *list; //按插入顺序的逆序排列的bucket链表头指针
    fcgi_hash_buckets *buckets; //存储bucket的物理内存
    fcgi_data_seg     *data; //存储字符串的堆内存首地址
}   ;
```

这个hashtable的实现采用了普遍采用的链地址法思路，不过bucket的内存分配（malloc）并不是每次都需要进行的，而是在hash初始化的时候，一次性预分配一个大小为128的连续的数组。上面的buckets指针指向这段内存。同时hashtable还维护了一个按照元素插入顺序逆序排列的全局单链表，list指向了这个链表的头元素。每一个bucket元素包括对key进行hash之后的hash_value、key的length、key的字符指针、value的length、value的字符指针、相同slot中下个bucket元素指针，全局单链表的下一个bucket元素指针。bucket中key和value并不直接存储字符数组（因为长度未知），而只是存储字符指针，真正的字符数组存储在hashtable的data指向的内存中。

下图展示了当我解析出FCGI_ROLE（通过解析FCGI_BEGIN_REQUEST）以及第一个key-value对（SCRIPT_FILENAME="/home/weibin..."）时，内存的示意图：

![][4]
## body信息读取

该阶段负责处理FCGI_STDIN数据包，这个数据包承载着原始http post请求的body数据。

也许你会想，为什么在头信息读取的时候，不同时将body数据读取出来呢？答案是为了适配多种Content-Type不同的行为。

感兴趣的同学可以做下实验，针对Content-Type为multipart/form-data类型的请求，从$_POST可以拿到body数据，但却不能通过php://input获取到原始的body数据流。而对于Content-Type为x-www-form-urlencoded的请求，这2者是都可以获取到的。

下表总结了3种不同的Content-Type的行为差异，本节我们说明php://input的行为差异原因之所在，而$_POST的差异则要在下一节进行讲解。

![][5]

在body信息读取阶段，对不同的Content-Type差异化处理的关键节点发生在sapi_read_post_data函数，见下图，展示了差异化处理的整体流程：

![][6]

下面我们基于上图，结合着代码进行详细分析。（代码可能会稍微多一点，这块代码比较核心，不是很好通过图的方式去画）

fpm在接收到请求连接并且读取并解析完头信息之后，会调用php_request_startup执行请求初始化。它又调用sapi_activate函数，该函数会判断如果当前请求是POST请求，那么会调用sapi_read_post_data函数对body数据进行读取。

```c
SAPI_API void sapi_activate(void)
{
    ...
    /* Handle request method */
    if (SG(server_context)) {
        if (PG(enable_post_data_reading)
        &&  SG(request_info).content_type
        &&  SG(request_info).request_method
        && !strcmp(SG(request_info).request_method, "POST")) {
            /* HTTP POST may contain form data to be processed into variables
             * depending on given content type */
            sapi_read_post_data();   //根据不同的Content-Type进行post数据的读取
        } else {
            SG(request_info).content_type_dup = NULL;
        }

       ...
    }
    ...
}
```

而在sapi_read_post_data中，会首先从SG(known_post_content_types)这个hashtable中查询是否有对应的钩子，如果有则调用，如果没有，则使用默认的处理方式。

```c
static void sapi_read_post_data(void)
{
    ...

    /* now try to find an appropriate POST content handler */
    if ((post_entry = zend_hash_str_find_ptr(&SG(known_post_content_types), content_type,
            content_type_length)) != NULL) { //content_type已注册钩子
        /* found one, register it for use */
        SG(request_info).post_entry = post_entry; //将钩子保存到SG
        post_reader_func = post_entry->post_reader; //钩子中的post_reader函数指针赋值给post_reader_func
    } else {
        /* fallback */
        SG(request_info).post_entry = NULL;
        if (!sapi_module.default_post_reader) {
            /* no default reader ? */
            SG(request_info).content_type_dup = NULL;
            sapi_module.sapi_error(E_WARNING, "Unsupported content type:  '%s'", content_type);
            return;
        }
    }
    ...

    if(post_reader_func) { //如果post_reader_func不为空，执行post_reader_func
        post_reader_func();
    }

    //否则，执行默认的处理逻辑（之所以post_reader_func和sapi_module.default_post_reader互斥，关键的逻辑在sapi_module.default_post_reader里面实现）
    if(sapi_module.default_post_reader) { 
        sapi_module.default_post_reader();
    }
}
```

SG(known_post_content_types)中为哪些Content-Type安装了钩子呢？答案是只有2种：application/x-www-form-urlencoded和multipart/form-data。在第二节曾经提到，在SAPI启动阶段，会执行一个神秘函数php_setup_sapi_content_types，它会遍历php_post_entries数组，将上面2个Content-Type对应的钩子注册到SG的known_post_content_types这个hashtable中。

```c
#define DEFAULT_POST_CONTENT_TYPE "application/x-www-form-urlencoded" 
#define MULTIPART_CONTENT_TYPE "multipart/form-data"
 
int php_setup_sapi_content_types(void) 
{
    sapi_register_post_entries(php_post_entries);  //安装内置的Content_Type处理钩子

    return SUCCESS;
}
 
static sapi_post_entry php_post_entries[] = { 
    { DEFAULT_POST_CONTENT_TYPE, sizeof(DEFAULT_POST_CONTENT_TYPE)-1, sapi_read_standard_form_data, php_std_post_handler },
    { MULTIPART_CONTENT_TYPE,    sizeof(MULTIPART_CONTENT_TYPE)-1,    NULL,                         rfc1867_post_handler },
    { NULL, 0, NULL, NULL }
};
 
struct _sapi_post_entry {
    char *content_type;
    uint content_type_len;
    void (*post_reader)(void); //post数据读取函数指针
    void (*post_handler)(char *content_type_dup, void *arg); //post数据后置处理函数指针，见下一小节
};
 
typedef struct _sapi_post_entry sapi_post_entry;
```

钩子包含了2个函数指针，post_reader在本阶段会被调用，而post_handler会在数据后置处理阶段被调用。从上面代码可以看到，php为application/x-www-form-urlencoded安装的钩子的post_reader函数指针指向sapi_read_standard_form_data，而multipart/form-data虽然钩子已安装，但是post_reader函数指针为NULL，所以在本阶段不进行任何处理。

让我们继续跟踪sapi_read_standard_form_data都干了些什么，它的整体流程可以参考下图：

![][7]

首先，它会创建一个phpstream，并将SG(request_info).request_body指向这个phpstream（phpstream是php对io的封装，比较复杂，这里不展开）。然后调用sapi_read_post_block函数读取htt ppost请求的body数据，内部它会调用sapi_cgi_read_post函数，这个函数会判断头信息里是否存在REQUEST_BODY_FILE字段（REQUEST_BODY_FILE用来在nginx和fpm传递size特别大的body时或者传递上传的文件时只传递文件名，这里不展开），如果有则直接读REQUEST_BODY_FILE对应的文件，如果没有则调用fcgi_read函数解析FCGI_STDIN数据包。最后将读取的数据写入到之前创建的phpstream中。

php://input其实就是基于这个stream做的读取包装。对于multipart/form-data，由于安装的钩子中post_reader是NULL，在本阶段并未做任何事儿，因此无法通过php://input获取到原始的post body数据流。

下面对照着上面的流程，跟踪下代码：

```c
SAPI_API SAPI_POST_READER_FUNC(sapi_read_standard_form_data)
{
    //创建phpstream
    SG(request_info).request_body = php_stream_temp_create_ex(TEMP_STREAM_DEFAULT, SAPI_POST_BLOCK_SIZE, PG(upload_tmp_dir)); 

    if (sapi_module.read_post) {
        size_t read_bytes;

        for (;;) {
            char buffer[SAPI_POST_BLOCK_SIZE];

            //调用sapi_module.read_post读取FCGI_STDIN数据包
            read_bytes = sapi_read_post_block(buffer, SAPI_POST_BLOCK_SIZE);

            if (read_bytes > 0) {
                //将body数据写到SG(request_info).request_body这个phpstream
                if (php_stream_write(SG(request_info).request_body, buffer, read_bytes) != read_bytes) {
                   ...
                }
            }
            ...
            if (read_bytes < SAPI_POST_BLOCK_SIZE) {
                /* done */
                break;
            }
        }
        php_stream_rewind(SG(request_info).request_body);
    }
}
```

sapi_read_post_block内部会调用sapi_module.read_post函数指针，而对于php-fpm而言，sapi_module.read_post指向sapi_cgi_read_post函数，该函数内部会调用fcgi_read读取FCGI_STDIN数据流。

```c
static sapi_module_struct cgi_sapi_module = {
    "fpm-fcgi",                     /* name */
    ...
    sapi_cgi_read_post,             /* read POST data */ 
    sapi_cgi_read_cookies,          /* read Cookies */
    ...
    STANDARD_SAPI_MODULE_PROPERTIES
};
 
static size_t sapi_cgi_read_post(char *buffer, size_t count_bytes)
{
    ...
    while (read_bytes < count_bytes) {
        ...
        if (request_body_fd == -1) {
            //检查是否有REQUEST_BODY_FILE头
            char *request_body_filename = FCGI_GETENV(request, "REQUEST_BODY_FILE");

            if (request_body_filename && *request_body_filename) {
                request_body_fd = open(request_body_filename, O_RDONLY);
                ...
            }
        }

        /* If REQUEST_BODY_FILE variable not available - read post body from fastcgi stream */
        if (request_body_fd < 0) {
            //如果没有REQUEST_BODY_FILE头，继续按照FastCGI协议读取FCGI_STDIN数据包
            tmp_read_bytes = fcgi_read(request, buffer + read_bytes, count_bytes - read_bytes);
        } else {
            //如果有REQUEST_BODY_FILE头，从文件读取body数据
            tmp_read_bytes = read(request_body_fd, buffer + read_bytes, count_bytes - read_bytes);
        }
        ...
        read_bytes += tmp_read_bytes;
    }
    return read_bytes;
}
 
int fcgi_read(fcgi_request *req, char *str, int len)
{
    int ret, n, rest;
    fcgi_header hdr;
    unsigned char buf[255];

    n = 0;
    rest = len;
    while (rest > 0) {
        if (req->in_len == 0) { //第一次循环，读取header
            if (safe_read(req, &hdr, sizeof(fcgi_header)) != sizeof(fcgi_header) ||
                hdr.version < FCGI_VERSION_1 ||
                hdr.type != FCGI_STDIN) { //如果header不是STDIN，异常退出
                req->keep = 0;
                return 0;
            }
            req->in_len = (hdr.contentLengthB1 << 8) | hdr.contentLengthB0;
            req->in_pad = hdr.paddingLength;
            if (req->in_len == 0) {
                return n;
            }
        }
        
        //读取FCGI_STDIN的data
        if (req->in_len >= rest) {
            ret = (int)safe_read(req, str, rest);
        } else {
            ret = (int)safe_read(req, str, req->in_len);
        }
        ...
    }
    return n;
}
```

至此，我们跟踪完成了application/x-www-form-urlencoded的整个body读取过程。

再回过头来看下application/json，由于并没有为它安装钩子，在sapi_read_post_data时，使用默认的处理方式。这里的默认行为会执行sapi_module.default_post_reader函数指针指向的函数。而这个函数指针指向哪个函数呢？

在第二节讲到的php_module_startup函数中有一个php_startup_sapi_content_types函数，它会指定sapi_module.default_post_reader是php_default_post_reader。

```c
int php_startup_sapi_content_types(void)
{
    sapi_register_default_post_reader(php_default_post_reader); //设置default_post_reader
    sapi_register_treat_data(php_default_treat_data);
    sapi_register_input_filter(php_default_input_filter, NULL);
    return SUCCESS;
}
 
SAPI_API SAPI_POST_READER_FUNC(php_default_post_reader)
{
    if (!strcmp(SG(request_info).request_method, "POST")) {  //如果是POST请求
        if (NULL == SG(request_info).post_entry) { //如果Content-Type没有对应的钩子
            /* no post handler registered, so we just swallow the data */
            sapi_read_standard_form_data(); //和application/x-www-form-urlencoded一样的处理逻辑
        }
    }
}
```

在php_default_post_reader中，我们看到，其实它执行的仍然是sapi_read_standard_form_data函数，也就是在body信息读取阶段，尽管application/json没有注册钩子，但是它和application/x-www-form-urlencoded仍然保持这一致的处理逻辑。这也解释了，为什么application/json可以通过php://input拿到原始post数据。

到现在，php://input的行为差异已经是可以解释的清了，而$_POST我们需要继续跟踪下去。
## 数据后置处理

数据后置处理阶段是用来对原始的body数据做后置处理的，$_POST就是在这个阶段产生。下图展示了在数据后置处理阶段，php执行的函数流程。

![][8]

第二节讲到，在php_module_startup函数中，会调用php_startup_auto_globals向CG(auto_globals)这个hashtable注册超全局变量_GET、_POST、_COOKIE、_SERVER的钩子，然后在合适的时机回调。

```c
void php_startup_auto_globals(void)
{
    zend_register_auto_global(zend_string_init("_GET", sizeof("_GET")-1, 1), 0, php_auto_globals_create_get);
    zend_register_auto_global(zend_string_init("_POST", sizeof("_POST")-1, 1), 0, php_auto_globals_create_post);
    zend_register_auto_global(zend_string_init("_COOKIE", sizeof("_COOKIE")-1, 1), 0, php_auto_globals_create_cookie);
    zend_register_auto_global(zend_string_init("_SERVER", sizeof("_SERVER")-1, 1), PG(auto_globals_jit), php_auto_globals_create_server);
    zend_register_auto_global(zend_string_init("_ENV", sizeof("_ENV")-1, 1), PG(auto_globals_jit), php_auto_globals_create_env);
    zend_register_auto_global(zend_string_init("_REQUEST", sizeof("_REQUEST")-1, 1), PG(auto_globals_jit), php_auto_globals_create_request);
    zend_register_auto_global(zend_string_init("_FILES", sizeof("_FILES")-1, 1), 0, php_auto_globals_create_files);
}
```

而这个合适的时机就是php_request_startup中在sapi_activate之后执行的php_hash_environment函数。该函数内部会调用zend_activate_auto_globals函数，这个函数遍历所有注册的auto global，回调相应的钩子。而$_POST对应的钩子是php_auto_globals_create_post。

```c
PHPAPI int php_hash_environment(void)
{
    memset(PG(http_globals), 0, sizeof(PG(http_globals)));
    zend_activate_auto_globals(); //激活超全局变量，回调startup时注册的钩子
    if (PG(register_argc_argv)) {
        php_build_argv(SG(request_info).query_string, &PG(http_globals)[TRACK_VARS_SERVER]);
    }
    return SUCCESS;
}
 
ZEND_API void zend_activate_auto_globals(void) /* {{{ */
{
    zend_auto_global *auto_global;

    ZEND_HASH_FOREACH_PTR(CG(auto_globals), auto_global) { //遍历所有的超全局变量
        if (auto_global->jit) {
            auto_global->armed = 1;
        } else if (auto_global->auto_global_callback) {
            auto_global->armed = auto_global->auto_global_callback(auto_global->name); //回调钩子函数
        } else {
            auto_global->armed = 0;
        }
    } ZEND_HASH_FOREACH_END();
}
```

php_auto_globals_create_post做了什么操作呢？下图展示了它的整体流程。

![][9]

在PG里有一个http_globals字段，它是包含6个zval的数组。这6个zval分别用来临时存储 _POST、_GET、_COOKIE、_SERVER、_ENV和_FILES 数据。

```c
struct _php_core_globals {
    ...
    zval http_globals[6]; //0-$_POST 1-$_GET 2-$_COOKIE 3-$_SERVER 4-$_ENV 5-$_FILES
    ...
};
```

对于一个简单的post请求：curl -d "a=1" [http://10.179.195.72][15]:8585/test/jiweibin ，Content-Type是application/x-www-form-urlencoded，php_auto_globals_create_post所做的操作可以分这么几步：


* 读取上一阶段写入到SG(request_info).request_body这个phpstream中的body数据到内存buf。这里body数据是"a=1"这个字符串。
* 解析post body数据（按&分割key-value对，按=分割key和value），并将解析后的数据通过调用add_post_vars函数，写入到PG(http_globals)[0]这个zval中，zval的类型是数组类型。
* 最后，为了让Zend引擎可以通过_POST这个字符串索引到上一步解析的zval，我们需要以"_POST"为key，刚刚zval为value注册到php Zend引擎的全局变量符号表EG(symbol_table)中。


在php_auto_globals_create_post函数中， 当发现当前的请求是POST请求时，会调sapi_module.treat_data函数指针。在php_module_startup阶段，php会设置sapi_module.treat_data函数指针指向php_default_treat_data函数。该函数会最终完成body数据解析并存储到PG(http_globals)[0]这个zval中。在调用完php_default_treat_data之后，会将"_POST"和PG(http_globals)[0]注册到符号表EG(symbol_table)。代码如下：

```c
static zend_bool php_auto_globals_create_post(zend_string *name)
{
    if (PG(variables_order) &&
            (strchr(PG(variables_order),'P') || strchr(PG(variables_order),'p')) &&
        !SG(headers_sent) &&
        SG(request_info).request_method &&
        !strcasecmp(SG(request_info).request_method, "POST")) { 
        sapi_module.treat_data(PARSE_POST, NULL, NULL); //从stream中读取并解析body数据，存储到PG(http_globals)[0]
    } else {
        zval_ptr_dtor(&PG(http_globals)[TRACK_VARS_POST]);
        array_init(&PG(http_globals)[TRACK_VARS_POST]);
    }

    zend_hash_update(&EG(symbol_table), name, &PG(http_globals)[TRACK_VARS_POST]); //将'_POST'和PG(http_globals)[0]注册到EG(symbol_table)
    Z_ADDREF(PG(http_globals)[TRACK_VARS_POST]);

    return 0; /* don't rearm */
}
```

在php_default_treat_data中，对于POST请求，会重新初始化PG(http_globals)[0]（TRACK_VARS_POST是一个宏，在编译阶段会被替换为0），然后调用sapi_handle_post函数，该函数会回调在SAPI启动阶段为Content-Type安装的钩子中的post_handler函数指针。

```c
SAPI_API SAPI_TREAT_DATA_FUNC(php_default_treat_data)
{
    ...
    zval array;
    ...

    ZVAL_UNDEF(&array);
    switch (arg) {
        case PARSE_POST:
        case PARSE_GET:
        case PARSE_COOKIE:
            array_init(&array);
            switch (arg) {
                case PARSE_POST:
                    zval_ptr_dtor(&PG(http_globals)[TRACK_VARS_POST]); //析构zval，释放上一次请求的旧数组内存
                    ZVAL_COPY_VALUE(&PG(http_globals)[TRACK_VARS_POST], &array);  //重新初始化zval,指向新的空数组内存
                    break;
                ...
            }
            break;
        default:
            ZVAL_COPY_VALUE(&array, destArray);
            break;
    }

    if (arg == PARSE_POST) {
        sapi_handle_post(&array); //回调Content-Type钩子
        return;
    }
    ...
}
 
SAPI_API void sapi_handle_post(void *arg)
{
    //如果Content-Type已经安装钩子
    if (SG(request_info).post_entry && SG(request_info).content_type_dup) { 
        SG(request_info).post_entry->post_handler(SG(request_info).content_type_dup, arg); //调用相应钩子的post_handler函数指针
        efree(SG(request_info).content_type_dup);
        SG(request_info).content_type_dup = NULL;
    }
```

对于application/x-www-form-urlencoded，post_handler是php_std_post_handler。

```c
SAPI_API SAPI_POST_HANDLER_FUNC(php_std_post_handler)
{
    zval *arr = (zval *) arg; //arg指向PG(http_globals)[0]
    php_stream *s = SG(request_info).request_body;
    post_var_data_t post_data;

    if (s && SUCCESS == php_stream_rewind(s)) {
        memset(&post_data, 0, sizeof(post_data));

        while (!php_stream_eof(s)) {
            char buf[SAPI_POST_HANDLER_BUFSIZ] = {0};
 
            //读取上一阶段被写入的phpstream
            size_t len = php_stream_read(s, buf, SAPI_POST_HANDLER_BUFSIZ); 

            if (len && len != (size_t) -1) {
                smart_str_appendl(&post_data.str, buf, len);
 
                //解析并插入到arr中，arr指向PG(http_globals)[0]
                if (SUCCESS != add_post_vars(arr, &post_data, 0)) {
                    smart_str_free(&post_data.str);
                    return;
                }
            }

            if (len != SAPI_POST_HANDLER_BUFSIZ){ //读到最后了
                break;
            }
        }

        if (post_data.str.s) {
            //解析并插入到arr中，arr指向PG(http_globals)[0]
            add_post_vars(arr, &post_data, 1);
            smart_str_free(&post_data.str);
        }
    }
}

```

对于multipart/form-data，post_handler是rfc1867_post_handler。由于它的代码过长，这里不再贴代码了。由于在body信息读取阶段，钩子的post_reader是空，所以rfc1867_post_handler会一边做FCGI_STDIN数据包的读取，一边做解析存储工作，最终将数据包中的key-value对存储到PG(http_globals)[0]中。另外，该函数还会对上传的文件进行处理，有兴趣的同学可以读下这个函数。

对于application/json，由于未安装任何钩子，所以在这里不会做任何事情，PG(http_globals)[0]是空数组。因此如果Content-Type是application/json，是无法获取到$_POST变量的。

php_auto_globals_create_post执行的最后，需要进行全局变量符号表的注册操作，这是为什么呢？其实这和Zend引擎的代码执行有关系了。Zend引擎的编译器碰到$_POST时，opcode会是ZEND_FETCH_R或者ZEND_FETCH_W（其中操作数是'_POST'，fetch_type是global），在执行阶段执行器会去EG(symbol_table)中根据key='_POST'去找到对应的zval。因此这里的注册操作是有必要的。

让我们用一个例子来验证下opcode，写一个简单的php脚本test.php：

```php
<?php
var_dump($_POST);
```

安装vld扩展之后，执行php -dvld.active=1 test.php，可以看到opcode是FETCH_R，正如我们预期。它会先从全局符号表中查找'_POST'对应的zval，然后赋值给$0（主函数栈的第一个变量，该变量是隐式声明）。

![][10]
## 四、postjson扩展

到这里，我们已经对$_POST的整体流程以及细节有所了解。让我们做点什么吧，写一个扩展，来让application/json的请求也可以享受到$_POST这个超全局变量带来的便利。（这个扩展的生产环境的意义不大，完全可以在php层通过php://input拿到请求body，更多的是学以致用的学习意义）

如何来实现我们的扩展呢？ 上面我们知道，之所以拿不到是因为没有为application/json安装钩子，导致在数据后置处理阶段并没有做post body的解析，所以这里我们需要安装一个钩子，钩子的post_reader可以是NULL（这样会走默认逻辑），也可以和application/x-www-form-urlencoded保持一致：sapi_read_standard_form_data。而post_handler则需要我们编写了，post_handler我们取名：php_json_post_handler。

下图展示了postjson扩展整体的执行流程：


* 它在模块初始化时，zend_startup_modules执行之后，会调用该扩展的MINIT函数，MINIT函数里面会进行ini entry注册，并获取到关心的ini配置的值（这里我们会注册一个开关配置postjson.parse表示是否开启扩展)，如果扩展开启，我们会向SG(known_post_content_types)注册application/json的钩子。
* 然后在请求初始化时，FastCGI协议处理的数据后置处理阶段，回调我们的钩子函数php_json_post_handler，完成json格式的post body的解析以及将解析后的key-value存储到PG(http_globals)[0]的操作。
* 后续php的框架代码php_auto_globals_create_post会完成后续的符号表注册操作。


![][11]

关于php_json_post_handler，对json的解析是一个复杂的过程，我们可以使用现有的轮子，看下php的json扩展是如何实现的:

```c
static PHP_FUNCTION(json_decode)
{
    char *str;
    size_t str_len;
    zend_bool assoc = 0; /* return JS objects as PHP objects by default */
    zend_long depth = PHP_JSON_PARSER_DEFAULT_DEPTH;
    zend_long options = 0;

    if (zend_parse_parameters(ZEND_NUM_ARGS(), "s|bll", &str, &str_len, &assoc, &depth, &options) == FAILURE) {
        return;
    }

    JSON_G(error_code) = 0;

    if (!str_len) {
        JSON_G(error_code) = PHP_JSON_ERROR_SYNTAX;
        RETURN_NULL();
    }

    /* For BC reasons, the bool $assoc overrides the long $options bit for PHP_JSON_OBJECT_AS_ARRAY */
    if (assoc) {
        options |=  PHP_JSON_OBJECT_AS_ARRAY;
    } else {
        options &= ~PHP_JSON_OBJECT_AS_ARRAY;
    }

    php_json_decode_ex(return_value, str, str_len, options, depth); //解析str,存储到return_value这个zval中
} 
```

我们可以使用php_json_decode_ex（它内部使用yacc完成语法解析）这个函数来做json解析，将return_value替换为&PG(http_globals)[0]。而str则从SG(request_info).request_body这个phpstream中去读取。所以，整体的思路已经通了，下面我们来操作一下。
## 生成扩展骨架

进入到源码目前的ext目录：cd /home/weibin/offcial_code/php/7.0.6/php-7.0.6/ext，执行  ./ext_skel --extname=postjson，这时在代码目录下可以看到postjson.c和php_postjson.h等文件。
## 编辑php_postjson.h文件

我们的扩展可以在php.ini中开关，开的方式是postjson.parse=On,关的方式是postjson.parse=Off，所以这里我们需要定义一个存储这个开关的结构体，parse字段表示这个开关。定义了2个常量：JSON_CONTENT_TYPE和CHUNK_SIZE，分别用来表示application/json的Content-Type和读取phpstream时的buffer大小。

```c
#ifndef PHP_POSTJSON_H
#define PHP_POSTJSON_H
 
#include "SAPI.h"
#include "ext/json/php_json.h"
#include "php_globals.h"
 
extern zend_module_entry postjson_module_entry;
#define phpext_postjson_ptr &postjson_module_entry
#define PHP_POSTJSON_VERSION "0.1.0" /* Replace with version number for your extension */
 
#ifdef PHP_WIN32
#    define PHP_POSTJSON_API __declspec(dllexport)
#elif defined(__GNUC__) && __GNUC__ >= 4
#    define PHP_POSTJSON_API __attribute__ ((visibility("default")))
#else
#    define PHP_POSTJSON_API
#endif
#ifdef ZTS
#include "TSRM.h"
#endif
 
ZEND_BEGIN_MODULE_GLOBALS(postjson)
    zend_long  parse;  //存储配置的结构体
ZEND_END_MODULE_GLOBALS(postjson)
 
SAPI_POST_HANDLER_FUNC(php_json_post_handler);
 
#define JSON_CONTENT_TYPE "application/json"
#define CHUNK_SIZE    8192
 
/* Always refer to the globals in your function as POSTJSON_G(variable).
   You are encouraged to rename these macros something shorter, see
   examples in any other php module directory.
*/
#define POSTJSON_G(v) ZEND_MODULE_GLOBALS_ACCESSOR(postjson, v)
#if defined(ZTS) && defined(COMPILE_DL_POSTJSON)
ZEND_TSRMLS_CACHE_EXTERN()
#endif
#endif    /* PHP_POSTJSON_H */
```
## 编辑postjson.c文件

这里定义ini配置，钩子数组post_entries，实现php_json_post_handler，并改写MINIT函数，判断ini中开关postjson.parse是否开启，如果开启，则注册钩子。

在php_json_post_handler中分配一个8k的zend_string，读取SG(request_info).request_body这个phpstream到一个8k的buffer，如果一次读取不完，分多次读取，zend_string不断扩容，最终包含整个json字符串。最后调用php_json_decode_ex函数完成json串解析并存储到PG(http_globlas)[0]中。

```c
#ifdef HAVE_CONFIG_H
#include "config.h"
#endif
#include "php.h"
#include "php_ini.h"
#include "ext/standard/info.h"
#include "php_postjson.h"

ZEND_DECLARE_MODULE_GLOBALS(postjson)
/* True global resources - no need for thread safety here */
static int le_postjson;
 
//postjson扩展使用到的ini
PHP_INI_BEGIN()
    STD_PHP_INI_BOOLEAN("postjson.parse",      "0", PHP_INI_ALL, OnUpdateLong, parse, zend_postjson_globals, postjson_globals)
PHP_INI_END()

static sapi_post_entry post_entries[] = { //定义Content-Type钩子
    { JSON_CONTENT_TYPE,    sizeof(JSON_CONTENT_TYPE)-1,    sapi_read_standard_form_data,  php_json_post_handler },
    { NULL, 0, NULL, NULL }
};
SAPI_POST_HANDLER_FUNC(php_json_post_handler){ //post handler
    size_t ret = 0;
    char *ptr;
    size_t len = 0, max_len;
    int step = CHUNK_SIZE;
    int min_room = CHUNK_SIZE / 4;
    int persistent = 0;
    zend_string *result;
    php_stream *s = SG(request_info).request_body;
    if (s && SUCCESS == php_stream_rewind(s)) {
        max_len = step;
    
        result = zend_string_alloc(max_len, persistent);
        ptr = ZSTR_VAL(result);
        while ((ret = php_stream_read(s, ptr, max_len - len)))    { //读取SG(request_info).request_body这个phpstream
            len += ret;
            if (len + min_room >= max_len) {
                result = zend_string_extend(result, max_len + step, persistent);
                max_len += step;
                ptr = ZSTR_VAL(result) + len;
            } else {
                ptr += ret;
            }
        }
        if (len) {
            result = zend_string_truncate(result, len, persistent);
            ZSTR_VAL(result)[len] = '\0';
            //解析json，并存储到PG(http_globals)[0]
            php_json_decode_ex(&PG(http_globals)[TRACK_VARS_POST], ZSTR_VAL(result), ZSTR_LEN(result), PHP_JSON_OBJECT_AS_ARRAY, PHP_JSON_PARSER_DEFAULT_DEPTH);
        } else {
            zend_string_free(result);
            result = NULL;
        }
    }
}

static void php_postjson_init_globals(zend_postjson_globals *postjson_globals)
{
    postjson_globals->parse = 0;
}

 
PHP_MINIT_FUNCTION(postjson)
{
    ZEND_INIT_MODULE_GLOBALS(postjson, php_postjson_init_globals, NULL);
    REGISTER_INI_ENTRIES();
    int parse = (int)POSTJSON_G(parse);
    if(parse == 1){ //如果ini中postjson.parse开启，那么将application/json的钩子注册到SG(known_post_content_types)中
        sapi_register_post_entries(post_entries);    
    }
    return SUCCESS;
}

const zend_function_entry postjson_functions[] = { //这里我们不注册任何php函数
        PHP_FE_END    /* Must be the last line in postjson_functions[] */
};

static zend_module_dep module_deps[] = { //本扩展依赖php的json扩展
    ZEND_MOD_REQUIRED("json")
    ZEND_MOD_END
};

zend_module_entry postjson_module_entry = {
    STANDARD_MODULE_HEADER_EX,NULL,
    module_deps,
    "postjson",
    postjson_functions,
    PHP_MINIT(postjson),
    PHP_MSHUTDOWN(postjson),
    PHP_RINIT(postjson),        /* Replace with NULL if there's nothing to do at request start */
    PHP_RSHUTDOWN(postjson),    /* Replace with NULL if there's nothing to do at request end */
    PHP_MINFO(postjson),
    PHP_POSTJSON_VERSION,
    STANDARD_MODULE_PROPERTIES
};
...
```
## 编译安装

```
   phpize 

   configure --with-php-config=../php-config

   make

   make install
```
## 配置php.ini

增加post配置：

```ini
[postjson]
extension="postjson.so"
postjson.parse=On
```

验证是否安装成功：php -m|grep postjson

![][12]
## 测试

重启php-fpm，kill -USR2`cat /home/weibin/php7/var/run/php-fpm.pid`编写测试脚本：

```php
<?php
namespace xxx\Test;

class Jiweibin{
    function index() {
        var_dump($_POST);
        var_dump(file_get_contents("php://input"));
    }

}
```

执行curl命令，curl -H "Content-Type: application/json" -d '{"a":1}' [http://10.179.195.72][15]:8585/test/jiweibin，执行结果如下，我们看到通过$_POST可以拿到解析后的post数据了，搞定。

![][13]
## 五、总结

本篇wiki，从源码角度分析了php中_POST的原理，展现了FastCGI协议的整体处理流程，以及针对不同Content-Type的处理差异化，并为application/json动手编写了php扩展，实现了_POST的解析，希望大家有所收获。但本篇wiki并不是终点，通过编写这篇wiki，对json解析（yacc）、Zend引擎原理有了比较浓厚的兴趣和探知欲，有时间的话，希望能分享给大家，另外感谢我的同事朱栋同学，一起跟代码的感觉还是很赞的。

[14]: http://php.net/manual/zh/reserved.variables.post.php
[15]: http://10.179.195.72
[16]: http://10.179.195.72
[17]: http://10.179.195.72
[0]: ../img/bVbiWnP.png
[1]: ../img/bVbiWoH.png
[2]: ../img/bVbiWoV.png
[3]: ../img/bVbiWoW.png
[4]: ../img/bVbiWo5.png
[5]: ../img/bVbiWo7.png
[6]: ../img/bVbiWpb.png
[7]: ../img/bVbiWpl.png
[8]: ../img/bVbiWpt.png
[9]: ../img/bVbiWpS.png
[10]: ../img/bVbjk5z.png
[11]: ../img/bVbiWp5.png
[12]: ../img/bVbjk5K.png
[13]: ../img/bVbiWqL.png