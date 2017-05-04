<?php 

/**
 * 网易2017春招笔试(调整队形)
 *
 * 一、前言

如果一直有看我的每周面试题的童鞋们，应该都知道，之前的面试题中很少或者说基本没有程序题，可是开发岗位的面试是无法避免程序题的，因此这周来一提程序题。

二、题目

在幼儿园有 n 个小朋友排列为一个队伍，从左到右一个挨着一个编号为 ( 0 ~ n-1 ) 。其中有一些是男生，有一些是女生，男生用 ’B’ 表示，女生用 ’G’ 表示。小朋友们都很顽皮，当一个男生挨着的是女生的时候就会发生矛盾。作为幼儿园的老师，你需要让男生挨着女生或者女生挨着男生的情况最少。你只能在原队形上进行调整，每次调整只能让相邻的两个小朋友交换位置，现在需要尽快完成队伍调整，你需要计算出最少需要调整多少次可以让上述情况最少。例如：

GGBBG -GGBGB -GGGBB
这样就使之前的两处男女相邻变为一处相邻，需要调整队形 2 次
输入描述:

输入数据包括一个长度为 n 且只包含 G 和 B 的字符串. n 不超过 50.
输出描述:

输出一个整数，表示最少需要的调整队伍的次数
输入例子:

GGBBG
输出例子:

2
三、解题

题目的意思应该很好就能理解，不能理解的看下给出的例子，基本就能知道题目表达的意思了。根据题目的意思，“男生挨着女生或者女生挨着男生的情况最少”，那么最终调整的队形无非就两个结果，第一：男生全部在左，女生全部在队列的右边，中间只有一男一女是相挨着的。第二：要么就是反过来，女生全部在左，男生在右。也就是假设长度为 8 的时候，只要调整为 BBBBGGGG 或者 GGGGBBBB 就行了。

这是根据第一个条件进行分析的结果，那么我们看第二个条件，“每次调整只能让相邻的两个小朋友交换位置，且需要的是最少需要调整的情况” ， 从上面可以知道，我们最终需要将队形调整成 BBBBGGGG 或者 GGGGBBBB 就可以了，那么调整的时候我们只需要调整 B 或者 G 就可以了，意思就是说调整 B 的时候，G 不动，如果调整 G ，B 不动。为什么呢？因为只动 B 和只动 G 是等价的。

用 JAVA 来编程的话，我们就只移动 B ,用一个 list 记录每个 B 所在的位置（从 0 开始），比如 GGBBG , list 中有两个值， 2 和 3 ，大小为 2 ，如果序列为 2 的 B 移动到最左边需要移动的次数是 2 次，也就变成 BGGBG，序列为 3 的 B 移动到最左边需要移动的次数是 3 次，可是因为之前已经移动号了一个，我们只需移动 2 次就行了，也就是移动到第一次移动到最左边的 B 的左边；所以我们可以对当前每个 B 的下标求和，每进行一次有用的调整必然使当前的和 +1 或者 -1，最后我们只要计算出当前的和与最终结果的和的差。

程序地址：https://github.com/TwoWater/Interview/blob/master/Interview/src/com/liangdianshui/AdjustFormation.java

public class AdjustFormation {

    public static void main(String[] args) {
        // 控制台输入
        Scanner sc = new Scanner(System.in);
        String s = sc.next();
        sc.close();

        int n = s.length();
        if (n <= 0 || n > 50) {
            throw new RuntimeException("the length is too long");
        }
        ArrayList<Integer> list = new ArrayList<>();
        for (int i = 0; i < n; i++) {
            if (s.charAt(i) == 'B') {
                list.add(i);
            }
        }

        int bSize = list.size();
        int indexSum = 0;
        for (int val : list) {
            indexSum += val;
        }

        int left = indexSum - bSize * (bSize - 1) / 2;
        int right = bSize * (n - 1) - indexSum - bSize * (bSize - 1) / 2;
        // 移左 或者 移右 ，选择最少的
        System.out.println(Math.min(left, right));
    }
}
 */