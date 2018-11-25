## Nginx if 指令工作原理

来源：[https://www.kawabangga.com/posts/3239](https://www.kawabangga.com/posts/3239)

时间 2018-11-18 14:28:46


Nginx 的`if`指令被认为是“邪恶”的，就和 C 语言的`goto`一样。甚至官方有一篇 [If is Evial][0] 来警告你不要使用`if`。但有时候 if 还是非常有用的，如果掌握了它的原理，在合适的地方正确使用 if，会让事情更简单一些。当然前提是你真正知道自己在做什么，就和 goto 一样。 ——这是我的观点。

首先每个接触 Nginx 的人应该意识到的事情是，[Nginx 是分 phase(阶段)][1]的，并不像 C 这种编程语言一样顺序执行。指令执行的顺序和书写的顺序没有太大关系（跟具体模块的实现有关），一个 phase 执行完了就会执行到下一个阶段。

If 是属于 rewrite 模块的，所以对于 if 来讲，会和其他的 rewrite 模块执行全部执行完之后再进行下一阶段。如果 if 指令的结果是 match 的，那么 if 会创建一个内嵌的 location 块，只有这里面的 content 处理指令（NGX_HTTP_CONTENT_PHASE 阶段）会执行。

下面是 agentzh 的四个例子，我这里稍加自己的解释。

```nginx
  location /proxy {
      set $a 32;
      if ($a = 32) {
          set $a 56;
      }
      set $a 76;

      proxy_pass http://127.0.0.1:$server_port/$a;
  }
 
  location ~ /(\d+) {
      echo $1;
  }


```

实验的机器 IP 是 172.28.128.4 ，结果如下：

```
➜  nginx git:(master) ✗ curl 172.28.128.4/proxy
76


```

首先，对于一个请求 Nginx 会执行 rewrite 阶段，即如下代码。rewrite 阶段的执行顺序和指令的顺序是一样的，这个 rewrite 模块的实现有关。

```nginx
      set $a 32;
      if ($a = 32) {
          set $a 56;
      }
      set $a 76;


```
`$a`被设为 32，然后进入 if block，`$a`在这里被设为 56，最后`$a`被设为 76. 中间 if block 生效。但是 if block 中没有任何 content 阶段的指令，所以会继承 outter block，即`ngx_proxy`模块的`proxy_pass`设置。这里要注意的是请求在 if block 内完成，if 命中之后就进入了 if block 来处理下一阶段，而不会跳出 if。

第二段示例如下：

```nginx
  location /proxy {
      set $a 32;
      if ($a = 32) {
          set $a 56;
          echo "a = $a";
      }
      set $a 76;
      proxy_pass http://127.0.0.1:$server_port/$a;
  }
 
  location ~ /(\d+) {
      echo $1;
  }


```

结果如下：

```
➜  nginx git:(master) curl 172.28.128.4/proxy
a = 76


```

Rewrite 阶段的过程和上面一样，不同是这一次 if block 中有了 content 阶段的指令，所以会执行 echo，不会执行到`proxy_pass`。

Rewrite 阶段的`break`可以终止 rewrite 阶段的执行。

```nginx
  location /proxy {
      set $a 32;
      if ($a = 32) {
          set $a 56;
          break;
 
          echo "a = $a";
      }
      set $a 76;
      proxy_pass http://127.0.0.1:$server_port/$a;
  }
 
  location ~ /(\d+) {
      echo $1;
  }


```

以上代码的结果是

```
➜  nginx git:(master) curl 172.28.128.4/proxy
a = 56


```

在 rewrite 阶段中，执行完`if ($a = 32)`之后执行`set $a 56`，此时下一行是`break`，然后 rewrite 阶段就停止了，进行下一阶段。`set $a 76`并没有被执行到。所以最后`$a`的值是 76。

ngx_proxy 会继承 outter scope，但是很多模块并不会这样，这个地方挺坑人的，我就是在这里被坑到的。

```nginx
location /proxy {
     set $a 32;
     if ($a = 32) {
         echo "python";
     }
     echo "hello";
     echo "java";
}


```

参考这段配置，正常来说，所有的`echo`都会执行，即如果不存在`if`的话，这段配置的结果应该是`hello \n java`。但是这里结果会是:

```
➜  nginx git:(master) ✗ curl 172.28.128.4/proxy
python


```

可以看到`echo`并没有继承 outter 。

顺便说一下我写的那段配置吧。简化之后如下：

```nginx
location /proxy {
     if ($request_method = POST) {
         access_by_lua 'lua code...';
     }
     access_by_lua_file file/location;
}


```

我期望如果进 if 和不进 if，都会执行我的`access_by_lua_file`，但事实看来，进入 if 之后并不会再出来，而且`access_by_lua`和`access_by_lua_file`像 echo 一样，if 内并不会继承外面的`access_by_lua_file`。所以如果 if 命中，那么`access_by_lua_file`永远不会执行到。

最后一个例子是会继承 outter 的：

```nginx
  location /proxy {
      set $a 32;
      if ($a = 32) {
          return 404;
      }
      set $a 76;
      proxy_pass http://127.0.0.1:$server_port/$a;
      more_set_headers "X-Foo: $a";
  }
 
  location ~ /(\d+) {
      echo $1;
  }


```

结果如下：

```
  $ curl 172.28.128.4/proxy
  HTTP/1.1 404 Not Found
  Server: nginx/0.8.54 (without pool)
  Date: Mon, 14 Feb 2011 05:24:00 GMT
  Content-Type: text/html
  Content-Length: 184
  Connection: keep-alive
  X-Foo: 32


```

可以看到，这个模块的 more_set_headers 指令是默认继承 outter 的。

所以，官方给出的建议是尽量不要使用 if 指令，比如说有些地方其实可以使用[try_files][2]。

如果用，那么尽量只在 if block 内使用 rewrite 模块的指令。因为大家都是在这一个 phase 里面的，不会有 surprise 了。

在某些情况下，这些需要 if 的指令可以用嵌入的第三方模块来完成，比如[ngx_lua][3][perl][4]等。

实在要用的话，做好充足的测试。


[0]: https://www.nginx.com/resources/wiki/start/topics/depth/ifisevil/
[1]: https://openresty.org/download/agentzh-nginx-tutorials-zhcn.html#02-NginxDirectiveExecOrder01
[2]: https://www.nginx.com/resources/wiki/start/topics/depth/ifisevil/#what-to-do-instead
[3]: https://github.com/openresty/lua-nginx-module
[4]: http://nginx.org/en/docs/http/ngx_http_perl_module.html