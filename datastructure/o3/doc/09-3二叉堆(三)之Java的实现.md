## [二叉堆(三)之 Java的实现][0]
<font face=黑体>
### **概要**

前面分别通过[C][1]和[C++][2]实现了二叉堆，本章给出二叉堆的Java版本。还是那句话，它们的原理一样，择其一了解即可。

**目录**   
[1. 二叉堆的介绍][3]   
[2. 二叉堆的图文解析][4]   
[3. 二叉堆的Java实现(完整源码)][5]   
[4. 二叉堆的Java测试程序][6]

转载请注明出处：[http://www.cnblogs.com/skywang12345/p/3610390.html][0]

- - -

**更多内容：**[数据结构与算法系列 目录][7]

(01) [二叉堆(一)之 图文解析 和 C语言的实现][1]   
(02) [二叉堆(二)之 C++的实现][2]   
(03) [二叉堆(三)之 Java的实][0]

### **二叉堆的介绍**

二叉堆是完全二元树或者是近似完全二元树，按照数据的排列方式可以分为两种： 最大堆 和 最小堆 。   
最大堆：父结点的键值总是大于或等于任何一个子节点的键值；最小堆：父结点的键值总是小于或等于任何一个子节点的键值。

二叉堆一般都通过"数组"来实现，下面是数组实现的最大堆和最小堆的示意图：

![](../img/182339209436216.jpg)

### **二叉堆的图文解析**

图文解析是以"最大堆"来进行介绍的。   
最大堆的核心内容是"添加"和"删除"，理解这两个算法，二叉堆也就基本掌握了。下面对它们进行介绍，其它内容请参考后面的完整源码。

**1. 添加**

假设在最大堆[90,80,70,60,40,30,20,10,50]种添加85，需要执行的步骤如下：

![](../img/182345301461858.jpg)

_如上图所示，当向最大堆中添加数据时：先将数据加入到最大堆的最后，然后尽可能把这个元素往上挪，直到挪不动为止！_  
将85添加到[90,80,70,60,40,30,20,10,50]中后，最大堆变成了[90,85,70,60,80,30,20,10,50,40]。

**最大堆的插入代码(Java语言)**

 
```java

    /*
     * 最大堆的向上调整算法(从start开始向上直到0，调整堆)
     *
     * 注：数组实现的堆中，第N个节点的左孩子的索引值是(2N+1)，右孩子的索引是(2N+2)。
     *
     * 参数说明：
     *     start -- 被上调节点的起始位置(一般为数组中最后一个元素的索引)
     */
    protected void filterup(int start) {
        int c = start;            // 当前节点(current)的位置
        int p = (c-1)/2;        // 父(parent)结点的位置 
        T tmp = mHeap.get(c);        // 当前节点(current)的大小
    
        while(c > 0) {
            int cmp = mHeap.get(p).compareTo(tmp);
            if(cmp >= 0)
                break;
            else {
                mHeap.set(c, mHeap.get(p));
                c = p;
                p = (p-1)/2;   
            }       
        }
        mHeap.set(c, tmp);
    }
      
    /* 
     * 将data插入到二叉堆中
     */
    public void insert(T data) {
        int size = mHeap.size();
    
        mHeap.add(data);    // 将"数组"插在表尾
        filterup(size);        // 向上调整堆
    }
```

insert(data)的作用：将数据data添加到最大堆中。mHeap是动态数组ArrayList对象。   
当堆已满的时候，添加失败；否则data添加到最大堆的末尾。然后通过上调算法重新调整数组，使之重新成为最大堆。

**2. 删除**

假设从最大堆[90,85,70,60,80,30,20,10,50,40]中删除90，需要执行的步骤如下：

![](../img/182348387716132.jpg)

_如上图所示，当从最大堆中删除数据时：先删除该数据，然后用最大堆中最后一个的元素插入这个空位；接着，把这个“空位”尽量往上挪，直到剩余的数据变成一个最大堆。_  
从[90,85,70,60,80,30,20,10,50,40]删除90之后，最大堆变成了[85,80,70,60,40,30,20,10,50]。

  
注意：考虑从最大堆[90,85,70,60,80,30,20,10,50,40]中删除60，执行的步骤不能单纯的用它的字节点来替换；而必须考虑到"替换后的树仍然要是最大堆"！

![](../img/182350015371912.jpg)

**二叉堆的删除代码(Java语言)**

 
```java

    /* 
     * 最大堆的向下调整算法
     *
     * 注：数组实现的堆中，第N个节点的左孩子的索引值是(2N+1)，右孩子的索引是(2N+2)。
     *
     * 参数说明：
     *     start -- 被下调节点的起始位置(一般为0，表示从第1个开始)
     *     end   -- 截至范围(一般为数组中最后一个元素的索引)
     */
    protected void filterdown(int start, int end) {
        int c = start;          // 当前(current)节点的位置
        int l = 2*c + 1;     // 左(left)孩子的位置
        T tmp = mHeap.get(c);    // 当前(current)节点的大小
    
        while(l <= end) {
            int cmp = mHeap.get(l).compareTo(mHeap.get(l+1));
            // "l"是左孩子，"l+1"是右孩子
            if(l < end && cmp<0)
                l++;        // 左右两孩子中选择较大者，即mHeap[l+1]
            cmp = tmp.compareTo(mHeap.get(l));
            if(cmp >= 0)
                break;        //调整结束
            else {
                mHeap.set(c, mHeap.get(l));
                c = l;
                l = 2*l + 1;   
            }       
        }   
        mHeap.set(c, tmp);
    }
    
    /*
     * 删除最大堆中的data
     *
     * 返回值：
     *      0，成功
     *     -1，失败
     */
    public int remove(T data) {
        // 如果"堆"已空，则返回-1
        if(mHeap.isEmpty() == true)
            return -1;
    
        // 获取data在数组中的索引
        int index = mHeap.indexOf(data);
        if (index==-1)
            return -1;
    
        int size = mHeap.size();
        mHeap.set(index, mHeap.get(size-1));// 用最后元素填补
        mHeap.remove(size - 1);                // 删除最后的元素
    
        if (mHeap.size() > 1)
            filterdown(index, mHeap.size()-1);    // 从index号位置开始自上向下调整为最小堆
    
        return 0;
    }
```

### **二叉堆的Java实现(完整源码)**

二叉堆的实现同时包含了"最大堆"和"最小堆"。   
二叉堆(最大堆)的实现文件(MaxHeap.java)

```java
/**
 * 二叉堆(最大堆)
 *
 * @author skywang
 * @date 2014/03/07
 */

import java.util.ArrayList;
import java.util.List;

public class MaxHeap<T extends Comparable<T>> {

    private List<T> mHeap;    // 队列(实际上是动态数组ArrayList的实例)

    public MaxHeap() {
        this.mHeap = new ArrayList<T>();
    }

    /* 
     * 最大堆的向下调整算法
     *
     * 注：数组实现的堆中，第N个节点的左孩子的索引值是(2N+1)，右孩子的索引是(2N+2)。
     *
     * 参数说明：
     *     start -- 被下调节点的起始位置(一般为0，表示从第1个开始)
     *     end   -- 截至范围(一般为数组中最后一个元素的索引)
     */
    protected void filterdown(int start, int end) {
        int c = start;          // 当前(current)节点的位置
        int l = 2*c + 1;     // 左(left)孩子的位置
        T tmp = mHeap.get(c);    // 当前(current)节点的大小

        while(l <= end) {
            int cmp = mHeap.get(l).compareTo(mHeap.get(l+1));
            // "l"是左孩子，"l+1"是右孩子
            if(l < end && cmp<0)
                l++;        // 左右两孩子中选择较大者，即mHeap[l+1]
            cmp = tmp.compareTo(mHeap.get(l));
            if(cmp >= 0)
                break;        //调整结束
            else {
                mHeap.set(c, mHeap.get(l));
                c = l;
                l = 2*l + 1;   
            }       
        }   
        mHeap.set(c, tmp);
    }

    /*
     * 删除最大堆中的data
     *
     * 返回值：
     *      0，成功
     *     -1，失败
     */
    public int remove(T data) {
        // 如果"堆"已空，则返回-1
        if(mHeap.isEmpty() == true)
            return -1;

        // 获取data在数组中的索引
        int index = mHeap.indexOf(data);
        if (index==-1)
            return -1;

        int size = mHeap.size();
        mHeap.set(index, mHeap.get(size-1));// 用最后元素填补
        mHeap.remove(size - 1);                // 删除最后的元素

        if (mHeap.size() > 1)
            filterdown(index, mHeap.size()-1);    // 从index号位置开始自上向下调整为最小堆

        return 0;
    }

    /*
     * 最大堆的向上调整算法(从start开始向上直到0，调整堆)
     *
     * 注：数组实现的堆中，第N个节点的左孩子的索引值是(2N+1)，右孩子的索引是(2N+2)。
     *
     * 参数说明：
     *     start -- 被上调节点的起始位置(一般为数组中最后一个元素的索引)
     */
    protected void filterup(int start) {
        int c = start;            // 当前节点(current)的位置
        int p = (c-1)/2;        // 父(parent)结点的位置 
        T tmp = mHeap.get(c);        // 当前节点(current)的大小

        while(c > 0) {
            int cmp = mHeap.get(p).compareTo(tmp);
            if(cmp >= 0)
                break;
            else {
                mHeap.set(c, mHeap.get(p));
                c = p;
                p = (p-1)/2;   
            }       
        }
        mHeap.set(c, tmp);
    }
      
    /* 
     * 将data插入到二叉堆中
     */
    public void insert(T data) {
        int size = mHeap.size();

        mHeap.add(data);    // 将"数组"插在表尾
        filterup(size);        // 向上调整堆
    }

    @Override
    public String toString() {
        StringBuilder sb = new StringBuilder();
        for (int i=0; i<mHeap.size(); i++)
            sb.append(mHeap.get(i) +" ");

        return sb.toString();
    }
 
    public static void main(String[] args) {
        int i;
        int a[] = {10, 40, 30, 60, 90, 70, 20, 50, 80};
        MaxHeap<Integer> tree=new MaxHeap<Integer>();

        System.out.printf("== 依次添加: ");
        for(i=0; i<a.length; i++) {
            System.out.printf("%d ", a[i]);
            tree.insert(a[i]);
        }

        System.out.printf("\n== 最 大 堆: %s", tree);

        i=85;
        tree.insert(i);
        System.out.printf("\n== 添加元素: %d", i);
        System.out.printf("\n== 最 大 堆: %s", tree);

        i=90;
        tree.remove(i);
        System.out.printf("\n== 删除元素: %d", i);
        System.out.printf("\n== 最 大 堆: %s", tree);
        System.out.printf("\n");
    }
}
```

二叉堆(最小堆)的实现文件(MinHeap.java)

```java
/**
 * 二叉堆(最小堆)
 *
 * @author skywang
 * @date 2014/03/07
 */

import java.util.ArrayList;
import java.util.List;

public class MinHeap<T extends Comparable<T>> {

    private List<T> mHeap;        // 存放堆的数组

    public MinHeap() {
        this.mHeap = new ArrayList<T>();
    }

    /* 
     * 最小堆的向下调整算法
     *
     * 注：数组实现的堆中，第N个节点的左孩子的索引值是(2N+1)，右孩子的索引是(2N+2)。
     *
     * 参数说明：
     *     start -- 被下调节点的起始位置(一般为0，表示从第1个开始)
     *     end   -- 截至范围(一般为数组中最后一个元素的索引)
     */
    protected void filterdown(int start, int end) {
        int c = start;          // 当前(current)节点的位置
        int l = 2*c + 1;     // 左(left)孩子的位置
        T tmp = mHeap.get(c);    // 当前(current)节点的大小

        while(l <= end) {
            int cmp = mHeap.get(l).compareTo(mHeap.get(l+1));
            // "l"是左孩子，"l+1"是右孩子
            if(l < end && cmp>0)
                l++;        // 左右两孩子中选择较小者，即mHeap[l+1]

            cmp = tmp.compareTo(mHeap.get(l));
            if(cmp <= 0)
                break;        //调整结束
            else {
                mHeap.set(c, mHeap.get(l));
                c = l;
                l = 2*l + 1;   
            }       
        }   
        mHeap.set(c, tmp);
    }
     
    /*
     * 最小堆的删除
     *
     * 返回值：
     *     成功，返回被删除的值
     *     失败，返回null
     */
    public int remove(T data) {
        // 如果"堆"已空，则返回-1
        if(mHeap.isEmpty() == true)
            return -1;

        // 获取data在数组中的索引
        int index = mHeap.indexOf(data);
        if (index==-1)
            return -1;

        int size = mHeap.size();
        mHeap.set(index, mHeap.get(size-1));// 用最后元素填补
        mHeap.remove(size - 1);                // 删除最后的元素

        if (mHeap.size() > 1)
            filterdown(index, mHeap.size()-1);    // 从index号位置开始自上向下调整为最小堆

        return 0;
    }

    /*
     * 最小堆的向上调整算法(从start开始向上直到0，调整堆)
     *
     * 注：数组实现的堆中，第N个节点的左孩子的索引值是(2N+1)，右孩子的索引是(2N+2)。
     *
     * 参数说明：
     *     start -- 被上调节点的起始位置(一般为数组中最后一个元素的索引)
     */
    protected void filterup(int start) {
        int c = start;            // 当前节点(current)的位置
        int p = (c-1)/2;        // 父(parent)结点的位置 
        T tmp = mHeap.get(c);        // 当前节点(current)的大小

        while(c > 0) {
            int cmp = mHeap.get(p).compareTo(tmp);
            if(cmp <= 0)
                break;
            else {
                mHeap.set(c, mHeap.get(p));
                c = p;
                p = (p-1)/2;   
            }       
        }
        mHeap.set(c, tmp);
    }
 
    /* 
     * 将data插入到二叉堆中
     */
    public void insert(T data) {
        int size = mHeap.size();

        mHeap.add(data);    // 将"数组"插在表尾
        filterup(size);        // 向上调整堆
    }
       
    public String toString() {
        StringBuilder sb = new StringBuilder();
        for (int i=0; i<mHeap.size(); i++)
            sb.append(mHeap.get(i) +" ");

        return sb.toString();
    }

    public static void main(String[] args) {
        int i;
        int a[] = {80, 40, 30, 60, 90, 70, 10, 50, 20};
        MinHeap<Integer> tree=new MinHeap<Integer>();

        System.out.printf("== 依次添加: ");
        for(i=0; i<a.length; i++) {
            System.out.printf("%d ", a[i]);
            tree.insert(a[i]);
        }

        System.out.printf("\n== 最 小 堆: %s", tree);

        i=15;
        tree.insert(i);
        System.out.printf("\n== 添加元素: %d", i);
        System.out.printf("\n== 最 小 堆: %s", tree);

        i=10;
        tree.remove(i);
        System.out.printf("\n== 删除元素: %d", i);
        System.out.printf("\n== 最 小 堆: %s", tree);
        System.out.printf("\n");
    }
}
```

### **二叉堆的Java测试程序**

测试程序已经包含在相应的实现文件中了，这里只说明运行结果。

最大堆(MaxHeap.java)的运行结果：

    == 依次添加: 10 40 30 60 90 70 20 50 80 
    == 最 大 堆: 90 80 70 60 40 30 20 10 50 
    == 添加元素: 85
    == 最 大 堆: 90 85 70 60 80 30 20 10 50 40 
    == 删除元素: 90
    == 最 大 堆: 85 80 70 60 40 30 20 10 50 

最小堆(MinHeap.java)的运行结果：

    == 最 小 堆: 10 20 30 50 90 70 40 80 60 
    == 添加元素: 15
    == 最 小 堆: 10 15 30 50 20 70 40 80 60 90 
    == 删除元素: 10
    == 最 小 堆: 15 20 30 50 90 70 40 80 60 

PS. 二叉堆是"堆排序"的理论基石。 以后讲解算法时会讲解到"堆排序"，理解了"二叉堆"之后，"堆排序"就很简单了。

</font>

[0]: http://www.cnblogs.com/skywang12345/p/3610390.html
[1]: http://www.cnblogs.com/skywang12345/p/3610187.html
[2]: http://www.cnblogs.com/skywang12345/p/3610382.html
[3]: #a1
[4]: #a2
[5]: #a3
[6]: #a4
[7]: http://www.cnblogs.com/skywang12345/p/3603935.html
