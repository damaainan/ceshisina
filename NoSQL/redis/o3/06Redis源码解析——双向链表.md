# Redis源码解析——双向链表

 时间 2016-12-09 00:03:38  方亮的专栏

原文[http://blog.csdn.net/breaksoftware/article/details/53525621][2]


## 基本结构

首先我们看链表元素的结构。因为是双向链表，所以其基本元素应该有一个指向前一个节点的指针和一个指向后一个节点的指针，还有一个记录节点值的空间

    typedef struct listNode {
        struct listNode *prev;
        struct listNode *next;
        void *value;
    } listNode;

因为双向链表不仅可以从头开始遍历，还可以从尾开始遍历，所以链表结构应该至少有头和尾两个节点的指针。

但是作为一个可以承载各种类型数据的链表，还需要链表使用者提供一些处理节点中数据的能力。因为这些数据可能是用户自定义的，所以像复制、删除、对比等操作都需要用户来告诉框架。在 [《Redis源码解析——字典结构》][5] 一文中，我们看到用户创建字典时需要传入的dictType结构体，就是一个承载数据的上述处理方法的载体。但是Redis在设计双向链表时则没有使用一个结构来承载这些方法，而是在链表结构中定义了 

    typedef struct list {
        listNode *head;
        listNode *tail;
        void *(*dup)(void *ptr);
        void (*free)(void *ptr);
        int (*match)(void *ptr, void *key);
        unsigned long len;
    } list;

至于链表结构中为什么要存链表长度字段len，我觉得从必要性上来说是没有必要的。有len字段的一个优点是不用每次计算链表长度时都要做一次遍历操作，缺点便是导出需要维护这个变量。

## 创建和释放链表

链表创建的过程比较简单。只是申请了一个list结构体空间，然后各个字段设置为NULL

    list *listCreate(void)
    {
        struct list *list;
    
        if ((list = zmalloc(sizeof(*list))) == NULL)
            return NULL;
        list->head = list->tail = NULL;
        list->len = 0;
        list->dup = NULL;
        list->free = NULL;
        list->match = NULL;
        return list;
    }

但是比较有意思的是，创建链表时没有设定链表类型——没有设置复制、释放、对比等方法的指针。作者单独提供了一些宏来设置，个人觉得这种割裂开的设计不是很好

    #define listSetDupMethod(l,m) ((l)->dup = (m))
    #define listSetFreeMethod(l,m) ((l)->free = (m))
    #define listSetMatchMethod(l,m) ((l)->match = (m))

释放链表的操作就是从头部向尾部一个个释放节点，迭代的方法是通过判断不断减小的链表长度字段len是否为0来进行。之前说过，len其实没有必要性，只要判断节点的next指针为空就知道到结尾了。

    void listRelease(list *list)
    {
        unsigned long len;
        listNode *current, *next;
    
        current = list->head;
        len = list->len;
        while(len--) {
            next = current->next;
            if (list->free) list->free(current->value);
            zfree(current);
            current = next;
        }
        zfree(list);
    }

## 新增节点

新增节点分为三种：头部新增、尾部新增和中间新增。头部和尾部新增都很简单，只是需要考虑一下新增之前链表是不是空的。如果是空的，要设置新增节点的指向前后指针为NULL，还要让链表的头尾指针都指向新增的节点

    list *listAddNodeHead(list *list, void *value)
    {
        listNode *node;
    
        if ((node = zmalloc(sizeof(*node))) == NULL)
            return NULL;
        node->value = value;
        if (list->len == 0) {
            list->head = list->tail = node;
            node->prev = node->next = NULL;
        } else {
            node->prev = NULL;
            node->next = list->head;
            list->head->prev = node;
            list->head = node;
        }
        list->len++;
        return list;
    }
    
    list *listAddNodeTail(list *list, void *value)
    {
        listNode *node;
    
        if ((node = zmalloc(sizeof(*node))) == NULL)
            return NULL;
        node->value = value;
        if (list->len == 0) {
            list->head = list->tail = node;
            node->prev = node->next = NULL;
        } else {
            node->prev = list->tail;
            node->next = NULL;
            list->tail->next = node;
            list->tail = node;
        }
        list->len++;
        return list;
    }

上述代码还说明一个问题，新建节点的数据指针指向传入的value内容，而没有使用复制操作将value所指向的数据复制下来。于是插入链表中的数据，要保证在链表生存周期之内都要有效。

在链表中间插入节点时，可以指定插入到哪个节点前还是后。这个场景下则需要考虑作为坐标的节点是否为链表的头结点或者尾节点；如果是，可能还要视新插入的节点的位置更新链表的头尾节点指向。

    list *listInsertNode(list *list, listNode *old_node, void *value, int after) {
        listNode *node;
    
        if ((node = zmalloc(sizeof(*node))) == NULL)
            return NULL;
        node->value = value;
        if (after) {
            node->prev = old_node;
            node->next = old_node->next;
            if (list->tail == old_node) {
                list->tail = node;
            }
        } else {
            node->next = old_node;
            node->prev = old_node->prev;
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

删除节点时要考虑节点是否为链表的头结点或者尾节点。如果是则要更新链表的信息，否则只要更新待删除的节点前后节点指向关系。

    void listDelNode(list *list, listNode *node)
    {
        if (node->prev)
            node->prev->next = node->next;
        else
            list->head = node->next;
        if (node->next)
            node->next->prev = node->prev;
        else
            list->tail = node->prev;
        if (list->free) list->free(node->value);
        zfree(node);
        list->len--;
    }

## 创建和释放迭代器

迭代器是一种辅助遍历链表的结构，它分为向前迭代器和向后迭代器。我们在迭代器结构中可以发现方向类型变量

    typedef struct listIter {
        listNode *next;
        int direction;
    } listIter;

创建一个迭代器，需要指定方向，从而可以让迭代器的next指针指向链表的头结点或者尾节点

    listIter *listGetIterator(list *list, int direction)
    {
        listIter *iter;
    
        if ((iter = zmalloc(sizeof(*iter))) == NULL) return NULL;
        if (direction == AL_START_HEAD)
            iter->next = list->head;
        else
            iter->next = list->tail;
        iter->direction = direction;
        return iter;
    }

还可以通过下面两个方法，让迭代器类型发生转变，比如可以让一个向前的迭代器变成一个向后的迭代器。还可以让这个迭代器指向另外一个链表，而非创建它时指向的链表。

    void listRewind(list *list, listIter *li) {
        li->next = list->head;
        li->direction = AL_START_HEAD;
    }
    
    void listRewindTail(list *list, listIter *li) {
        li->next = list->tail;
        li->direction = AL_START_TAIL;
    }

因为通过listGetIterator创建的迭代器是在堆上动态分配的，所以不使用时要释放

    void listReleaseIterator(listIter *iter) {
        zfree(iter);
    }

## 迭代器遍历

迭代器的遍历其实就是简单的通过节点向前向后指针访问到下一个节点的过程

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

## 链表复制

链表的复制过程就是通过一个从头向尾访问的迭代器，将元链表中的数据复制到新建的链表中。但是这儿有个地方需要注意下，就是复制操作可能分为深拷贝和浅拷贝。如果我们通过listSetDupMethod设置了数据的复制方法，则使用该方法进行数据的复制，然后将复制出来的新数据放到新的链表中。如果没有设置，则只是把老链表中元素的value字段赋值过去。 

    list *listDup(list *orig)
    {
        list *copy;
        listIter iter;
        listNode *node;
    
        if ((copy = listCreate()) == NULL)
            return NULL;
        copy->dup = orig->dup;
        copy->free = orig->free;
        copy->match = orig->match;
        listRewind(orig, &iter);
        while((node = listNext(&iter)) != NULL) {
            void *value;
    
            if (copy->dup) {
                value = copy->dup(node->value);
                if (value == NULL) {
                    listRelease(copy);
                    return NULL;
                }
            } else
                value = node->value;
            if (listAddNodeTail(copy, value) == NULL) {
                listRelease(copy);
                return NULL;
            }
        }
        return copy;
    }

## 查找元素

查找元素同样是通过迭代器遍历整个链表，然后视用户是否通过listSetMatchMethod设置对比方法来决定是使用用户定义的方法去对比，还是直接使用value去对比。如果是使用value直接去对比，则是强对比，即要求对比的数据和链表的数据在内存中位置是一样的。

    listNode *listSearchKey(list *list, void *key)
    {
        listIter iter;
        listNode *node;
    
        listRewind(list, &iter);
        while((node = listNext(&iter)) != NULL) {
            if (list->match) {
                if (list->match(node->value, key)) {
                    return node;
                }
            } else {
                if (key == node->value) {
                    return node;
                }
            }
        }
        return NULL;
    }

## 通过下标访问链表

下标可以是负数，代表返回从后数第几个元素。 

    listNode *listIndex(list *list, long index) {
        listNode *n;
    
        if (index < 0) {
            index = (-index)-1;
            n = list->tail;
            while(index-- && n) n = n->prev;
        } else {
            n = list->head;
            while(index-- && n) n = n->next;
        }
        return n;
    }

## 结尾节点前移为头结点

这个方法在Redis代码中使用比较多。它将链表最后一个节点移动到链表头部。我想设计这么一个方法是为了让链表内容可以在无状态记录的情况下被均匀的轮询到。

    void listRotate(list *list) {
        listNode *tail = list->tail;
    
        if (listLength(list) <= 1) return;
    
        /* Detach current tail */
        list->tail = tail->prev;
        list->tail->next = NULL;
        /* Move it as head */
        list->head->prev = tail;
        tail->prev = NULL;
        tail->next = list->head;
        list->head = tail;
    }


[2]: http://blog.csdn.net/breaksoftware/article/details/53525621

[5]: http://blog.csdn.net/breaksoftware/article/details/53485416