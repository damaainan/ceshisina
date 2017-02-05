#!/usr/bin/python
# -*- coding: UTF-8 -*-

#机器人原型机

import time;  
import logging;  
  
class Robot(object):  
    def __init__(self):  
        pass;  
  
    def say(self, msg):  
        TIME = time.strftime('%Y-%m-%d %A %H:%M:%S', time.localtime());  
        DATA = {'time':TIME, 'user':'[机器小伟]'};  
        FORMAT = "%(time)-20s %(user)-8s %(message)s";  
        logging.basicConfig(format=FORMAT);  
        logger = logging.getLogger('machine');  
        logger.warn('说：%s', msg, extra=DATA);  
        string = DATA['time']+' ' + DATA['user']+ '说：'+msg+'\n';  
        print(string);  
        fout.write(string);  
          
        return;  
          
      
if __name__ == '__main__':  
    fout = open('output.txt', 'a');  
    a = Robot();  
    a.say('OK***');  
    fout.close();  