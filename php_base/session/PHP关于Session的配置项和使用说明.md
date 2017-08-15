## PHP关于Session的配置项和使用说明


最近总有人说在面试的时候，会被问到“如果浏览器禁用Cookie后，Session还能使用吗”这个问题， **答案是肯定可以使用** 。

PHP中常用的Session的配置项有下面这些

    [Session]
    ; session.save_handler 定义了来存储和获取与会话关联的数据的处理器的名字。
    ; 默认为 files; 可以用 session_set_save_handler() 自定义配置
    session.save_handler=files
    
    ; session.save_path 定义了传递给存储处理器的参数。如果选择了默认的 files 
    ; 文件处理器，则此值是创建文件的路径。默认为 /tmp。参见 session_save_path()。
    ; 此指令还有一个可选的 N 参数来决定会话文件分布的目录深度。例如，设定为 
    ; '5;/tmp' 将使创建的会话文件和路径类似于 /tmp/4/b/1/e/3/sess_45a174If。
    ; 要使用 N 参数，必须在使用前先创建好这些目录。在 ext/session 目录下有个
    ; 小的 shell 脚本名叫 mod_files.sh，windows 版本是 mod_files.bat 可以用来
    ; 做这件事。此外注意如果使用了 N 参数并且大于 0，那么将不会执行自动垃圾回收，
    ; 更多信息见 php.ini。另外如果用了 N 参数，要确保将 session.save_path 
    ; 的值用双引号 "quotes" 括起来，因为分隔符分号（ ;）在 php.ini 中也是注释
    ; 符号。文件储存模块默认使用 mode 600 创建文件。通过 修改可选参数 MODE 
    ; 来改变这种默认行为： N;MODE;/path ，其中 MODE 是 mode 的八进制表示。 
    ; MODE 设置不影响进程的掩码(umask)。
    session.save_path="d:\xampp\tmp"
    
    ; 但是它确实是实现会话 ID 管理的优选方案。 尽可能的仅使用 cookie 来进行会话 
    ; ID 管理， 而且大部分应用也确实是只使用 cookie 来记录会话 ID 的。
    session.use_cookies=1
    
    ; 仅允许在 HTTPS 协议下访问会话 ID cookie。 如果你的 web 站点仅支持 
    ; HTTPS，那么必须将此选项设置为 On。 
    ;session.cookie_secure =
    
    ; session.use_only_cookies 指定是否在客户端仅仅使用 cookie 来存放会话 ID。
    ; 启用此设定可以防止有关通过 URL 传递会话 ID 的攻击。此设定是 PHP 4.3.0 
    ; 添加的。自PHP 5.3.0开始，默认值改为1（启用）
    session.use_only_cookies=1
    
    ; session.use_trans_sid 指定是否启用透明 SID 支持。默认为 0（禁用）
    session.use_trans_sid=1
    
    ; session.name 指定会话名以用做 cookie 的名字。只能由字母数字组成，
    ; 默认为 PHPSESSID。参见 session_name()
    session.name=PHPSESSID
    
    ; session.auto_start 指定会话模块是否在请求开始时自动启动一个会话
    ; 默认为 0（不启动）
    session.auto_start=0
    
    ; session.cookie_lifetime 以秒数指定了发送到浏览器的 cookie 的生命周期。
    ; 值为 0 表示“直到关闭浏览器”。默认为 0
    ; 参见 session_get_cookie_params() 和 session_set_cookie_params()。
    session.cookie_lifetime=1440
    
    ; session.cookie_path 指定了要设定会话 cookie 的路径。默认为 /
    session.cookie_path=/
    
    ; session.cookie_domain 指定了要设定会话 cookie 的域名。默认为无，表示根据 
    ; cookie 规范产生 cookie 的主机名
    session.cookie_domain=
    
    ; 禁止 JavaScript 访问会话 cookie。 此设置项可以保护 cookie 不被 ;
    ; JavaScript 窃取。 虽然可以使用会话 ID 来作为防范跨站请求伪造（CSRF）的
    ; 关键数据，但是不建议你这么做。 例如，攻击者可以把 HTML 源代码保存下来并且
    ; 发送给其他用户。 为了安全起见， 开发者不应该在 web 页面中显示会话 ID。 
    ; 几乎所有的应用都应该对会话 ID cookie 设置 httponly 为 On
    session.cookie_httponly=
    
    ; session.gc_maxlifetime 指定过了多少秒之后数据就会被视为“垃圾”并被清除
    ; 垃圾搜集可能会在 session 启动的时候开始（ 取决于session.gc_probability 
    ; 和 session.gc_divisor
    session.gc_maxlifetime=5
    
    ; session.gc_probability 与 session.gc_divisor 合起来用来管理 
    ; gc（garbage collection 垃圾回收）进程启动的概率。默认为 1
    ; 详见 session.gc_divisor。
    session.gc_probability = 1
    
    ; session.gc_divisor 与 session.gc_probability 合起来定义了在每个会话初始化
    ; 时启动 gc（garbage collection 垃圾回收）进程的概率
    ; 此概率用 gc_probability/gc_divisor 计算得来。例如 1/100 意味着在每个请求
    ; 中有 1% 的概率启动 gc 进程。session.gc_divisor 默认为 100。
    session.gc_divisor = 100
    
    ; session.serialize_handler 定义用来序列化／解序列化的处理器名字。 当前支持
    ; PHP 序列化格式 (名为 php_serialize)、 PHP PHP 内部格式 (名为 php 及
    ; php_binary) 和 WDDX (名为 wddx)。 如果 PHP 编译时加入了 WDDX 支持，
    ; 则只能用 WDDX。 自 PHP 5.5.4 起可以使用 php_serialize。
    ; php_serialize 在内部简单地直接使用 serialize/unserialize 函数，并且不会有
    ; php 和 php_binary 所具有的限制。 使用较旧的序列化处理器导致 $_SESSION 
    ; 的索引既不能是数字也不能包含特殊字符(| and !) 
    ; 使用 php_serialize 避免脚本退出时，数字及特殊字符索引导致出错。默认使用 php
    session.serialize_handler=php
    
    ; session.use_strict_mode=On。 此设置防止会话模块使用未初始化的会话 ID。
    ; 也就是说，会话模块仅接受由它自己创建的有效的会话 ID， 而拒绝由用户自己提供的
    ; 会话 ID。使用 JavaScript 对 cookie 进行注入就可以实现对会话 ID 的注入， 
    ; 甚至可以在 URL 的查询字符串中或者表单参数中实现会话 ID 的注入。 
    ; 大部分应用没理由也不应该接受由用户提供的未经初始化的会话 ID。
    session.use_strict_mode=0
    
    ; session.referer_check 包含有用来检查每个 HTTP Referer 的子串。
    ; 如果客户端发送了 Referer 信息但是在其中并未找到该子串，则嵌入的会话
    ; ID 会被标记为无效。默认为空字符串
    session.referer_check=
    
    ; session.entropy_file 给出了一个到外部资源（文件）的路径，该资源将在会话 ID 
    ; 创建进程中被用作附加的熵值资源。例如在许多 Unix 系统下都可以用 /dev/random
    ; 或 /dev/urandom。 在 Windows 上自 PHP 5.3.3 起加入了此功能。 
    ; 设置 session.entropy_length 为非零的值将使 PHP使用 Windows Random API
    ; 作为熵值源。
    session.entropy_file = /dev/urandom
    
    ; session.entropy_length 指定了从上面的文件中读取的字节数。默认为 0（禁用）
    session.entropy_length=0
    
    ; session.cache_limiter 指定会话页面所使用的缓冲控制方法（none/nocache/private
    ; /private_no_expire/public）。默认为 nocache 参见 session_cache_limiter()
    session.cache_limiter=nocache
    
    ; session.cache_expire 以分钟数指定缓冲的会话页面的存活期，此设定对 nocache
    ; 缓冲控制方法无效。默认为 180 参见 session_cache_expire()。
    session.cache_expire=180
    
    ; session.hash_function 允许用户指定生成会话 ID 的散列算法。'0' 表示 MD5
    ; 128 位），'1' 表示 SHA-1（160 位）
    session.hash_function=1
    
    ; session.hash_bits_per_character 允许用户定义将二进制散列数据转换为可读的
    ; 格式时每个字符存放多少个比特。
    ; 可能值为 '4'（0-9，a-f），'5'（0-9，a-v），以及 '6'（0-9，a-z，A-Z，"-"，","）。
    session.hash_bits_per_character=5
    
    ; url_rewriter.tags 指定在使用透明 SID 支持时哪些 HTML 标记会被修改以加入
    ; 会话 ID。默认为
    ; a=href,area=href,frame=src,input=src,form=fakeentry,fieldset=
    ; url_rewriter.tags="a=href,area=href,frame=src,input=src,form=fakeentry"
    
    ; 当 session.upload_progress.enabled INI 选项开启时，PHP 能够在每一个文件
    ; 上传时监测上传进度。 这个信息对上传请求自身并没有什么帮助，
    ; 但在文件上传时应用可以发送一个POST请求到终端（例如通过XHR）来检查这个状态
    ;session.upload_progress.enabled = On
    
    ; 当一个上传在处理中，同时POST一个与INI中设置的session.upload_progress.name同名
    ; 变量时，上传进度可以在$_SESSION中获得。 当PHP检测到这种POST请求时，
    ; 它会在$_SESSION中添加一组数据, 索引是 session.upload_progress.prefix 与 
    ; session.upload_progress.name连接在一起的值。 通常这些键值可以通过读取INI设置
    ; 来获得Cleanup the progress information as soon as all POST data has been read
    ;session.upload_progress.cleanup = On
    
    ;session.upload_progress.prefix = "upload_progress_"
    ;session.upload_progress.name = "PHP_SESSION_UPLOAD_PROGRESS"
    
    ; session.upload_progress.freq 和 session.upload_progress.min_freq 
    ; 选项控制了上传进度信息应该多久被重新计算一次。 通过合理设置这两个选项的值，
    ; 这个功能的开销几乎可以忽略不计
    ;session.upload_progress.freq =  "1%"
    ;session.upload_progress.min_freq = "1"

