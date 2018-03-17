## 邻接矩阵有向图(三)之 Java详解

来源：[http://www.cnblogs.com/skywang12345/p/3707618.html](http://www.cnblogs.com/skywang12345/p/3707618.html)

2014-05-11 09:25


前面分别介绍了邻接矩阵有向图的C和C++实现，本文通过Java实现邻接矩阵有向图。

 **`目录`**  
 **`1`** . [邻接矩阵有向图的介绍][100] 
 **`2`** . [邻接矩阵有向图的代码说明][101] 
 **`3`** . [邻接矩阵有向图的完整源码][102]

转载请注明出处：[http://www.cnblogs.com/skywang12345/][103]

更多内容：[数据结构与算法系列 目录][104]


 

<a name="anchor1"></a>

### **`邻接矩阵有向图的介绍 `** 

邻接矩阵有向图是指通过邻接矩阵表示的有向图。

![][0]

上面的图G2包含了"A,B,C,D,E,F,G"共7个顶点，而且包含了"<A,B>,<B,C>,<B,E>,<B,F>,<C,E>,<D,C>,<E,B>,<E,D>,<F,G>"共9条边。

上图右边的矩阵是G2在内存中的邻接矩阵示意图。A[i][j]=1表示第i个顶点到第j个顶点是一条边，A[i][j]=0则表示不是一条边；而A[i][j]表示的是第i行第j列的值；例如，A[1,2]=1，表示第1个顶点(即顶点B)到第2个顶点(C)是一条边。

<a name="anchor2"></a>

### **`邻接矩阵有向图的代码说明 `** 

 **`1. 基本定义 `** 



```java
public class MatrixDG {

    private char[] mVexs;       // 顶点集合
    private int[][] mMatrix;    // 邻接矩阵

    ...
}

```



MatrixDG是邻接矩阵有向图对应的结构体。

mVexs用于保存顶点，mMatrix则是用于保存矩阵信息的二维数组。例如，mMatrix[i][j]=1，则表示"顶点i(即mVexs[i])"和"顶点j(即mVexs[j])"是邻接点，且顶点i是起点，顶点j是终点。

 **`2. 创建矩阵 `** 

这里介绍提供了两个创建矩阵的方法。一个是 **`用已知数据`** ，另一个则 **`需要用户手动输入数据`** 。

 **`2.1 创建图(用已提供的矩阵) `** 



```java
/*
 * 创建图(用已提供的矩阵)
 *
 * 参数说明：
 *     vexs  -- 顶点数组
 *     edges -- 边数组
 */
public MatrixDG(char[] vexs, char[][] edges) {

    // 初始化"顶点数"和"边数"
    int vlen = vexs.length;
    int elen = edges.length;

    // 初始化"顶点"
    mVexs = new char[vlen];
    for (int i = 0; i < mVexs.length; i++)
        mVexs[i] = vexs[i];

    // 初始化"边"
    mMatrix = new int[vlen][vlen];
    for (int i = 0; i < elen; i++) {
        // 读取边的起始顶点和结束顶点
        int p1 = getPosition(edges[i][0]);
        int p2 = getPosition(edges[i][1]);

        mMatrix[p1][p2] = 1;
    }
}

```



该函数的作用是创建一个邻接矩阵有向图。实际上，该方法创建的有向图，就是上面的图G2。它的调用方法如下：



```java
char[] vexs = {'A', 'B', 'C', 'D', 'E', 'F', 'G'};
char[][] edges = new char[][]{
    {'A', 'B'}, 
    {'B', 'C'}, 
    {'B', 'E'}, 
    {'B', 'F'}, 
    {'C', 'E'}, 
    {'D', 'C'}, 
    {'E', 'B'}, 
    {'E', 'D'}, 
    {'F', 'G'}}; 
MatrixDG pG;

pG = new MatrixDG(vexs, edges);

```



 **`2.2 创建图(自己输入) `** 



```java
/* 
 * 创建图(自己输入数据)
 */
public MatrixDG() {

    // 输入"顶点数"和"边数"
    System.out.printf("input vertex number: ");
    int vlen = readInt();
    System.out.printf("input edge number: ");
    int elen = readInt();
    if ( vlen < 1 || elen < 1 || (elen > (vlen*(vlen - 1)))) {
        System.out.printf("input error: invalid parameters!\n");
        return ;
    }

    // 初始化"顶点"
    mVexs = new char[vlen];
    for (int i = 0; i < mVexs.length; i++) {
        System.out.printf("vertex(%d): ", i);
        mVexs[i] = readChar();
    }

    // 初始化"边"
    mMatrix = new int[vlen][vlen];
    for (int i = 0; i < elen; i++) {
        // 读取边的起始顶点和结束顶点
        System.out.printf("edge(%d):", i);
        char c1 = readChar();
        char c2 = readChar();
        int p1 = getPosition(c1);
        int p2 = getPosition(c2);

        if (p1==-1 || p2==-1) {
            System.out.printf("input error: invalid edge!\n");
            return ;
        }

        mMatrix[p1][p2] = 1;
    }
}

```



该函数是读取用户的输入，将输入的数据转换成对应的有向图。

<a name="anchor3"></a>

### **`邻接矩阵有向图的完整源码 `** 

点击查看：[源代码][105]

[0]: ../img/07.jpg
[100]: #anchor1
[101]: #anchor2
[102]: #anchor3
[103]: http://www.cnblogs.com/skywang12345/
[104]: http://www.cnblogs.com/skywang12345/p/3603935.html
[105]: https://github.com/wangkuiwu/datastructs_and_algorithm/blob/master/source/graph/basic/dg/java/MatrixDG.java