#!/usr/bin/python
# -*- coding: UTF-8 -*-
from yunjiao import YunJiao;#导入的是类名
def printYunJiao():  
    a = ['a', 'o', 'e', 'i', 'u', 'v', 'ai', 'ei', 'ui', 'ao', 'ou', 'iu', 'ie','ve', 'er', 'an', 'en', 'in', 'un', 'vn', 'ang', 'eng', 'ing', 'ong'];  
  
    b = ['1', '2', '3', '4'];  
      
    s = '[';  
    for i in range(len(a)):  
        for j in range(4):  
            s1 = a[i] + b[j];  
            s += '[\''+s1+'\''+', '+ s1 + '], ';  
  
    s += ']';  
  
    print(s);  
  
def stat():  
    yj = YunJiao();  
    yj.genTable();  
  
    k = yj.keys();  
  
    a = [];  
  
    for i in range(len(k)):  
        v = yj.dict[k[i]];  
        a.append([k[i], len(v), v]);  
  
    a = sorted(a, key = lambda v : (v[1], v[0]));  
  
    total = 0;  
    for i in range(len(a)):  
        print('韵脚{0}共收录汉字{1}个'.format(a[i][0], a[i][1]));  
        total += a[i][1];  
  
    print('此次共收录汉字{0}个'.format(total));  
  
  
#查找某一韵脚相关的汉字  
def findYunjiao(key):  
    yj = YunJiao();  
    yj.genTable();  
    yj.find(key); 


findYunjiao('ong4')    