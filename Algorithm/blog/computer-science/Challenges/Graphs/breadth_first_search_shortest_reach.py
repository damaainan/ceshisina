#Given an undirected graph consisting of N nodes (labelled 1 to N) where a specific given node S represents the start position 
# and an edge between any two nodes is of length 6 units in the graph.

import collections
import Queue

def node_factory(value):
    return Node(value)

class MyDict(dict):
    def __init__(self, factory):
        self.factory = factory
    def __missing__(self, key):
        self[key] = self.factory(key)
        return self[key]

class Node:    
    def __init__(self, name):
        self.name = name
        self.adjacent = []
        

class Solution():
    
    def process(self, cost):
        cases = int(raw_input())
        for i in xrange(cases):
            self.processCase(cost)
    
    def processCase(self, cost):
        nodes, edges = [int(x) for x in raw_input().split(' ')]
        N = MyDict(node_factory)
        for j in xrange(edges):
            start, end = [int(x) for x in raw_input().split(' ')]
            N[start].adjacent.append(N[end])
            N[end].adjacent.append(N[start])
            
        start = int(raw_input())
        ans = self.findDistances(N[start], nodes, cost)
        print " ".join(ans)
    
    #n - nodes, e - edges
    #Time O(n + e), Space O(e + n)
    def findDistances(self, node, nodes, cost):
        
        q = Queue.Queue()
        q.put((0, node))

        distances = {}
        for i in xrange(1, nodes+1):
            distances[i] = -1
        visited = {}
        
        while not q.empty():
            cost_so_far, n = q.get()
            distances[n.name] = cost_so_far
            for adj in n.adjacent:
                if not adj.name in visited:
                    q.put((cost_so_far + cost, adj))
                    visited[adj.name] = True

        ans = []
        for x in xrange(1, nodes+1):
            if x != node.name:
                ans.append(str(distances[x]))
        return ans

cost = 6

solution = Solution()

solution.process(cost)
