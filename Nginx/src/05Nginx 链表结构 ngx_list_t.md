### 链表结构

ngx_list_t 是 Nginx 封装的链表容器，链表容器内存分配是基于内存池进行的，操作方便，效率高。Nginx 链表容器和普通链表类似，均有链表表头和链表节点，通过节点指针组成链表。其结构定义如下：

```c
    /* 链表结构 */
    typedef struct ngx_list_part_s  ngx_list_part_t;
    
    /* 链表中的节点结构 */
    struct ngx_list_part_s {
        void             *elts; /* 指向该节点数据区的首地址 */
        ngx_uint_t        nelts;/* 该节点数据区实际存放的元素个数 */
        ngx_list_part_t  *next; /* 指向链表的下一个节点 */
    };
    
    /* 链表表头结构 */
    typedef struct {
        ngx_list_part_t  *last; /* 指向链表中最后一个节点 */
        ngx_list_part_t   part; /* 链表中表头包含的第一个节点 */
        size_t            size; /* 元素的字节大小 */
        ngx_uint_t        nalloc;/* 链表中每个节点所能容纳元素的个数 */
        ngx_pool_t       *pool; /* 该链表节点空间的内存池对象 */
    } ngx_list_t;
```
    

链表数据结构如下图所示：

![][0]

### 链表操作

Nginx 链表的操作只有两个：创建链表 和 添加元素。由于链表的内存分配是基于内存池，所有内存的销毁由内存池进行，即链表没有销毁操作。

#### 创建链表

创建新的链表时，首先分配链表表头，再对该链表进行初始化，在初始化过程中分配头节点数据区内存。

```c
    /* 创建链表 */
    ngx_list_t *
    ngx_list_create(ngx_pool_t *pool, ngx_uint_t n, size_t size)
    {
        ngx_list_t  *list;
    
        /* 分配链表表头的内存 */
        list = ngx_palloc(pool, sizeof(ngx_list_t));
        if (list == NULL) {
            return NULL;
        }
    
        /* 初始化链表 */
        if (ngx_list_init(list, pool, n, size) != NGX_OK) {
            return NULL;
        }
    
        return list;
    }
    
    /* 初始化链表 */
    static ngx_inline ngx_int_t
    ngx_list_init(ngx_list_t *list, ngx_pool_t *pool, ngx_uint_t n, size_t size)
    {
        /* 分配节点数据区内存，并返回该节点数据区的首地址 */
        list->part.elts = ngx_palloc(pool, n * size);
        if (list->part.elts == NULL) {
            return NGX_ERROR;
        }
    
        /* 初始化节点成员 */
        list->part.nelts = 0;
        list->part.next = NULL;
        list->last = &list->part;
        list->size = size;
        list->nalloc = n;
        list->pool = pool;
    
        return NGX_OK;
    }
```
    

#### 添加元素

添加元素到链表时，都是从最后一个节点开始，首先判断最后一个节点的数据区是否由内存存放新增加的元素，若足以存储该新元素，则返回存储新元素内存的位置，若没有足够的内存存储新增加的元素，则分配一个新的节点，再把该新的节点连接到现有链表中，并返回存储新元素内存的位置。注意：添加的元素可以是整数，也可以是一个结构。

```c
    /* 添加一个元素 */
    void *
    ngx_list_push(ngx_list_t *l)
    {
        void             *elt;
        ngx_list_part_t  *last;
    
        /* last节点指针指向链表最后一个节点 */
        last = l->last;
    
        /* 若最后一个节点的数据区已满 */
        if (last->nelts == l->nalloc) {
    
            /* the last part is full, allocate a new list part */
    
            /* 则分配一个新的节点 */
            last = ngx_palloc(l->pool, sizeof(ngx_list_part_t));
            if (last == NULL) {
                return NULL;
            }
    
            /* 分配新节点数据区内存，并使节点结构指向该数据区的首地址 */
            last->elts = ngx_palloc(l->pool, l->nalloc * l->size);
            if (last->elts == NULL) {
                return NULL;
            }
    
            /* 初始化新节点结构 */
            last->nelts = 0;
            last->next = NULL;
    
            /* 把新节点连接到现有链表中 */
            l->last->next = last;
            l->last = last;
        }
    
        /* 计算存储新元素的位置 */
        elt = (char *) last->elts + l->size * last->nelts;
        last->nelts++;  /* 实际存放元素加1 */
    
        /* 返回新元素所在位置 */
        return elt;
    }
```
    

