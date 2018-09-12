# php配置文件详解

 Posted by Francis Soung on November 11, 2015

> 优化php性能的时候，优化配置文件这个是首当其冲的。下边跟大家分享下我总结的关于php.ini配置文件里的每一行的解释，希望能帮助大家进一步了解php。

    [ipv6@ipv6 ~]# grep -v ";" /etc/php5/apache2/php.ini

```
    [PHP]
    engine = On  --->  是否启用PHP解析引擎
    short_open_tag = Off    --->  是否使用简介标志
    asp_tags = Off  --->  不允许asp类标志
    precision = 14  --->  浮点型数据显示的有效期
    y2k_compliance = On  --->  是否强制打开2000年适应(可能在非Y2K适应的浏览器中导致问题)。
    output_buffering = 4096  --->  输出缓冲区大小(字节)。建议值为4096~8192。
    zlib.output_compression = Off  --->  是否开启zlib输出压缩
    implicit_flush = Off   --->  是否要求PHP输出层在每个输出块之后自动刷新数据
    这等效于在每个 print()、echo()、HTML块 之后自动调用flush()函数。打开这个选项对程序执行的性能有严重的影响，通常只推荐在调试时使用。在CLI SAPI的执行模式下，该指令默认为 On 。
    unserialize_callback_func =
    serialize_precision = 17
    ####将浮点型和双精度型数据序列化存储时的精度(有效位数)。默认值能够确保浮点型数据被解序列化程序解码时不会丢失数据。
    allow_call_time_pass_reference = Off
    ####是否强迫在函数调用时按引用传递参数(每次使用此特性都会收到一条警告)。
    ; php反对这种做法，并在将来的版本里不再支持，因为它影响到了代码的整洁。
    ; 鼓励的方法是在函数声明里明确指定哪些参数按引用传递。
    ; 我们鼓励你关闭这一选项，以保证你的脚本在将来版本的语言里仍能正常工作。
    safe_mode = Off    --->   安全模式
    safe_mode_gid = Off
    safe_mode_include_dir =
    #在安全模式下，该组目录和其子目录下的文件被包含时，将跳过UID/GID检查。换句话说，如果此处的值为空，任何UID/GID不符合的文件都不允许被包含。这里设置的目录必须已经存在于include_path指令中或者用完整路径来包含。多个目录之间用冒号(Win下为分号)隔开。指定的限制实际上是一个前缀，而非一个目录名。
    safe_mode_exec_dir =  --->  安全模式下的可执行文件存放目录
    safe_mode_allowed_env_vars = PHP_
    ####在安全模式下，用户仅可以更改的环境变量的前缀列表(逗号分隔)。允许用户设置某些环境变量，可能会导致潜在的安全漏洞。注意: 如果这一参数值为空，PHP将允许用户更改任意环境变量。
    safe_mode_protected_env_vars = LD_LIBRARY_PATH
    ####在安全模式下，用户不能更改的环境变量列表(逗号分隔)。这些变量即使在safe_mode_allowed_env_vars指令设置为允许的情况下也会得到保护。
    disable_functions =   --->  该指令接受一个用逗号分隔的函数名列表，以禁用特定的函数。
    disable_classes =   --->  该指令接受一个用逗号分隔的类名列表，以禁用特定的类
    zend.enable_gc = On  ——→
    expose_php = On   --->  在网页头部显示php信息
    max_execution_time = 30   --->  每个脚本最大执行秒数
    max_input_time = 60   --->  每个脚本用来分析请求数据的最大限制时间
    memory_limit = 128M   --->  每个脚本执行的内存限制
    error_reporting = E_ALL & ~E_DEPRECATED
    display_errors = Off   --->  #显示失误（该关闭，换成日志显示）
    display_startup_errors = Off   --->  #显示启动失误
    log_errors = On   --->  生成错误错误日志显示
    log_errors_max_len = 1024   --->  设定error_log最大长度
    ignore_repeated_errors = Off   --->  打开后，不记录重复的信息
    ignore_repeated_source = Off   --->  打开后当记录重复的信息时忽略来源
    report_memleaks = On   --->  报告内存泄露，仅在debug编译模式下有效
    track_errors = Off   --->  ####在$php_errormsg中保存最后一次错误/警告消息 (逻辑值).永远不要再生产环境中使用此特性：html_errors 会显示php错误所在的html标签
    html_errors = Off   --->  是否开启静态网页错误提示
    variables_order = "GPCS"   --->  ####此指令描述了PHP注册GET, POST, Cookie, 环境 和 内置变量的顺序  (各自使用G, P, C, E 和 S , 一般使用 EGPCS 或 GPC).  注册使用从左往右的顺序, 新的值会覆盖旧的值.
    request_order = "GP"
    ####此指令描述的顺序PHP注册GET，POST和COOKIE变量_REQUEST数组。注册是由左到右，新的值将覆盖旧值。如果这个指令没有设置，variables_order中使用$_REQUEST内容。请注意，默认分配的php.ini文件中不包含'C'饼干，出于安全方面的考虑。
    register_globals = Off   --->  ##是否打开register全局变量
    register_long_arrays = Off
    ####是否注册老形式的输入数组, HTTP_GET_VARS 和相关数组；如果你不使用他们,建议为了提高性能关闭他们.
    register_argc_argv = Off
    ####此指令让PHP确认是否申明 argv&argc 变量 (这些变量会包含GET信息). ;如果你不使用这些变量,为了提升性能应该关闭此选项.
    auto_globals_jit = On
    ####当打开此项, SERVER 和 ENV 变量将在第一次被使用时而不是脚本一开始时创建(运行时);如果这些变量在脚本中没有被使用过, 打开此项会增加一点性能.;为了使此指令有效,PHP指令 register_globals, register_long_arrays,;以及 register_argc_argv 必须被关闭.
    post_max_size = 8M   --->  #PHP可以接受的最大的POST数据大小
    magic_quotes_gpc = Off   --->  #针对GET/POST/Cookie数据打开Magic quotes.
    magic_quotes_runtime = Off
    ####针对实时产生的数据打开Magic quotes,例如从SQL获取的数据, 从exec()返回的数据等等.
    magic_quotes_sybase = Off  ##使用 Sybase 风格的 magic quotes (使用"来引导'替代\').
    auto_prepend_file =   --->  #在任何PHP文档之前或之后自动增加文件
    auto_append_file =
    ####两个有趣的变量是auto_prepend_file以及auto_append_file。这些变量指定PHP自动添加到任何PHP文档文件头或文件尾的其他文件。这对于为PHP产生的页面添加页眉或页脚非常有用，可以节省为每个PHP文档添加代码的时间。但需要注意这里的指定文件将会添加到所有的PHP文档中，所以这些变量必须适合单应用程序（single-application）的服务器。所包含的文件要么是PHP脚本，要么是普通的HTML文档。嵌入式PHP代码必须用标准<?php...?>标记括起来。
    default_mimetype = "text/html"   --->  #PHP内建默认为text/html
    doc_root =   --->  #PHP的"根目录"。仅在非空时有效。
    ; 如果safe_mode=On，则此目录之外的文件一概被拒绝。 ; 如果编译PHP时没有指定FORCE_REDIRECT，并且在非IIS服务器上以CGI方式运行， ; 则必须设置此指令(参见手册中的安全部分)。 ; 替代方案是使用的cgi.force_redirect指令
    user_dir =
    ####告诉php在使用 /~username 打开脚本时到哪个目录下去找，仅在非空时有效。  也就是在用户目录之下使用PHP文件的基本目录名，例如："public_html"
    enable_dl = Off
    ####是否允许使用dl()函数。dl()函数仅在将PHP作为apache模块安装时才有效。 禁用dl()函数主要是出于安全考虑，因为它可以绕过open_ｂａｓｅdir指令的限制。 在安全模式下始终禁用dl()函数，而不管此处如何设置。
    file_uploads = On  --->  是否开启上传功能
    upload_max_filesize = 2M  --->  #最大可上传文件，2M
    max_file_uploads = 20  --->  最大同时可以上传20个文件
    allow_url_fopen = On  --->  #是否允许打开远程文件
    allow_url_include = Off  --->  #是否允许include/require远程文件
    default_socket_timeout = 60  --->  默认的socket超时时间
    [Date]  --->  日期
    [filter]
    [iconv]
    [intl]
    [sqlite]
    [sqlite3]
    [Pcre]
    [Pdo]
    [Pdo_mysql]
    pdo_mysql.cache_size = 2000   --->  Ped_mysql的缓存大小
    pdo_mysql.default_socket=   --->  默认的socket时间
    [Phar]
    [Syslog]
    define_syslog_variables  = Off   --->  是否定义各种的系统日志变量
    [mail function]    --->  邮件功能
    SMTP = localhost   --->  本地作为邮件服务器
    smtp_port = 25   邮件端口号默认是25
    mail.add_x_header = On   --->  是否开启最大的header
    [SQL]
    sql.safe_mode = Off
    ####是否使用SQL安全模式。如果打开，指定默认值的数据库连接函数将会使用这些默认值代替支持的参数。对于每个不同数据库的连接函数，其默认值请参考相应的手册页面。
    [ODBC]
    odbc.allow_persistent = On   --->  允许或阻止持久连接.
    odbc.check_persistent = On   --->  在重用前检查连接是否可用
    odbc.max_persistent = -1   --->  持久连接的最大数目，-1意味着没有限制.
    odbc.max_links = -1   --->  最大连接数(持久 + 非持久).-1意味着没有限制.
    odbc.defaultlrl = 4096   --->  长字段处理.返回变量的字节数.0 意味着略过.
    odbc.defaultbinmode = 1
    ####二进制数据处理.0意味着略过,1按照实际返回,2转换到字符.;查看odbc_binmode和odbc_longreadlen 的文档来获取针对uodbc.defaultlrl和uodbc.defaultbinmode的解释
    [Interbase]   --->  Interbase数据库
    ibase.allow_persistent = 1  ——→ 允许或组织持久连接。
    ibase.max_persistent = -1   --->  持久连接的最大数目，-1意味着没有限制.
    ibase.max_links = -1   --->  最大连接数(持久 + 非持久).-1意味着没有限制.
    ibase.timestampformat = "%Y-%m-%d %H:%M:%S"   --->  数据库时间记录模式
    ibase.dateformat = "%Y-%m-%d"
    ibase.timeformat = "%H:%M:%S"
    [MySQL]
    mysql.allow_local_infile = On   --->  是否允许本地文件连接数据库
    mysql.allow_persistent = On    --->  允许或禁止 持久连接
    mysql.cache_size = 2000   --->  mysql缓存大小
    mysql.max_persistent = -1   --->  持久连接的最大数目.  -1 意味着没有限制.
    mysql.max_links = -1   --->  连接的最大数目（持久和非持久）。-1 代表无限制
    mysql.default_port = 
    ####mysql_connect() 使用的默认端口，如不设置，mysql_connect()
    ;将使用变量 $MYSQL_TCP_PORT，或在/etc/services 下的mysql-tcp 条目(unix)，
    ;或在编译是定义的 MYSQL_PORT(按这样的顺序)
    ;Win32环境，将仅检查MYSQL_PORT。
    mysql.default_socket =
    ####用于本地 MySql 连接的默认的套接字名。为空，使用 MYSQL 内建值
    mysql.default_host =   --->  mysql_connect() 默认使用的主机（安全模式下无效）
    mysql.default_user =   --->  mysql_connect() 默认使用的用户名（安全模式下无效）
    mysql.default_password =   --->  mysql_connect() 默认使用的密码（安全模式下无效
    mysql.connect_timeout = 60   --->  连接超时时间，默认是60s
    mysql.trace_mode = Off
    [MySQLi]
    mysqli.max_persistent = -1   --->  持久连接的最大数目.  -1 意味着没有限制.
    mysqli.allow_persistent = On   --->  允许或拒绝之久连接
    mysqli.max_links = -1   --->  最大连接数.  -1 意味着没有限制.
    mysqli.cache_size = 2000   --->  连接缓存大小
    mysqli.default_port = 3306   --->  连接端口号
    ####mysqli_connect()默认的端口号.如果没有设置, mysql_connect() 会使用 $MYSQL_TCP_PORT;或者 位于/etc/services的 mysql-tcp 入口或者编译时定义的MYSQL_PORT 值(按照此顺序查找).;Win32 只会查找MYSQL_PORT值.
    mysqli.default_socket =
    ####对于本地MySQL连接的默认socket名称. 如果为空, 则使用MySQL内建默认值.
    mysqli.default_host =
    ####mysqli_connect()的默认host值(在安全模式中不会生效)
    mysqli.default_user =
    ####mysqli_connect()的默认user值(在安全模式中不会生效).
    mysqli.default_pw =
    ####mysqli_connect() 的默认password值(在安全模式中不会生效).
    ; 注意在此文件中保存密码一般来说是 *糟糕* 的主义.
    ; *任何* 使用PHP的用户可以执行 echo get_cfg_var("mysqli.default_password")
    ; 并且获取到此密码! 而且理所当然, 任何有对此文件读权限的用户都可以获取到此密码.
    mysqli.reconnect = Off   --->  允许或阻止持久连接
    [mysqlnd]
    mysqlnd.collect_statistics = On
    mysqlnd.collect_memory_statistics = Off
    [OCI8]
    [PostgreSQL]
    pgsql.allow_persistent = On   --->  允许或阻止持久连接.
    pgsql.auto_reset_persistent = Off 
    ####总是在 pg_pconnect() 时检测断开的持久连接.;自动重置特性会引起一点开销.
    pgsql.max_persistent = -1   --->  持久连接的最大数目.  -1 意味着没有限制.
    pgsql.max_links = -1   --->  最大连接数 (持久 + 非持久).  -1 意味着没有限制
    pgsql.ignore_notice = 0   --->    是否忽略 PostgreSQL 后端通告消息.;通告消息记录会需要一点开销.
    pgsql.log_notice = 0
    ####是否记录 PostgreSQL 后端通告消息.;除非 pgsql.ignore_notice=0, 否则模块无法记录通告消息。
    [Sybase-CT]
    sybct.allow_persistent = On   --->  允许或阻止持久连接.
    sybct.max_persistent = -1   --->  持久连接的最大数目.  -1 意味着没有限制.
    sybct.max_links = -1   --->  最大连接数 (持久 + 非持久).  -1 意味着没有限制.
    sybct.min_server_severity = 10   --->  显示出的错误最小严重程度.
    sybct.min_client_severity = 10    --->   显示出的消息最小严重程度
    [bcmath]
    bcmath.scale = 0    --->  #用于所有bcmath函数的10十进制数数字的个数
    [browscap]
    [Session]   
    session.save_handler = files   --->   用于保存/取回数据的控制方式
    session.use_cookies = 1   --->  是否使用cookies
    session.use_only_cookies = 1
    ####这个选项允许管理员去保护那些在URL中传送session id的用户免于被攻击;默认是0.
    session.name = PHPSESSID    --->  session 的名字（同时作为cookie的名称
    session.auto_start = 0   ——→ 在请求开始时初始化 session
    session.cookie_lifetime = 0   --->  cookie的存活秒数，如果为0，则是直到浏览器重新启动
    session.cookie_path = /   --->  cookie的有效路径
    session.cookie_domain =   --->  cookie的有效域名
    session.cookie_httponly = 
    ####是否将httpOnly标志增加到cookie上,
    增加后则cookie无法被浏览器的脚本语言(例如JavaScript)存取.
    session.serialize_handler = php    用于序列化数据的处理器. php是标准的PHP序列化器.
    session.gc_probability = 1 
    ####; 定义'垃圾回收'进程在每次session初始化时开始的比例.
    ; 比例由 gc_probability/gc_divisor来得出,
    ; 例如. 1/100 意味着在每次请求时有1%的机会启动'垃圾回收'进程.
    session.gc_divisor = 1000
    session.gc_maxlifetime = 1440
    ####在这里数字所指的秒数后，保存的数据将被视为'碎片(garbage)'并由gc进程清理掉。
    session.bug_compat_42 = Off
    ####PHP 4.2 和更早版本有一个未公开的 特性/bug , 此特性允许你在全局初始化一个session变量,即便 register_globals已经被关闭.;如果此特性被使用,PHP 4.3 和更早版本会警告你.;你可以关闭此特性并且隔离此警告. 这时候,如果打开bug_compat_42,那此警告只是被显示出来.
    session.bug_compat_warn = Off
    session.referer_check =
    ####检查HTTP Referer来防止带有id的外部URL.;HTTP_REFERER 必须包含从session来的这个字段才会被认为是合法的.
    session.entropy_length = 0   --->   从此文件读取多少字节
    session.cache_limiter = nocache
    ####设置为{nocache,private,public,}来决定HTTP缓冲的类型;留空则防止发送anti-caching头.
    session.cache_expire = 180   --->  文档在n分钟之后过期.
    session.use_trans_sid = 0
    ####trans sid 支持默认关闭.
    ;使用 trans sid 可能让你的用户承担安全风险.;使用此项必须小心.; - 用户也许通过email/irc/其他途径发送包含有效的session ID的URL给其他人.; - 包含有效session ID的URL可能被存放在容易被公共存取的电脑上.; - 用户可能通过在浏览器历史记录或者收藏夹里面的包含相同的session ID的URL来访问你的站点.
    session.hash_function = 0   --->  选择hash方法;0:MD5(128 bits);1:SHA-1(160 bits)
    session.hash_bits_per_character = 5
    ; 当转换二进制hash数据到可读形式时,每个字符保存时有几位.
    ; 4 bits: 0-9, a-f; 5 bits: 0-9, a-v; 6 bits: 0-9, a-z, A-Z, "-", ","
    url_rewriter.tags = "a=href,area=href,frame=src,input=src,form=fakeentry"
     URL rewriter会在已经定义的一组HTML标签内查找URL.; form/fieldset 是特殊字符; 如果你在这里包含他们, rewriter会增加一个包含信息的隐藏<input>字段否则就是在URL中附加信息.; 如果你你想遵守XHTML, 删除form的入口.; 注意 所有合法的入口都需要一个"="符号, 甚至是没有任何值的.
    [MSSQL]
    mssql.allow_persistent = On   --->  允许或阻止持久连接
    mssql.max_persistent = -1   --->  持久连接的最大数目.  -1 意味着没有限制.
    mssql.max_links = -1   --->  最大连接数 (持久 + 非持久).  -1 意味着没有限制.
    mssql.min_error_severity = 10   --->  显示出的错误最小严重程度.
    mssql.min_message_severity = 10   --->  显示出的消息最小严重程度
    mssql.compatability_mode = Off   --->  PHP 3.0 老版本的兼容模式.
    mssql.secure_connection = Off   --->  当连接到服务器时使用NT验证
    [Assertion]
    [COM]
    [mbstring]
    [gd]
    [exif]
    [Tidy]
    ####当调用tidy时,默认指向tidy配置文件的路径 tidy是否自动清除和修复输出?; 警告: 不要在你产生非html内容时使用此项,例如产生动态图片时
    tidy.clean_output = Off    清除功能是否开启，本文中为关闭状态
    [soap]
    soap.wsdl_cache_enabled=1   --->  打开或关闭WSDL缓冲特性.
    soap.wsdl_cache_dir="/tmp"   --->  设置SOAP扩展存放缓冲文件的目录
    soap.wsdl_cache_ttl=86400  --->  (存活时间)设置当缓冲文件被用来替换原有缓冲文件的秒数
    soap.wsdl_cache_limit = 5    --->  最小缓存
    [sysvshm]
    [ldap]
    ldap.max_links = -1
    [mcrypt]
    [dba]
    [xsl]
```
