# 解决nginx在记录post数据时 中文字符转成16进制的问题

作者  [lework][0] 已关注 2016.11.10 21:49  字数 1094  阅读 1265 评论 8 喜欢 4

## 1. 问题描述

nginx 在获取post数据时候，如果是中文，则转换成16进制显示在日志文件中，如下图所示。

![][1]



Paste_Image.png

日志格式为： log_format postdata '$remote_addr | $request_body | $resp_body';此篇文章记录下解决此次问题的过程。

## 2. 软件版本

* 系统 centos 6.7 X86_64
* nginx 1.11.5
* lua-nginx-module 0.10.7
* PHP 5.6.27

测试环境部署见：[Nginx 使用lua-nginx-module 来获取post请求中得request和response信息][2]

## 3. 收集信息

###### 收集信息-阶段1：

> 在遇到此类问题的时候，我们大多是使用搜索引擎搜索答案，因为这样来的更快一些。当遇到这个问题的时候，我感觉也无从下手，随即在google中搜索答案，没过多久，便找到了同类人，也遇到了这个问题

此次搜索关键字： nginx log 中文 16进制

![][12]

Paste_Image.png

出处：[https://groups.google.com/forum/#!topic/openresty/PYvvfj5RKCg][3]

这个里面提到了：

* 为什么会出现这个问题？
* 解决办法

当时情况，在大量的搜索结果下，刚开始没注意到这里面的问题，认为这个是openresty的解决办法。就继续搜索信息了。

###### 收集信息-阶段2：

> 经过上面得信息，我们可以得知，nginx现在是把中文字符转换成16进制。

所以关键字变成了：nginx 不支持中文

从这个关键字便发现了下面得信息

![][4]



Paste_Image.png

来自： [http://navyaijm.blog.51cto.com/4647068/1082169][5]

从这里面获得了：

    -  通过降级nginx来解决问题

这位博主通过过降级nginx 程序来达到支持中文得效果，当时目测这文章是2012年得，比较久远，而且还需要降级，就没有尝试这类方法。

###### 信息收集-阶段3：

> 这次搜索解决答案也有一段时间了，突然想起了阶段1时发现得解决方法，里面有个命令可以关闭nginx转换16进制得命令。随即搜索关键字改成： > nginx log escape characters通过这个关键字找到了下列有用信息。

![][6]



Paste_Image.png

来自： [http://mailman.nginx.org/pipermail/nginx/2008-January/003051.html][7]

从这里面获得了：

* 在2008年得时候，通过这个path，让不可打印得字符转成16进制。
* attachment.bin 文件记录了是哪个源代码文件的补丁。

通过查看这个文件，发现了 ngx_http_log_escape 这函数是转换16进制的。要知道nginx源代码已经被很多国人都阅读过，肯定有相关的解释。

随即关键字变成了： nginx ngx_http_log_escape通过搜索发现了下列的源码解释

```c
    static uintptr_t
    ngx_http_log_escape(u_char *dst, u_char *src, size_t size)
    {
        ngx_uint_t      n;
        /* 这是十六进制字符表 */
        static u_char   hex[] = "0123456789ABCDEF";
    
        /* 这是ASCII码表，每一位表示一个符号，其中值为1表示此符号需要转换，值为0表示不需要转换 */
        static uint32_t   escape[] = {
            0xffffffff, /* 1111 1111 1111 1111  1111 1111 1111 1111 */
    
                        /* ?>=< ;:98 7654 3210  /.-, +*)( '&%$ #"!  */
            0x00000004, /* 0000 0000 0000 0000  0000 0000 0000 0100 */
    
                        /* _^]\ [ZYX WVUT SRQP  ONML KJIH GFED CBA@ */
            0x10000000, /* 0001 0000 0000 0000  0000 0000 0000 0000 */
    
                        /*  ~}| {zyx wvut srqp  onml kjih gfed cba` */
            0x80000000, /* 1000 0000 0000 0000  0000 0000 0000 0000 */
    
            0xffffffff, /* 1111 1111 1111 1111  1111 1111 1111 1111 */
            0xffffffff, /* 1111 1111 1111 1111  1111 1111 1111 1111 */
            0xffffffff, /* 1111 1111 1111 1111  1111 1111 1111 1111 */
            0xffffffff, /* 1111 1111 1111 1111  1111 1111 1111 1111 */
        };
    
    
        if (dst == NULL) {
    
            /* find the number of the characters to be escaped */
    
            n = 0;
    
            while (size) {
                if (escape[*src >> 5] & (1 << (*src & 0x1f))) {
                    n++;
                }
                src++;
                size--;
            }
    
            return (uintptr_t) n;
            /* 返回需要转换的字符总数*/
        }
    
        while (size) {
             /* escape[*src >> 5],escape每一行保存了32个符号，
             所以右移5位，即除以32就找到src对应的字符保存在escape的行，
             (1 << (*src & 0x1f))此符号在escape一行中的位置，
             相&结果就是判断src符号位是否为1，需不需要转换 */
            if (escape[*src >> 5] & (1 << (*src & 0x1f))) {
                *dst++ = '\\';
                *dst++ = 'x';
                /* 一个字符占一个字节8位，每4位转成一个16进制表示 */
                /* 高4位转换成16进制 */
                *dst++ = hex[*src >> 4];
                /* 低4位转换成16进制*/
                *dst++ = hex[*src & 0xf];
                src++;
    
            } else {
                /* 不需要转换的字符直接赋值 */
                *dst++ = *src++;
            }
            size--;
        }
    
        return (uintptr_t) dst;
    }
```
感谢大神：[http://blog.csdn.net/l09711/article/details/46712325][8]

从上面解释来看，我们只需要*src不转换16进制就可以。

## 4. 解决方法

源码文件为：src/http/modules/ngx_http_log_module.c修改源码如下图所示，

![][9]



Paste_Image.png

然后重新编译，安装nginx

    ./configure   --prefix=/usr/local/nginx   --user=nginx   --group=nginx   --with-http_ssl_module   --with-http_flv_module   --with-http_stub_status_module   --with-http_gzip_static_module   --with-http_realip_module   --http-client-body-temp-path=/var/tmp/nginx/client/   --http-proxy-temp-path=/var/tmp/nginx/proxy/   --http-fastcgi-temp-path=/var/tmp/nginx/fcgi/   --http-uwsgi-temp-path=/var/tmp/nginx/uwsgi   --http-scgi-temp-path=/var/tmp/nginx/scgi   --with-pcre --add-module=../lua-nginx-module-0.10.7
     /usr/local/nginx/sbin/nginx -s stop
    make -j2 && make install
    /usr/local/nginx/sbin/nginx

再次post 数据到nginx里

![][10]



Paste_Image.png

查看日志会发现中文不在转换16进制了。

![][11]



Paste_Image.png

第1-2行，是没有修改源码前，向nginx url post数据，中文被转换成16进制。  
第3-5行，修改源码后，中文就不会转换为16进制了。也没有什么乱码。

> 至此，遇到得问题已解决，在修改源码得情况下，目前还没有发现什么影响之处，如由朋友发现，请联系我lework[@]yeah.net

## 5. 总结

在遇到错误得时候，我们往往不知道该怎么搜索此类答案，我想大家应该都会把错误信息放在搜索引擎中搜索，关键字要随着搜索得到的信息从而不断变化，才能往根源得问题靠近。在搜索引擎给出的大量信息，要懂得抓取有用的信息，不能忽视已经给出问题答案的信息，即使信息比较久远。像阶段1得情况，我如果仔细阅读上面得解答信息，应该会很快得找到问题所在的根源。

[0]: /u/ace85431b4bb
[1]: ../img/3629406-258e95a7aa96d037.png
[2]: http://www.jianshu.com/p/78853c58a225
[3]: https://groups.google.com/forum/#!topic/openresty/PYvvfj5RKCg
[4]: ../img/3629406-5ee329c72e7c56eb.png
[5]: http://navyaijm.blog.51cto.com/4647068/1082169
[6]: ../img/3629406-48dd738387dafa3d.png
[7]: http://mailman.nginx.org/pipermail/nginx/2008-January/003051.html
[8]: http://blog.csdn.net/l09711/article/details/46712325
[9]: ../img/3629406-780384b318f0b920.png
[10]: ../img/3629406-39914a00031a685e.png
[11]: ../img/3629406-917ef01b22b63f06.png
[12]: ../img/3629406-eee916903c9419df.png