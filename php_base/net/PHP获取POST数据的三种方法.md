### [PHP获取POST数据的三种方法][0]

### 方法一，$_POST  

`$_POST`或`$_REQUEST`存放的是PHP以`key=>value`的形式格式化以后的数据。

### 方法二，使用`file_get_contents("php://input")`  

对于未指定 Content-Type 的POST数据，则可以使用`file_get_contents("php://input")`;来获取原始数据。   

事实上，用PHP接收POST的任何数据均使用本方法。而不用考虑Content-Type，包括二进制文件流也是可行的。   

同`$HTTP_RAW_POST_DATA`比起来，它给内存带来的压力较小，并且不需要任何特殊的 php.ini 设置。   

`php://input`无法读取Content-Type为multipart/form-data的POST数据，需要设置php.ini中的`always_populate_raw_post_data`值为`On`才可以。   

`php://input`读取不到`$_GET`数据。是因为`$_GET`数据作为q`uery_path`写在http请求头部(header)的PATH字段，而不是写在http请求的body部分。

### 方法三，使用全局变量`$GLOBALS['HTTP_RAW_POST_DATA']`  

在`$GLOBALS['HTTP_RAW_POST_DATA']`存放的是POST过来的原始数据。   

但`$GLOBALS['HTTP_RAW_POST_DATA']`中是否保存POST过来的数据取决于centent-Type的设置，只有在PHP在无法识别的Content-Type的情况下，才会将POST过来的数据原样地填入变量`$GLOBALS['HTTP_RAW_POST_DATA']`中，象Content-Type=application/x-www-form-urlencoded时，该变量是空的。   

另外，它同样无法读取Content-Type为multipart/form-data的POST数据，也需要设置php.ini中的`always_populate_raw_post_data`值为On，PHP才会总把POST数据填入变量`$http_raw_post_data`。

转载自：[http://www.open-open.com/code/view/1455250701089][1]

[0]: /leedaning/article/details/50769841
[1]: http://www.open-open.com/code/view/1455250701089