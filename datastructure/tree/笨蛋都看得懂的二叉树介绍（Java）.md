## 笨蛋都看得懂的二叉树介绍（Java）

来源：[https://segmentfault.com/a/1190000014035178](https://segmentfault.com/a/1190000014035178)

本文专门针对笨蛋介绍如何编写二叉树，包括二叉树的结构、如何添加节点、如何删除节点。  
首先介绍二叉树的结构。  
![][0]  
二叉树的结构有三个要点： 

* 每个节点最多有两个子节点，分别称作左子节点和右子节点。
* 每个节点的左子节点的值比它小，右子节点的值比它大。
* 每个节点的左子树每个节点的值都比它小，右子树每个节点的值都比它大。


看上面这个例子，就完全符合这三点。  
这时候笨蛋就会问了：前面两点我理解，但是第三点是怎么做到的？  
所以接下来介绍下二叉树是如何 “生长” 起来的：  
![][1]  
如上图所示，当加入一个新节点时，从根节点开始对它进行比较。如果它比根节点小，则放入根节点的左子树，如果比根节点大，则放入根节点的右子树。  
然后再进行下一级节点的比较，直到遇到最后一级节点，才将新节点加入为该节点的左或右子节点。  
以第四幅图的节点 25 为例，它第一次会与根节点 10 比较，结果就是 25 应该放入 10 的右子树，这就排除了它放入左子树的可能，即 25 不可能放到 4 的下面。  
然后 25 再和节点 33 比较，结果是它比较小，所以应该放入 33 的左子树。因为 33 没有左子节点，那么 25 就直接作为 33 的左子节点了。  
通过这种生长方式，我们无论何时都能得到满足前面三个要素的二叉树。  
那么写代码该如何实现呢？所谓慢工出细活，我们一步一步来。  
首先我们创建二叉树节点的基本结构。每个二叉树都有四个成员，如下所示。  

```java
public class BasicBTree {

    public int value;            // 节点的值

    public BasicBTree left;      // 节点的左子节点

    public BasicBTree right;     // 节点的右子节点

    public BasicBTree parent;    // 节点的父节点。如果为 null 则表示该节点是根节点
    
    // 构造方法
    public BasicBTree(int value) {
        this.value = value;
    }
}
```

回头看第一张图，你会发现每个节点最多有三根线连着，上面的线就代表`BasicBTree`的`parent`，下面两根线就分别代表`left`和`right`了。而节点中的数字就是`BasicBTree`的`value`。  
接下来我们要为`BasicBTree`编写两个简单的方法，用来给它添加左子节点和右子节点：  

```java
// 将一个节点加为当前节点的左子节点
public void setLeft(BasicBTree node) {
    if (this.left != null) {
        this.left.parent = null;  // 解除当前的左子节点
    }
    this.left = node;
    if (this.left != null) {
        this.left.parent = this;  // 设置新子节点的父节点为自身
    }
}

// 将一个节点加为当前节点的右子节点
public void setRight(BasicBTree node) {
    if (this.right != null) {
        this.right.parent = null; // 解除当前的右子节点
    }
    this.right = node;
    if (this.right != null) {
        this.right.parent = this; // 设置新子节点的父节点为自身
    }
}
```

在上面两个方法的基础上，我们可以添加一个添加任意值节点的方法：  

```java
// 将一个节点加为当前节点的左或右子节点
public void setChild(BasicBTree node) {
    if (node == null) {
        return;
    }

    if (node.value < this.value) {
        setLeft(node);
    } else if (node.value > this.value) {
        setRight(node);
    }
}
```

另外我们再添加一个删除左子节点或右子节点的方法：  

```java
// 删除当前节点的一个直接子节点
public void deleteChild(BasicBTree node) {
    if (node == null) {
        return;
    }

    if (node == this.left) {
        node.parent = null;
        this.left = null;
    } else if (node == right) {
        node.parent = null;
        this.right = null;
    }
}
```

这几个方法都是非常简单的，其中`setChild()`和`deleteChild()`这两个方法，我们后面介绍删除节点的时候会用到。  
现在我们正式实现构造树的方法，就是把一个一个数字加到树里面去，让树越长越大的方法：  

```java
// 向当前节点下面的树中添加一个值作为新节点
public void add(int value) {
    if (value < this.value) {           // 表示应该放入左子树
        if (this.left == null) {        // 如果左子树为空则构建一个节点加进去
            setLeft(new BasicBTree(value));
        } else {
            this.left.add(value);       // 否则对左子树同样调用 add 方法（即递归）
        }
    } else if (value > this.value) {    // 表示应该放入右子树
        if (this.right == null) {       // 如果右子树为空则构建一个节点加进去
            setRight(new BasicBTree(value));
        } else {
            this.right.add(value);      // 否则对右子树同样调用 add 方法（即递归）
        }
    }
}
```

这个方法稍微复杂一些，主要是因为逻辑上使用了递归。这个方法怎么用呢？以最开始的树为例，演示如何长成这棵树：  

```java
public static void main(String[] args) {
    // 根节点
    BasicBTree tree = new BasicBTree(10);
    
    // 第一层子节点
    tree.add(4);
    tree.add(33);
    
    // 第二层子节点
    tree.add(25);
    tree.add(46);
    tree.add(8);
    tree.add(1);
}
```

你可能会注意到，加入每一层的子节点时，层内节点的添加顺序可以任意调换，构造出来的树都是一样的；但是如果将不同层的节点顺序互换，构造出来的二叉树就会变样了。这当中的原因可以自己想想。  

-----

最后来介绍二叉树中 **`最复杂的操作`** ：删除节点。为什么这个操作最复杂呢？因为删除一个节点之后，要把它下面的节点接上来，同时要保持这棵树继续满足三要素。  
如何把下面的节点接上来呢？最笨的方法当然是把被删节点的所有子节点一个个重新往树里面加。但是这样做效率实在不高。想想如果被删节点有上百万个子节点，那操作步骤就太多了（如下图所示）。  
![][2]  
怎么做才能效率高呢？有一个办法，就是从被删节点的子节点中找到一个合适的，替换掉被删节点。这样做的步骤就少得多了。  
不过这样的节点是否存在呢？答案是，除非被删节点没有子节点，否则是一定存在的。  
而且这样的节点可能不止一个。原则上讲，被删节点的左子树的最大值，或右子树的最小值，都是满足条件的，都可以用来替换被删节点。比如说，将左子树的最大值节点替换上去之后，左子树的剩余节点的值都仍然小于该位置的节点。下面是一个例子：  
![][3]  
比如要删除节点 33，而该节点左子树的最大值为 31，那么直接将 31 替换到 33 的位置即可，整棵树仍然满足三要素。  
同理，被删节点右子树的最小值也可以用来替换被删节点。比如上图中 33 节点的右子节点 46 也可以用来替换 33，整棵树仍然满足三要素。  
所以这个问题就转化为：如何寻找被删节点的左子树的最大值和右子树的最小值。显然，因为二叉树所有的左节点都比较小，右节点都比较大，所以要找最大值，顺着右节点找即可；要找最小值，顺着左节点找即可。下面是实现的代码：  

```java
// 搜索当前节点左子树中的最大值节点，如果没有左子节点则返回 null
public BasicBTree leftMax() {
    if (this.left == null) {
        return null;
    }

    BasicBTree result = this.left;  // 起始节点
    while (result.right != null) {  // 顺着右节点找
        result = result.right;
    }
    return result;
}

// 搜索当前节点右子树中的最小值节点，如果没有右子节点则返回 null
public BasicBTree rightMin() {
    if (this.right == null) {
        return null;
    }

    BasicBTree result = this.right; // 起始节点
    while (result.left != null) {   // 顺着左节点找
        result = result.left;
    }
    return result;
}
```

我们还剩下两个准备工作，第一个是实现节点的查找：  

```java
// 查询指定值的节点，如果找不到则返回 null
public BasicBTree find(int value) {
    BasicBTree result = this;     // 起始节点

    if (result.value == value) {
        return result;
    }

    while (result.left != null || result.right != null) {
        // 如果查找的值比当前节点小则顺着左子树查找；
        // 如果比当前节点大则顺着右子树查找。
        if (value < result.value && result.left != null) {
            result = result.left;
        } else if (value > result.value && result.right != null) {
            result = result.right;
        }

        if (result.value == value) {
            return result;
        }
    }

    return null;
}
```

第二个是实现节点的替换：  

```java
// 将节点 node 替换为节点 replace
public BasicBTree replace(BasicBTree node, BasicBTree replace) {

    // 1. replace 接管 node 的子节点
    replace.setLeft(node.left);
    replace.setRight(node.right);

    // 2. replace 从原来的 parent 脱离
    if (replace.parent != null) {
        replace.parent.deleteChild(replace);
    }

    // 3. node 原来的 parent 接管 replace
    if (node.parent != null) {
        node.parent.setChild(replace);
    }

    // 注意 2 必须在 3 之前，1 位置不论
    return replace;
}
```

注意这里用到了之前的`setChild()`和`deleteChild()`两个方法。而`replace()`方法之所以设计为返回`replace`参数，是为了使用方便。  
最后我们就可以正式实现二叉树删除节点的方法了：  

```java
// 从树的子节点中删除指定的值，并重组剩余节点
public BasicBTree delete(int value) {
    BasicBTree node = find(value);
    if (node == null) {
        return this;
    }

    // 没有子节点，直接删除即可
    if (node.left == null && node.right == null) {
        if (node.parent != null) {
            node.parent.deleteChild(node);
            return this;
        } else {
            // 表示整棵树唯一的根节点删了，只能返回 null
            return null;
        }
    }

    // 如果有子节点，则取左子树的最大值或者右子树的最小值都可以，
    // 来取代该节点。这里优先取左子树的最大值
    BasicBTree replace;
    if (node.left != null) {
        replace = replace(node, node.leftMax());
    } else {
        replace = replace(node, node.rightMin());
    }

    // 如果被删除的是根节点，则返回用于替换的节点，否则还是返回根节点
    return node == this ? replace : this;
}
```

[0]: ./img/bV62ti.png
[1]: ./img/bV62vY.png
[2]: ./img/bV625t.png
[3]: ./img/bV628r.png
