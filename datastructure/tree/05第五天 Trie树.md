# [6天通吃树结构—— 第五天 Trie树][0] 

很有段时间没写此系列了，今天我们来说Trie树，Trie树的名字有很多，比如字典树，前缀树等等。

一：概念

下面我们有and,as,at,cn,com这些关键词，那么如何构建trie树呢？

![][1]

从上面的图中，我们或多或少的可以发现一些好玩的特性。

第一：根节点不包含字符，除根节点外的每一个子节点都包含一个字符。

第二：从根节点到某一节点，路径上经过的字符连接起来，就是该节点对应的字符串。

第三：每个单词的公共前缀作为一个字符节点保存。

二：使用范围

既然学Trie树，我们肯定要知道这玩意是用来干嘛的。

第一：词频统计。

可能有人要说了，词频统计简单啊，一个hash或者一个堆就可以打完收工，但问题来了，如果内存有限呢？还能这么

玩吗？所以这里我们就可以用trie树来压缩下空间，因为公共前缀都是用一个节点保存的。

第二: 前缀匹配

就拿上面的图来说吧，如果我想获取所有以"a"开头的字符串，从图中可以很明显的看到是：and,as,at，如果不用trie树，

你该怎么做呢？很显然朴素的做法时间复杂度为O(N2) ，那么用Trie树就不一样了，它可以做到h，h为你检索单词的长度，

可以说这是秒杀的效果。

举个例子：现有一个编号为1的字符串”and“，我们要插入到trie树中，采用动态规划的思想，将编号”1“计入到每个途径的节点中，

那么以后我们要找”a“，”an“，”and"为前缀的字符串的编号将会轻而易举。

![][2]

三：实际操作

到现在为止，我想大家已经对trie树有了大概的掌握，下面我们看看如何来实现。

1：定义trie树节点

为了方便，我也采用纯英文字母，我们知道字母有26个，那么我们构建的trie树就是一个26叉树，每个节点包含26个子节点。

 

```csharp
#region Trie树节点
/// <summary>
/// Trie树节点
/// </summary>
public class TrieNode
{
    /// <summary>
    /// 26个字符，也就是26叉树
    /// </summary>
    public TrieNode[] childNodes;

    /// <summary>
    /// 词频统计
    /// </summary>
    public int freq;

    /// <summary>
    /// 记录该节点的字符
    /// </summary>
    public char nodeChar;

    /// <summary>
    /// 插入记录时的编码id
    /// </summary>
    public HashSet<int> hashSet = new HashSet<int>();

    /// <summary>
    /// 初始化
    /// </summary>
    public TrieNode()
    {
        childNodes = new TrieNode[26];
        freq = 0;
    }
}
#endregion
```

2: 添加操作

既然是26叉树，那么当前节点的后续子节点是放在当前节点的哪一叉中，也就是放在childNodes中哪一个位置，这里我们采用

int k = word[0] - 'a'来计算位置。

 

```csharp
/// <summary>
/// 插入操作
/// </summary>
/// <param name="root"></param>
/// <param name="s"></param>
public void AddTrieNode(ref TrieNode root, string word, int id)
{
    if (word.Length == 0)
        return;

    //求字符地址，方便将该字符放入到26叉树中的哪一叉中
    int k = word[0] - 'a';

    //如果该叉树为空，则初始化
    if (root.childNodes[k] == null)
    {
        root.childNodes[k] = new TrieNode();

        //记录下字符
        root.childNodes[k].nodeChar = word[0];
    }

    //该id途径的节点
    root.childNodes[k].hashSet.Add(id);

    var nextWord = word.Substring(1);

    //说明是最后一个字符，统计该词出现的次数
    if (nextWord.Length == 0)
        root.childNodes[k].freq++;

    AddTrieNode(ref root.childNodes[k], nextWord, id);
}
#endregion
```

3：删除操作

删除操作中，我们不仅要删除该节点的字符串编号，还要对词频减一操作。

 

```csharp
/// <summary>
/// 删除操作
/// </summary>
/// <param name="root"></param>
/// <param name="newWord"></param>
/// <param name="oldWord"></param>
/// <param name="id"></param>
public void DeleteTrieNode(ref TrieNode root, string word, int id)
{
    if (word.Length == 0)
        return;

    //求字符地址，方便将该字符放入到26叉树种的哪一颗树中
    int k = word[0] - 'a';

    //如果该叉树为空,则说明没有找到要删除的点
    if (root.childNodes[k] == null)
        return;

    var nextWord = word.Substring(1);

    //如果是最后一个单词，则减去词频
    if (word.Length == 0 && root.childNodes[k].freq > 0)
        root.childNodes[k].freq--;

    //删除途经节点
    root.childNodes[k].hashSet.Remove(id);

    DeleteTrieNode(ref root.childNodes[k], nextWord, id);
}
```

