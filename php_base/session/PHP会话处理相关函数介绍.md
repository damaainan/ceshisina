# PHP会话处理相关函数介绍

时间：2015-03-20 18:28:00 | 阅读数：

> [导读] 在PHP开发中,比起Cookie，Session 是存储在服务器端的会话，相对安全，并且不像 Cookie 那样有存储长度限制，这里我们详细介绍一下PHP处理会话函数将要用到10个函数。

在PHP开发中,比起Cookie，Session 是存储在服务器端的会话，相对安全，并且不像 Cookie 那样有存储长度限制，这里我们详细介绍一下PHP处理会话函数将要用到10个函数。  
  
![22][1]

  
**PHP处理会话函数1、 session_start**

函数功能：开始一个会话或者返回已经存在的会话。  
函数原型：boolean session_start(void);  
返回值：布尔值  
功能说明：这个函数没有参数，且返回值均为true。最好将这个函数置于最先，而且在它之前不能有任何输出，否则会报警，如：Warning: Cannot send session cache limiter – headers already sent (output started at /usr/local/apache/htdocs/cga/member/1.php:2) in /usr/local/apache/htdocs/cga/member/1.php on line 3

**PHP处理会话函数2、 session_register**

函数功能：登记一个新的变量为会话变量  
函数原型：boolean session_register(string name);  
返回值：布尔值。  
功能说明：这个函数是在全局变量中增加一个变量到当前的SESSION中，参数name就是想要加入的变量名，成功则返回逻辑值true。可以用`$_SESSION[name]`或`$HTTP_SESSION_VARS[name]`的形式来取值或赋值。

**PHP处理会话函数3、 session_is_registered**

函数功能：检查变量是否被登记为会话变量。  
函数原型：boobean session_is_registered(string name);  
返回值：布尔值  
功能说明：这个函数可检查当前的session之中是否已有指定的变量注册，参数name就是要检查的变量名。成功则返回逻辑值true。

**PHP处理会话函数4、 session_unregister**

函数功能：删除已注册的变量。  
函数原型：boolean session_session_unregister(string name);  
返回值：布尔值  
功能说明：这个函数在当前的session之中删除全局变量中的变量。参数name就是欲删除的变量名，成功则返回true。

**PHP处理会话函数5、 session_destroy**

函数功能：结束当前的会话，并清空会话中的所有资源。  
函数原型：boolean session destroy(void);  
返回值：布尔值。  
功能说明：这个函数结束当前的session，此函数没有参数，且返回值均为true。

**PHP处理会话函数6、 session_encode**

函数功能：sesssion信息编码  
函数原型：string session_encode(void);  
返回值：字符串  
功能说明：返回的字符串中包含全局变量中各变量的名称与值，形式如：a|s:12:”it is a test”;c|s:4:”lala”; a是变量名 s:12代表变量a的值”it is a test的长度是12 变量间用分号”;”分隔。

**PHP处理会话函数7、 session_decode**

函数功能：sesssion信息解码  
函数原型：boolean session_decode (string data)  
返回值：布尔值  
功能说明：这个函数可将session信息解码，成功则返回逻辑值true。

**PHP处理会话函数8、 session_name**

函数功能：存取当前会话名称  
函数原型：boolean session_name(string [name]);  
返回值：字符串  
功能说明：这个函数可取得或重新设置当前session的名称。若无参数name则表示获取当前session名称，加上参数则表示将session名称设为参数name。

**PHP处理会话函数9、 session_id**

函数功能：存取当前会话标识号  
函数原型：boolean session_id(string [id]);  
返回值：字符串  
功能说明：这个函数可取得或重新设置当前存放session的标识号。若无参数id则表示只获取当前session的标识号，加上参数则表示将session的标识号设成新指定的id。

**PHP处理会话函数10、 session_unset**

函数功能：删除所有已注册的变量。  
函数原型：void session_unset (void)  
返回值：布尔值  
功能说明：这个函数和Session_destroy不同，它不结束会话。就如同用函数session_unregister逐一注销掉所有的会话变量。


[1]: ../img/20150322033941246.jpg