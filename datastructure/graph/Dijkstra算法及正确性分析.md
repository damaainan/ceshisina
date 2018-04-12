## Dijkstra算法及正确性分析

来源：[https://juejin.im/post/5acde1bc6fb9a028e25deb23](https://juejin.im/post/5acde1bc6fb9a028e25deb23)

时间 2018-04-11 18:46:48

 
最近朋友问了一个关于列车调度的问题，求两个地点之间的最短路径。听起来挺简单的问题，可是仔细思考后发现完全无从下手。最近空闲下来便恶补了一番数据结构。
 
求最短路径的方法有Dijkstra，Floyd，BFS等等 其中Floyd适合多源最短路径，BFS适合无权值的情况，这里问题属于单源最短路径，所以我们采用Dijkstra算法.
 
开干吧！拿出地铁卡就是一顿画，现在我们就把问题抽象为求 **`宝安中心-老街`**  的最短路径
 
 ![][0]
开始之前首先我们介绍几个概念
 
### found集合
 
对于这种起点到某个顶点的真正的最短路径，我们称它为全局最短路径。已经找到全局最短路径的顶点，我们将其储在found集合中.现在来初始化一下found
 
```php
// 初始时,我们已知的就只有宝安中心到宝安中心的最短路径
   $found = ['宝安中心'];
```
 
### dist集合
 
用来存储起点(宝安中心)到某个顶点u， 相对于S的最短路径。通俗解释就是：宝安中心到世界之窗经过了新安，那么必须有新安∈ S。 否则我们无法直接知道其相对最短路径，在dist中则将其记为不可能存在的大值，用来表示这个点我们目前还没有办法探索到。
 
dist中存储的最短路径称之为相对于S的最短路径。下文我们简称为相对最短路径，和其对应的我们有一个全局最短路径
 
初始一下dist集合
 
```php
const MAX = 65525; //65525是我们定义的一个不可能出现的大值

 $dist = [
     '深圳北站' => 5,
     '新安' => 8,
     '世界之窗' => MAX,
     '福田' => MAX,
     '购物公园' => MAX,
     '老街' => MAX,
     '布吉' => MAX,
 ];
```
 
## 算法描述
 
开始我们的算法~ 我们首先找到`dist`中的最小值，这里是`深圳北站 => 5`。
 
顺便告诉你一个激动人心的消息，我们找到了宝安中心到深圳北站的全局最短路径。
 
what?为什么dist中的最小值就是我们要找的全局最短路径(这里是`宝安中心-深圳北站`)？ 我们要找的不是`宝安中心-老街`的全局最短路径吗，知道了`宝安中心-深圳北站`的最短路径有什么用吗？ 我现在没法给你一个很好的解释，我们继续往下看。
 
继续进行算法，下面是红色顶点表示已经确定了全局最短路径的顶点
 
 ![][1]
 既然已经又找到了一个全局最短路径顶点，我们就把它更新到  **`found`**  
集合中
 
```php
$found = ['宝安中心', '深圳北站'];
```
 
集合found中的元素增加了一枚后，我们的视野变的宽广了。我们可以通过`深圳北站`作为一个中转更新 **`dist`**  这个 **`相对最短路径`**  集合了
 
 ![][2]
通过深圳北站这个中转站我们可以得到已下相对最短路径
 `宝安中心-深圳北站-新安`= 5 + 2 = 7`宝安中心-深圳北站-福田`= 5 + 5 = 10`宝安中心-深圳北站-布吉`= 5 + 10 = 15
 
现在可以马上去替换我们 **`dist`**  集合中的值了吗？别急，我们需要的是相对最短路径，可不是什么阿猫阿狗就能进来的。所以我们需要进行一个比较.
 
```php
// 如果新的相对最短路径比原有的相对最短路径要小，我们则进行一个更新
if ($newWeight < $dist['新安']) {
    $dist['新安'] = $newWeight;
}
```
 
dist集合更新如下
 
```php
$dist = [
     '深圳北站' => 5, // ok
     '新安' => 7, //8 -> 7
     '世界之窗' => MAX,
     '福田' => 10, // MAX -> 10
     '购物公园' => MAX,
     '老街' => MAX,
     '布吉' => 15 // MAX -> 15
 ];
```
 
现在我们重复之前的步骤，找一个最小值，其就是我们下一个全局最短路径。要记住，深圳北站就不要加入查找队列了，其已经被found了
 
人眼扫描后可以确定下一个全局最短路径的顶点为新安。并且有了新安的中转，我们可以再次拓宽我们的视野
 
为了表示清晰,对于还没有探索到相对最短路径，先隐藏其权重值
 
 ![][3]
 更新后的  **`found`**  和  **`dist`**  
如下
 
```php
$found = ['宝安中心', '深圳北站','新安'];

$dist = [
     '深圳北站' => 5, // ok
     '新安' => 7, // ok
     '世界之窗' => 16, // MAX -> 16 = 9+7 = 宝安->新安 + 新安->世界之窗
     '福田' => 10, // MAX -> 10
     '购物公园' => MAX,
     '老街' => MAX,
     '布吉' => 15
 ];
```
 
再次循环 (目标已经出现在我们的视野中啦，别着急，我们还没有确定其全局最短路径) ↓
 
 ![][4]
更新后的s和dist如下
 
```php
$found = ['宝安中心', '深圳北站', '新安', '福田'];

$dist = [
     '深圳北站' => 5, // ok
     '新安' => 7, // ok
     '世界之窗' => 16,
     '福田' => 10, // ok
     '购物公园' => 12, // MAX -> 12
     '老街' => 15, // MAX -> 15
     '布吉' => 15
 ];
```
 
再次循环↓
 
 ![][5]
更新后的found和dist如下
 
```php
$s = ['宝安中心', '深圳北站', '新安', '福田', '购物公园'];

$dist = [
     '深圳北站' => 5, // ok
     '新安' => 7, // ok
     '世界之窗' => 16,
     '福田' => 10, // ok
     '购物公园' => 12, // ok
     '老街' => 15,
     '布吉' => 15
 ];
```
 
再次寻找dist中最小值时， 找到了我们的目标，老街。
 
 ![][6]

#### 算法描述完毕!
 
## 算法实现

```php
<?php
namespace Algorithm;
class Dijkstra
{
    /**
     * 图(路径)数据, 为了方便这里采用`邻接矩阵`存储.
     * 邻接矩阵为图数据存储的一种方式
     * @var $graph
     */
     protected $graph;
    /**
     * @var array 已经找到了全局最短路径的节点. 既算法描述中的集合s
     */
     protected $found = [];
    /**
     * @var array 相对于S的最短路径集合 dist
     */
     protected $distance = [];
    /**
     * @var int
     */
    protected $vertexCount = 0;
    public function __construct(array $graph, array $vertex)
    {
        $this->vertexCount = count($graph);
        $this->graph = $graph;
    }
    /**
     * 查找给点起点到终点的最小权值
     * @param $begin
     * @param $end
     * @return mixed
     */
    public function findWeight($begin, $end)
    {
        $this->initFound($begin);
        $this->initDistance($begin);
        for ($i = 0; $i < $this->vertexCount; ++$i) {
            $minVertex = $this->findMinVertex();
            if ($minVertex === $end) {
                return $this->distance[$minVertex];
            }
            $this->found[] = $minVertex;
            $this->updateDistance($minVertex);
        }
    }
    private function initFound($begin)
    {
        $this->found[] = $begin;
    }
    private function initDistance($begin)
    {
        $this->distance = $this->graph[$begin];
    }
    private function findMinVertex()
    {
        $temp = array_diff_key($this->distance, array_flip($this->found));
        return array_keys($temp, min($temp))[0];
    }
    /**
     * 每一次找到一个全局最短路径顶点之后,我们都要根据此顶点进行中转来更新我们的相对最短顶点集合
     * @param $vertex 宝安中心/老街
     */
    private function updateDistance($vertex)
    {
        foreach ($this->graph[$vertex] as $key => $value) {
            $newValue = $this->distance[$vertex] + $value;
            if ($newValue < $this->distance[$key]) {
                $this->distance[$key] =$newValue;
            }
        }
    }
}
```

```php 
// 测试
<?php
namespace Algorithm\Test;
use Algorithm\Dijkstra;
use PHPUnit\Framework\TestCase;
class DijkstraTest extends TestCase
{
    const MAX = 65525;
    private function createGraph($arc, $vertex)
    {
        $graph = [];
        //init
        foreach($vertex as $row){
            foreach($vertex as $column){
                $graph[$row][$column] = $row == $column ? 0 : self::MAX;
            }
        }
        //create
        foreach ($arc as $value) {
            $graph[$value['begin']][$value['end']] = $value['weight'];
            // 无向图需要反向设置
            $graph[$value['end']][$value['begin']] = $value['weight'];
        }
        return $graph;
    }
    public function testFindWeight()
    {
        $vertex = ['宝安中心', '新安', '深圳北站', '福田', '购物公园', '世界之窗', '老街', '布吉'];
        $arc = [
            [
                'begin' => '宝安中心',
                'end' => '深圳北站',
                'weight' => 5,
            ],
            [
                'begin' => '宝安中心',
                'end' => '新安',
                'weight' => 8,
            ],
            [
                'begin' => '新安',
                'end' => '深圳北站',
                'weight' => 2,
            ],
            [
                'begin' => '新安',
                'end' => '世界之窗',
                'weight' => 9,
            ],
            [
                'begin' => '深圳北站',
                'end' => '福田',
                'weight' => 5,
            ],
            [
                'begin' => '深圳北站',
                'end' => '布吉',
                'weight' => 10,
            ],
            [
                'begin' => '福田',
                'end' => '世界之窗',
                'weight' => 6,
            ],
            [
                'begin' => '福田',
                'end' => '购物公园',
                'weight' => 2,
            ],
            [
                'begin' => '福田',
                'end' => '老街',
                'weight' => 5,
            ],
            [
                'begin' => '购物公园',
                'end' => '世界之窗',
                'weight' => 4,
            ],
            [
                'begin' => '购物公园',
                'end' => '老街',
                'weight' => 4,
            ],
            [
                'begin' => '老街',
                'end' => '布吉',
                'weight' => 6,
            ]
        ];
        $graph = $this->createGraph($arc, $vertex);
        $dijkstra = new Dijkstra($graph, $vertex);
        $weight = $dijkstra->findWeight('宝安中心', '老街');
        $this->assertEquals(15, $weight);
    }
}
```
 
[github.com/weiwenhao/a…][11]
 
## 正确性分析
 
为什么dist集合中的最小值就是我们要找的全局最短路径？
 
```php
$dist = [
     '福田' => 10, // ok
     '购物公园' => 12,
     '老街' => 15,
 ];
```
 
以某一次dist集合的部分数据为例子，按照算法描述`宝安中心-福田-购物公园`是我们要找的全局最短路径。现在我们假设到`宝安中心-购物公园`存在更短的路径，则存在如下两种情况
 
### 情况1
 
 ![][7]
红色区域代表集合found，表示已经找到了全局最短路径的顶点集合。 上面提到过，dist集合中存储的是相对于S的最短路径。
 
对于这种情况，算法在进行dist集合更新操作的时候就已经判断了`宝安中心-福田-购物公园`和`宝安中心-X-购物公园`之间的更小值，因此这种情况不可能存在。我们继续来看另外一种更加可能出现的情况
 

* 情况2 
 

![][8]
是否会存在这样一条最短路径呢？因为`y-购物公园`的距离我们并没有探索过，所以这种情况是需要慎重思考一种情况。
 
先让时光倒流
 
 ![][9]
此时我们的dist集合中一共有5个顶点。其中宝安中心，福田，X已经被加入到了found集合中。Y通过X的中转后被发现，购物公园通过福田中专后被发现。 此时根据我们的算法，将会在Y和购物公园中选取一个最小值，作为下一个全局最短路径顶点。这里算法选择了购物公园。说明`宝安中心-福田-购物公园 < 宝安中心-X-Y`回到情况2
 
 ![][8]
在有了`宝安中心-福田-购物公园 < 宝安中心-X-Y`前提下。`宝安中心-X-Y-购物公园 < 宝安中心-福田-购物公园`是否能够成立呢？假如等式不成立，则说明不可能存在一条比`宝安中心-福田-购物公园`更短的全局最短路径。
 
等式是否成立我相信你一目了然。
 
#### 正确性分析完毕！
 
## 结语
 
你可能还在惊讶于dijkstra算法为什么这么神奇？就算我们已经知道了算法步骤，分析了算法的正确性。可还是不禁会感叹，到底是怎么做到的，到底是怎么找到最优解的？
 
回过头去看看算法描述你会发现，其实 **`dijkstra`**  并不知道自己什么时候能够找到自己想要的目标，它只是关注于眼前的最优解，然后碰巧在某一时刻眼前的最优解就是要寻找的目标值。这看起来有点笨，但是在某些情况十分有用，比如路由寻址中查找最短路径必须要用到这种策略。
 
哦~对了，这种只关注于眼前最优解的方法其实有个更加有逼格的名字 —— 贪心算法。
 


[11]: https://github.com/weiwenhao/algorithm/blob/master/src/Dijkstra.php
[0]: ../img/yAVNvee.jpg 
[1]: ../img/VJjiU3j.jpg 
[2]: ../img/N7rQZ3Y.jpg 
[3]: ../img/ZVfueaB.jpg 
[4]: ../img/VBZn6ve.jpg 
[5]: ../img/yuuMN3r.jpg 
[6]: ../img/263A32b.jpg 
[7]: ../img/JjueU3B.jpg 
[8]: ../img/iyYNzqe.jpg 
[9]: ../img/iQvmumB.jpg 
[10]: ../img/iyYNzqe.jpg 