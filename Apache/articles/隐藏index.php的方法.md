
Apache 下隐藏 index.php 的方法，将 .htaccess 文件中的内容放入虚拟主机设置，需要开启 Apache 的重写模块

```apache
<VirtualHost *:80>
  ServerName demo.stage.com
  DocumentRoot "..../htdocs/www"
  <Directory "..../htdocs/www/">
    RewriteEngine on
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php/$1 [QSA,PT,L]
    AllowOverride All
    Order allow,deny
    Allow from all
  </Directory>
</VirtualHost>
```