以上是全部PHP的Session配置，今天重点看一下这几个配置

 **session.use_cookies = 1 ;**

是否使用 cookie 来保存 SessionID, 这一项一般肯定要开启的

 **session.use_only_cookies = 0;**

是否只使用 cookie 来保存 , 这一项，如果开启了，表明只能使用 cookie ，那如果客户端禁用了 cookie之后，不好意思， session 也不能使用了。这里设置成0表示关闭

 **session.use_trans_sid = 1 ;**

是否开启 SID 支持, 就是是否使用URL来传递SessionId; 如果把这一项开启并且use_only_cookies也关闭了， 这时，如果客户端禁用了 cookie ，程序会自动在每个连接上添加上SessionID的值

这里写一个程序做看一下效果，配置是

    session.use_cookies = 1
    session.use_only_cookies = 0
    session.use_trans_sid = 1

如果客户端没有禁用 Cookie的情况下，我们的连接就是我们写好的地址，如图，

![1221.jpg][0]

但是，我们把浏览器的Cookie禁用后，（chrome浏览器禁用的方法是：点右角的3点->设置->隐私设置->内容设置）刷新发现，会自动在每一个连接地址后面添加上一个SessionId，如图：

![1222.jpg][1]

 这也证明了开始提的问题，禁用Cookie后，session 是完全可以使用的 。但是，如果把 use_only_cookies 设置成 1，即打开的情况下。再把Cookie禁用后，Session就不能使用了。

[0]: ../img/1482120598763316.jpg
[1]: ../img/1482120598511606.jpg