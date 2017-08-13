# B树的那点事儿

<font face=微软雅黑>

 时间 2017-08-13 14:00:04  ylvanasSun Blog

原文[https://sylvanassun.github.io/2017/08/13/2017-08-13-BTrees/][1]


B树( `B-Tree` )是一种自平衡的树,能够保证数据有序.同时它还保证了在查找、插入、删除等操作时性能都能保持在  `O(logn)` .需要注意的一点是, `B-Tree`并不是一棵自平衡的二叉查找树,它拥有多个分叉,且为大块数据的读写操作做了优化,同时它也可以用来描述外部存储(支持对保存在磁盘或者网络上的符号表进行外部查找).

在当今的互联网环境下,数据量已经大到无法想象,而能够在巨型数据集合中快速地进行查找操作是非常重要的,而 `B-Tree` 的神奇之处正在于: 只需要使用4~5个指向一小块数据的引用即可有效支持在数百亿甚至更多元素的符号表中进行查找和插入等操作. 

`B-Tree` 的主要应用在于文件系统与数据库系统,例如 `Mysql` 中的 `InnoDB` 存储引擎就使用到了 `B-Tree` 来实现索引. 

本文作者为: [SylvanasSun][4] .转载请务必将下面这段话置于文章开头处(保留超链接). 

本文转发自SylvanasSun Blog,原文链接: [https://sylvanassun.github.io/2017/08/13/2017-08-13-BTrees/][5]

### 数据表示 

我们使用页来表示一块连续的数据,访问一页的数据需要将它读入本地内存.一个页可能是本地计算机上的一个文件,也可能是服务器上的某个文件的一部分等等.页的访问次数(无论读写)即是外部查找算法的成本模型.

首先,构造一棵 `B-Tree`**不会将数据保存在树中** ,而是会构造一棵 **由键的副本组成的树,每个副本都关联着一条链接** .这种方法能够将索引与符号表进行分离,同时我们还需要遵循以下的规定: 

* 选择一个参数 `M` 来构造一棵多向树( `M` 一般为偶数),每个节点最多含有 `M - 1` 对键和链接.

* 每个节点最少含有 `M / 2` 对键和链接,根节点例外(它最少可以含有2对).

* .使用 M 阶的 `B-Tree` 来指定 `M` 的值,例如: 在一棵4阶 `B-Tree` 中,每个节点都含有至少2对至多3对.

* `B-Tree` 含有两种不同类型的节点,内部节点与外部节点.

* 内部节点含有与页相关联的键的副本: 每个键都与一个节点相关联(一条链接),以此节点为根的子树中,所有的键都大于等于与此节点关联的键,但小于原内部节点中更大的键(如果存在的话).

* 外部节点含有指向实际数据的引用: 每个键都对应着实际的值,外部节点就是一张普通的符号表.

![][6]

```java
// max children per B-tree node = M - 1
// must be even and greater than 2
private static final int M = 4;
// root of the B-tree
private Node root;
// height of the B-tree
private int height;
// number of key-value paris int the B-tree
private int N;
// B-tree node data type
private static final class Node {
    private int children_length;
    private Entry[] children = new Entry[M];
    // create a node with k children
    private Node(int k) {
        children_length = k;
    }
}
// internal nodes : only use key and next
// external nodes : only use key and value
private static class Entry {
    private Comparable key;
    private final Object value;
    private Node next;
    private Entry(Comparable key, Object value, Node next) {
        this.key = key;
        this.value = value;
        this.next = next;
    }
}
```
    

### 查找 

在 `B-Tree` 中进行查找操作每次都会结束于一个外部节点.在查找时, 从根节点开始,根据被查找的键来选择当前节点中的适当区间并根据对应的链接从一个节点移动到下一层节点 .最终,查找过程会到达树底的一个含有键的页(也就是外部节点),如果被查找的键在该页中,查找命中并结束,如果不在,则查找未命中. 

```java
public Value get(Key key) {
    validateKey(key, "argument key to get() is null.");
    return search(root, key, height);
}
private Value search(Node x, Key key, int height) {
    while (x != null) {
        Entry[] children = x.children;
        int children_length = x.children_length;
        // 当树的高度已经递减为0时,也就到达了树的底部(一个外部节点)
        // 遍历当前节点的每个键进行比较,如果找到则查找命中返回对应的值.
        if (height == 0) {
            for (int j = 0; j < children_length; j++) {
                if (eq(key, children[j].key))
                    return (Value) children[j].value;
            }
        } else {
            // 当还是内部节点时,根据键来查找适当的区间
            for (int j = 0; j < children_length; j++) {
                if (j + 1 == children_length || less(key, children[j + 1].key)) {
                    // 找到适当的区间后,移动到下一层节点
                    x = children[j].next;
                    height--;
                    break;
                }
            }
        }
    }
    return null;
}
```

### 插入 

插入操作也要先从根节点不断递归地查找到合适的区间,但需要注意一点,如果查找到的外部节点已经满了怎么办呢?

解决方法也很简单,我们允许被插入的节点暂时”溢出”,然后在递归调用自底向上不断地进行分裂.例如:当 `M` 为5时,根节点溢出为 `6-节点` ,只需要将它分裂为连接了两个 `3-节点` 的 `2-节点` .即将一个 `M-` 的父节点 k 分裂为连接着两个 `(M/2)-` 节点的 `(k+1)-` 节点. 

```java
public void put(Key key, Value value) {
    validateKey(key, "argument key to put() is null.");
    Node u = insert(root, key, value, height);
    N++;
    if (u == null)
        return;
    // need to split root
    Node t = new Node(2);
    t.children[0] = new Entry(root.children[0].key, null, root);
    t.children[1] = new Entry(u.children[0].key, null, u);
    root = t;
    height++;
}
private Node insert(Node x, Key key, Value value, int height) {
    int j;
    Entry t = new Entry(key, value, null);
    Entry[] children = x.children;
    int children_length = x.children_length;
    // external node
    if (height == 0) {
        for (j = 0; j < children_length; j++) {
            if (less(key, children[j].key))
                break;
        }
    } else {
        // internal node
        for (j = 0; j < children_length; j++) {
            if (j + 1 == children_length || less(key, children[j + 1].key)) {
                // 找到合适的区间后继续递归调用
                Node u = insert(children[j++].next, key, value, height - 1);
                // 如果下一层没有进行过分裂操作,直接返回null
                if (u == null)
                    return null;    
                t.key = u.children[0].key;
                t.next = u;
                break;
            }
        }
    }
    // 将j之后的元素全部右移(为了腾出j的插入位置)
    for (int i = children_length; i > j; i--) {
        children[i] = children[i - 1];
    }
    
    children[j] = t;
    x.children_length++;
    if (x.children_length < M)
        return null;
    else
        return split(x); // 如果空间已满,进行分裂
}   
 // 将x分裂为两个含有new_length对键的节点
private Node split(Node x) {
    int new_length = M / 2;
    Node t = new Node(new_length);
    x.children_length = new_length;
    for (int j = 0; j < new_length; j++)
        t.children[j] = x.children[new_length + j];
    return t;
}
```

</font>

[1]: https://sylvanassun.github.io/2017/08/13/2017-08-13-BTrees/

[4]: https://github.com/SylvanasSun
[5]: https://sylvanassun.github.io/2017/08/13/2017-08-13-BTrees/
[6]: ../img/btree.png