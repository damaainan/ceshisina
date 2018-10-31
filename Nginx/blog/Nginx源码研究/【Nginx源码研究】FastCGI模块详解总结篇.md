## 【Nginx源码研究】FastCGI模块详解总结篇

来源：[https://segmentfault.com/a/1190000016564382](https://segmentfault.com/a/1190000016564382)

运营研发 李乐
## 1.初识FastCGI协议

FastCGI 是一种协议，规定了FastCGI应用和支持FastCGI的Web服务器之间的接口。FastCGI是二进制连续传递的。
## 1.1消息头

FastCGI定义了多种类型的消息；nginx对FastCGI消息类型定义如下：

```c
#define NGX_HTTP_FASTCGI_BEGIN_REQUEST  1
#define NGX_HTTP_FASTCGI_ABORT_REQUEST  2
#define NGX_HTTP_FASTCGI_END_REQUEST    3
#define NGX_HTTP_FASTCGI_PARAMS         4
#define NGX_HTTP_FASTCGI_STDIN          5
#define NGX_HTTP_FASTCGI_STDOUT         6
#define NGX_HTTP_FASTCGI_STDERR         7
#define NGX_HTTP_FASTCGI_DATA           8
```

一般情况下，最先发送的是BEGIN_REQUEST类型的消息，然后是PARAMS和STDIN类型的消息；

当FastCGI响应处理完后，将发送STDOUT和STDERR类型的消息，最后以END_REQUEST表示请求的结束。

FastCGI定义了一个统一结构的8个字节消息头，用来标识每个消息的消息体，以及实现消息数据的分割。结构体定义如下：

```c
typedef struct {
    u_char  version; //FastCGI协议版本
    u_char  type;    //消息类型
    u_char  request_id_hi; //请求ID
    u_char  request_id_lo;
    u_char  content_length_hi; //内容
    u_char  content_length_lo;
    u_char  padding_length;    //内容填充长度
    u_char  reserved;          //保留
} ngx_http_fastcgi_header_t;
```

我们看到请求ID与内容长度分别用两个u_char存储，实际结果的计算方法如下：

```c
requestId = (request_id_hi << 8) + request_id_lo;
contentLength = (content_length_hi << 8) + content_length_lo;
```

消息体的长度始终是8字节的整数倍，当实际内容长度不足时，需要填充若干字节；填充代码如下所示：

```c
padding = 8 - len % 8;
padding = (padding == 8) ? 0 : padding;
```
## 1.2消息体举例

BEGIN_REQUEST类型的消息标识FastCGI请求的开始，结构固定，定义如下：

```c
typedef struct {
    u_char  role_hi; //标记FastCGI应用应该扮演的角色
    u_char  role_lo;
    u_char  flags;
    u_char  reserved[5];
} ngx_http_fastcgi_begin_request_t;
```

角色同样使用两个u_char存储，计算方法为：

```c
role = (role_hi << 8) + role_lo;
```

最常用的是响应器(Responder)角色，FastCGI应用接收所有与HTTP请求相关的信息，并产生一个HTTP响应。

nginx配置文件中，fastcgi_param指令配置的若干参数，以及HTTP请求的消息头，都是通过FCGI_PARAMS类型的消息传递的，此消息就是若干个名—值对（此名—值对在php中可以通过$_SERVER[ ]获取）；

传输格式为nameLength+valueLength+name+value。

为了节省空间，对于0~127长度的值，Length使用了一个char来表示，第一位为0，对于大于127的长度的值，Length使用了4个char来表示，第一位为1；如下图所示：

![][0]

Length字段编码的逻辑如下：

```c
if (val_len > 127) {
    *b->last++ = (u_char) (((val_len >> 24) & 0x7f) | 0x80);
    *b->last++ = (u_char) ((val_len >> 16) & 0xff);
    *b->last++ = (u_char) ((val_len >> 8) & 0xff);
    *b->last++ = (u_char) (val_len & 0xff);
 
} else {
    *b->last++ = (u_char) val_len;
}
```
## 2.基础知识
## 2.1 FastCGI配置

代码中搜索ngx_http_fastcgi_commands，查看fastcgi模块提供的配置指令；

```c
static ngx_command_t  ngx_http_fastcgi_commands[] = {
 
    { ngx_string("fastcgi_pass"),
      NGX_HTTP_LOC_CONF|NGX_HTTP_LIF_CONF|NGX_CONF_TAKE1, //只能出现在location块中
      ngx_http_fastcgi_pass,
      NGX_HTTP_LOC_CONF_OFFSET,
      0,
      NULL },
    { ngx_string("fastcgi_param"),
      NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF|NGX_HTTP_LOC_CONF|NGX_CONF_TAKE23, //可以出现在http配置块、server配置块、location配置块中
      ngx_http_upstream_param_set_slot,
      NGX_HTTP_LOC_CONF_OFFSET,
      offsetof(ngx_http_fastcgi_loc_conf_t, params_source),   //ngx_http_fastcgi_loc_conf_t结构的params_source字段是存储配置参数的array，
      NULL },
    …………
}
```

fastcgi_pass指令用于配置上游FastCGI应用的ip:port，ngx_http_fastcgi_pass方法解析此指令（设置handler为ngx_http_fastcgi_handler方法，命中当前location规则的HTTP请求，请求处理的内容产生阶段会调用此handler）；

fastcgi_param用于配置nginx向FastCGI应用传递的参数，在php中，我们可以通过$_SERVER[" "]获取这些参数；

解析fastcgi_param配置的代码实现如下：

```c
char * ngx_http_upstream_param_set_slot(ngx_conf_t *cf, ngx_command_t *cmd, void *conf)
{
    a = (ngx_array_t **) (p + cmd->offset);   //ngx_http_fastcgi_loc_conf_t结构首地址加params_source字段的偏移
    param = ngx_array_push(*a);
     
    value = cf->args->elts;
    param->key = value[1];
    param->value = value[2];
    param->skip_empty = 0;
 
    if (cf->args->nelts == 4) {   //if_not_empty用于配置参数是否必传（如果配置，当值为空时不会传向FastCGI应用传递此参数）
        if (ngx_strcmp(value[3].data, "if_not_empty") != 0) {
            return NGX_CONF_ERROR;
        }
        param->skip_empty = 1;
    }
    return NGX_CONF_OK;
}
```
## 2.2FastCGI配置预处理

fastcgi_param配置的所有参数会会存储在ngx_http_fastcgi_loc_conf_t结构体的params_source字段；

nginx为了方便生成fastcgi请求数据，会提前对params_source做一些预处理，预先初始化号每个名—值对的长度以及数据拷贝方法等；

2.1节查看fastcgi模块提供的配置指令时发现，某些配置指令出现在location配置块，有些配置却可以出现http配置块、server配置块和location配置块；即可能出现同一个指令同时出现在好几个配置块中，此时如何解析配置？

对于这些配置指令，nginx最终会执行一个merge操作，合并多个配置为一个；观察nginx的HTTP模块，大多模块都会存在一个merge_loc_conf字段（函数指针），用于merge配置；

fastcgi模块的merge操作由ngx_http_fastcgi_merge_loc_conf完成，其同时对params_source进行了一些预处理；代码如下：

```c
static char * ngx_http_fastcgi_merge_loc_conf(ngx_conf_t *cf, void *parent, void *child)
{
    ngx_conf_merge_msec_value(conf->upstream.connect_timeout,
                          prev->upstream.connect_timeout, 60000);
    ngx_conf_merge_value(conf->upstream.pass_request_headers,
                          prev->upstream.pass_request_headers, 1);  //配置HTTP头部是否传递给FastCGI应用，默认为1
    ngx_conf_merge_value(conf->upstream.pass_request_body,
                          prev->upstream.pass_request_body, 1);     //配置HTTP body是否传递给FastCGI应用，默认为1
    …………
 
    if (ngx_http_fastcgi_merge_params(cf, conf, prev) != NGX_OK) {  //重点，merger并预处理传递给FastCGI应用的参数
    return NGX_CONF_ERROR;
    }
}
```

ngx_http_fastcgi_merge_params方法主要params_source做了一些预处理，主要处理逻辑如下：

注意：配置参数的名称以HTTP_开始时，此参数可能还是HTTP请求头，需要记录这些参数，以便传递HTTP请求头时排除掉。

```c
static ngx_int_t ngx_http_fastcgi_merge_params(ngx_conf_t *cf,
    ngx_http_fastcgi_loc_conf_t *conf, ngx_http_fastcgi_loc_conf_t *prev)
{
    if (conf->params_source) {
        src = conf->params_source->elts;
        nsrc = conf->params_source->nelts;
    }
 
    conf->params_len = ngx_array_create(cf->pool, 64, 1); //params_len用于计算参数名—值的长度
    conf->params = ngx_array_create(cf->pool, 512, 1);    //params用于名—值对数据内容的处理（拷贝）
      
    if (ngx_array_init(&headers_names, cf->temp_pool, 4, sizeof(ngx_hash_key_t)) != NGX_OK){ //存储以HTTP_开始的配置参数，hash表
        return NGX_ERROR;
    }
 
    for (i = 0; i < nsrc; i++) {
        //以HTTP_开始，存储在headers_names hash表
        if (src[i].key.len > sizeof("HTTP_") - 1 && ngx_strncmp(src[i].key.data, "HTTP_", sizeof("HTTP_") - 1) == 0){
            hk = ngx_array_push(&headers_names);
            hk->key.len = src[i].key.len - 5;
            hk->key.data = src[i].key.data + 5;
            hk->key_hash = ngx_hash_key_lc(hk->key.data, hk->key.len);
            hk->value = (void *) 1;
        }
 
 
        //ngx_http_script_copy_code_t结构体包含两个字段：code函数指针，用于计算参数名称的长度（方法内部直接返回了了len字段）；len是参数名称的长度
        copy = ngx_array_push_n(conf->params_len, sizeof(ngx_http_script_copy_code_t));
        copy->code = (ngx_http_script_code_pt) ngx_http_script_copy_len_code;
        copy->len = src[i].key.len;
 
        //这里的len表示参数是否必传；对于非必传参数，当此参数的值为空时，可以不传递此参数；（ngx_http_script_copy_len_code方法内部直接返回了了len字段，即skip_empty）
        copy = ngx_array_push_n(conf->params_len, sizeof(ngx_http_script_copy_code_t));
        copy->code = (ngx_http_script_code_pt) ngx_http_script_copy_len_code;
        copy->len = src[i].skip_empty;
 
        //ngx_http_script_copy_code_t结构体包含两个字段：code函数指针，实现参数名称内容的拷贝；len数参数名称的长度
        //空间大小为ngx_http_script_copy_code_t结构体长度，加参数名称的长度；最后再8字节对齐
        size = (sizeof(ngx_http_script_copy_code_t) + src[i].key.len + sizeof(uintptr_t) - 1) & ~(sizeof(uintptr_t) - 1);
        copy = ngx_array_push_n(conf->params, size);
        copy->code = ngx_http_script_copy_code;
        copy->len = src[i].key.len;
        //拷贝数据
        p = (u_char *) copy + sizeof(ngx_http_script_copy_code_t);
        ngx_memcpy(p, src[i].key.data, src[i].key.len);
 
        //params_len与params分别存储NULL，以实现存储空间的分隔；及参数与参数之间使用NULL进行隔离；
        code = ngx_array_push_n(conf->params_len, sizeof(uintptr_t));
        *code = (uintptr_t) NULL;
        code = ngx_array_push_n(conf->params, sizeof(uintptr_t));
        *code = (uintptr_t) NULL;
    }
 
    conf->header_params = headers_names.nelts; //以HTTP_开始的参数存储在conf的header_params与headers_hash字段
    hash.hash = &conf->headers_hash;
    ……
    return ngx_hash_init(&hash, headers_names.elts, headers_names.nelts);  
}
```

根据上面的代码逻辑，很容易画出params_len与params的内部存储结构：

![][1]

问题：参数是名—值对，这里的代码只对参数名称进行了预处理，参数的值呢？参数的值应该与请求相对应的，在解析配置文件时，并没有请求对应的信息，如何预处理参数的值呢？

一般fastcgi的参数是以下这些配置：

```c
fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
fastcgi_param  QUERY_STRING       $query_string;
fastcgi_param  REQUEST_METHOD     $request_method;
fastcgi_param  CONTENT_TYPE       $content_type;
fastcgi_param  CONTENT_LENGTH     $content_length;
…………
```

参数的值其实就是nginx提供的一系列可以直接使用变量（在ngx_http_variable.c文件中查找ngx_http_core_variables数组，即nginx提供的变量），每个变量都有一个索引值；

预处理fastcgi的配置参数时，其实只需要初始化参数值对应的变量索引即可；（注意参数的值可能是由多个nginx变量组合而成）

注意到ngx_http_fastcgi_merge_params方法中还有以下一段代码：

```c
for (i = 0; i < nsrc; i++) {
    sc.cf = cf;
    sc.source = &src[i].value;
    sc.flushes = &conf->flushes;
    sc.lengths = &conf->params_len;
    sc.values = &conf->params;
 
    if (ngx_http_script_compile(&sc) != NGX_OK) {
        return NGX_ERROR;
    }
}
```

我们看到sc的这些字段values（params）、lengths（params_len）、source（src[i].value，即参数的值）；ngx_http_script_compile可以对params和params_len字段进行修改；其实现如下：

```c
ngx_int_t ngx_http_script_compile(ngx_http_script_compile_t *sc)
{
    for (i = 0; i < sc->source->len; /* void */ ) {
         
        //针对$document_root$fastcgi_script_name这种配置，会执行两次
        if (sc->source->data[i] == '$') {
             
            if (ngx_http_script_add_var_code(sc, &name) != NGX_OK) { //name是变量名称
                return NGX_ERROR;
            }
        }
    }
}
 
//同一个参数，值可能由多个变量组合而成，同一个参数可能会调用此方法多次
static ngx_int_t ngx_http_script_add_var_code(ngx_http_script_compile_t *sc, ngx_str_t *name)
{
    index = ngx_http_get_variable_index(sc->cf, name); //获取变量的索引
 
    //ngx_http_script_var_code_t结构体包含两个字段：code函数指针，计算为变量长度（方法内部查找索引为index的变量，返回其长度）；index为变量索引
    code = ngx_http_script_add_code(*sc->lengths, sizeof(ngx_http_script_var_code_t), NULL);  //存储到lengths，即params_len
    code->code = (ngx_http_script_code_pt) ngx_http_script_copy_var_len_code;
    code->index = (uintptr_t) index;
 
    //ngx_http_script_var_code_t结构体包含两个字段：code函数指针，拷贝变量内容（方法内部查找索引为index的变量，拷贝变量内容）；index为变量索引
    code = ngx_http_script_add_code(*sc->values, sizeof(ngx_http_script_var_code_t), &sc->main);  //存储到values，即params
    code->code = ngx_http_script_copy_var_code;
    code->index = (uintptr_t) index;
 
    return NGX_OK;
}
```

最终params_len与params的内部存储结构入下图：

![][2]
## 3.构造FastCGI请求

方法ngx_http_fastcgi_create_request创建FastCGI请求，初始化请求内容（包括BEGIN_REQUEST、PARAMS和STDIN类型的请求消息）；
## 3.1FastCGI请求结构

FastCGI应用即为nginx的upstream，输出缓冲区的类型为ngx_chain_t，是由多个buf组成的链表

```c
struct ngx_chain_s {
    ngx_buf_t    *buf;
    ngx_chain_t  *next;
};
```

nginx将FastCGI请求分为三个部分，由三个buf链成一个ngx_chain_s；nginx构造的FastCGI请求结构如下图所示；

![][3]

其中第一部分主要包括fastcgi_param配置的参数以及HTTP请求的header，其他内容固定不变；第二部分是HTTP请求的body，其buf在解析HTTP请求时已经初始化好了，此处只需要将此buf添加到ngx_chain_s链中即可；第三部分内容固定；
## 3.2 计算请求第一部分长度

为第一部分分配buf时，首先需要计算buf所需空间的大小；第一部分空间分为fastcgi_param参数与HTTP请求header；计算方法见下文：

* 1）计算fastcgi_param参数所需空间大小：

```c
if (flcf->params_len) {
    ngx_memzero(&le, sizeof(ngx_http_script_engine_t));
 
    ngx_http_script_flush_no_cacheable_variables(r, flcf->flushes);
    le.flushed = 1;
 
    le.ip = flcf->params_len->elts;  //le.ip即为params_len存储的元素
    le.request = r;
 
    while (*(uintptr_t *) le.ip) { //循环计算索引参数key与value长度之和
 
        lcode = *(ngx_http_script_len_code_pt *) le.ip;   //key长度，lcode指向方法ngx_http_script_copy_len_code
        key_len = lcode(&le);
 
        lcode = *(ngx_http_script_len_code_pt *) le.ip;   //是否必传，lcode指向方法ngx_http_script_copy_len_code
        skip_empty = lcode(&le);
 
        for (val_len = 0; *(uintptr_t *) le.ip; val_len += lcode(&le)) { //value长度，lcode指向方法ngx_http_script_copy_var_len_code（注意value可能又多个值组合而成）
            lcode = *(ngx_http_script_len_code_pt *) le.ip;
        }
        le.ip += sizeof(uintptr_t);   //跳参数之间分割的NULL
 
        if (skip_empty && val_len == 0) {  //非必传参数，值为空时可跳过
            continue;
        }
 
        len += 1 + key_len + ((val_len > 127) ? 4 : 1) + val_len;
    }
}
```

* 2）HTTP请求header所需空间大小

```c
if (flcf->upstream.pass_request_headers) {  //是否需要向FastCGI应用传递header
 
    part = &r->headers_in.headers.part;
    header = part->elts;
 
    for (i = 0; /* void */; i++) {
        //header_params记录fastcgi_param是否配置了以HTTP_开始的参数，headers_hash存储此种类型的配置参数
        if (flcf->header_params) {  
            
 
            for (n = 0; n < header[i].key.len; n++) {
                ch = header[i].key.data[n];
 
                if (ch >= 'A' && ch <= 'Z') {
                    ch |= 0x20;
 
                } else if (ch == '-') {
                    ch = '_';
                }
 
                hash = ngx_hash(hash, ch);
                lowcase_key[n] = ch;
            }
            if (ngx_hash_find(&flcf->headers_hash, hash, lowcase_key, n)) { //查询此HTTP请求头是否已经由fastcgi_param指令配置；有则忽略此HTTP请求头
                ignored[header_params++] = &header[i];
                continue;
            }
 
            n += sizeof("HTTP_") - 1;  //请求头添加HTTP_前缀（n已经累加到header[i].key.len了）
 
        } else {
            n = sizeof("HTTP_") - 1 + header[i].key.len; //请求头添加HTTP_前缀
        }
 
        len += ((n > 127) ? 4 : 1) + ((header[i].value.len > 127) ? 4 : 1)
            + n + header[i].value.len;
    }
}
```

* 3）创建第一部分buf

```c
if (len > 65535) {
    return NGX_ERROR;
}
 
padding = 8 - len % 8;
padding = (padding == 8) ? 0 : padding;
 
size = sizeof(ngx_http_fastcgi_header_t)
       + sizeof(ngx_http_fastcgi_begin_request_t)
 
       + sizeof(ngx_http_fastcgi_header_t)  /* NGX_HTTP_FASTCGI_PARAMS */
       + len + padding
       + sizeof(ngx_http_fastcgi_header_t)  /* NGX_HTTP_FASTCGI_PARAMS */
 
       + sizeof(ngx_http_fastcgi_header_t); /* NGX_HTTP_FASTCGI_STDIN */
 
 
b = ngx_create_temp_buf(r->pool, size);
cl = ngx_alloc_chain_link(r->pool);
cl->buf = b;
```
## 3.3填充请求第一部分

nginx的缓冲区buf主要关注以下四个字段：

```c
struct ngx_buf_s {
    u_char          *pos;   //当buf所指向的数据在内存里的时候，pos指向的是这段数据开始的位置
    u_char          *last; //当buf所指向的数据在内存里的时候，last指向的是这段数据结束的位置
    off_t            file_pos; //当buf所指向的数据是在文件里的时候，file_pos指向的是这段数据的开始位置在文件中的偏移量
    off_t            file_last;//当buf所指向的数据是在文件里的时候，file_last指向的是这段数据的结束位置在文件中的偏移量
```

* 1）填充fastcgi_param参数

```c
if (flcf->params_len) {
 
    e.ip = flcf->params->elts;  //e.ip是params
    e.pos = b->last;
    le.ip = flcf->params_len->elts; ////le.ip是params_len
 
    while (*(uintptr_t *) le.ip) {
 
        lcode = *(ngx_http_script_len_code_pt *) le.ip; //key的长度
        key_len = (u_char) lcode(&le);
 
        lcode = *(ngx_http_script_len_code_pt *) le.ip; //是否必传
        skip_empty = lcode(&le);
 
        for (val_len = 0; *(uintptr_t *) le.ip; val_len += lcode(&le)) { //value的长度
            lcode = *(ngx_http_script_len_code_pt *) le.ip;
        }
        le.ip += sizeof(uintptr_t);
 
        if (skip_empty && val_len == 0) { //跳过
           …………
        }
 
        *e.pos++ = (u_char) key_len; //填充key_len
        //填充value_len
        if (val_len > 127) {
            *e.pos++ = (u_char) (((val_len >> 24) & 0x7f) | 0x80);
            *e.pos++ = (u_char) ((val_len >> 16) & 0xff);
            *e.pos++ = (u_char) ((val_len >> 8) & 0xff);
            *e.pos++ = (u_char) (val_len & 0xff);
 
        } else {
            *e.pos++ = (u_char) val_len;
        }
        //填充key和value的数据内容；key的填充方法为ngx_http_script_copy_code，value的填充方法ngx_http_script_copy_var_code，
        while (*(uintptr_t *) e.ip) {
            code = *(ngx_http_script_code_pt *) e.ip;
            code((ngx_http_script_engine_t *) &e);
        }
        e.ip += sizeof(uintptr_t); //跳过参数之间分割的NULL  
    }
 
    b->last = e.pos;
}
```

* 2）填充HTTP请求头

```c
if (flcf->upstream.pass_request_headers) {
 
    part = &r->headers_in.headers.part;
    header = part->elts;
 
    for (i = 0; /* void */; i++) {
 
        for (n = 0; n < header_params; n++) {  //上一步计算长度时，会记录跳过的header在ignored；填充阶段直接跳过
            if (&header[i] == ignored[n]) {
                goto next;
            }
        }
 
        key_len = sizeof("HTTP_") - 1 + header[i].key.len;   //填充key长度
        if (key_len > 127) {
            *b->last++ = (u_char) (((key_len >> 24) & 0x7f) | 0x80);
            *b->last++ = (u_char) ((key_len >> 16) & 0xff);
            *b->last++ = (u_char) ((key_len >> 8) & 0xff);
            *b->last++ = (u_char) (key_len & 0xff);
 
        } else {
            *b->last++ = (u_char) key_len;
        }
 
        val_len = header[i].value.len;   //填充value长度
        if (val_len > 127) {
            *b->last++ = (u_char) (((val_len >> 24) & 0x7f) | 0x80);
            *b->last++ = (u_char) ((val_len >> 16) & 0xff);
            *b->last++ = (u_char) ((val_len >> 8) & 0xff);
            *b->last++ = (u_char) (val_len & 0xff);
 
        } else {
            *b->last++ = (u_char) val_len;
        }
 
        b->last = ngx_cpymem(b->last, "HTTP_", sizeof("HTTP_") - 1); //填充HTTP_前缀
 
        for (n = 0; n < header[i].key.len; n++) {   //填充key数据内容
            ch = header[i].key.data[n];
 
            if (ch >= 'a' && ch <= 'z') {
                ch &= ~0x20;
 
            } else if (ch == '-') {
                ch = '_';
            }
 
            *b->last++ = ch;
        }
 
        b->last = ngx_copy(b->last, header[i].value.data, val_len);  //填充value数据内容
    next:
        continue;
    }
}
```
## 3.4填充请求第二三部分

HTTP请求的body同样存储在ngx_chain_t结构中，nginx需要遍历链表的所有buf，构造fastcgi的请求数据；

注意：nginx构造fastcgi请求时，第二部分请求（http_body）的长度最长为32K，当超过此限制时，HTTP请求体会被分割为多个http_body请求；入下图所示：

![][4]

```c
do {
    b = ngx_alloc_buf(r->pool);
    
    b->pos = pos;
    pos += 32 * 1024;
 
    if (pos >= body->buf->last) { //数据小于32k，next赋值为1，结束while循环；否则就切割为了32K大小的数据包
        pos = body->buf->last;
        next = 1;
    }
 
    b->last = pos;
    len = (ngx_uint_t) (pos - b->pos);
 
    padding = 8 - len % 8;
    padding = (padding == 8) ? 0 : padding;
 
    cl->next = ngx_alloc_chain_link(r->pool);
    cl = cl->next;  //添加http_body请求包到buf链表中
    cl->buf = b;  
 
    …………
    b = ngx_create_temp_buf(r->pool, sizeof(ngx_http_fastcgi_header_t) + padding);
    cl->next = ngx_alloc_chain_link(r->pool);
    cl = cl->next;   //添加padding与header请求包到buf链表中
    cl->buf = b;
 
} while (!next);
```
## 4.实战
## 4.1配置

nginx配置如下：

```nginx
http{
    …………
    fastcgi_connect_timeout 300;
    fastcgi_send_timeout 300;
    fastcgi_read_timeout 300;
 
    server {
        listen       80;
        server_name  localhost;
        root /home/xiaoju;
        index index.php index.html;
 
        location / {
            fastcgi_index index.php;
            fastcgi_pass 127.0.0.1:9000;
            include fastcgi.conf;
        }
    }
}
 
fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
fastcgi_param  QUERY_STRING       $query_string;
fastcgi_param  REQUEST_METHOD     $request_method;
fastcgi_param  CONTENT_TYPE       $content_type;
fastcgi_param  CONTENT_LENGTH     $content_length;
………………
```

编写PHP脚本，只是简单的将post入参返回即可：

```php
<?php
foreach($_POST as $key=>$v){
    $ret['ret-'.$key] = 'ret-'.$v;
}
echo json_encode($ret);
```
## 4.2FastCGI请求包

我们GDB nginx worker进程；

注意：为了方便调试，nginx配置文件中，worker_processes配置为1，即只能存在一个work进程。

查看FastCGI请求参数，在ngx_http_fastcgi_create_request方法添加断点，执行到函数最后一行（此时请求数据已经构造完成），输出数据存储在表达式r->upstream->request_bufs表示的缓冲区；

查看FastCGI应用（php-fpm）返回的数据，在ngx_http_fastcgi_process_record方法添加断点，方法入参ngx_http_fastcgi_ctx_t的pos和last分别指向读入数据的开始与结尾，此方法杜泽解析读入数据；

添加断点如下：

```
Num     Type           Disp Enb Address            What
1       breakpoint     keep y   0x0000000000418f05 in ngx_process_events_and_timers at src/event/ngx_event.c:203 inf 3, 2, 1
    breakpoint already hit 17 times
2       breakpoint     keep y   0x000000000045b7fa in ngx_http_fastcgi_create_request at src/http/modules/ngx_http_fastcgi_module.c:735 inf 3, 2, 1
    breakpoint already hit 4 times
3       breakpoint     keep y   0x000000000045c2af in ngx_http_fastcgi_create_request at src/http/modules/ngx_http_fastcgi_module.c:1190 inf 3, 2, 1
    breakpoint already hit 4 times
4       breakpoint     keep y   0x000000000045a573 in ngx_http_fastcgi_process_record at src/http/modules/ngx_http_fastcgi_module.c:2145 inf 3, 2, 1
    breakpoint already hit 1 time
```

执行到ngx_http_fastcgi_create_request函数结尾（断点3），打印r->upstream->request_bufs三个buf：

注意：gdb使用命令p打印字符串时，需设置set print element 0才不会省略部分字符串，否则字符串不会打印完全；@符号表示打印多少个字符（fastcgi请求时二进制数据，不能依据0判断结尾）；

字符串显示时，显示‘222’时，为8进制表示，需转换为10进制计算才行；

```
(gdb) p *r->upstream->request_bufs->buf->pos@1000
$18 =
\001\001\000\001\000\b\000\000                  //8字节头部，type=1（BEGIN_REQUEST）
\000\001\000\000\000\000\000\000                //8字节BEGIN_REQUEST数据包
\001\004\000\001\002\222\006\000                //8字节头部，type=4（PARAMS），数据内容长度=2*256+146=658(不是8字节整数倍，需要填充6个字节)
\017\025SCRIPT_FILENAME/home/xiaoju/test.php    //key-value，格式为：keylen+valuelen+key+value
\f\000QUERY_STRING\016\004REQUEST_METHODPOST
\f!CONTENT_TYPEapplication/x-www-form-urlencoded
\016\002CONTENT_LENGTH19
\v\tSCRIPT_NAME/test.php
\v\nREQUEST_URI//test.php
\f\tDOCUMENT_URI/test.php
\r\fDOCUMENT_ROOT/home/xiaoju
\017\bSERVER_PROTOCOLHTTP/1.1
\021\aGATEWAY_INTERFACECGI/1.1
\017\vSERVER_SOFTWAREnginx/1.6.2
\v\tREMOTE_ADDR127.0.0.1
\v\005REMOTE_PORT54276
\v\tSERVER_ADDR127.0.0.1
\v\002SERVER_PORT80
\v\tSERVER_NAMElocalhost
\017\003REDIRECT_STATUS200
\017dHTTP_USER_AGENTcurl/7.19.7 (x86_64-redhat-linux-gnu) libcurl/7.19.7 NSS/3.27.1 zlib/1.2.3 libidn/1.18 libssh2/1.4.2
\t\tHTTP_HOSTlocalhost
\v\003HTTP_ACCEPT*/*
\023\002HTTP_CONTENT_LENGTH19
\021!HTTP_CONTENT_TYPEapplication/x-www-form-urlencoded
\000\000\000\000\000\000           //6字节内容填充
\001\004\000\001\000\000\000\000   //8字节头部，type=4（PARAMS），表示PARAMS请求结束
\001\005\000\001\000\023\005\000   //8字节头部，type=5（STDIN），请求体数据长度19个字节
 
 
(gdb) p *r->upstream->request_bufs->next->buf->pos@20
$19 = "name=hello&gender=1"   //HTTP请求体，长度19字节，需填充5个字节
 
 
(gdb) p *r->upstream->request_bufs->next->next->buf->pos@20
$20 =
\000\000\000\000\000            //5字节填充
\001\005\000\001\000\000\000    //8字节头部，type=5（STDIN），表示STDIN请求结束
```

执行到方法ngx_http_fastcgi_process_record，打印读入请求数据：

```
p *f->pos@1000
$26 =
\001\006\000\001\000\377\001\000  //8字节头部，type=6（STDOUT），返回数据长度为255字节（需要填充1个字节）
Set-Cookie: PHPSESSID=3h9lmb2mvp6qlk1rg11id3akd3; path=/\r\n    //返回数据内容，以换行符分隔
Expires: Thu, 19 Nov 1981 08:52:00 GMT\r\n
Cache-Control: no-store, no-cache, must-revalidate\r\n
Pragma: no-cache\r\n
Content-type: text/html; charset=UTF-8\r\n
\r\n
{\"ret-name\":\"ret-hello\",\"ret-gender\":\"ret-1\"}
\000
\001\003\000\001\000\b\000\000   //8字节头部，type=3（END_REQUEST），表示fastcgi请求结束，数据长度为8
\000\000\000\000\000\000\000\000    //8字节END_REQUEST数据
```

返回数据包见下图：

![][5]

END_REQUEST body数据体8个字节，其定义可以在php源码中查看：

```c
typedef struct _fcgi_end_request {
    unsigned char appStatusB3;   ////结束状态，0为正常
    unsigned char appStatusB2;
    unsigned char appStatusB1;
    unsigned char appStatusB0;
    unsigned char protocolStatus;  //为协议所处的状态，0为正常状态
    unsigned char reserved[3];
} fcgi_end_request;
```
## 总结

本文通过分析ngx_http_fastcgi_module模块构造FastCGI请求的代码，学习FastCGI协议格式，并通过GDB打印FastCGI请求与相应数据，以此对FastCGI协议有了更直观的理解。

[0]: ./img/bVbdXAi.png
[1]: ./img/bVbhFhM.png
[2]: ./img/bVbhFif.png
[3]: ./img/bVbhFir.png
[4]: ./img/bVbhFiL.png
[5]: ./img/bVbhFjv.png