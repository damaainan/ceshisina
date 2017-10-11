# PHP standard函数getcwd源码阅读小记

 时间 2017-07-14 10:40:38 

原文[http://www.jwlchina.cn/2017/06/28/PHP standard函数getcwd源码阅读小记/][1]



遇到了php-fpm register_shutdown_function()的一个bug：

```php
    echo "Expected:" . getcwd() . "<br>";
    
    register_shutdown_function(function (){
        echo "In shutdown function:" . getcwd() . "<br>";
    });
```

php-fpm输出如下 

```
    Expected:/var/www/html/test
    In shutdown function:/
```

在cli模式下输出正常 

```
    Expected:/var/www/html/test<br>In shutdown function:/var/www/html/test<br>
```

去翻翻源码，找到getcwd的定义

函数定义的位置在： ext/standard/dir.c 的364行 

```c
    PHP_FUNCTION(getcwd)
    {
        char path[MAXPATHLEN];
        char *ret=NULL;
    
        if (zend_parse_parameters_none() == FAILURE) {
            return;
        }
    
    #if HAVE_GETCWD
        ret = VCWD_GETCWD(path, MAXPATHLEN);  //这里调用ZEND的方法，获取当前工作目录
    #elif HAVE_GETWD
        ret = VCWD_GETWD(path);
    #endif
    
        if (ret) {
            RETURN_STRING(path);
        } else {
            RETURN_FALSE;
        }
    }
```

可见，此方法其实是关联到一个ZEND方法 `VCWD_GETWD()`

```c
    #define VCWD_GETCWD(buff, size) virtual_getcwd(buff, size)
```

PHP内核定义了十分之多的宏，所以需要找到真正定义还得费点劲

最终找到真正定义位置，在`Zend\zend_virtual_cwd.h`

```c
    CWD_API char *virtual_getcwd_ex(size_t *length)
    {
        cwd_state *state;
    
        state = &CWDG(cwd);   //获取全局的virtual_getcwd
    
        if (state->cwd_length == 0) {
            char *retval;
    
            *length = 1;
            retval = (char *) emalloc(2);
            if (retval == NULL) {
                return NULL;
            }
            retval[0] = DEFAULT_SLASH;
            retval[1] = '\0';
            return retval;
        }
    
    #ifdef ZEND_WIN32
        /* If we have something like C: */
        if (state->cwd_length == 2 && state->cwd[state->cwd_length-1] == ':') {
            char *retval;
    
            *length = state->cwd_length+1;
            retval = (char *) emalloc(*length+1);
            if (retval == NULL) {
                return NULL;
            }
            memcpy(retval, state->cwd, *length);
            retval[0] = toupper(retval[0]);
            retval[*length-1] = DEFAULT_SLASH;
            retval[*length] = '\0';
            return retval;
        }
    #endif
        if (!state->cwd) {
            *length = 0;
            return NULL;
        }
    
        *length = state->cwd_length;
        return estrdup(state->cwd);
    }
    
    CWD_API char *virtual_getcwd(char *buf,size_t size)
    {
        size_t length;
        char *cwd;
    
        cwd = virtual_getcwd_ex(&length);    // 真正获取cwd的地方
    
        if (buf == NULL) {
            return cwd;
        }
        if (length > size-1) {
            efree(cwd);
            errno = ERANGE; /* Is this OK? */
            return NULL;
        }
        if (!cwd) {
            return NULL;
        }
        memcpy(buf, cwd, length+1);   //复制内容
        efree(cwd);  // 防止内存泄漏
        return buf;
    }
```

找到CWDG定义 

```c
    #ifdef ZTS
    extern ts_rsrc_id cwd_globals_id;
    # define CWDG(v) ZEND_TSRMG(cwd_globals_id, virtual_cwd_globals *, v)
    #else
    extern virtual_cwd_globals cwd_globals;
    # define CWDG(v) (cwd_globals.v)
    #endif
```

可以看到，方法是获取一个全局变量，`cwd_globals`

根据上面源码的阅读，可以进一步推测应该是`shutdown_function`的时候`cwd_globals`已经被修改了

这种情况只发生在php-fpm模式下，cli未发现


[1]: http://www.jwlchina.cn/2017/06/28/PHP standard函数getcwd源码阅读小记/
