### nginx的access log中记录post请求的参数


常见的nginx配置中access log一般都只有GET请求的参数，而POST请求的参数却不行。http://wiki.nginx.org/NginxHttpCoreModule#.24request_body

    $request_body

> This variable(0.7.58+) contains the body of the request. The significance of this variable appears in locations with directives proxy_pass or fastcgi_pass.

正如上文件所示，只需要使用`$request_body`即可打出post的数据，在现存的server段加上下面的设置即可：


    log_format access '$remote_addr - $remote_user [$time_local] "$request" $status $body_bytes_sent $request_body "$http_referer" "$http_user_agent" $http_x_forwarded_for';
    access_log logs/test.access.log access;
