# Trie树介绍及实现（传统&双数组）

<font face=微软雅黑>

 时间 2016-11-02 09:13:54  是非之地

原文[http://vinoit.me/2016/11/02/trie-tree/][1]


Trie树，又叫字典树、前缀树（Prefix Tree）、单词查找树 或 键树，是一种树形结构。典型应用是用于统计和排序大量的字符串（但不仅限于字符串），所以经常被搜索引擎系统用于文本词频统计。它的优点是最大限度地减少无谓的字符串比较， 查询效率比较高 。 

Trie的核心思想是空间换时间，利用字符串的公共前缀来降低查询时间的开销以达到提高效率的目的。

它有3个基本性质：

1. 根节点不包含字符，除根节点外每一个节点都只包含一个字符。
1. 从根节点到某一节点，路径上经过的字符连接起来，为该节点对应的字符串。
1. 每个节点的所有子节点包含的字符都不相同。

其结构大致如下：

![][3]

#### Trie树的传统实现 

传统的实现方式中，每个节点都包含着一个指针数组，用于指向子节点：

```c
struct Node {
  bool endOfWord;//是否为单词的结尾
  char ch;
  struct Node* child[MAX_NUM]; //26-tree->a, b ,c, .....z
};
```

因为可能有多个字符串拥有相同的前缀，所以用一个 bool 的字段来表示该字母是否为一个字符串的结尾。插入（Insert）、删除（ Delete）和查找（Find）都非常简单，用一个一重循环即可，即第i 次循环找到前i 个字母所对应的子树，然后进行相应的操作。其实现如下： 

```c
#define MAX_NUM 26
struct Node {
  bool endOfWord;//是否为单词的结尾
  char ch;
  struct Node* child[MAX_NUM]; //26-tree->a, b ,c, .....z
};
struct Node* ROOT; //tree root
 
struct Node* createNewNode(char ch){
  // create a new node
  struct Node *new_node = (struct Node*)malloc(sizeof(struct Node));
  new_node->ch = ch;
  new_node->endOfWord == false;
  int i;
  for(i = 0; i < MAX_NUM; i++)
    new_node->child[i] = NULL;
  return new_node;
}
 
void initialization() {
//intiazation: creat an empty tree, with only a ROOT
ROOT = createNewNode(' ');
}
 
int charToindex(char ch) { //a "char" maps to an index<br>
return ch - 'a';
}
 
int find(const char chars[], int len) {
  struct Node* ptr = ROOT;
  int i = 0;
  while(i < len) {
   if(ptr->child[charToindex(chars[i])] == NULL) {
   break;
  }
  ptr = ptr->child[charToindex(chars[i])];
  i++;
  }
  return (i == len) && (ptr->endOfWord == true);
}
 
void insert(const char chars[], int len) {
  struct Node* ptr = ROOT;
  int i;
  for(i = 0; i < len; i++) {
   if(ptr->child[charToindex(chars[i])] == NULL) {
    ptr->child[charToindex(chars[i])] = createNewNode(chars[i]);
  }
  ptr = ptr->child[charToindex(chars[i])];
}
  ptr->endOfWord = true;
}

```

trie树的检索，插入，删除都很快，但是它占用了很大的内存空间，而且空间的复杂度是基于节点的个数和字符的个数。如果是纯单词，而且兼顾大小写的话，每个节点就要分配52*4的内存空间，耗费很大。

#### Trie树的双数组实现 

该实现基本上是按照该文的算法： [http://blog.csdn.net/zzran/article/details/8462002][4]

```java
import java.util.ArrayList;
import java.util.HashMap;
import java.util.Map;
import java.util.Arrays;
public class DoubleArrayTrie {
    final char END_CHAR = '#';
    final int DEFAULT_LEN = 1024;
    int Base[]  = new int [DEFAULT_LEN];
    int Check[] = new int [DEFAULT_LEN];
    char Tail[] = new char [DEFAULT_LEN];
    int Pos = 1;//TAIL数组下一个可用下标
    //用于将字符转换为索引
    Map<Character ,Integer> CharMap = new HashMap<Character,Integer>();
    //用于将索引转换为字符
    ArrayList<Character> CharList = new ArrayList<Character>();
    public DoubleArrayTrie()
    {
    //一系列初始化
        Base[1] = 1;
        CharMap.put(END_CHAR,1);
        CharList.add(END_CHAR);
        CharList.add(END_CHAR);
        for(int i=0;i<26;++i)//a对应2，z对应27
        {
            CharMap.put((char)('a'+i),CharMap.size()+1);
            CharList.add((char)('a'+i));
        }
    }
    private void Extend_Array()
    {
        Base = Arrays.copyOf(Base, Base.length*2);
        Check = Arrays.copyOf(Check, Check.length*2);
    }
    private void Extend_Tail()
    {
        Tail = Arrays.copyOf(Tail, Tail.length*2);
    }
    private int GetCharCode(char c)
    {
        if (!CharMap.containsKey(c))
        {
            CharMap.put(c,CharMap.size()+1);
            CharList.add(c);
        }
        return CharMap.get(c);
    }
    private int CopyToTailArray(String s,int p)
    {
        int _Pos = Pos;
        while(s.length()-p+1 > Tail.length-Pos)
        {
            Extend_Tail();
        }
        for(int i=p; i<s.length();++i)
        {
            Tail[_Pos] = s.charAt(i);
            _Pos++;
        }
        return _Pos;
    }
    private int x_check(Integer []set)//获得一个可行的最小base，set中的每一个下标都要是空闲的
    {
        for(int i=1; ; ++i)
        {
            boolean flag = true;
            for(int j=0;j<set.length;++j)
            {
                int cur_p = i+set[j];
                if(cur_p>= Base.length) Extend_Array();
                if(Base[cur_p]!= 0 || Check[cur_p]!= 0)//必须是空闲未使用的
                {
                    flag = false;
                    break;
                }
            }
            if (flag) return i;
        }
    }
    private ArrayList<Integer> GetChildList(int p)//p为父状态
    {
        ArrayList<Integer> ret = new ArrayList<Integer>();
        for(int i=1; i<=CharMap.size();++i)
        {
            if(Base[p]+i >= Check.length) break;
            if(Check[Base[p]+i] == p)
            {
                ret.add(i);
            }
        }
        return ret;
    }
    private boolean TailContainString(int start,String s2)
    {
        for(int i=0;i<s2.length();++i)
        {
            if(s2.charAt(i) != Tail[i+start]) return false;
        }
        return true;
    }
    private boolean TailMatchString(int start,String s2)
    {
        s2 += END_CHAR;
        for(int i=0;i<s2.length();++i)
        {
            if(s2.charAt(i) != Tail[i+start]) return false;
        }
        return true;
    }
    public void Insert(String s) throws Exception
    {
        s += END_CHAR;
        int pre_p = 1;
        int cur_p;
        for(int i=0; i<s.length(); ++i)
        {
            //获取状态位置
            cur_p = Base[pre_p]+GetCharCode(s.charAt(i));
            //如果长度超过现有，拓展数组
            if (cur_p >= Base.length) Extend_Array();
            //空闲状态
            if(Base[cur_p] == 0 && Check[cur_p] == 0)
            {
                Base[cur_p] = -Pos;//pos是TAIL数组的下标
                Check[cur_p] = pre_p;//CHECK中为对应的父状态
                Pos = CopyToTailArray(s,i+1);//将尾串直接存储到TAIL数组，并更新pos
                break;
            }else
            //已存在状态
            if(Base[cur_p] > 0 && Check[cur_p] == pre_p)
            {
                pre_p = cur_p;//更新pre_p,切换到下一个状态
                continue;
            }else
            //冲突 1：遇到 Base[cur_p]小于0的，即遇到一个被压缩存到Tail中的字符串
            if(Base[cur_p] < 0 && Check[cur_p] == pre_p)
            {
                int head = -Base[cur_p];//head为TAIL数组的下标
                if(s.charAt(i+1)== END_CHAR && Tail[head]==END_CHAR)    //插入重复字符串
                {
                    break;
                }
                //公共字母的情况，因为上一个判断已经排除了结束符，所以一定是2个都不是结束符
                if (Tail[head] == s.charAt(i+1))
                {
                    //因为和TAIL数组中的尾串字母重复，则这两个字母需要提取出来共用一个状态，需要一个新的base
                    int avail_base = x_check(new Integer[]{GetCharCode(s.charAt(i+1))});
                    Base[cur_p] = avail_base;//更新当前状态的base
                    //修改CHECK数组和BASE数组
                    Check[avail_base+GetCharCode(s.charAt(i+1))] = cur_p;
                    //论文中是将数组的字符串左移，这边是将下标右移，节省了几步操作
                    Base[avail_base+GetCharCode(s.charAt(i+1))] = -(head+1);
                    pre_p = cur_p;
                    continue;
                }
                else
                {
                    //2个字母不相同的情况，可能有一个为结束符。同时需要将这个两个不同的字符给提取出来，
                    //分配到两个不同的状态，也就是不同的index，需要新的base
                    int avail_base ;
                    avail_base = x_check(new Integer[]{GetCharCode(s.charAt(i+1)),GetCharCode(Tail[head])});
                    Base[cur_p] = avail_base;//更新base
                    //修改新的CHECK数组的值为cur_p(父状态)
                    Check[avail_base+GetCharCode(Tail[head])] = cur_p;
                    Check[avail_base+GetCharCode(s.charAt(i+1))] = cur_p;
                    //Tail 为END_FLAG 的情况
                    if(Tail[head] == END_CHAR)
                        Base[avail_base+GetCharCode(Tail[head])] = 0;
                    else//修改为TAIL数组在剩余尾串的下标
                        Base[avail_base+GetCharCode(Tail[head])] = -(head+1);
                    if(s.charAt(i+1) == END_CHAR)
                        Base[avail_base+GetCharCode(s.charAt(i+1))] = 0;
                    else
                        Base[avail_base+GetCharCode(s.charAt(i+1))] = -Pos;
                    Pos = CopyToTailArray(s,i+2);//插入串的剩余部分插入到TAIL数组中
                    break;
                }
            }else
            //冲突2：当前结点已经被占用，需要调整pre的base，然后将之前的数据迁移到新的base
            if(Check[cur_p] != pre_p)
            {
                ArrayList<Integer> list = GetChildList(pre_p);//获取所有子状态
                int origin_base = Base[pre_p];//保存原来的base
                list.add(GetCharCode(s.charAt(i)));
                //新base
                int avail_base = x_check(list.toArray(new Integer[list.size()]));
                list.remove(list.size()-1);
                //更新base
                Base[pre_p] = avail_base;
                for(int j=0; j<list.size(); ++j)
                {
                    //迁移数据
                    int tmp1 = origin_base + list.get(j);
                    int tmp2 = avail_base + list.get(j);
                    Base[tmp2] = Base[tmp1];
                    Check[tmp2] = Check[tmp1];
                    //有后续
                    if(Base[tmp1] > 0)
                    {
                        ArrayList<Integer> subsequence = GetChildList(tmp1);
                        for(int k=0; k<subsequence.size(); ++k)
                        {
                            Check[Base[tmp1]+subsequence.get(k)] = tmp2;
                        }
                    }
                    //将之前的数组槽置为空闲
                    Base[tmp1] = 0;
                    Check[tmp1] = 0;
                }
                //更新新的cur_p
                cur_p = Base[pre_p]+GetCharCode(s.charAt(i));
                if(s.charAt(i) == END_CHAR)
                    Base[cur_p] = 0;
                else
                    Base[cur_p] = -Pos;
                Check[cur_p] = pre_p;
                Pos = CopyToTailArray(s,i+1);//同样，将插入串剩余的部分插入TAIL数组
                break;
            }
        }
    }
    public boolean Exists(String word)
    {
        int pre_p = 1;
        int cur_p = 0;
        for(int i=0;i<word.length();++i)
        {
            cur_p = Base[pre_p]+GetCharCode(word.charAt(i));
            if(Check[cur_p] != pre_p) return false;
            if(Base[cur_p] < 0)
            {
                if(TailMatchString(-Base[cur_p],word.substring(i+1)))
                    return true;
                return false;
            }
            pre_p = cur_p;
        }
        if(Check[Base[cur_p]+GetCharCode(END_CHAR)] == cur_p)
            return true;
        return false;
    }
    class FindStruct
    {
        int p;
        String prefix="";
    }
    private FindStruct Find(String word)
    {
        int pre_p = 1;
        int cur_p = 0;
        FindStruct fs = new FindStruct();
        for(int i=0;i<word.length();++i)
        {
            // BUG
            fs.prefix += word.charAt(i);
            cur_p = Base[pre_p]+GetCharCode(word.charAt(i));
            if(Check[cur_p] != pre_p)
            {
                fs.p = -1;
                return fs;
            }
            if(Base[cur_p] < 0)
            {
                if(TailContainString(-Base[cur_p],word.substring(i+1)))
                {
                    fs.p = cur_p;
                    return fs;
                }
                fs.p = -1;
                return fs;
            }
            pre_p = cur_p;
        }
        fs.p =  cur_p;
        return fs;
    }
    public ArrayList<String> GetAllChildWord(int index)
    {
        ArrayList<String> result = new ArrayList<String>();
        if(Base[index] == 0)
        {
            result.add("");
            return result;
        }
        if(Base[index] < 0)
        {
            String r="";
            for(int i=-Base[index];Tail[i]!=END_CHAR;++i)
            {
                r+= Tail[i];
            }
            result.add(r);
            return result;
        }
        for(int i=1;i<=CharMap.size();++i)
        {
            if(Check[Base[index]+i] == index)
            {
                for(String s:GetAllChildWord(Base[index]+i))
                {
                    result.add(CharList.get(i)+s);
                }
                //result.addAll(GetAllChildWord(Base[index]+i));
            }
        }
        return result;
    }
    public ArrayList<String> FindAllWords(String word)
    {
        ArrayList<String> result = new ArrayList<String>();
        String prefix = "";
        FindStruct fs = Find(word);
        int p = fs.p;
        if (p == -1) return result;
        if(Base[p]<0)
        {
            String r="";
            for(int i=-Base[p];Tail[i]!=END_CHAR;++i)
            {
                r+= Tail[i];
            }
            result.add(fs.prefix+r);
            return result;
        }
        if(Base[p] > 0)
        {
            ArrayList<String> r =  GetAllChildWord(p);
            for(int i=0;i<r.size();++i)
            {
                r.set(i, fs.prefix+r.get(i));
            }
            return r;
        }
        return result;
    }
    
}
```

#### Trie树的应用 

1. 字符串检索，词频统计，搜索引擎的热门查询  
    事先将已知的一些字符串（字典）的有关信息保存到trie树里，查找另外一些未知字符串是否出现过或者出现频率。举例：
    1. 有一个1G大小的一个文件，里面每一行是一个词，词的大小不超过16字节，内存限制大小是1M。返回频数最高的100个词。
    1. 给出N 个单词组成的熟词表，以及一篇全用小写英文书写的文章，请你按最早出现的顺序写出所有不在熟词表中的生词。
    1. 给出一个词典，其中的单词为不良单词。单词均为小写字母。再给出一段文本，文本的每一行也由小写字母构成。判断文本中是否含有任何不良单词。例如，若rob是不良单词，那么文本problem含有不良单词。
    1. 1000万字符串，其中有些是重复的，需要把重复的全部去掉，保留没有重复的字符串
    1. 寻找热门查询：搜索引擎会通过日志文件把用户每次检索使用的所有检索串都记录下来，每个查询串的长度为1-255字节。假设目前有一千万个记录，这些查询串的重复读比较高，虽然总数是1千万，但是如果去除重复和，不超过3百万个。一个查询串的重复度越高，说明查询它的用户越多，也就越热门。请你统计最热门的10个查询串，要求使用的内存不能超过1G。

1. 字符串最长公共前缀
    Trie树利用多个字符串的公共前缀来节省存储空间，反之，当我们把大量字符串存储到一棵trie树上时，我们可以快速得到某些字符串的公共前缀。举例：  
    1. 给出N 个小写英文字母串，以及Q 个询问，即询问某两个串的最长公共前缀的长度是多少. 解决方案：首先对所有的串建立其对应的字母树。此时发现，对于两个串的最长公共前缀的长度即它们所在结点的公共祖先个数，于是，问题就转化为了离线   （Offline）的最近公共祖先（Least Common Ancestor，简称LCA）问题。 而最近公共祖先问题同样是一个经典问题，可以用下面几种方法：   
        1. 利用并查集（Disjoint Set），可以采用采用经典的Tarjan 算法；
        1. 求出字母树的欧拉序列（Euler Sequence ）后，就可以转为经典的最小值查询（Range Minimum Query，简称RMQ）问题了；

1. 排序  
    Trie树是一棵多叉树，只要先序遍历整棵树，输出相应的字符串便是按字典序排序的结果。举例： 给你N 个互不相同的仅由一个单词构成的英文名，让你将它们按字典序从小到大排序输出。

1. 作为其他数据结构和算法的辅助结构如后缀树，AC自动机等。

参考：

* [http://turbopeter.github.io/2013/09/02/prefix-match/][5]
* [http://blog.csdn.net/zzran/article/details/8462002][4]
* [http://dongxicheng.org/structure/trietree/][6]

</font>

[1]: http://vinoit.me/2016/11/02/trie-tree/

[3]: ./img/VBRf22m.png
[4]: http://blog.csdn.net/zzran/article/details/8462002
[5]: http://turbopeter.github.io/2013/09/02/prefix-match/
[6]: http://dongxicheng.org/structure/trietree/