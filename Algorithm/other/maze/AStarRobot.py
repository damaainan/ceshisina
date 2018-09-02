import helper

# https://github.com/JadynAi/Python_D

# 迷宫数据，0代表可通行，1起点，3终点，2障碍
env_data = helper.fetch_maze()

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


def compute_cost(loc):
    """
    计算loc到终点消耗的代价
    :param loc:
    :return:
    """
    return min(abs(loc[0] - des_loc[0]), abs(loc[1] - des_loc[1]))


# 已经走过的路径list
road_list = [start_loc]
# 证实是失败的路径
failed_list = []

# 没有到达终点就一直循环
while road_list[len(road_list) - 1] != des_loc:
    if len(road_list) == 0:
        print("迷宫无解")
        break
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

half = len(road_list) // 2
print("路径为 ： ", road_list[:half])
print("路径为 ： ", road_list[half:len(road_list)])
print("抵达终点!共耗费{}步".format(len(road_list) - 2))