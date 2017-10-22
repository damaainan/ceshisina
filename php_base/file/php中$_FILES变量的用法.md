`$_FILES`: 经由 HTTP POST 文件上传而提交至脚本的变量。类似于旧数组 `$HTTP_POST_FILES` 数组（依然有效，但反对使用）。详细信息可参阅 POST 方法上传。

## ①$_FILES数组内容

`$_FILES['myFile']['name']` 客户端文件的原名称。

`$_FILES['myFile']['type']` 文件的 MIME 类型，需要浏览器提供该信息的支持，例如"image/gif"。

`$_FILES['myFile']['size']` 已上传文件的大小，单位为字节。

`$_FILES['myFile']['tmp_name']` 文件被上传后在服务端储存的临时文件名，一般是系统默认。可以在`php.ini`的`upload_tmp_dir` 指定。

`$_FILES['myFile']['error']` 和该文件上传相关的错误代码。`['error']` 是在 PHP 4.2.0 版本中增加的。下面是它的说明：(它们在PHP3.0以后成了常量)  

`UPLOAD_ERR_OK` 值：0; 没有错误发生，文件上传成功。  
`UPLOAD_ERR_INI_SIZE` 值：1; 上传的文件超过了 php.ini 中 `upload_max_filesize` 选项限制的值。  
`UPLOAD_ERR_FORM_SIZE` 值：2; 上传文件的大小超过了 HTML 表单中 `MAX_FILE_SIZE` 选项指定的值。（我们可以在form表单中指定input type='hidden' name='MAX_FILE_SIZE' value='附件的最大字节数'）  
`UPLOAD_ERR_PARTIAL` 值：3; 文件只有部分被上传。  
`UPLOAD_ERR_NO_FILE` 值：4; 没有文件被上传。

## ②文件的上传过程

* 文件被上传结束后，默认地被存储在了临时目录中，这时必须将它从临时目录中删除或移动到其它地方，如果没有，则会被删除。也就是不管是否上传成功，脚本执行完后临时目录里的文件肯定会被删除。所以在删除之前要用PHP的 `copy()`或者`move_upload_file()` 函数将它复制或者移动到其它位置，此时，才算完成了上传文件过程。
* 用form上传文件时，一定要加上属性内容 `enctype="multipart/form-data"`，否则用`$_FILES[filename]`获取文件信息时会报异常。

> `<input name="myFile" type="file">  <input type="submit" value="上传文件">`  

* 默认地，表单数据会编码为`"application/x-www-form-urlencoded"`。就是说，在发送到服务器之前，所有字符都会进行编码**（空格转换为"+" 加号，特殊符号转换为 ASCII HEX 值）**。
* `application/x-www-form-urlencoded` 在发送前编码所有字符（默认）
* `multipart/form-data` 不对字符编码。 在使用包含文件上传控件的表单时，必须使用该值。
* text/plain 空格转换为 "+" 加号，但不对特殊字符编码。
