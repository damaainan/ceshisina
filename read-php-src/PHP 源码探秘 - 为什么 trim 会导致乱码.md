# PHP 源码探秘 - 为什么 trim 会导致乱码

 时间 2017-10-18 23:05:13  周梦康的博客

原文[http://mengkang.net/1039.html][2]


以下代码：

    $tag = '互联网产品、';
    $text = rtrim($tag, "、");
    print_r($text);

运行，我们可能以为会得到的结果是 互联网产品 ，实际结果是 互联网产� 。为什么会这样呢？ 

## 原理

trim 函数文档 

    string trim ( string $str [, string $character_mask = " \t\n\r\0\x0B" ] )

该函数不是多字节函数，也就是说，汉字这样的多字节字符，会拿其头或尾的单字节来和后面的 $character_mask 对应的char数组进行匹配，如果在后面的数组中，则删掉，继续匹配。比如： 

    echo ltrim("bcdf","abc"); // df

如下面的 demo 中的函数 `string_print_char` 所示： 

`、` 由 `0xe3` `0x80` `0x81` 三字节组成， 

`品` 由 `0xe5` `0x93` `0x81` 三字节组成。 

所以在执行 rtrim 的时候，通过字节比对，会将 0x81 去掉，导致了最后出现了乱码。 

## 源码精简版演示

查看 PHP7 的源码，然后提炼出下面的小 demo ，方便大家一起学习，其实PHP源码的学习并不难，每天进步一点点。

```c
    //
    //  main.c
    //  trim
    //
    //  Created by 周梦康 on 2017/10/18.
    //  Copyright © 2017年 周梦康. All rights reserved.
    //
    
    #include <stdio.h>
    #include <stdlib.h>
    #include <string.h>
    
    void string_print_char(char *str);
    void php_charmask(unsigned char *input, size_t len, char *mask);
    char *ltrim(char *str,char *character_mask);
    char *rtrim(char *str,char *character_mask);
    
    
    int main(int argc, char const *argv[])
    {
        printf("%s\n",ltrim("bcdf","abc"));
        
        string_print_char("品"); // e5    93    81
        string_print_char("、"); // e3    80    81
        
        printf("%s\n",rtrim("互联网产品、","、"));
        
        
        return 0;
    }
    
    char *ltrim(char *str,char *character_mask)
    {
        char *res;
        char mask[256];
        register size_t i;
        int trimmed = 0;
        
        size_t len = strlen(str);
        
        php_charmask((unsigned char*)character_mask, strlen(character_mask), mask);
        
        for (i = 0; i < len; i++) {
            if (mask[(unsigned char)str[i]]) {
                trimmed++;
            } else {
                break;
            }
        }
        
        len -= trimmed;
        str += trimmed;
        
        res = (char *) malloc(sizeof(char) * (len+1));
        memcpy(res,str,len);
        
        return res;
    }
    
    char *rtrim(char *str,char *character_mask)
    {
        char *res;
        char mask[256];
        register size_t i;
        
        size_t len = strlen(str);
        
        php_charmask((unsigned char*)character_mask, strlen(character_mask), mask);
        
        if (len > 0) {
            i = len - 1;
            do {
                if (mask[(unsigned char)str[i]]) {
                    len--;
                } else {
                    break;
                }
            } while (i-- != 0);
        }
        
        res = (char *) malloc(sizeof(char) * (len+1));
        memcpy(res,str,len);
        
        return res;
    }
    
    void string_print_char(char *str)
    {
        unsigned long l = strlen(str);
        
        for (int i=0; i < l; i++) {
            printf("%02hhx\t",str[i]);
        }
        
        printf("\n");
    }
    
    void php_charmask(unsigned char *input, size_t len, char *mask)
    {
        unsigned char *end;
        unsigned char c;
        
        memset(mask, 0, 256);
        
        for (end = input+len; input < end; input++) {
            c = *input;
            mask[c]= 1;
        }
    }
```

## PHP7 相关源码
```c
    PHP_FUNCTION(trim)
    {
        php_do_trim(INTERNAL_FUNCTION_PARAM_PASSTHRU, 3);
    }
    PHP_FUNCTION(rtrim)
    {
        php_do_trim(INTERNAL_FUNCTION_PARAM_PASSTHRU, 2);
    }
    PHP_FUNCTION(ltrim)
    {
        php_do_trim(INTERNAL_FUNCTION_PARAM_PASSTHRU, 1);
    }
```

```c
    static void php_do_trim(INTERNAL_FUNCTION_PARAMETERS, int mode)
    {
        zend_string *str;
        zend_string *what = NULL;
    
        ZEND_PARSE_PARAMETERS_START(1, 2)
            Z_PARAM_STR(str)
            Z_PARAM_OPTIONAL
            Z_PARAM_STR(what)
        ZEND_PARSE_PARAMETERS_END();
    
        ZVAL_STR(return_value, php_trim(str, (what ? ZSTR_VAL(what) : NULL), (what ? ZSTR_LEN(what) : 0), mode));
    }
```

```c
    PHPAPI zend_string *php_trim(zend_string *str, char *what, size_t what_len, int mode)
    {
        const char *c = ZSTR_VAL(str);
        size_t len = ZSTR_LEN(str);
        register size_t i;
        size_t trimmed = 0;
        char mask[256];
    
        if (what) {
            if (what_len == 1) {
                char p = *what;
                if (mode & 1) {
                    for (i = 0; i < len; i++) {
                        if (c[i] == p) {
                            trimmed++;
                        } else {
                            break;
                        }
                    }
                    len -= trimmed;
                    c += trimmed;
                }
                if (mode & 2) {
                    if (len > 0) {
                        i = len - 1;
                        do {
                            if (c[i] == p) {
                                len--;
                            } else {
                                break;
                            }
                        } while (i-- != 0);
                    }
                }
            } else {
                php_charmask((unsigned char*)what, what_len, mask);
    
                if (mode & 1) {
                    for (i = 0; i < len; i++) {
                        if (mask[(unsigned char)c[i]]) {
                            trimmed++;
                        } else {
                            break;
                        }
                    }
                    len -= trimmed;
                    c += trimmed;
                }
                if (mode & 2) {
                    if (len > 0) {
                        i = len - 1;
                        do {
                            if (mask[(unsigned char)c[i]]) {
                                len--;
                            } else {
                                break;
                            }
                        } while (i-- != 0);
                    }
                }
            }
        } else {
            if (mode & 1) {
                for (i = 0; i < len; i++) {
                    if ((unsigned char)c[i] <= ' ' &&
                        (c[i] == ' ' || c[i] == '\n' || c[i] == '\r' || c[i] == '\t' || c[i] == '\v' || c[i] == '\0')) {
                        trimmed++;
                    } else {
                        break;
                    }
                }
                len -= trimmed;
                c += trimmed;
            }
            if (mode & 2) {
                if (len > 0) {
                    i = len - 1;
                    do {
                        if ((unsigned char)c[i] <= ' ' &&
                            (c[i] == ' ' || c[i] == '\n' || c[i] == '\r' || c[i] == '\t' || c[i] == '\v' || c[i] == '\0')) {
                            len--;
                        } else {
                            break;
                        }
                    } while (i-- != 0);
                }
            }
        }
    
        if (ZSTR_LEN(str) == len) {
            return zend_string_copy(str);
        } else {
            return zend_string_init(c, len, 0);
        }
    }
```

```c
    /* {{{ php_charmask
     * Fills a 256-byte bytemask with input. You can specify a range like 'a..z',
     * it needs to be incrementing.
     * Returns: FAILURE/SUCCESS whether the input was correct (i.e. no range errors)
     */
    static inline int php_charmask(unsigned char *input, size_t len, char *mask)
    {
        unsigned char *end;
        unsigned char c;
        int result = SUCCESS;
    
        memset(mask, 0, 256);
        for (end = input+len; input < end; input++) {
            c=*input;
            if ((input+3 < end) && input[1] == '.' && input[2] == '.'
                    && input[3] >= c) {
                memset(mask+c, 1, input[3] - c + 1);
                input+=3;
            } else if ((input+1 < end) && input[0] == '.' && input[1] == '.') {
                /* Error, try to be as helpful as possible:
                   (a range ending/starting with '.' won't be captured here) */
                if (end-len >= input) { /* there was no 'left' char */
                    php_error_docref(NULL, E_WARNING, "Invalid '..'-range, no character to the left of '..'");
                    result = FAILURE;
                    continue;
                }
                if (input+2 >= end) { /* there is no 'right' char */
                    php_error_docref(NULL, E_WARNING, "Invalid '..'-range, no character to the right of '..'");
                    result = FAILURE;
                    continue;
                }
                if (input[-1] > input[2]) { /* wrong order */
                    php_error_docref(NULL, E_WARNING, "Invalid '..'-range, '..'-range needs to be incrementing");
                    result = FAILURE;
                    continue;
                }
                /* FIXME: better error (a..b..c is the only left possibility?) */
                php_error_docref(NULL, E_WARNING, "Invalid '..'-range");
                result = FAILURE;
                continue;
            } else {
                mask[c]=1;
            }
        }
        return result;
    }
    /* }}} */
```

[2]: http://mengkang.net/1039.html
