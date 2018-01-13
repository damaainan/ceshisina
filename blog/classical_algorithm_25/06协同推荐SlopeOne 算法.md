# [经典算法题每日演练——第六题 协同推荐SlopeOne 算法][0]

相信大家对如下的Category都很熟悉，很多网站都有类似如下的功能，“商品推荐”,"猜你喜欢“，在实体店中我们有导购来为我们服务，在网络上

我们需要同样的一种替代物，如果简简单单的在数据库里面去捞，去比较，几乎是完成不了的,这时我们就需要一种协同推荐算法，来高效的推荐浏览者喜

欢的商品。

![][1]

![][2]

一：概念

SlopeOne的思想很简单，就是用均值化的思想来掩盖个体的打分差异，举个例子说明一下：

![][3]

在这个图中，系统该如何计算“王五“对”电冰箱“的打分值呢？刚才我们也说了，slopeone是采用均值化的思想,也就是：R王五 =4-{[(5-10)+(4-5)]/2}=7 。

下面我们看看多于两项的商品，如何计算打分值。

rb = (n * (ra - R(A->B)) + m * (rc - R(C->B)))/(m+n)

注意： a,b,c 代表“商品”。

ra 代表“商品的打分值”。

ra->b 代表“A组到B组的平均差（均值化）”。

m,n 代表人数。

![][4]

根据公式，我们来算一下。

r王五 = (2 * (4 - R(洗衣机->彩电)) + 2 * (10 - R(电冰箱->彩电 ))+ 2 * (5 - R(空调->彩电)))/(2+2+2)=6.8

是的，slopeOne就是这么简单，实战效果非常不错。

二：实现

1：定义一个评分类Rating。

 

```csharp
    /// <summary>
    /// 评分实体类
    /// </summary>
    public class Rating
    {
        /// <summary>
        /// 记录差值
        /// </summary>
        public float Value { get; set; }

        /// <summary>
        /// 记录评分人数，方便公式中的 m 和 n 的值
        /// </summary>
        public int Freq { get; set; }

        /// <summary>
        /// 记录打分用户的ID
        /// </summary>
        public HashSet<int> hash_user = new HashSet<int>();

        /// <summary>
        /// 平均值
        /// </summary>
        public float AverageValue
        {
            get { return Value / Freq; }
        }
    }
```

2： 定义一个产品类

 

```csharp
    /// <summary>
    /// 产品类
    /// </summary>
    public class Product
    {
        public int ProductID { get; set; }

        public string ProductName { get; set; }

        /// <summary>
        /// 对产品的打分
        /// </summary>
        public float Score { get; set; }
    }
```


3：SlopeOne类

参考了网络上的例子，将二维矩阵做成线性表，有效的降低了空间复杂度。

![][5]


