# Redis源码剖析--双端链表Sdlist

 时间 2016-12-05 17:19:00  ZeeCoder

_原文_[http://zcheng.ren/2016/12/03/TheAnnotatedRedisSourceSdlist/][2]


今天来分析Redis的一个基本数据结构–双端链表，其定义和实现主要在sdlist.h和sdlist.c文件中。其主要用在实现列表键、事务模块保存输入命令和服务器模块，订阅模块保存多个客户端等。 

## sdlist的数据结构 

Redis为双端链表的每一个节点定义了如下的结构体。 

    // 链表节点定义
    typedef struct listNode {
        struct listNode *prev;  // 指向前一个节点
        struct listNode *next;  // 指向后一个节点
        void *value; // 节点值
    } listNode;
    

与一般的双端链表无异，定义了链表节点的结构体之后，下面就定义链表的结构体，用来方便管理链表节点，其结构体定义如下： 

    typedef struct list {
        listNode *head;  // 指向链表头节点
        listNode *tail;  // 指向链表尾节点
        void *(*dup)(void *ptr); // 自定义节点值复制函数
        void (*free)(void *ptr); // 自定义节点值释放函数
        int (*match)(void *ptr, void *key); // 自定义节点值匹配函数
        unsigned long len; // 链表长度
    } list;
    

Redis在实现链表的时候，定义其为双端无环链表，其示意图如下：

![][6]

此外，Redis对其结构体提供了一系列的宏定义函数，方便操作其结构体参数 

    #definelistLength(l) ((l)->len)// 获取list长度
    #definelistFirst(l) ((l)->head)// 获取list头节点指针
    #definelistLast(l) ((l)->tail)// 获取list尾节点指针
    #definelistPrevNode(n) ((n)->prev)// 获取当前节点前一个节点
    #definelistNextNode(n) ((n)->next)// 获取当前节点后一个节点
    #definelistNodeValue(n) ((n)->value)// 获取当前节点的值
    
    #definelistSetDupMethod(l,m) ((l)->dup = (m))// 设定节点值复制函数
    #definelistSetFreeMethod(l,m) ((l)->free = (m))// 设定节点值释放函数
    #definelistSetMatchMethod(l,m) ((l)->match = (m))// 设定节点值匹配函数
    
    #definelistGetDupMethod(l) ((l)->dup)// 获取节点值赋值函数
    #definelistGetFree(l) ((l)->free)// 获取节点值释放函数
    #definelistGetMatchMethod(l) ((l)->match)// 获取节点值匹配函数
    

## sdlist迭代器结构 

Redis为sdlist定义了一个迭代器结构，其能正序和逆序的访问list结构。 

    typedef struct listIter {
        listNode *next; // 指向下一个节点
        int direction; // 方向参数，正序和逆序
    } listIter;
    

对于direction参数，Redis提供了两个宏定义 

    #defineAL_START_HEAD 0// 从头到尾
    #defineAL_START_TAIL 1// 从尾到头
    

## sdlist基本操作 

## sdlist创建 

sdlist提供了listCreate函数来创建一个空的链表。 

    list*listCreate(void)
    {
        struct list *list; // 定义一个链表指针
    
        if ((list = zmalloc(sizeof(*list))) == NULL) // 申请内存
            return NULL; 
        list->head = list->tail = NULL;  // 空链表的头指针和尾指针均为空
        list->len = 0;  // 设定长度
        list->dup = NULL;    // 自定义复制函数初始化
        list->free = NULL;   // 自定义释放函数初始化
        list->match = NULL;  // 自定义匹配函数初始化
        return list;
    }
    

## sdlist释放 

sdlist提供了listRelease函数来释放整个链表 

    voidlistRelease(list*list)
    {
        unsigned long len;
        listNode *current, *next;
    
        current = list->head;
        len = list->len;
        while(len--) {
            next = current->next;
            // 如果定义了节点值释放函数，需要调用
            if (list->free) list->free(current->value);
            zfree(current);  // 释放当前节点
            current = next;
        }
        zfree(list);  // 释放链表头
    }
    

## 插入节点 

sdlist提供了三个函数来完成向list中插入一个节点的功能。

### 向头部插入节点 

    // 该函数向list的头部插入一个节点
    list*listAddNodeHead(list*list,void*value)
    {
        listNode *node;
    
        if ((node = zmalloc(sizeof(*node))) == NULL)
            return NULL;
        node->value = value;
        if (list->len == 0) { // 如果链表为空
            list->head = list->tail = node;
            node->prev = node->next = NULL;
        } else {  // 如果链表非空
            node->prev = NULL;
            node->next = list->head;
            list->head->prev = node;
            list->head = node;
        }
        list->len++;  // 长度+1
        return list;
    }
    

### 向尾部添加节点 

    // 该函数可以在list的尾部添加一个节点
    list*listAddNodeTail(list*list,void*value)
    {
        listNode *node;
    
        if ((node = zmalloc(sizeof(*node))) == NULL)
            return NULL;
        node->value = value;
        if (list->len == 0) { // 如果链表为空
            list->head = list->tail = node;
            node->prev = node->next = NULL;
        } else {  // 如果链表非空
            node->prev = list->tail;
            node->next = NULL;
            list->tail->next = node;
            list->tail = node;
        }
        list->len++;  // 长度+1
        return list;
    }
    

### 向任意位置插入节点 

    // 向任意位置插入节点
    // 其中，old_node为插入位置
    // value为插入节点的值
    // after为0时表示插在old_node前面，为1时表示插在old_node后面
    list*listInsertNode(list*list, listNode *old_node,void*value,intafter){
        listNode *node;
    
        if ((node = zmalloc(sizeof(*node))) == NULL)
            return NULL;
        node->value = value;
        if (after) { // 向后插入
            node->prev = old_node;
            node->next = old_node->next;
            // 如果old_node为尾节点的话需要改变tail
            if (list->tail == old_node) {
                list->tail = node;
            }
        } else {  // 向前插入
            node->next = old_node;
            node->prev = old_node->prev;
            // 如果old_node为头节点的话需要改变head
            if (list->head == old_node) {
                list->head = node;
            }
        }
        if (node->prev != NULL) {
            node->prev->next = node;
        }
        if (node->next != NULL) {
            node->next->prev = node;
        }
        list->len++;
        return list;
    }
    

## 删除节点 

    voidlistDelNode(list*list, listNode *node)
    {
        if (node->prev) // 删除节点不为头节点
            node->prev->next = node->next;
        else // 删除节点为头节点需要改变head的指向
            list->head = node->next;
        if (node->next)  // 删除节点不为尾节点
            node->next->prev = node->prev;
        else // 删除节点为尾节点需要改变tail的指向
            list->tail = node->prev;
        if (list->free) list->free(node->value); // 释放节点值
        zfree(node);  // 释放节点
        list->len--;
    }
    

## 迭代器相关操作 

sdlist为其迭代器提供了一些操作，用来完成获取迭代器，释放迭代器，重置迭代器，获取下一个迭代器等操作，具体源码见如下分析。

### 获取迭代器 

    listIter *listGetIterator(list*list,intdirection)
    {
        listIter *iter;  // 声明迭代器
    
        if ((iter = zmalloc(sizeof(*iter))) == NULL) return NULL;
        // 根据迭代方向来初始化iter
        if (direction == AL_START_HEAD)
            iter->next = list->head;
        else
            iter->next = list->tail;
        iter->direction = direction;
        return iter;
    }
    

### 释放迭代器 

    voidlistReleaseIterator(listIter *iter){
        zfree(iter); // 直接调用zfree来释放
    }
    

### 重置迭代器 

重置迭代器分为两种，一种是重置正向迭代器，一种是重置为逆向迭代器 

    // 重置为正向迭代器
    voidlistRewind(list*list, listIter *li){
        li->next = list->head;
        li->direction = AL_START_HEAD;
    }
    // 重置为逆向迭代器
    voidlistRewindTail(list*list, listIter *li){
        li->next = list->tail;
        li->direction = AL_START_TAIL;
    }
    

### 获取下一个迭代器 

    // 根据direction属性来获取下一个迭代器
    listNode *listNext(listIter *iter)
    {
        listNode *current = iter->next;
    
        if (current != NULL) {
            if (iter->direction == AL_START_HEAD)
                iter->next = current->next;
            else
                iter->next = current->prev;
        }
        return current;
    }
    

## 链表复制函数 

sdlist提供了listDup函数，用于复制整个链表。 

    list*listDup(list*orig)
    {
        list *copy;
        listIter iter;
        listNode *node;
    
        if ((copy = listCreate()) == NULL)
            return NULL;
        // 复制节点值操作函数
        copy->dup = orig->dup;
        copy->free = orig->free;
        copy->match = orig->match;
        // 重置迭代器
        listRewind(orig, &iter);
        while((node = listNext(&iter)) != NULL) {
            void *value;
            // 复制节点
            // 如果定义了dup函数，则按照dup函数来复制节点值
            if (copy->dup) {
                value = copy->dup(node->value);
                if (value == NULL) {
                    listRelease(copy);
                    return NULL;
                }
            } else // 如果没有则直接赋值
                value = node->value;
            // 依次向尾部添加节点
            if (listAddNodeTail(copy, value) == NULL) {
                listRelease(copy);
                return NULL;
            }
        }
        return copy;
    }
    

## 查找函数 

sdlist提供了两种查找函数。其一是根据给定节点值，在链表中查找该节点 

    listNode *listSearchKey(list*list,void*key)
    {
        listIter iter;
        listNode *node;
    
        listRewind(list, &iter);
        while((node = listNext(&iter)) != NULL) {
            if (list->match) { // 如果定义了match匹配函数，则利用该函数进行节点匹配
                if (list->match(node->value, key)) {
                    return node;
                }
            } else { // 如果没有定义match，则直接比较节点值
                if (key == node->value) { // 找到该节点
                    return node;
                }
            }
        }
        // 没有找到就返回NULL
        return NULL;
    }
    

其二是根据序号来查找节点 

    listNode *listIndex(list*list,longindex){
        listNode *n;
    
        if (index < 0) {  // 序号为负，则倒序查找
            index = (-index)-1;
            n = list->tail;
            while(index-- && n) n = n->prev;
        } else { // 正序查找
            n = list->head;
            while(index-- && n) n = n->next;
        }
        return n;
    }
    

## 链表旋转函数 

旋转操作其实就是讲表尾节点移除，然后插入到表头，成为新的表头 

    voidlistRotate(list*list){
        listNode *tail = list->tail;
    
        if (listLength(list) <= 1) return;
    
        // 取出表尾指针
        list->tail = tail->prev;
        list->tail->next = NULL;
        // 将其移动到表头并成为新的表头指针
        list->head->prev = tail;
        tail->prev = NULL;
        tail->next = list->head;
        list->head = tail;
    }
    

## sdlist小结 

分析完sdlist的源码，着实是把双向链表的基本操作都复习了一遍，Redis的作者还真是喜欢造轮子，不愧是轮子界的鼻祖啊！虽然这些基本操作很简单，但是可以学到一些优秀的设计，例如：sdlist迭代器的设计等，这些都对理解Redis的相关操作有着很大的帮助作用。


[2]: http://zcheng.ren/2016/12/03/TheAnnotatedRedisSourceSdlist/?utm_source=tuicool&utm_medium=referral

[6]: http://img2.tuicool.com/YNZZFvv.png!web