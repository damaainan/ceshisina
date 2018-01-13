# [经典算法题每日演练——第十七题 Dijkstra算法][0]

或许在生活中，经常会碰到针对某一个问题，在众多的限制条件下，如何去寻找一个最优解？可能大家想到了很多诸如“线性规划”，“动态规划”

这些经典策略，当然有的问题我们可以用贪心来寻求整体最优解，在图论中一个典型的贪心法求最优解的例子就莫过于“最短路径”的问题。

一：概序

从下图中我要寻找V0到V3的最短路径，你会发现通往他们的两点路径有很多：V0->V4->V3，V0->V1->V3，当然你会认为前者是你要找的最短

路径，那如果说图的顶点非常多，你还会这么轻易的找到吗？下面我们就要将刚才我们那点贪心的思维系统的整理下。

![][1]

二：构建

如果大家已经了解Prim算法，那么Dijkstra算法只是在它的上面延伸了下，其实也是很简单的。

1.边节点

这里有点不一样的地方就是我在边上面定义一个vertexs来记录贪心搜索到某一个节点时曾经走过的节点，比如从V0贪心搜索到V3时，我们V3

的vertexs可能存放着V0,V4,V3这些曾今走过的节点，或许最后这三个节点就是我们要寻找的最短路径。
```csharp
#region 边的信息
        /// <summary>
        /// 边的信息
        /// </summary>
        public class Edge
        {
            //开始边
            public int startEdge;

            //结束边
            public int endEdge;

            //权重
            public int weight;

            //是否使用
            public bool isUse;

            //累计顶点
            public HashSet<int> vertexs = new HashSet<int>();
        }
        #endregion
```
2.Dijkstra算法

![][2]

首先我们分析下Dijkstra算法的步骤：

有集合M={V0,V1,V2,V3,V4}这样5个元素，我们用

TempVertex表示该顶点是否使用。

Weight表示该Path的权重(默认都为MaxValue)。

Path表示该顶点的总权重。

①. 从集合M中挑选顶点V0为起始点。给V0的所有邻接点赋值，要赋值的前提是要赋值的weight要小于原始的weight，并且排除已经访问过

的顶点，然后挑选当前最小的weight作为下一次贪心搜索的起点，就这样V0V1为挑选为最短路径，如图2。

②. 我们继续从V1这个顶点开始给邻接点以同样的方式赋值，最后我们发现V0V4为最短路径。也就是图3。

。。。

③. 最后所有顶点的最短路径就这样求出来了 。
```csharp
#region Dijkstra算法
        /// <summary>
        /// Dijkstra算法
        /// </summary>
        public Dictionary<int, Edge> Dijkstra()
        {
            //收集顶点的相邻边
            Dictionary<int, Edge> dic_edges = new Dictionary<int, Edge>();

            //weight=MaxValue:标识没有边
            for (int i = 0; i < graph.vertexsNum; i++)
            {
                //起始边
                var startEdge = i;

                dic_edges.Add(startEdge, new Edge() { weight = int.MaxValue });
            }

            //取第一个顶点
            var start = 0;

            for (int i = 0; i < graph.vertexsNum; i++)
            {
                //标记该顶点已经使用过
                dic_edges[start].isUse = true;

                for (int j = 1; j < graph.vertexsNum; j++)
                {
                    var end = j;

                    //取到相邻边的权重
                    var weight = graph.edges[start, end];

                    //赋较小的权重
                    if (weight < dic_edges[end].weight)
                    {
                        //与上一个顶点的权值累加
                        var totalweight = dic_edges[start].weight == int.MaxValue ? weight : dic_edges[start].weight + weight;

                        if (totalweight < dic_edges[end].weight)
                        {
                            //将该顶点的相邻边加入到集合中
                            dic_edges[end] = new Edge()
                            {
                                startEdge = start,
                                endEdge = end,
                                weight = totalweight
                            };

                            //将上一个边的节点的vertex累加
                            dic_edges[end].vertexs = new HashSet<int>(dic_edges[start].vertexs);

                            dic_edges[end].vertexs.Add(start);
                            dic_edges[end].vertexs.Add(end);
                        }
                    }
                }

                var min = int.MaxValue;

                //下一个进行比较的顶点
                int minkey = 0;

                //取start邻接边中的最小值
                foreach (var key in dic_edges.Keys)
                {
                    //取当前 最小的 key(使用过的除外)
                    if (min > dic_edges[key].weight && !dic_edges[key].isUse)
                    {
                        min = dic_edges[key].weight;
                        minkey = key;
                    }
                }

                //从邻接边的顶点再开始找
                start = minkey;
            }

            return dic_edges;
        }
        #endregion
```

总的代码：复杂度很烂O(N2)。。。

