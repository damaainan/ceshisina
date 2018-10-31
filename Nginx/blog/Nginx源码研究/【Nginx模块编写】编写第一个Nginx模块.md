## 【Nginx模块编写】编写第一个Nginx模块

来源：[https://segmentfault.com/a/1190000016856451](https://segmentfault.com/a/1190000016856451)

运营研发团队 季伟滨
## 模块名：ngx_http_jiweibin_module

## 1、建立模块源码目录

mkdir /data/code/c/nginx-1.6.2/src/plugin
## 2、新建config文件

vim /data/code/c/nginx-1.6.2/src/plugin/config，写入如下配置：

```cfg
ngx_addon_name=ngx_http_jiweibin_module
HTTP_MODULES="$HTTP_MODULES ngx_http_jiweibin_module"
NGX_ADDON_SRCS="$NGX_ADDON_SRCS $ngx_addon_dir/ngx_http_jiweibin_module.c"
```
## 3、新建ngx_http_jiweibin_module.c

```c
#include <ngx_config.h>
#include <ngx_core.h>
#include <ngx_http.h>
 
static char *ngx_http_jiweibin_cmd_set(ngx_conf_t *cf,ngx_command_t *cmd,void *conf);
static ngx_int_t ngx_http_jiweibin_handler(ngx_http_request_t *r);
 
static ngx_command_t ngx_http_jiweibin_commands[] = {
    {
        ngx_string("jiweibin"),
        NGX_HTTP_MAIN_CONF|NGX_HTTP_SRV_CONF|NGX_HTTP_LOC_CONF|NGX_HTTP_LMT_CONF|NGX_CONF_NOARGS,
        ngx_http_jiweibin_cmd_set,
        NGX_HTTP_LOC_CONF_OFFSET,
        0,
        NULL
    },
    ngx_null_command   
};
 
static char * ngx_http_jiweibin_cmd_set(ngx_conf_t *cf,ngx_command_t *cmd,void *conf){
    ngx_http_core_loc_conf_t *clcf;
         
    clcf = ngx_http_conf_get_module_loc_conf(cf,ngx_http_core_module);
    clcf->handler = ngx_http_jiweibin_handler;
    return NGX_CONF_OK;
}
static ngx_http_module_t ngx_http_jiweibin_module_ctx = {
    NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL
}; 
 
ngx_module_t ngx_http_jiweibin_module = {
    NGX_MODULE_V1,
    &ngx_http_jiweibin_module_ctx,
    ngx_http_jiweibin_commands,
    NGX_HTTP_MODULE,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NULL,
    NGX_MODULE_V1_PADDING
};
static ngx_int_t ngx_http_jiweibin_handler(ngx_http_request_t *r){
    if(!(r->method & (NGX_HTTP_GET|NGX_HTTP_POST))){
        return NGX_HTTP_NOT_ALLOWED;
    }  
    ngx_int_t rc = ngx_http_discard_request_body(r);
    if(rc != NGX_OK){
        return rc;
    }
    ngx_str_t content_type = ngx_string("text/plain");
    ngx_str_t response = ngx_string("hello world");
    r->headers_out.status = NGX_HTTP_OK;
    r->headers_out.content_length_n = response.len;
    r->headers_out.content_type = content_type;
    rc = ngx_http_send_header(r);
    if(rc == NGX_ERROR || rc > NGX_OK){
        return rc;
    }  
    ngx_buf_t *b;
    b = ngx_create_temp_buf(r->pool,response.len);
    if(b == NULL){
        return NGX_HTTP_INTERNAL_SERVER_ERROR;
    }
    ngx_memcpy(b->pos,response.data,response.len);
    b->last = b->pos + response.len;
    b->last_buf = 1;
    ngx_chain_t out;
    out.buf = b;
    out.next = NULL;
    return ngx_http_output_filter(r,&out);
}
```
## 4、configure

```
cd /data/code/c/nginx-1.6.2

./configure --prefix=/home/xiaoju/nginx-jiweibin --add-module=/data/code/c/nginx-1.6.2/src/plugin/
```
## 5、make & make install

![][0]

![][1]
## 6、配置nginx

![][2]
## 7、杀死旧的nginx进程

![][3]
## 8、启动新编译的带有插件的nginx

/home/xiaoju/nginx-jiweibin/sbin/nginx  -c /home/xiaoju/nginx-jiweibin/conf/nginx.conf

![][4]
## 9、验证自己写的插件

[http://10.179.195.72][6]:8080/hello

![][5]

[6]: http://10.179.195.72
[0]: ./img/bVbiTh7.png
[1]: ./img/bVbiTh9.png
[2]: ./img/bVbiTia.png
[3]: ./img/bVbiTid.png
[4]: ./img/bVbiTii.png
[5]: ./img/bVbiTij.png