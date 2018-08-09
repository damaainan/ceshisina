## 使用A*算法求解机器人迷宫最短路径

来源：[http://ailoli.me/2018/07/29/A-maze/](http://ailoli.me/2018/07/29/A-maze/)

时间 2018-07-29 20:18:46

 
#### 原创文章，转载请联系作者
 
时光只解催人老，不信多情，长恨离亭，泪滴春衫酒易醒。
 
### 前言
 
最近接触了一个挺有意思的小课题，跟大家分享一下。就是利用`A*`算法，来计算迷宫可行路径。有关这个算法的知识，大家可以看看[A 算法维基百科](https://zh.wikipedia.org/wiki/A  %E6%90%9C%E5%B0%8B%E6%BC%94%E7%AE%97%E6%B3%95)以及 [A星算法详解][2] 来稍作了解。
 
代码地址在此 [Maze][3] ,喜欢`Python`的小可爱们可以拿去练练手。
 
### 提要说明
 
本题中的迷宫，是以宫格类型呈现的，在代码中的呈现为`二维数组`。其次在迷宫中的移动，也只有 **`上、下、左、右`**  四个动作可选。如下所示：
 
其中 **`1`**  代表入口， **`2`**  代表障碍物不可通行， **`3`**  代表出口
 
```
[[3, 2, 2, 2, 2, 2, 2, 2, 1],
 [0, 0, 2, 2, 2, 2, 2, 0, 0],
 [2, 0, 0, 2, 2, 2, 0, 0, 2],
 [2, 2, 0, 0, 2, 0, 0, 2, 2],
 [2, 2, 2, 0, 0, 0, 2, 2, 2]]
```
 
其实在`A*算法`中，对单位搜索区域的描述为–`节点nodes`。在本题中，我们可以把搜索区域视为正方形，会更简单一点。
 
### A*算法逻辑解析
 `A*算法`的逻辑其实并不是很难，简化起来就是两个词： **`评估`**  、 **`循环`**  。
 
从起点开始行动，首先找到起点周围可以行走的`节点`，然后在这个节点中，`评估`出距离终点最优（最短）的`节点`。那么这个`最优`节点，将作为下一步行动的点，以此类推，直至找到终点。
 
可以看到，在这个逻辑中，其实最重要的就是`评估`这一步了。`A*算法`的评估函数为：
 `f(n) = g(n) + h(n)``g(n)`–代表移动到这个点的代价，在本题中均为1.因为只可以水平或者数值运动。要是斜角可以移动的话，那么这个值就为`√2`
`h(n)`–从这个点移动到终点的代价，这是一个猜测值。本题中，将迷宫视作坐标系的话，那么`h(n)`就是取和终点x、y各自差值的最小者。譬如点[4,2]和终点[1,1]的`h(n)`取值为：1
 
### 代码实现
 
代码中对点的描述，均为实际值，并非以0为开始值计算。
 
#### 定位起点和终点，使用列表存储四个移动命令，以下代码`env_data`代表迷宫数组： 
 
```python
# 上下左右四个移动命令，只具备四个移动命令
orders = ['u', 'd', 'l', 'r']

# 定位起点和终点
start_loc = []
des_loc = []
for index, value in enumerate(env_data, 1):
    if len(start_loc) == 0 or len(des_loc) != 0:
        if 1 in value:
            start_loc = (index, value.index(1) + 1)
        if 3 in value:
            des_loc = (index, value.index(3) + 1)
    else:
        break
```
 
#### 判断节点所有可执行的移动命令：
 
```python
def valid_actions(loc):
    """
    :param loc:
    :return: 当前位置所有可用的命令
    """
    loc_actions = []
    for order in orders:
        if is_move_valid(loc, order):
            loc_actions.append(order)
    return loc_actions

def is_move_valid(loc, act):
    """
    判断当前点，是否可使用此移动命令
    """
    x = loc[0] - 1
    y = loc[1] - 1
    if act not in orders:
        return false
    else:
        if act == orders[0]:
            return x != 0 and env_data[x - 1][y] != 2
        elif act == orders[1]:
            return x != len(env_data) - 1 and env_data[x + 1][y] != 2
        elif act == orders[2]:
            return y != 0 and env_data[x][y - 1] != 2
        else:
            return y != len(env_data[0]) - 1 and env_data[x][y + 1] != 2
```
 
#### 拿到节点周围移动单位为`1`的所有可到达点,不包括此节点： 
 
```python
def get_all_valid_loc(loc):
    """
    计算当前点，附近所有可用的点
    :param loc:
    :return:
    """
    all_valid_data = []
    cur_acts = valid_actions(loc)
    for act in cur_acts:
        all_valid_data.append(move_robot(loc, act))
    if loc in all_valid_data:
        all_valid_data.remove(loc)
    return all_valid_data
    
def move_robot(loc, act):
    """
    移动机器人，返回新位置
    :param loc:
    :param act:
    :return:
    """
    if is_move_valid(loc, act):
        if act == orders[0]:
            return loc[0] - 1, loc[1]
        elif act == orders[1]:
            return loc[0] + 1, loc[1]
        elif act == orders[2]:
            return loc[0], loc[1] - 1
        else:
            return loc[0], loc[1] + 1
    else:
        return loc
```
 
####`h(n)`函数体现： 
 
```python
def compute_cost(loc):
    """
    计算loc到终点消耗的代价
    :param loc:
    :return:
    """
    return min(abs(loc[0] - des_loc[0]), abs(loc[1] - des_loc[1]))
```
 
#### 开始计算
 
使用`road_list`来保存走过的路径，同时用另一个集合保存失败的节点——即此节点附近无可用节点， **`死胡同`**  。
 
```python
# 已经走过的路径list，走过的路
road_list = [start_loc]
# 证实是失败的路径
failed_list = []

# 没有到达终点就一直循环
while road_list[len(road_list) - 1] != des_loc:
    # 当前点
    cur_loc = road_list[len(road_list) - 1]
    # 当前点四周所有可用点
    valid_loc_data = get_all_valid_loc(cur_loc)
    # 如果可用点里包括已经走过的节点，则移除
    for cl in road_list:
        if cl in valid_loc_data:
            valid_loc_data.remove(cl)
    # 如果可用点集合包括失败的节点，则移除
    for fl in failed_list:
        if fl in valid_loc_data:
            valid_loc_data.remove(fl)
    # 没有可用点，视作失败，放弃该节点。从走过的路集合中移除掉
    if len(valid_loc_data) == 0:
        failed_list.append(road_list.pop())
        continue
    # 用评估函数对可用点集合排序，取末端的值，加入走过的路集合中
    valid_loc_data.sort(key=compute_cost, reverse=True)
    road_list.append(valid_loc_data.pop())
```
 
#### 看运行结果
 
![][0]
 
### 结语
 
  
人生苦短，我用`Python`。代码地址在此 [Maze][3] ,喜欢`Python`的小可爱们可以拿去练练手。
 
在研究迷宫的过程中，发现生成迷宫的算法也是很有意思的，等忙完这段时间再去研究研究。嘻~~~~~
 
以上
 
  
* [ Previous 使用DSL模式构建Recyclerview适配器 ][5]  
 
 


[2]: https://blog.csdn.net/hitwhylz/article/details/23089415
[3]: https://github.com/JadynAi/Python_D/blob/master/venv/include/maze/AStarRobot.py
[4]: https://github.com/JadynAi/Python_D/blob/master/venv/include/maze/AStarRobot.py
[5]: http://ailoli.me/2018/07/05/kotlin-adapter/
[0]: https://img1.tuicool.com/JjMjQr6.jpg 