```csharp
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;

namespace SupportCenter.Test
{
    #region Slope One 算法
    /// <summary>
    /// Slope One 算法
    /// </summary>
    public class SlopeOne
    {
        /// <summary>
        /// 评分系统
        /// </summary>
        public static Dictionary<int, Product> dicRatingSystem = new Dictionary<int, Product>();

        public Dictionary<string, Rating> dic_Martix = new Dictionary<string, Rating>();

        public HashSet<int> hash_items = new HashSet<int>();

        #region 接收一个用户的打分记录
        /// <summary>
        /// 接收一个用户的打分记录
        /// </summary>
        /// <param name="userRatings"></param>
        public void AddUserRatings(IDictionary<int, List<Product>> userRatings)
        {
            foreach (var user1 in userRatings)
            {
                //遍历所有的Item
                foreach (var item1 in user1.Value)
                {
                    //该产品的编号（具有唯一性）
                    int item1Id = item1.ProductID;

                    //该项目的评分
                    float item1Rating = item1.Score;

                    //将产品编号字存放在hash表中
                    hash_items.Add(item1.ProductID);

                    foreach (var user2 in userRatings)
                    {
                        //再次遍历item，用于计算俩俩 Item 之间的差值
                        foreach (var item2 in user2.Value)
                        {
                            //过滤掉同名的项目
                            if (item2.ProductID <= item1Id)
                                continue;

                            //该产品的名字
                            int item2Id = item2.ProductID;

                            //该项目的评分
                            float item2Rating = item2.Score;

                            Rating ratingDiff;

                            //用表的形式构建矩阵
                            var key = Tools.GetKey(item1Id, item2Id);

                            //将俩俩 Item 的差值 存放到 Rating 中
                            if (dic_Martix.Keys.Contains(key))
                                ratingDiff = dic_Martix[key];
                            else
                            {
                                ratingDiff = new Rating();
                                dic_Martix[key] = ratingDiff;
                            }

                            //方便以后以后userrating的编辑操作，（add)
                            if (!ratingDiff.hash_user.Contains(user1.Key))
                            {
                                //value保存差值
                                ratingDiff.Value += item1Rating - item2Rating;

                                //说明计算过一次
                                ratingDiff.Freq += 1;
                            }

                            //记录操作人的ID，方便以后再次添加评分
                            ratingDiff.hash_user.Add(user1.Key);
                        }
                    }
                }
            }
        }
        #endregion

        #region 根据矩阵的值，预测出该Rating中的值
        /// <summary>
        /// 根据矩阵的值，预测出该Rating中的值
        /// </summary>
        /// <param name="userRatings"></param>
        /// <returns></returns>
        public IDictionary<int, float> Predict(List<Product> userRatings)
        {
            Dictionary<int, float> predictions = new Dictionary<int, float>();

            var productIDs = userRatings.Select(i => i.ProductID).ToList();

            //循环遍历_Items中所有的Items
            foreach (var itemId in this.hash_items)
            {
                //过滤掉不需要计算的产品编号
                if (productIDs.Contains(itemId))
                    continue;

                Rating itemRating = new Rating();

                // 内层遍历userRatings
                foreach (var userRating in userRatings)
                {
                    if (userRating.ProductID == itemId)
                        continue;

                    int inputItemId = userRating.ProductID;

                    //获取该key对应项目的两组AVG的值
                    var key = Tools.GetKey(itemId, inputItemId);

                    if (dic_Martix.Keys.Contains(key))
                    {
                        Rating diff = dic_Martix[key];

                        //关键点：运用公式求解（这边为了节省空间，对角线两侧的值呈现奇函数的特性）
                        itemRating.Value += diff.Freq * (userRating.Score + diff.AverageValue * ((itemId < inputItemId) ? 1 : -1));

                        //关键点：运用公式求解 累计每两组的人数
                        itemRating.Freq += diff.Freq;
                    }
                }

                predictions.Add(itemId, itemRating.AverageValue);
            }

            return predictions;
        }
        #endregion
    }
    #endregion

    #region 工具类
    /// <summary>
    /// 工具类
    /// </summary>
    public class Tools
    {
        public static string GetKey(int Item1Id, int Item2Id)
        {
            return (Item1Id < Item2Id) ? Item1Id + "->" + Item2Id : Item2Id + "->" + Item1Id;
        }
    }
    #endregion
}
```


4: 测试类Program

这里我们灌入了userid=1000，2000，3000的这三个人，然后我们预测userID=3000这个人对 “彩电” 的打分会是多少？

 

```csharp
    public class Program
    {
        static void Main(string[] args)
        {
            SlopeOne test = new SlopeOne();

            Dictionary<int, List<Product>> userRating = new Dictionary<int, List<Product>>();

            //第一位用户
            List<Product> list = new List<Product>()
            {
                new Product(){ ProductID=1, ProductName="洗衣机",Score=5},
                new Product(){ ProductID=2, ProductName="电冰箱", Score=10},
                new Product(){ ProductID=3, ProductName="彩电", Score=10},
                new Product(){ ProductID=4, ProductName="空调", Score=5},
            };

            userRating.Add(1000, list);

            test.AddUserRatings(userRating);

            userRating.Clear();
            userRating.Add(1000, list);

            test.AddUserRatings(userRating);

            //第二位用户
            list = new List<Product>()
            {
                new Product(){ ProductID=1, ProductName="洗衣机",Score=4},
                new Product(){ ProductID=2, ProductName="电冰箱", Score=5},
                new Product(){ ProductID=3, ProductName="彩电", Score=4},
                 new Product(){ ProductID=4, ProductName="空调", Score=10},
            };

            userRating.Clear();
            userRating.Add(2000, list);

            test.AddUserRatings(userRating);

            //第三位用户
            list = new List<Product>()
            {
                new Product(){ ProductID=1, ProductName="洗衣机", Score=4},
                new Product(){ ProductID=2, ProductName="电冰箱", Score=10},
                new Product(){ ProductID=4, ProductName="空调", Score=5},
            };

            userRating.Clear();
            userRating.Add(3000, list);

            test.AddUserRatings(userRating);

            //那么我们预测userID=3000这个人对 “彩电” 的打分会是多少？
            var userID = userRating.Keys.FirstOrDefault();
            var result = userRating[userID];

            var predictions = test.Predict(result);

            foreach (var rating in predictions)
                Console.WriteLine("ProductID= " + rating.Key + " Rating: " + rating.Value);
        }
    }
```


![][6]

[0]: http://www.cnblogs.com/huangxincheng/archive/2012/11/22/2782647.html
[1]: ./img/2012112213341826.png
[2]: ./img/2012112213342664.png
[3]: ./img/2012112214024826.png
[4]: ./img/2012112213580452.png
[5]: ./img/2012112214310871.png
[6]: ./img/2012112214345248.png