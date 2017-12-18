# [apache AH01630: client denied by server configuration错误解决方法][0]

**apache AH01630: client denied by server configuration错误解决方法**

出现这个错误的原因是， apache2.4 与 apache2.2 的虚拟主机配置写法不同导致。

apache2.2的写法：

```apache
    <VirtualHost *:80>
     ServerName fdipzone.demo.com
     DocumentRoot "/home/fdipzone/sites/www"
     DirectoryIndex index.html index.php
    
     <Directory "/home/fdipzone/sites/www">
      Options -Indexes +FollowSymlinks
      AllowOverride All
      Order deny,allow
      Allow from all
     </Directory>
    
    </VirtualHost>
```
  
如果在2.4中使用以上写法就会有apache AH01630: client denied by server configuration错误。  
  解决方法 ，apache2.4中

```apache
    Order deny,allow
    Allow from all
    Allow from host ip
```

修改为 

```apache
    Require all granted
    Require host ip
```
  
**修改后的配置如下：**  

```apache
<VirtualHost *:80>  
 ServerName fdipzone.demo.com  
 DocumentRoot "/home/fdipzone/sites/www"  
 DirectoryIndex index.html index.php  
  
 <Directory "/home/fdipzone/sites/www">  
  Options -Indexes +FollowSymlinks  
  AllowOverride All  
  Require all granted   # 这一句最重要  如果只需本地能访问即可 可以写作 Require local
 </Directory>  
  
</VirtualHost>  
```

[0]: /fdipzone/article/details/40512229
[1]: http://www.csdn.net/tag/apache
[2]: http://www.csdn.net/tag/AH01630
[3]: http://www.csdn.net/tag/403
[4]: http://www.csdn.net/tag/client%20denied
[5]: http://www.csdn.net/tag/Require%20all%20granted
[6]: #comments
[7]: javascript:void(0);
[8]: #report
[9]: http://static.blog.csdn.net/images/category_icon.jpg
[10]: #