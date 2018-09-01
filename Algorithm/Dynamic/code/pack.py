#!/usr/bin/env python3
# -*- coding:utf-8 -*-
import copy

class ZOPACK(object):
    def __init__(self,n,m,w,v):
        self.num = n
        self.capacity = m
        self.weight_list = [0,] + w
        self.value_list = [0,] + v
        self.Sum_Value_Metrix = self.__CreateMetrix__(self.num+1,self.capacity+1,0)
        
    def __CreateMetrix__(self,x,y,init_value):
        d2_list = []
        for i in range(x):
            d1_list = []
            for j in range(y):
                d1_list.append(init_value)
            d2_list.append(d1_list)
        return d2_list
        
    def dp(self):
        sum_v = self.Sum_Value_Metrix
        num = self.num
        capacity = self.capacity
        w = self.weight_list
        v = self.value_list
        for i in range(1,num+1):
            for j in range(1,capacity+1):
                if j >=w[i]:
                    #print("i,j:%s,%s" % (i,j))
                    sum_v[i][j] = max(sum_v[i-1][j-w[i]] + v[i], sum_v[i-1][j])
                else:
                    sum_v[i][j] = sum_v[i-1][j]
        print("The max value we can get is: ", sum_v[-1][-1])
        print(sum_v)

if __name__ == "__main__":
    num = 5
    capacity = 10
    weight_list = [2, 2, 6, 5, 4]
    value_list = [6, 3, 5, 4, 6]
    q = ZOPACK(num,capacity,weight_list,value_list)
    q.dp()