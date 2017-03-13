# 一些nginx配置 

* [一些nginx配置][0]
  * [使用独立目录, 然后include具体配置][1]
    * [gzip on][2]
  * [for multi processers][3]
  * [static file cache][4]
  * [proxy pass][5]
  * [静态目录 or 文件][6]
  * [静态站][7]
  * [服务转发][8]
  * [return][9]
  * [for mobile][10]
  * [redirect to www][11]
  * [allow and deny][12]
  * [负载均衡][13]
  * [centos service cmds][14]

nginx配置

开年第一篇, 梳理笔记本中....

没啥, 一些用到以及后面可能用到的nginx配置, 记录一下, 备查备用

ps. 之前一篇 [nginx基础笔记][15]

## 使用独立目录, 然后include具体配置

目录

    nginx.conf
    site/
        a.conf
        b.conf
    

nginx.conf

    http {
    
        .......
        include /etc/nginx/conf.d/*.conf;
        include sites/*.conf;
    }
    

### gzip on

加到http模块中, 开启gzip, 注意gzip_types配置得是压缩的资源类型

nginx.conf

    http {
    
    
        .....
    
    
        gzip on;
        gzip_min_length 1k;
        gzip_comp_level 5;
        gzip_proxied expired no-cache no-store private auth;
        gzip_types text/plain text/css application/javascript text/javascript application/x-javascript text/xml application/xml application/xml+rss application/json image/x-icon image/png image/jpg image/jpeg application/font-woff;
        gzip_vary on;
    }
    

## for multi processers

nginx.conf

    worker_processes  4;
    events {
        worker_connections  2048;
        use epoll;
        multi_accept on;
    }
    
    worker_rlimit_nofile 100000;
    

## static file cache

        location ~* \.(?:css|js)$ {
          expires 12h;
          access_log off;
          add_header Cache-Control "public";
          proxy_pass http://127.0.0.1:5000;
          proxy_redirect off;
        }

## proxy pass

        location /
        {
            proxy_pass http://127.0.0.1:8000;
            proxy_pass_header Server;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header X-Scheme $scheme;
            proxy_set_header Host $http_host;
            proxy_redirect off;
        }

可以设置超时时间

            proxy_connect_timeout 500s;
            proxy_read_timeout 500s;
            proxy_send_timeout 500s;
    

## 静态目录 or 文件

        location /movies/ {
            alias /Volumes/Media/Movies/;
            allow all;
        }
    
        location = /abc.txt {
            alias /data/www/static/abc.txt;
            expires  30d;
            access_log off;
        }
    

## 静态站

    server {
        listen       192.168.1.1:80;
        server_name  www.abc.com;
    
        client_max_body_size 1M;
        access_log logs/blog_access.log;
        error_log logs/blog_error.log;
    
        root /data/static_site_dir;
        index index.html;
    
    }

## 服务转发

将收到的服务url/参数等, 原封不动转给另一个服务

    server {
        listen 80;
        server_name  www.xxxx.com;
        location ~ ^/(.*)$ {
            proxy_pass http://192.168.1.1:80/another/service/$1$is_args$args;
            proxy_read_timeout 90;
        }
    }

## return

直接return语法

    return http_code;
    return http_code "content";
    

e.g.

    location /api/test/ {
        return 403;
    }
    
    location /stat/ {
        return 204;
    }
    
    location /ping/ {
        return 200;
    }
    

## for mobile

移动端和网站端互相跳转

        location = / {
            try_files $uri @mobile_rewrite;
        }
    
        location ~ ^/(login|register|search|album|404|album/\d+|item/\d+|topic)$ {
            try_files $uri @mobile_rewrite;
        }
    
    
        location @mobile_rewrite {
    
            if ($http_user_agent ~* "(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino") {
                set $mobile_rewrite perform;
            }
            if ($http_user_agent ~* "^(1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-)") {
                set $mobile_rewrite perform;
            }
    
            if ($arg_mobile = 'no') {
                set $mobile_rewrite do_not_perform;
            }
    
            if ($arg_mobile = 'yes') {
                set $mobile_rewrite perform;
            }
    
            if ($mobile_rewrite = perform) {
                rewrite ^ http://$server_name/m$request_uri permanent;
                break;
            }
    
            proxy_pass http://127.0.0.1:5000;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header Host $http_host;
            proxy_redirect off;
    
        }
    
    
        location /m/
        {
    
            set $pc_rewrite 1;
            if ($http_user_agent ~* "(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino") {
                set $pc_rewrite 0;
            }
            if ($http_user_agent ~* "^(1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-)") {
                set $pc_rewrite 0;
            }
            if ($pc_rewrite = 1) {
                rewrite ^/m/(.*)$ http://$server_name/$1 permanent;
            }
    
            proxy_pass http://127.0.0.1:5000;
            proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
            proxy_set_header X-Real-IP $remote_addr;
            proxy_set_header Host $http_host;
            proxy_redirect off;
        }

## redirect to www

    server {
        server_name  abc.com;
        rewrite ^(.*) http://www.abc.com$1 permanent;
    }

## allow and deny

访问ip控制

    location /test/ {
        allow 192.168.1.1;
        deny all;
    
    }
    

## 负载均衡

nginx.conf

    http {
    
        upstream A {
            server 192.168.1.1:5000;
            server 192.168.1.2:5000;
        }
    }

sites/a.conf

    server {
    
        location / {
            proxy_pass A;
        }
    
    }
    

- - -

其他

## centos service cmds

    检查配置文件正确性
    service nginx configtest
    
    
    重新加载配置
    service nginx reload

[0]: #
[1]: #include
[2]: #gzip-on
[3]: #for-multi-processers_1
[4]: #static-file-cache
[5]: #proxy-pass
[6]: #or
[7]: #_1
[8]: #_2
[9]: #return
[10]: #for-mobile
[11]: #redirect-to-www
[12]: #allow-and-deny
[13]: #_3
[14]: #centos-service-cmds
[15]: http://www.wklken.me/posts/2013/11/23/nginx-base.html