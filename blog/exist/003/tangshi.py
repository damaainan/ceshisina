#!/usr/bin/python
# -*- coding: UTF-8 -*-
import time;  
#选取唐诗韵脚  
def yunJiaoInTangPoem(yunjiao):  
    limitLine = 1000000;  
    count = 0;  
    showLimit = 100;  
  
    if (len(yunjiao) != 1):  
        print('韵脚只能是一个字');  
        return;  
  
    a = set();  
    startTime = endTime = 0;  
      
    try:  
        fin = open('TangPoem.txt', 'r');  
  
        #计时开始  
        startTime = time.clock();  
  
        for line in fin.readlines():  
            #print(line[-2]);  
            if (len(line) > 2):  
                if (line[-2] == yunjiao):  
                    a.add(line[:-1]);  
  
            count += 1;  
            if (count > limitLine):  
                break;              
  
        #计时结束  
        endTime = time.clock();  
    finally:  
        fin.close();  
  
    len_ = len(a);  
    if (len_ > 0):  
        a = list(a);  
        print('共有记录{0}条'.format(len_));  
        print(a[:showLimit]);  
    else:  
        print('没有相关记录。');  
  
    #打印结果  
    print('操作用时：{0:.3e} s'.format(endTime-startTime));  
  
def statYunJiaoInTangPoem():  
    limitLine = 1000000;  
    count = 0;  
    showLimit = 10000;  
  
    dict_ = dict();  
    startTime = endTime = 0;  
      
    try:  
        fin = open('TangPoem.txt', 'r');  
        fout = open('output.txt', 'w');  
  
        #计时开始  
        startTime = time.clock();  
  
        for line in fin.readlines():  
            #print(line[-2]);  
            if (len(line) > 2):  
                yunjiao = line[-2];  
    
                if (yunjiao in dict_.keys()):  
                    dict_[yunjiao].add(line[:-1]);  
                else:  
                    dict_[yunjiao] = set();  
                    dict_[yunjiao].add(line[:-1]);  
  
            count += 1;  
            if (count > limitLine):  
                break;              
  
        #计时结束  
        endTime = time.clock();  
  
        for key, value in dict_.items():  
            len_ = len(value);  
            fout.write('--- {0} 有记录 {1}条---\r\n'.format(key, len_));  
            if (len_ > 0):  
                fout.write('[{0}, {1}]\r\n'.format(key, value));  
              
    finally:  
        fin.close();  
        fout.close();  
  
    #打印结果  
    print('操作用时：{0:.3e} s'.format(endTime-startTime));  