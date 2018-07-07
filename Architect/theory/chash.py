#!/usr/bin/env python
# coding=utf-8
import zlib

class Server:
    def __init__(self):
        self.servers = {}
        self.sortedkeys = []

    def addServer(self,ip,port):
        original_name = ip + port
        for i in range(1,32):
            name = ip + port + str(i)
            index = abs(zlib.crc32(name))
            self.servers[index] =  original_name
        self.sortedkeys = sorted(self.servers.iterkeys())


    def delServer(self,ip,port):
        str = ip + port
        index = zlib.crc32(str)
        del self.servers[index]

    def getServer(self,ip,port):
        str = ip + port
        index = abs(zlib.crc32(str))
        for v in self.sortedkeys:
            if index < v:
                return self.servers[v]
        #返回第一个
        return self.servers[self.sortedkeys[0]]
        

if __name__ == '__main__':
    server = Server()
    server.addServer("127.0.0.1","4700")
    server.addServer("127.0.0.1","4701")
    server.addServer("127.0.0.1","4702")
    server.addServer("127.0.0.1","4703")
    result = {}
    for i in range(1,50000):
        ret = server.getServer("127.0.0.1",str(i))
        if(result.get(ret)):
            result[ret] += 1
        else:
            result[ret] = 1
    print result