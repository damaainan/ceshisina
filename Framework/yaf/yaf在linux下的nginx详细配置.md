需要在 `application/views` 中新建 `cache/compile` ，并赋予`777` 权限  

需要执行的目录也需要赋予 `777` 权限，例如 `controller` 、 `views` 、 `models` 、 `modules`  




```nginx

server {
    listen 82;
    server_name  localhost;
    root  /var/www/html/yafex;
    index  index.php index.html index.htm;

    location / {
       if (!-e $request_filename) {
           rewrite ^/(.*)$ /index.php/$1 last;
           break;
       }
    }
    location ~ \.php {
       fastcgi_pass 127.0.0.1:9000;
       fastcgi_index index.php;
       fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
       include fastcgi_params;
       set $real_script_name $fastcgi_script_name;
       if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
           set $real_script_name $1;
           set $path_info $2;
       }
       fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
       # fastcgi_param SCRIPT_NAME $real_script_name;
       fastcgi_param PATH_INFO $path_info;
    }

}
```