```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Diagnostics;
using System.Threading;
using System.IO;
using System.Threading.Tasks;
 
namespace ConsoleApplication2
{
    public class Program
    {
        public static void Main()
        {
            Dictionary<int, string> dic = new Dictionary<int, string>();
 
            MatrixGraph graph = new MatrixGraph();
 
            graph.Build();
 
            var result = graph.Dijkstra();
 
            Console.WriteLine("各节点的最短路径为:");
 
            foreach (var key in result.Keys)
            {
                Console.WriteLine("{0}", string.Join("->", result[key].vertexs));
            }
 
            Console.Read();
        }
    }
 
    #region 定义矩阵节点
    /// <summary>
    /// 定义矩阵节点
    /// </summary>
    public class MatrixGraph
    {
        Graph graph = new Graph();
 
        public class Graph
        {
            /// <summary>
            /// 顶点信息
            /// </summary>
            public int[] vertexs;
 
            /// <summary>
            /// 边的条数
            /// </summary>
            public int[,] edges;
 
            /// <summary>
            /// 顶点个数
            /// </summary>
            public int vertexsNum;
 
            /// <summary>
            /// 边的个数
            /// </summary>
            public int edgesNum;
        }
 
        #region 矩阵的构建
        /// <summary>
        /// 矩阵的构建
        /// </summary>
        public void Build()
        {
            //顶点数
            graph.vertexsNum = 5;
 
            //边数
            graph.edgesNum = 6;
 
            graph.vertexs = new int[graph.vertexsNum];
 
            graph.edges = new int[graph.vertexsNum, graph.vertexsNum];
 
            //构建二维数组
            for (int i = 0; i < graph.vertexsNum; i++)
            {
                //顶点
                graph.vertexs[i] = i;
 
                for (int j = 0; j < graph.vertexsNum; j++)
                {
                    graph.edges[i, j] = int.MaxValue;
                }
            }
 
            //定义 6 条边
            graph.edges[0, 1] = graph.edges[1, 0] = 2;
            graph.edges[0, 2] = graph.edges[2, 0] = 5;
            graph.edges[0, 4] = graph.edges[4, 0] = 3;
            graph.edges[1, 3] = graph.edges[3, 1] = 4;
            graph.edges[2, 4] = graph.edges[4, 2] = 5;
            graph.edges[3, 4] = graph.edges[4, 3] = 2;
 
        }
        #endregion
 
        #region 边的信息
        /// <summary>
        /// 边的信息
        /// </summary>
        public class Edge
        {
            //开始边
            public int startEdge;
 
            //结束边
            public int endEdge;
 
            //权重
            public int weight;
 
            //是否使用
            public bool isUse;
 
            //累计顶点
            public HashSet<int> vertexs = new HashSet<int>();
        }
        #endregion
 
        #region Dijkstra算法
        /// <summary>
        /// Dijkstra算法
        /// </summary>
        public Dictionary<int, Edge> Dijkstra()
        {
            //收集顶点的相邻边
            Dictionary<int, Edge> dic_edges = new Dictionary<int, Edge>();
 
            //weight=MaxValue:标识没有边
            for (int i = 0; i < graph.vertexsNum; i++)
            {
                //起始边
                var startEdge = i;
 
                dic_edges.Add(startEdge, new Edge() { weight = int.MaxValue });
            }
 
            //取第一个顶点
            var start = 0;
 
            for (int i = 0; i < graph.vertexsNum; i++)
            {
                //标记该顶点已经使用过
                dic_edges[start].isUse = true;
 
                for (int j = 1; j < graph.vertexsNum; j++)
                {
                    var end = j;
 
                    //取到相邻边的权重
                    var weight = graph.edges[start, end];
 
                    //赋较小的权重
                    if (weight < dic_edges[end].weight)
                    {
                        //与上一个顶点的权值累加
                        var totalweight = dic_edges[start].weight == int.MaxValue ? weight : dic_edges[start].weight + weight;
 
                        if (totalweight < dic_edges[end].weight)
                        {
                            //将该顶点的相邻边加入到集合中
                            dic_edges[end] = new Edge()
                            {
                                startEdge = start,
                                endEdge = end,
                                weight = totalweight
                            };
 
                            //将上一个边的节点的vertex累加
                            dic_edges[end].vertexs = new HashSet<int>(dic_edges[start].vertexs);
 
                            dic_edges[end].vertexs.Add(start);
                            dic_edges[end].vertexs.Add(end);
                        }
                    }
                }
 
                var min = int.MaxValue;
 
                //下一个进行比较的顶点
                int minkey = 0;
 
                //取start邻接边中的最小值
                foreach (var key in dic_edges.Keys)
                {
                    //取当前 最小的 key(使用过的除外)
                    if (min > dic_edges[key].weight && !dic_edges[key].isUse)
                    {
                        min = dic_edges[key].weight;
                        minkey = key;
                    }
                }
 
                //从邻接边的顶点再开始找
                start = minkey;
            }
 
            return dic_edges;
        }
        #endregion
    }
    #endregion
}
```

![][3]


[0]: http://www.cnblogs.com/huangxincheng/archive/2012/12/18/2823042.html
[1]: ./img/18102058-4910ef17bcdf481cb1ad8203e1b2b574.png
[2]: ./img/18112343-35605dc09a144287baab8fa3d0df82e8.png
[3]: ./img/18120221-e58f488d911e46548a5b0e8a8d3b5a97.png