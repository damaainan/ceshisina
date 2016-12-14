<?php 
/**
 * 数字游戏
小易邀请你玩一个数字游戏，小易给你一系列的整数。你们俩使用这些整数玩游戏。每次小易会任意说一个数字出来，然后你需要从这一系列数字中选取一部分出来让它们的和等于小易所说的数字。 例如： 如果{2,1,2,7}是你有的一系列数，小易说的数字是11.你可以得到方案2+2+7 = 11.如果顽皮的小易想坑你，他说的数字是6，那么你没有办法拼凑出和为6 现在小易给你n个数，让你找出无法从n个数中选取部分求和的数字中的最小数。 
输入描述:
输入第一行为数字个数n (n ≤ 20)
第二行为n个数xi (1 ≤ xi ≤ 100000)


输出描述:
输出最小不能由n个数选取求和组成的数

输入例子:
3
5 1 2

输出例子:
4
 */

//转化成 求出所有和的问题

function deal($arr){
	sort($arr);
	if($arr[0]>1){
		echo 'number===1';
		return;
	}
	$turn[]=1;
	$len=count($arr);
	for($i=1;$i<$len;$i++){

	}
}
function sum($n,$arr){
	//求出 arr 中 所有组合为 n 个元素的和
}


$arr=[2,3,5];
deal($arr);


/**
 *
 * 思路分析：对于给定的数组，我们必须先对其进行小到大的排序。如果最小的数不是
1（大于1），输出的结果必然就是1啦。如果是1，通过当前数与前边所有数的和加1进
行比较，如果当前数小于等于前边数的和加1，那么当前数也是可以找到它的几个因数
的；否则，直接返回前边数的和加1.跳出循环的有两种情况，break和循环终止条件，如
果是终止条件出来的，那就是说明从1到数组中所有元素的和，都可以找到它的因子，
此时返回所有数的和加1即可。
【源代码】
[cpp] view plain copy 在CODE上查看代码片派生到我的代码片
#include<stdio.h>  
#include<malloc.h>  
  
void sort(int arr[], int num)  
{  
    int i = 0;  
    int j = 0;  
    int tmp = 0;  
    for (i = 1; i < num; i++)  
    {  
        int m = arr[i];  
        for (j = i - 1; (j >= 0) && (arr[j] > m);j--)  
        {  
            arr[j + 1] = arr[j];  
        }  
        arr[j + 1] = m;  
    }  
}  
int fun(int arr[], int num)  
{  
    int i = 0;  
    int  r = 0, ans = -1;  
    //if (arr[0] != 1) 
    //   return 1; 
    for (int i = 0;i< num ;i++)  
    {  
        int tl =  arr[i], tr = r + arr[i];  
        if (tl <= r + 1)  
        {  
            r = tr;  
        }  
        else  
        {  
            ans = r + 1;  
            break;  
        }  
    }  
    if (ans == -1) ans = r + 1;  
    return ans;  
}  
int main()  
{  
    int *arr = NULL;  
    int num = 0;  
    int i = 0;  
    scanf("%d",&num);  
    if (num <= 0)  
    {  
        printf("input error");  
        return 0;  
    }  
    arr = (int *)malloc(num * sizeof(int));  
    if (NULL == arr)  
    {  
        printf("out of memory\n");  
        return 0;  
    }  
    for (i = 0; i < num; i++)  
    {  
        scanf("%d",&arr[i]);  
    }  
    sort(arr,num);  
    int ret = fun(arr, num);  
    printf("%d",ret);  
    return 0;  
}  


通过代码，我们发现，如果数组的最小元素不是1，仍然可以通过for循环判断出来，不
需要fun函数中最前边的if判断。
 */