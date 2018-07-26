## PHP编译参数configure配置详解(持续更新中)

来源：[https://segmentfault.com/a/1190000015604367](https://segmentfault.com/a/1190000015604367)


## 编译参数-使用
`./configure -h`
在源代码目录中，该命令可以查看所有编译参数以及对应的英文解释
## 编译参数-说明

```
--prefix=/opt/php                      //指定 php 安装目录 
--with-apxs2=/usr/local/apache/bin/apxs  //整合Apache
--with-config-file-path=/opt/php/etc    //指定php.ini位置
--with-config-file-scan-dir=/opt/php/etc/php.d //指定额外拓展配置归放处文件夹 
--enable-safe-mode    //打开安全模式 
--enable-ftp          //打开ftp的支持 
--enable-zip          //打开对zip的支持 
--with-bz2            //打开对bz2文件的支持 
--with-jpeg-dir       //打开对jpeg图片的支持 
--with-png-dir        //打开对png图片的支持 
--with-freetype-dir   //打开对freetype字体库的支持 
--without-iconv       //关闭iconv函数，各种字符集间的转换 
--with-libXML-dir     //打开libxml2库的支持 
--with-XMLrpc         //打开xml-rpc的c语言 
--with-zlib-dir       //打开zlib库的支持 
--with-gd             //打开gd库的支持 
--enable-gd-native-ttf //支持TrueType字符串函数库 
--with-curl            //打开curl浏览工具的支持 
--with-curlwrappers    //运用curl工具打开url流 
--with-ttf             //打开freetype1.*的支持，可以不加了 
--with-xsl             //打开XSLT 文件支持，扩展了libXML2库 ，需要libxslt软件 
--with-gettext         //打开gnu 的gettext 支持，编码库用到 
--with-pear            //打开pear命令的支持，PHP扩展用的 
--enable-calendar      //打开日历扩展功能 
--enable-mbstring      //多字节，字符串的支持 
--enable-bcmath        //打开图片大小调整,用到zabbix监控的时候用到了这个模块
--enable-sockets       //打开 sockets 支持
--enable-exif          //图片的元数据支持 
--enable-magic-quotes  //魔术引用的支持 
--disable-rpath        //关闭额外的运行库文件 
--disable-debug        //关闭调试模式
```