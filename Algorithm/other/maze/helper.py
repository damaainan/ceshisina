import random
import time

MAZE_1 = [
    [0, 0, 0, 0, 0, 2, 3],
    [1, 2, 2, 2, 0, 2, 0],
    [2, 0, 0, 0, 0, 2, 0],
    [2, 2, 2, 2, 0, 0, 0]]

MAZE_2 = [
    [2, 2, 3, 0, 2, 0],
    [1, 2, 2, 0, 2, 0],
    [0, 0, 2, 0, 2, 0],
    [2, 0, 2, 0, 0, 0],
    [2, 0, 0, 0, 2, 0]]

MAZE_3 = [
    [3, 2, 2, 2, 2, 2, 2, 2, 1],
    [0, 0, 2, 2, 2, 2, 2, 0, 0],
    [2, 0, 0, 2, 2, 2, 0, 0, 2],
    [2, 2, 0, 0, 2, 0, 0, 2, 2],
    [2, 2, 2, 0, 0, 0, 2, 2, 2]]

MAZE_LIST = [MAZE_1, MAZE_2, MAZE_3]


def fetch_maze():
    maze_id = random.randint(0, len(MAZE_LIST) - 1)
    print("maze-id {}-{}".format(1, round(time.time())))
    print('[' + str(MAZE_LIST[maze_id][0]) + ',')
    for line in MAZE_LIST[maze_id][1:-1]:
        print(' ' + str(line) + ',')
    print(' ' + str(MAZE_LIST[maze_id][-1]) + ']')
    return MAZE_LIST[maze_id]