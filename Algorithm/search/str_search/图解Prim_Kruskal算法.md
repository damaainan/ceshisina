# 图解Prim&Kruskal算法

 时间 2017-12-18 11:59:54  

原文[https://juejin.im/post/5a3719c26fb9a045211ecb34][1]


假设以下情景，有一块木板，板上钉上了一些钉子，这些钉子可以由一些细绳连接起来。假设每个钉子可以通过一根或者多根细绳连接起来，那么一定存在这样的情况，即用最少的细绳把所有钉子连接起来。 更为实际的情景是这样的情况，在某地分布着 N 个村庄，现在需要在 N 个村庄之间修路，每个村庄之前的距离不同，问怎么修最短的路，将各个村庄连接起来。 以上这些问题都可以归纳为最小生成树问题，用正式的表述方法描述为：给定一个无方向的带权图 G=(V, E) ，最小生成树为集合 T , T 是以最小代价连接 V 中所有顶点所用边 E 的最小集合。 集合 T 中的边能够形成一颗树，这是因为每个节点（除了根节点）都能向上找到它的一个父节点。 

解决最小生成树问题已经有前人开道， Prime 算法和 Kruskal 算法，分别从点和边下手解决了该问题。 

## Prim算法

Prim 算法是一种产生最小生成树的算法。该算法于 1930 年由捷克数学家沃伊捷赫·亚尔尼克（英语： Vojtěch Jarník ）发现；并在 1957 年由美国计算机科学家罗伯特·普里姆（英语： Robert C. Prim ）独立发现； 1959 年，艾兹格·迪科斯彻再次发现了该算法。 

Prim 算法从任意一个顶点开始，每次选择一个与当前顶点集最近的一个顶点，并将两顶点之间的边加入到树中。 Prim 算法在找当前最近顶点时使用到了贪婪算法。 

#### 图解算法流程：

1 . 在一个加权连通图中，顶点集合 V ，边集合为 E 2 . 任意选出一个点作为初始顶点,标记为 visit ,计算所有与之相连接的点的距离，选择距离最短的，标记 visit . 

3 . 重复以下操作，直到所有点都被标记为 visit ： 在剩下的点钟，计算与已标记 visit 点距离最小的点，标记 visit ,证明加入了最小生成树。 

下面我们来看一个最小生成树生成的过程：

1 起初，从顶点 a 开始生成最小生成树 

![][4]

2 选择顶点 a 后，顶点啊置成 visit（涂黑）,计算周围与它连接的点的距离：

![][5]

3 与之相连的点距离分别为 7 , 6 , 4 ，选择 C 点距离最短，涂黑 C，同时将这条边高亮加入最小生成树：

![][6]

4 计算与 a,c 相连的点的距离（已经涂黑的点不计算），因为与 a 相连的已经计算过了，只需要计算与 c 相连的点，如果一个点与 a,c 都相连，那么它与 a 的距离之前已经计算过了，如果它与c的距离更近，则更新距离值，这里计算的是未涂黑的点距离涂黑的点的最近距离，很明显， b 和 a 为 7 ， b 和 c 的距离为 6 ，更新 b 和已访问的点集距离为 6 ，而 f , e 和 c 的距离分别是 8 , 9 ，所以还是涂黑 b ,高亮边 bc：

![][7]

5 接下来很明显， d 距离 b 最短，将 d 涂黑， bd高亮：

![][8]

6 f 距离 d 为 7 ，距离 b 为 4 ，更新它的最短距离值是 4 ，所以涂黑 f ，高亮 bf：

![][9]

7 最后只有 e了：

![][10]

#### 代码实现：

    #include<iostream>
    #define INF 10000
    using namespace std;
    const int N = 6;
    bool visit[N];
    int dist[N] = { 0, };
    int graph[N][N] = { {INF,7,4,INF,INF,INF},   //INF代表两点之间不可达
                        {7,INF,6,2,INF,4}, 
                        {4,6,INF,INF,9,8}, 
                        {INF,2,INF,INF,INF,7}, 
                        {INF,INF,9,INF,INF,1}, 
                        {INF,4,8,7,1,INF}
                      };
    int prim(int cur)
    {
        int index = cur;
        int sum = 0;
        int i = 0;
        int j = 0;
        cout << index << " ";
        memset(visit, false, sizeof(visit));
        visit[cur] = true;
        for (i = 0; i < N; i++)
            dist[i] = graph[cur][i];//初始化，每个与a邻接的点的距离存入dist
        for (i = 1; i < N; i++)
        {
            int minor = INF;
            for (j = 0; j < N; j++)
            {
                if (!visit[j] && dist[j] < minor)          //找到未访问的点中，距离当前最小生成树距离最小的点
                {
                    minor = dist[j];
                    index = j;
                }
            }
            visit[index] = true;
            cout << index << " ";
            sum += minor;
            for (j = 0; j < N; j++)
            {
                if (!visit[j] && dist[j]>graph[index][j])      //执行更新，如果点距离当前点的距离更近，就更新dist
                {
                    dist[j] = graph[index][j];
                }
            }
        }
        cout << endl;
        return sum;               //返回最小生成树的总路径值
    }
    int main()
    {
        cout << prim(0) << endl;//从顶点a开始
        return 0;
    }

## Kruskal算法

Kruskal是另一个计算最小生成树的算法，其算法原理如下。首先，将每个顶点放入其自身的数据集合中。然后，按照权值的升序来选择边。当选择每条边时，判断定义边的顶点是否在不同的数据集中。如果是，将此边插入最小生成树的集合中，同时，将集合中包含每个顶点的联合体取出，如果不是，就移动到下一条边。重复这个过程直到所有的边都探查过。

#### 图解算法流程：

1 初始情况，一个联通图，定义针对边的数据结构，包括起点，终点，边长度：

    typedef struct _node{
        int val;   //长度
        int start; //边的起点
        int end;   //边的终点
    }Node;

![][11]

2 在算法中首先取出所有的边，将边按照长短排序，然后首先取出最短的边，将 a , e放入同一个集合里，在实现中我们使用到了并查集的概念：

![][12]

3 继续找到第二短的边，将 c , d再放入同一个集合里：

![][13]

4 继续找，找到第三短的边 ab ，因为 a , e 已经在一个集合里，再将 b加入：

![][14]

5 继续找，找到 b , e ，因为 b , e 已经同属于一个集合，连起来的话就形成环了，所以边 be不加入最小生成树：

![][15]

6 再找，找到 bc ，因为 c , d 是一个集合的， a , b , e是一个集合，所以再合并这两个集合：

![][16]

这样所有的点都归到一个集合里，生成了最小生成树。

#### 代码实现：

    #include<iostream>
    #define N 7
    using namespace std;
    typedef struct _node{
        int val;
        int start;
        int end;
    }Node;
    Node V[N];
    int cmp(const void *a, const void *b)
    {
        return (*(Node *)a).val - (*(Node*)b).val;
    }
    int edge[N][3] = {  { 0, 1, 3 },
                        { 0, 4, 1 }, 
                        { 1, 2, 5 }, 
                        { 1, 4, 4 },
                        { 2, 3, 2 }, 
                        { 2, 4, 6 }, 
                        { 3, 4, 7} 
                        };
    
    int father[N] = { 0, };
    int cap[N] = {0,};
    
    void make_set()              //初始化集合，让所有的点都各成一个集合，每个集合都只包含自己
    {
        for (int i = 0; i < N; i++)
        {
            father[i] = i;
            cap[i] = 1;
        }
    }
    
    int find_set(int x)              //判断一个点属于哪个集合，点如果都有着共同的祖先结点，就可以说他们属于一个集合
    {
        if (x != father[x])
         {                              
            father[x] = find_set(father[x]);
        }     
        return father[x];
    }                                  
    
    void Union(int x, int y)         //将x,y合并到同一个集合
    {
        x = find_set(x);
        y = find_set(y);
        if (x == y)
            return;
        if (cap[x] < cap[y])
            father[x] = find_set(y);
        else
        {
            if (cap[x] == cap[y])
                cap[x]++;
            father[y] = find_set(x);
        }
    }
    
    int Kruskal(int n)
    {
        int sum = 0;
        make_set();
        for (int i = 0; i < N; i++)//将边的顺序按从小到大取出来
        {
            if (find_set(V[i].start) != find_set(V[i].end))     //如果改变的两个顶点还不在一个集合中，就并到一个集合里，生成树的长度加上这条边的长度
            {
                Union(V[i].start, V[i].end);  //合并两个顶点到一个集合
                sum += V[i].val;
            }
        }
        return sum;
    }
    int main()
    {
        for (int i = 0; i < N; i++)   //初始化边的数据，在实际应用中可根据具体情况转换并且读取数据,这边只是测试用例
        {
            V[i].start = edge[i][0];
            V[i].end = edge[i][1];
            V[i].val = edge[i][2];
        }
        qsort(V, N, sizeof(V[0]), cmp);
        cout << Kruskal(0)<<endl;
        return 0;
    }


[1]: https://juejin.im/post/5a3719c26fb9a045211ecb34

[4]: ../img/ABNbYbN.png
[5]: ../img/jU3qyab.png
[6]: ../img/mYJzE3Q.png
[7]: ../img/MFnu22.png
[8]: ../img/jiQzeyi.png
[9]: ../img/ZbaYju3.png
[10]: ../img/riMBNrE.png
[11]: ../img/jiIRvum.png
[12]: ../img/3aQZvu3.png
[13]: ../img/Q3YJ3qu.png
[14]: ../img/QBRJjyi.png
[15]: ../img/3iyIJnR.png
[16]: ../img/riIVvaB.png