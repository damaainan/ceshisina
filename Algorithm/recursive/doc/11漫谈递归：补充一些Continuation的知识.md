# 漫谈递归：补充一些Continuation的知识

 [kelinlin][0]  2012-10-15 

### 尾递归与Continuation的联系

前面谈了尾递归与Continuation，但是感觉还有些要补充下。  
  
Continuation是一种非常古老的程序结构，简单说来就是entire default future of a computation, 即对程序“接下来要做的事情”所进行的一种建模，即为“完成某件事情”之后“还需要做的事情”。而这种做法，也可以体现在尾递归构造中。   
  
例如以下为阶乘方法的传统递归定义：  


    int FactorialRecursively(int n)
    {
        if (n == 0) return 1;
        return FactorialRecursively(n - 1) * n;
    }

  
  
显然，这不是一个尾递归的方式，当然我们轻易将其转换为之前提到的尾递归调用方式。不过我们现在把它这样“理解”：每次计算n的阶乘时，其实是“先获取n - 1的阶乘”之后再“与n相乘并返回”，于是我们的FactorialRecursively方法可以改造成：  


    int FactorialRecursively(int n)
    {
        return FactorialContinuation(n - 1, r => n * r);
    }
    
    //  FactorialContinuation(n, x => x)
    int FactorialContinuation(int n, Func<int, int> continuation)
    {
        ...
    }

  
  
FactorialContinuation方法的含义是“计算n的阶乘，并将结果传入continuation方法，并返回其调用结果”。于是，很容易得出，FactorialContinuation方法自身便是一个递归调用：  


    public static int FactorialContinuation(int n, Func<int, int> continuation)
    {
        return FactorialContinuation(n - 1,
            r => continuation(n * r));
    }

  
  
FactorialContinuation方法的实现可以这样表述：“计算n的阶乘，并将结果传入continuation方法并返回”，也就是“计算n - 1的阶乘，并将结果与n相乘，再调用continuation方法”。为了实现“并将结果与n相乘，再调用continuation方法”这个逻辑，代码又构造了一个匿名方法，再次传入FactorialContinuation方法。当然，我们还需要为它补充递归的出口条件：  


    public static int FactorialContinuation(int n, Func<int, int> continuation)
    {
        if (n == 0) return continuation(1);
        return FactorialContinuation(n - 1,
            r => continuation(n * r));
    }

  
  
很明显，FactorialContinuation实现了尾递归。如果要计算n的阶乘，我们需要如下调用FactorialContinuation方法，表示“计算10的阶乘，并将结果直接返回”：  


    FactorialContinuation(10, x => x)

  
  
再加深一下印象，大家是否能够理解以下计算“斐波那契”数列第n项值的写法？  


    public static int FibonacciContinuation(int n, Func<int, int> continuation)
    {
        if (n < 2) return continuation(n);
        return FibonacciContinuation(n - 1,
            r1 => FibonacciContinuation(n - 2,
                r2 => continuation(r1 + r2)));
    }

  
  
在函数式编程中，此类调用方式便形成了“Continuation Passing Style（CPS）”。由于C#的Lambda表达式能够轻松构成一个匿名方法，我们也可以在C#中实现这样的调用方式。您可能会想——汗，何必搞得这么复杂，计算阶乘和“斐波那契”数列不是一下子就能转换成尾递归形式的吗？不过，您试试看以下的例子呢？  
  
对二叉树进行先序遍历（pre-order traversal）是典型的递归操作，假设有如下TreeNode类：  


    public class TreeNode
    {
        public TreeNode(int value, TreeNode left, TreeNode right)
        {
            this.Value = value;
            this.Left = left;
            this.Right = right;
        }
    
        public int Value { get; private set; }
    
        public TreeNode Left { get; private set; }
    
        public TreeNode Right { get; private set; }
    }

  
  
于是我们来传统的先序遍历一下：  


    public static void PreOrderTraversal(TreeNode root)
    {
        if (root == null) return;
    
        Console.WriteLine(root.Value);
        PreOrderTraversal(root.Left);
        PreOrderTraversal(root.Right);
    }

  
  
您能用“普通”的方式将它转换为尾递归调用吗？这里先后调用了两次PreOrderTraversal，这意味着必然有一次调用没法放在末尾。这时候便要利用到Continuation了：  


    public static void PreOrderTraversal(TreeNode root, Action<TreeNode> continuation)
    {
        if (root == null)
        {
            continuation(null);
            return;
        }
    
        Console.WriteLine(root.Value);
    
        PreOrderTraversal(root.Left,
            left => PreOrderTraversal(root.Right,
                right => continuation(right)));
    }

  
  
我们现在把每次递归调用都作为代码的最后一次操作，把接下来的操作使用Continuation包装起来，这样就实现了尾递归，避免了堆栈数据的堆积。可见，虽然使用Continuation是一个略有些“诡异”的使用方式，但是在某些时候它也是必不可少的使用技巧。  


### Continuation的改进

看看刚才的先序遍历实现，您有没有发现一个有些奇怪的地方？  


    PreOrderTraversal(root.Left,
        left => PreOrderTraversal(root.Right,
            right => continuation(right)));

  
  
关于最后一步，我们构造了一个匿名函数作为第二次PreOrderTraversal调用的Continuation，但是其内部直接调用了continuation参数——那么我们为什么不直接把它交给第二次调用呢？如下：  


    PreOrderTraversal(root.Left,
        left => PreOrderTraversal(root.Right, continuation));

  
  
我们使用Continuation实现了尾递归，其实是把原本应该分配在栈上的信息丢到了托管堆上。每个匿名方法其实都是托管堆上的对象，虽然说这种生存周期短的对象不会对内存资源方面造成多大问题，但是尽可能减少此类对象，对于性能肯定是有帮助的。这里再举一个更为明显的例子，求二叉树的大小（Size）：  


    public static int GetSize(TreeNode root, Func<int, int> continuation)
    {
        if (root == null) return continuation(0);
        return GetSize(root.Left,
            leftSize => GetSize(root.Right,
                rightSize => continuation(leftSize + rightSize + 1)));
    }

  
  
GetSize方法使用了Continuation，它的理解方法是“获取root的大小，再将结果传入continuation，并返回其调用结果”。我们可以将其进行改写，减少Continuation方法的构造次数：  


    public static int GetSize2(TreeNode root, int acc, Func<int, int> continuation)
    {
        if (root == null) return continuation(acc);
        return GetSize2(root.Left, acc,
            accLeftSize => GetSize2(root.Right, accLeftSize + 1, continuation));
    }

  
  
GetSize2方法多了一个累加器参数，同时它的理解方式也有了变化：“将root的大小累加到acc上，再将结果传入continuation，并返回其调用结果”。也就是说GetSize2返回的其实是一个累加值，而并非是root参数的实际尺寸。当然，我们在调用时GetSize2时，只需将累加器置零便可：  


    GetSize2(root, 0, x => x)

  


### 小结

在命令式编程中，我们解决一些问题往往可以使用循环来代替递归，这样便不会因为数据规模造成堆栈溢出。但是在函数式编程中，要实现“循环”的唯一方法便是“递归”，因此尾递归和CPS对于函数式编程的意义非常重大。在函数式语言中，continuation的引入是非常自然的过程，实际上任何程序都可以通过所谓的CPS(Continuation Passing Style)变换而转换为使用continuation结构。了解尾递归，对于编程思维也有很大帮助，因此大家不妨多加思考和练习，让这样的方式为自己所用。

[0]: http://www.lai18.com/user/214130.html
