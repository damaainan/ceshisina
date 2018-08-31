## PHP7新特性

来源：[https://zhuanlan.zhihu.com/p/34429176](https://zhuanlan.zhihu.com/p/34429176)

时间：编辑于 2018-03-16

[https://github.com/Mr-litt/demo](https://github.com/Mr-litt/demo)

![][0]

## 前言

PHP7的发布使性能有了极大的提升，官方数据显示，Benchmark压测中PHP7对比PHP5.6耗时从耗时从2.991下降到1.186，大幅度下降60%，而在WordPress项目中，PHP7对比PHP5.6，QPS提升2.77倍，让我们聚焦一下PHP7的主要变化。

## 新特性

 **标量类型声明和返回值类型声明：** 

```php

<?php

declare(strict_types=1);

function add(int $a, int $b):int {
    return $a+$b;
}

var_dump(add(1, 2)); // 3
var_dump(add(1.5, 2.5)); // declare(strict_types=0)时输出3，declare(strict_types=1)时抛出异常

```

 **更多的Error变为可捕获的Exception：** 

在 PHP 7 中，很多致命错误以及可恢复的致命错误，都被转换为异常来处理了。例如：调用一个不存在的函数。

 **AST（Abstract Syntax Tree，抽象语法树）：** 

AST在PHP编译过程作为一个中间件的角色，替换原来直接从解释器吐出opcode的方式，让解释器（parser）和编译器（compliler）解耦，可以减少一些Hack代码，同时，让实现更容易理解和可维护。由此带来了一些语义上的改变，例如yield，list()等。

 **其它特性：** 

foreach表现行为一致（Consistently foreach behaviors）

新的操作符 <=>, ??

Unicode字符格式支持（\u{xxxxx}）

等，更多查看：[PHP7新特性][5]

## 性能提升的几个关键点

 **Zval的改变：** 

PHP中的变量的载体就是Zval，Zval是C语言实现的一个结构体。

PHP5的Zval，内存占据24个字节：

![][1]

PHP7的Zval，内存占据16个字节：

![][2]

通过对比可以看出，PHP7通过union（联合体）使用更多的变量使Zval从24字节下降到16字节。

 **zend_string的改变：** 

PHP当中的字符串（string）的载体时zend_string结构体。

PHP7的zend_string：

```c

struct _zend_string {
    zend_refcounted_h gc;     /* 垃圾回收结构体 */
    zend_ulong        h;      /* 字符串哈希值 */
    size_t            len;    /* 字符串长度 */
    char              val[1]; /* 字符串内容 */
};

```

PHP7的zend_string结构体最后一个成员变量采用char数组，而不是使用char*，这里有一个小优化技巧，可以降低CPU的cache miss。

 **PHP数组的变化（HashTable和Zend Array）:** 

PHP5的数组是由HashTable实现的，PHP7的数组由Zend Array实现。

PHP5的HashTable:

![][3]

HashTable是C语言中一个很重要的结构，如下为实现一个简单的HashTable结构：

```c

#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <time.h>

#define HASH_TABLE_MAX_SIZE 10
typedef struct HashNode_Struct HashNode;
struct HashNode_Struct
{
    char *sKey;
    int nValue;
    HashNode *pNext;
};

HashNode *hashTable[HASH_TABLE_MAX_SIZE]; //hash table数组
int hash_table_size;  //保存hash table数组当前元素尺寸

//初始化
void hash_table_init()
{
    hash_table_size = 0;
    memset(hashTable, 0, sizeof(HashNode *) * HASH_TABLE_MAX_SIZE);
}

//hash函数
unsigned int hash_table_hash_str(const char *skey)
{
    const signed char *p = (const signed char *)skey;
    unsigned int h = *p;
    if(h)
    {
        for(p += 1; *p != '\0'; ++p)
            h = (h << 5) - h + *p;
    }
    return h;
}

//插入元素
void hash_table_insert(const char *skey, int nvalue)
{
    if(hash_table_size >= HASH_TABLE_MAX_SIZE)
    {
        printf("out of hash table memory!\n");
        return;
    }

    unsigned int pos = hash_table_hash_str(skey) % HASH_TABLE_MAX_SIZE;

    HashNode *pHead =  hashTable[pos];
    while(pHead)
    {
        if(strcmp(pHead->sKey, skey) == 0)
        {
            printf("%s already exists!\n", skey);
            return ;
        }
        pHead = pHead->pNext;
    }

	// 指针生成
    HashNode *pNewNode = (HashNode *)malloc(sizeof(HashNode));
    memset(pNewNode, 0, sizeof(HashNode));
    pNewNode->sKey = (char *)malloc(sizeof(char) * (strlen(skey) + 1));
    strcpy(pNewNode->sKey, skey);
    pNewNode->nValue = nvalue;
	pNewNode->pNext = NULL;
	if (pHead == NULL) {
    	pNewNode->pNext = hashTable[pos];
    }
    
    hashTable[pos] = pNewNode;
    hash_table_size++;
}

// 根据key查找value
HashNode* hash_table_lookup(const char* skey)
{
    unsigned int pos = hash_table_hash_str(skey) % HASH_TABLE_MAX_SIZE;
    if(hashTable[pos])
    {
        HashNode *pHead = hashTable[pos];
        while(pHead)
        {
            if(strcmp(skey, pHead->sKey) == 0)
                return pHead;
            pHead = pHead->pNext;
        }
    }
    return NULL;
}

// 打印hash table
void hash_table_print()
{
	printf("===========content of hash table===========\n");
    int i;
    for(i = 0; i < HASH_TABLE_MAX_SIZE; ++i)
        if(hashTable[i])
        {
            HashNode* pHead = hashTable[i];
            while(pHead)
            {
                printf("%s:%d  ", pHead->sKey, pHead->nValue);
                pHead = pHead->pNext;
            }
            printf("\n");
        }
}

//主程序
int main(int argc, char** argv)
{
	printf("===========init begin===========\n");
    hash_table_init();
    printf("===========init end===========\n");

    printf("===========insert begin===========\n");
    const char *key1 = "aaa";
    const char *key2 = "bbb";
    hash_table_insert(key1, 110);
    hash_table_insert(key2, 220);
    printf("===========insert end===========\n");

    printf("===========print begin===========\n");
    hash_table_print();
    printf("===========print end===========\n");

	printf("===========print begin===========\n");
    HashNode* pNode = hash_table_lookup(key1);
    printf("lookup result:%d\n", pNode->nValue);
    pNode = hash_table_lookup(key2);
    printf("lookup result:%d\n", pNode->nValue);
    printf("===========print end===========\n");

    return 0;
}

```

PHP7的Zend Array:

![][4]

Zend Array将整块的数组元素和hash映射表全部连接在一起，被分配在同一块内存内，这样遍历一个整形的简单类型数据效率非常高，当然，最重要的是它能够避免CPU Cache Miss（CPU缓存命中率下降）。

 **函数调用机制（Function Calling Convention）：** 

PHP7改进了函数的调用机制，通过优化参数传递（减少重复发送send_val和recv参数）的环节，减少了一些指令，提高执行效率。

 **通过宏定义和内联函数（inline），让编译器提前完成部分工作：** 

PHP7在这方面做了不少的优化，将不少需要在运行阶段要执行的工作（例如固定的字符常量），放到了编译阶段。

 **JIT与性能：** 

Just In Time（即时编译）是一种软件优化技术，指在运行时才会去编译字节码为机器码。其实，Facebook推出HHVM+Hack方案就让他们PHP性能提升了几个数量级，而实际上，PHP早就做过JIT的尝试，然后在WordPress的测试当中并没有看到性能明显的提升，后来经过测试得到了两个结论：（1）JIT生成的ByteCodes如果太大，会引起CPU缓存命中率下降（CPU Cache Miss），（2）JIT性能的提升效果取决于项目的实际瓶颈，因此JIT并没有被列入PHP7的特性当中。

## 建议

 **开启Zend Opcache** 

OPcache 通过将 PHP 脚本预编译的字节码存储到共享内存中来提升 PHP 的性能， 存储预编译字节码的好处就是 省去了每次加载和解析 PHP 脚本的开销。

安装：

PHP 5.5.0以及后续版本：已经绑定了OPcache扩展，

PHP 5.2, 5.3 和 5.4 版本：[ZendOpcache扩展][6]

参数配置：

```cfg

; 开关打开
opcache.enable=1
; 可用内存, 酌情而定, 单位为：Mb
opcache.memory_consumption=128
; Zend Optimizer + 暂存池中字符串的占内存总量.(单位:MB)
opcache.interned_strings_buffer=8
; 对多缓存文件限制, 命中率不到 100% 的话, 可以试着提高这个值
opcache.max_accelerated_files=4000
; Opcache 会在一定时间内去检查文件的修改时间, 这里设置检查的时间周期, 默认为 2, 定位为秒
opcache.revalidate_freq=60
; 打开快速关闭, 打开这个在PHP Request Shutdown的时候回收内存的速度会提高
opcache.fast_shutdown=1
; 开启CLI
opcache.enable_cli=1

```

APC和APCU：

APC（Alternative PHP Cache ）是一个开放自由的 PHP opcode 缓存。APC一方面可以用作OPcache，另一方面用作共享内存（用户自定义缓存，类似于Memcached的数据缓存），而PHP 5.5.0以及后续版本已经自带OPcache扩展，因此，若要使用使用APC则需要关闭APC的OPcache功能：

```cfg

apc.opcode_cache_enable = 0

```

或者使用APCU（APC User Cache），即被剥离了操作码缓存的APC。

 **上述demo可以戳这里：** [demo][7]

## 总结

PHP7带来的变化也是令人激动的，消除了很多歧义的地方，也越来越规范，建议新版本都使用PHP7，也希望PHP发展越来越好。

[5]: https://link.zhihu.com/?target=http%3A//php.net/manual/zh/migration70.new-features.php
[6]: https://link.zhihu.com/?target=http%3A//pecl.php.net/package/ZendOpcache
[7]: https://link.zhihu.com/?target=https%3A//github.com/Mr-litt/demo/tree/master/articles/PHP7/new_features

[0]: ./img/v2-ceffba4c26dcc1a8ba1b133d78cd4ff1_1200x500.jpg
[1]: ./img/v2-3ca4070514337f96f935432b9152c7a4_b.jpg
[2]: ./img/v2-64de8fc0c087856f5101e0021677e3b0_r.jpg
[3]: ./img/v2-af530ebc03122ff67a4d9fda3b403784_r.jpg
[4]: ./img/v2-9bffa06afc5f3513e6aa7111b734a7d0_r.jpg