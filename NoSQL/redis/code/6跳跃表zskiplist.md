# Redis源码剖析--跳跃表zskiplist

 时间 2016-12-06 21:33:16  ZeeCoder

_原文_[http://zcheng.ren/2016/12/06/TheAnnotatedRedisSourceZskiplist/][2]



跳跃表是一种有序的数据结构，它通过在每个节点中维持多个指向其他节点的指针，从而达到快速访问的目的。跳跃表在插入、删除和查找操作上的平均复杂度为O（logN），最坏为O（N），可以和红黑树相媲美，但是在实现起来，比红黑树简单很多。

说起跳跃表，在前段时间面试中可帮了我的大忙。腾讯一面的时候面试官要求设计一个数据结构，里面的元素要求按一定顺序存放，能以最低的复杂度获取每个元素的名次，且增、删等操作的复杂度尽可能低，博主最终就是用跳跃表来解决了这个问题，平均复杂度能达到O（logN）。

## 跳跃表数据结构 

跳跃表的结构体定义在server.h文件中。其中包括跳跃表节点zskiplistNode和跳跃表zskiplist两个结构体。 

    typedef struct zskiplistNode {
        robj *obj; // 成员对象
        double score;  // 分值
        struct zskiplistNode *backward; // 后向指针
        // 层
        struct zskiplistLevel {
            struct zskiplistNode *forward; // 前向指针
            unsigned int span; // 跨度
        } level[];
    } zskiplistNode;
    
    typedef struct zskiplist {
        // 跳跃表的表头节点和表尾节点
        struct zskiplistNode *header, *tail;
        // 表中节点的数量
        unsigned long length;
        // 表中层数最大的节点层数
        int level;
    } zskiplist;
    

对于跳跃表节点来说：

* obj 存放着该节点对于的成员对象，一般指向一个sds结构
* score 表示该节点你的分值，跳跃表按照分值大小进行顺序排列
* backward 指向跳跃表的前一个节点
* level[] 这个属性至关重要，是跳跃表的核心所在，初始化一个跳跃表节点的时候会为其随机生成一个层大小，每个节点的每一层以链表的形式连接起来。

看完上面的解释之后，可能读者对跳跃表还没有一个清晰的认识，下面我画了一张图来形象的描述一下跳跃表结构。

![][5]

## 跳跃表基本操作 

Redis中关于跳跃表的相关操作函数定义在t_zset.c文件中，下面分别介绍几个基本操作函数的实现源码。

## 创建跳跃表 

Redis在创建一个跳跃表的时候完成以下操作：

* 创建一个zskiplist结构
* 设定其level为1，长度length为0
* 初始化一个表头结点，其层数为32层，每一层均指向NULL

    // 创建跳跃表
    zskiplist *zslCreate(void){
        int j;
        zskiplist *zsl;
        // 申请内存
        zsl = zmalloc(sizeof(*zsl));
        // 初始化跳跃表属性
        zsl->level = 1;
        zsl->length = 0;
        // 创建一个层数为32，分值为0，成员对象为NULL的表头结点
        zsl->header = zslCreateNode(ZSKIPLIST_MAXLEVEL,0,NULL);
        // 设定每层的forward指针指向NULL
        for (j = 0; j < ZSKIPLIST_MAXLEVEL; j++) {
            zsl->header->level[j].forward = NULL;
            zsl->header->level[j].span = 0;
        }
        // 设定backward指向NULL
        zsl->header->backward = NULL;
        zsl->tail = NULL;
        return zsl;
    }
    // 创建一个跳跃表节点
    zskiplistNode *zslCreateNode(intlevel,doublescore, robj *obj){
        // 申请内存
        zskiplistNode *zn = zmalloc(sizeof(*zn)+level*sizeof(struct zskiplistLevel));
        // 设定分值
        zn->score = score;
        // 设定成员对象
        zn->obj = obj;
        return zn;
    }
    

## 插入节点 

往跳跃表中插入一个节点，必然会改变跳表的长度，可能会改变其长度。而且对于插入位置处的前后节点的backward和forward指针均要改变。

插入节点的关键在找到在何处插入该节点，跳跃表是按照score分值进行排序的，其查找步骤大致是：从当前最高的level开始，向前查找，如果当前节点的score小于插入节点的score，继续向前；反之，则降低一层继续查找，直到第一层为止。此时，插入点就位于找到的节点之后。

    zskiplistNode *zslInsert(zskiplist *zsl,doublescore, robj *obj){
        // updata[]数组记录每一层位于插入节点的前一个节点
        zskiplistNode *update[ZSKIPLIST_MAXLEVEL], *x;
        // rank[]记录每一层位于插入节点的前一个节点的排名
        unsigned int rank[ZSKIPLIST_MAXLEVEL];
        int i, level;
    
        serverAssert(!isnan(score));
        x = zsl->header; // 表头节点
        // 从最高层开始查找
        for (i = zsl->level-1; i >= 0; i--) {
            // 存储rank值是为了交叉快速地到达插入位置
            rank[i] = i == (zsl->level-1) ? 0 : rank[i+1];
            // 前向指针不为空，前置指针的分值小于score或当前向指针的分值等// 于空但成员对象不等于o的情况下，继续向前查找
            while (x->level[i].forward &&
                (x->level[i].forward->score < score ||
                    (x->level[i].forward->score == score &&
                    compareStringObjects(x->level[i].forward->obj,obj) < 0))) {
                rank[i] += x->level[i].span;
                x = x->level[i].forward;
            }
            // 存储当前层上位于插入节点的前一个节点
            update[i] = x;
        }
        // 此处假设插入节点的成员对象不存在于当前跳跃表内，即不存在重复的节点
        // 随机生成一个level值
        level = zslRandomLevel();
        if (level > zsl->level) {
            // 如果level大于当前存储的最大level值
            // 设定rank数组中大于原level层以上的值为0
            // 同时设定update数组大于原level层以上的数据
            for (i = zsl->level; i < level; i++) {
                rank[i] = 0;
                update[i] = zsl->header;
                update[i]->level[i].span = zsl->length;
            }
            // 更新level值
            zsl->level = level;
        }
        // 创建插入节点
        x = zslCreateNode(level,score,obj);
        for (i = 0; i < level; i++) {
            // 针对跳跃表的每一层，改变其forward指针的指向
            x->level[i].forward = update[i]->level[i].forward;
            update[i]->level[i].forward = x;
    
            // 更新插入节点的span值
            x->level[i].span = update[i]->level[i].span - (rank[0] - rank[i]);
            // 更新插入点的前一个节点的span值
            update[i]->level[i].span = (rank[0] - rank[i]) + 1;
        }
    
        // 更新高层的span值
        for (i = level; i < zsl->level; i++) {
            update[i]->level[i].span++;
        }
        // 设定插入节点的backward指针
        x->backward = (update[0] == zsl->header) ? NULL : update[0];
        if (x->level[0].forward)
            x->level[0].forward->backward = x;
        else
            zsl->tail = x;
        // 跳跃表长度+1
        zsl->length++;
        return x;
    }
    

## 跳跃表删除 

Redis提供了三种跳跃表节点删除操作。分别如下：

* 根据给定分值和成员来删除节点，由zslDelete函数实现
* 根据给定分值来删除节点，由zslDeleteByScore函数实现
* 根据给定排名来删除节点，由zslDeleteByRank函数实现

上述三种操作的删除节点部分都由zslDeleteNode函数完成。zslDeleteNode函数用于删除某个节点，需要给定当前节点和每一层下当前节点的前一个节点。 

    voidzslDeleteNode(zskiplist *zsl, zskiplistNode *x, zskiplistNode **update){
        int i;
        for (i = 0; i < zsl->level; i++) {
            if (update[i]->level[i].forward == x) {
                // 如果x存在于该层，则需要修改前一个节点的前向指针
                update[i]->level[i].span += x->level[i].span - 1;
                update[i]->level[i].forward = x->level[i].forward;
            } else {
                // 反之，则只需要将span-1
                update[i]->level[i].span -= 1;
            }
        }
        // 修改backward指针，需要考虑x是否为尾节点
        if (x->level[0].forward) {
            x->level[0].forward->backward = x->backward;
        } else {
            zsl->tail = x->backward;
        }
        // 如果被删除的节点为当前层数最多的节点，
        while(zsl->level > 1 && zsl->header->level[zsl->level-1].forward == NULL)
            zsl->level--;
        zsl->length--;
    }
    

以zslDelete为例，根据节点的分值和成员来删除该节点，其他两个操作无非是在查找节点上有区别。 

    intzslDelete(zskiplist *zsl,doublescore, robj *obj){
        zskiplistNode *update[ZSKIPLIST_MAXLEVEL], *x;
        int i;
    
        x = zsl->header;
        // 找到要删除的节点，以及每一层上该节点的前一个节点
        for (i = zsl->level-1; i >= 0; i--) {
            while (x->level[i].forward &&
                (x->level[i].forward->score < score ||
                    (x->level[i].forward->score == score &&
                    compareStringObjects(x->level[i].forward->obj,obj) < 0)))
                x = x->level[i].forward;
            update[i] = x;
        }
        // 跳跃表中可能存在分值相同的节点
        // 所以此处需要判断成员是否相等
        x = x->level[0].forward;
        if (x && score == x->score && equalStringObjects(x->obj,obj)) {
            // 调用底层删除节点函数
            zslDeleteNode(zsl, x, update);
            zslFreeNode(x);
            return 1;
        }
        // 没有删除成功
        return 0; 
    }
    

## 获取给定分值和成员的节点的排名 

开篇提到博主在腾讯一面中被问的问题，需要获取每个玩家的排名，跳跃表获取排名的平均复杂度为O（logN），最坏为O（n）。其实现如下： 

    unsignedlongzslGetRank(zskiplist *zsl,doublescore, robj *o){
        zskiplistNode *x;
        unsigned long rank = 0;
        int i;
    
        x = zsl->header;
        // 从最高层开始查询
        for (i = zsl->level-1; i >= 0; i--) {
            while (x->level[i].forward &&
                (x->level[i].forward->score < score ||
                    (x->level[i].forward->score == score &&
                    compareStringObjects(x->level[i].forward->obj,o) <= 0))) {
                // 前向指针不为空，前置指针的分值小于score或当前向指针的// 分值等于空但成员对象不等于o的情况下，继续向前查找
                rank += x->level[i].span;
                x = x->level[i].forward;
            }
    
            // 此时x可能是header，所以此处需要判断一下
            if (x->obj && equalStringObjects(x->obj,o)) {
                return rank;
            }
        }
        return 0;
    }
    

这里粗略的画了一张图来说明查找过程，红线代表查找的路线。

![][6]

## 区间操作 

Redis提供了一些区间操作，用于获取某段区间上的节点或者删除某段区间上的所有节点等操作，这些操作大大提高了Redis的易用性。

    // 获取某个区间上第一个符合范围的节点。
    zskiplistNode *zslFirstInRange(zskiplist *zsl, zrangespec *range){
        zskiplistNode *x;
        int i;
    
        // 判断给定的分值范围是否在跳跃表的范围内
        if (!zslIsInRange(zsl,range)) return NULL;
    
        x = zsl->header;
    
        for (i = zsl->level-1; i >= 0; i--) {
            // 如果当前节点的分值小于给定范围的下限则一直向前查找
            while (x->level[i].forward &&
                !zslValueGteMin(x->level[i].forward->score,range))
                    x = x->level[i].forward;
        }
    
        // x的下一个节点才是我们要找的节点
        x = x->level[0].forward;
        serverAssert(x != NULL);
    
        // 检查该节点不超过给定范围范围
        if (!zslValueLteMax(x->score,range)) return NULL;
        return x;
    }
    // 获取某个区间上最后一个符合范围的节点。
    zskiplistNode *zslLastInRange(zskiplist *zsl, zrangespec *range){
        zskiplistNode *x;
        int i;
    
        // 判断给定的分值范围是否在跳跃表的范围内
        if (!zslIsInRange(zsl,range)) return NULL;
    
        x = zsl->header;
        for (i = zsl->level-1; i >= 0; i--) {
            // 如果在给定范围内则一直向前查找
            while (x->level[i].forward &&
                zslValueLteMax(x->level[i].forward->score,range))
                    x = x->level[i].forward;
        }
    
        // x即为要找的节点
        serverAssert(x != NULL);
    
        // 判断该分值是否在给定范围内
        if (!zslValueGteMin(x->score,range)) return NULL;
        return x;
    }
    // 删除给定分值范围内的所有元素
    unsignedlongzslDeleteRangeByScore(zskiplist *zsl, zrangespec *range, dict *dict){
        zskiplistNode *update[ZSKIPLIST_MAXLEVEL], *x;
        unsigned long removed = 0;
        int i;
    
        x = zsl->header;
        // 找到小于或等于给定范围最小分值的节点
        // 并将每层上的节点保存到update数组
        for (i = zsl->level-1; i >= 0; i--) {
            while (x->level[i].forward && (range->minex ?
                x->level[i].forward->score <= range->min :
                x->level[i].forward->score < range->min))
                    x = x->level[i].forward;
            update[i] = x;
        }
    
        // x的下一个节点则是给定区间内分值最小的节点
        x = x->level[0].forward;
    
        // 删除该区间下的所有节点
        while (x &&
               (range->maxex ? x->score < range->max : x->score <= range->max))
        {
            // 保存下一个节点
            zskiplistNode *next = x->level[0].forward;
            // 删除该节点
            zslDeleteNode(zsl,x,update);
            // 删除该节点的成员
            dictDelete(dict,x->obj);
            // 释放该节点
            zslFreeNode(x);
            removed++;
            x = next;
        }
        // 返回删除节点的个数
        return removed;
    }
    // 删除给定排名区间内的所有节点
    unsignedlongzslDeleteRangeByRank(zskiplist *zsl,unsignedintstart,unsignedintend, dict *dict){
        zskiplistNode *update[ZSKIPLIST_MAXLEVEL], *x;
        unsigned long traversed = 0, removed = 0;
        int i;
    
        x = zsl->header;
        // 找到给定排名区间内名次最小的节点
        // 并保存每一层下该节点的前一个节点
        for (i = zsl->level-1; i >= 0; i--) {
            while (x->level[i].forward && (traversed + x->level[i].span) < start) {
                traversed += x->level[i].span;
                x = x->level[i].forward;
            }
            update[i] = x;
        }
        // traversed保存当前删除节点的排名值
        traversed++;
        x = x->level[0].forward;
        while (x && traversed <= end) {
            // 记录下一个节点
            zskiplistNode *next = x->level[0].forward;
            // 删除该节点
            zslDeleteNode(zsl,x,update);
            // 删除该节点的成员
            dictDelete(dict,x->obj);
            // 释放该节点
            zslFreeNode(x);
            // 个数+1
            removed++;
            // 排名值加1
            traversed++;
            x = next;
        }
        // 返回删除的节点个数
        return removed;
    }
    

## 跳跃表小结 

跳跃表是有序集合的底层实现之一。在同一个跳跃表中，多个节点可以包含相同的分值，但每个节点的成员对象必须是唯一的。跳跃表的节点是按照分值进行排序的，当分值相同时，节点按照成员对象的大小进行排序。


[2]: http://zcheng.ren/2016/12/06/TheAnnotatedRedisSourceZskiplist/?utm_source=tuicool&utm_medium=referral

[5]: http://img0.tuicool.com/qQjeeiN.png!web
[6]: http://img2.tuicool.com/Fvm2UzA.jpg!web