测试程序：

```c
    #include "ngx_config.h"
    #include <stdio.h>
    #include "ngx_conf_file.h"
    #include "nginx.h"
    #include "ngx_core.h"
    #include "ngx_string.h"
    #include "ngx_palloc.h"
    #include "ngx_list.h"
    
    volatile ngx_cycle_t  *ngx_cycle;
    
    void ngx_log_error_core(ngx_uint_t level, ngx_log_t *log, ngx_err_t err,
                const char *fmt, ...)
    {
    }
    void dump_list_part(ngx_list_t* list, ngx_list_part_t* part)  
    {  
        int *ptr = (int*)(part->elts);  
        int loop = 0;  
      
        printf("  .part = 0x%x\n", &(list->part));  
        printf("    .elts = 0x%x  ", part->elts);  
        printf("(");  
        for (; loop < list->nalloc - 1; loop++)  
        {  
            printf("%d, ", ptr[loop]);  
        }  
        printf("%d)\n", ptr[loop]);  
        printf("    .nelts = %d\n", part->nelts);  
        printf("    .next = 0x%x", part->next);  
        if (part->next)  
            printf(" -->\n");  
        printf(" \n");  
    }  
    void dump_list(ngx_list_t* list)
    {
        if (list)
        {
            printf("list = 0x%x\n", list);
            printf("  .last = 0x%x\n", list->last);
            printf("  .part = %d\n", &(list->part));
            printf("  .size = %d\n", list->size);
            printf("  .nalloc = %d\n", list->nalloc);
            printf("  .pool = 0x%x\n", list->pool);
    
            printf("elements: \n");
            ngx_list_part_t *part = &(list->part);
            while(part)
            {
                dump_list_part(list, part);
                part = part->next;
            }
            printf("\n");
        }
    }
    
    int main()
    {
        ngx_pool_t *pool;
        int i;
    
        printf("--------------------------------\n");
        printf("create a new pool:\n");
        printf("--------------------------------\n");
        pool = ngx_create_pool(1024, NULL);
    
        printf("--------------------------------\n");
        printf("alloc an list from the pool:\n");
        printf("--------------------------------\n");
        ngx_list_t *list = ngx_list_create(pool, 5, sizeof(int));
    
        if(NULL == list)
        {
            return -1;
        }
        for (i = 0; i < 5; i++)
        {
            int *ptr = ngx_list_push(list);
            *ptr = 2*i;
        }
    
        dump_list(list);
    
        ngx_destroy_pool(pool);
        return 0;
    }
```
    
输出结果：

```

    $ ./list_test 
    --------------------------------
    create a new pool:
    --------------------------------
    --------------------------------
    alloc an list from the pool:
    --------------------------------
    list = 0x98ce048
      .last = 0x98ce04c
      .part = 160227404
      .size = 4
      .nalloc = 5
      .pool = 0x98ce020
    elements: 
      .part = 0x98ce04c
        .elts = 0x98ce064  (0, 2, 4, 6, 8)
        .nelts = 5
        .next = 0x0 
```
    

参考资料：

《深入理解 Nginx 》

《[nginx源码分析—链表结构ngx_list_t][1]》

[0]: ./img/2016-09-01_57c7edcf652d0.jpg
[1]: http://blog.csdn.net/livelylittlefish/article/details/6599065