4：测试

这里我从网上下载了一套的词汇表，共2279条词汇，现在我们要做的就是检索“go”开头的词汇，并统计go出现的频率。

 

```csharp
public static void Main()
{
    Trie trie = new Trie();

    var file = File.ReadAllLines(Environment.CurrentDirectory + "//1.txt");

    foreach (var item in file)
    {
        var sp = item.Split(new char[] { ' ' }, StringSplitOptions.RemoveEmptyEntries);

        trie.AddTrieNode(sp.LastOrDefault().ToLower(), Convert.ToInt32(sp[0]));
    }

    Stopwatch watch = Stopwatch.StartNew();

    //检索go开头的字符串
    var hashSet = trie.SearchTrie("go");

    foreach (var item in hashSet)
    {
        Console.WriteLine("当前字符串的编号ID为:{0}", item);
    }

    watch.Stop();

    Console.WriteLine("耗费时间:{0}", watch.ElapsedMilliseconds);

    Console.WriteLine("\n\ngo 出现的次数为:{0}\n\n", trie.WordCount("go"));
}
```

![][3]

下面我们拿着ID到txt中去找一找，嘿嘿，是不是很有意思。

![][4]

测试文件：[1.txt][5]

完整代码：

```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Diagnostics;
using System.Threading;
using System.IO;

namespace ConsoleApplication2
{
    public class Program
    {
        public static void Main()
        {
            Trie trie = new Trie();

            var file = File.ReadAllLines(Environment.CurrentDirectory + "//1.txt");

            foreach (var item in file)
            {
                var sp = item.Split(new char[] { ' ' }, StringSplitOptions.RemoveEmptyEntries);

                trie.AddTrieNode(sp.LastOrDefault().ToLower(), Convert.ToInt32(sp[0]));
            }

            Stopwatch watch = Stopwatch.StartNew();

            //检索go开头的字符串
            var hashSet = trie.SearchTrie("go");

            foreach (var item in hashSet)
            {
                Console.WriteLine("当前字符串的编号ID为:{0}", item);
            }

            watch.Stop();

            Console.WriteLine("耗费时间:{0}", watch.ElapsedMilliseconds);

            Console.WriteLine("\n\ngo 出现的次数为:{0}\n\n", trie.WordCount("go"));
        }
    }

    public class Trie
    {
        public TrieNode trieNode = new TrieNode();

        #region Trie树节点
        /// <summary>
        /// Trie树节点
        /// </summary>
        public class TrieNode
        {
            /// <summary>
            /// 26个字符，也就是26叉树
            /// </summary>
            public TrieNode[] childNodes;

            /// <summary>
            /// 词频统计
            /// </summary>
            public int freq;

            /// <summary>
            /// 记录该节点的字符
            /// </summary>
            public char nodeChar;

            /// <summary>
            /// 插入记录时的编号id
            /// </summary>
            public HashSet<int> hashSet = new HashSet<int>();

            /// <summary>
            /// 初始化
            /// </summary>
            public TrieNode()
            {
                childNodes = new TrieNode[26];
                freq = 0;
            }
        }
        #endregion

        #region 插入操作
        /// <summary>
        /// 插入操作
        /// </summary>
        /// <param name="word"></param>
        /// <param name="id"></param>
        public void AddTrieNode(string word, int id)
        {
            AddTrieNode(ref trieNode, word, id);
        }

        /// <summary>
        /// 插入操作
        /// </summary>
        /// <param name="root"></param>
        /// <param name="s"></param>
        public void AddTrieNode(ref TrieNode root, string word, int id)
        {
            if (word.Length == 0)
                return;

            //求字符地址，方便将该字符放入到26叉树中的哪一叉中
            int k = word[0] - 'a';

            //如果该叉树为空，则初始化
            if (root.childNodes[k] == null)
            {
                root.childNodes[k] = new TrieNode();

                //记录下字符
                root.childNodes[k].nodeChar = word[0];
            }

            //该id途径的节点
            root.childNodes[k].hashSet.Add(id);

            var nextWord = word.Substring(1);

            //说明是最后一个字符，统计该词出现的次数
            if (nextWord.Length == 0)
                root.childNodes[k].freq++;

            AddTrieNode(ref root.childNodes[k], nextWord, id);
        }
        #endregion

        #region 检索操作
        /// <summary>
        /// 检索单词的前缀,返回改前缀的Hash集合
        /// </summary>
        /// <param name="s"></param>
        /// <returns></returns>
        public HashSet<int> SearchTrie(string s)
        {
            HashSet<int> hashSet = new HashSet<int>();

            return SearchTrie(ref trieNode, s, ref hashSet);
        }

        /// <summary>
        /// 检索单词的前缀,返回改前缀的Hash集合
        /// </summary>
        /// <param name="root"></param>
        /// <param name="s"></param>
        /// <returns></returns>
        public HashSet<int> SearchTrie(ref TrieNode root, string word, ref HashSet<int> hashSet)
        {
            if (word.Length == 0)
                return hashSet;

            int k = word[0] - 'a';

            var nextWord = word.Substring(1);

            if (nextWord.Length == 0)
            {
                //采用动态规划的思想，word最后节点记录这途经的id
                hashSet = root.childNodes[k].hashSet;
            }

            SearchTrie(ref root.childNodes[k], nextWord, ref hashSet);

            return hashSet;
        }
        #endregion

        #region 统计指定单词出现的次数

        /// <summary>
        /// 统计指定单词出现的次数
        /// </summary>
        /// <param name="root"></param>
        /// <param name="word"></param>
        /// <returns></returns>
        public int WordCount(string word)
        {
            int count = 0;

            WordCount(ref trieNode, word, ref count);

            return count;
        }

        /// <summary>
        /// 统计指定单词出现的次数
        /// </summary>
        /// <param name="root"></param>
        /// <param name="word"></param>
        /// <param name="hashSet"></param>
        /// <returns></returns>
        public void WordCount(ref TrieNode root, string word, ref int count)
        {
            if (word.Length == 0)
                return;

            int k = word[0] - 'a';

            var nextWord = word.Substring(1);

            if (nextWord.Length == 0)
            {
                //采用动态规划的思想，word最后节点记录这途经的id
                count = root.childNodes[k].freq;
            }

            WordCount(ref root.childNodes[k], nextWord, ref count);
        }

        #endregion

        #region 修改操作
        /// <summary>
        /// 修改操作
        /// </summary>
        /// <param name="newWord"></param>
        /// <param name="oldWord"></param>
        /// <param name="id"></param>
        public void UpdateTrieNode(string newWord, string oldWord, int id)
        {
            UpdateTrieNode(ref trieNode, newWord, oldWord, id);
        }

        /// <summary>
        /// 修改操作
        /// </summary>
        /// <param name="root"></param>
        /// <param name="newWord"></param>
        /// <param name="oldWord"></param>
        /// <param name="id"></param>
        public void UpdateTrieNode(ref TrieNode root, string newWord, string oldWord, int id)
        {
            //先删除
            DeleteTrieNode(oldWord, id);

            //再添加
            AddTrieNode(newWord, id);
        }
        #endregion

        #region 删除操作
        /// <summary>
        ///  删除操作
        /// </summary>
        /// <param name="root"></param>
        /// <param name="newWord"></param>
        /// <param name="oldWord"></param>
        /// <param name="id"></param>
        public void DeleteTrieNode(string word, int id)
        {
            DeleteTrieNode(ref trieNode, word, id);
        }

        /// <summary>
        /// 删除操作
        /// </summary>
        /// <param name="root"></param>
        /// <param name="newWord"></param>
        /// <param name="oldWord"></param>
        /// <param name="id"></param>
        public void DeleteTrieNode(ref TrieNode root, string word, int id)
        {
            if (word.Length == 0)
                return;

            //求字符地址，方便将该字符放入到26叉树种的哪一颗树中
            int k = word[0] - 'a';

            //如果该叉树为空,则说明没有找到要删除的点
            if (root.childNodes[k] == null)
                return;

            var nextWord = word.Substring(1);

            //如果是最后一个单词，则减去词频
            if (word.Length == 0 && root.childNodes[k].freq > 0)
                root.childNodes[k].freq--;

            //删除途经节点
            root.childNodes[k].hashSet.Remove(id);

            DeleteTrieNode(ref root.childNodes[k], nextWord, id);
        }
        #endregion
    }
}
```
[0]: http://www.cnblogs.com/huangxincheng/archive/2012/11/25/2788268.html
[1]: ./img/2012112521092438.png
[2]: ./img/2012112521371883.png
[3]: ./img/2012112522045926.png
[4]: ./img/2012112522115572.png
[5]: http://files.cnblogs.com/huangxincheng/1.zip
