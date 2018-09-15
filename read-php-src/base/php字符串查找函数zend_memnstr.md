# [php字符串查找函数zend_memnstr][0]

标签： [zend_api][1][PHP内核][2][PHP扩展][3]

2015-07-02 10:55  754人阅读  

Zend/zend_operators.h:

```c
static inline char * zend_memnstr(char *haystack, char *needle, int needle_len, char *end)
{
     //字符首指针
     char *p = haystack;
     //最后一个字符
     char ne = needle[needle_len-1]; 
     //减小查询范围，判断needle_len应该小于end还算比较巧妙哦
     end -= needle_len;
     while (p <= end) {
        //在数组的前n个字节中搜索字符 memchr(p, *needle, (end-p+1)) 
   
        if ((p = (char *)memchr(p, *needle, (end-p+1))) && ne == p[needle_len-1]) {
            //如果找到首字节并且最后一个字节相同
            if (!memcmp(needle, p, needle_len-1)) {
                //对比找到啦那么返回首指针
                return p;
            }
        }
        if (p == NULL) {
            return NULL;
        }
        p++;
    }

    return NULL;
}
```

[PHP][9]函数strpos、explode都用到了此函数，具体代码可以查看：ext/standard/string.c

[0]: http://blog.csdn.net//pangudashu/article/details/46723697
[1]: http://www.csdn.net/tag/zend_api
[2]: http://www.csdn.net/tag/PHP%e5%86%85%e6%a0%b8
[3]: http://www.csdn.net/tag/PHP%e6%89%a9%e5%b1%95
[8]: #
[9]: http://lib.csdn.net/base/php