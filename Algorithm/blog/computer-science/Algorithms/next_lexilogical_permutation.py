import sys

class Solution():
    
    def _split(self, word):
        j = len(word) - 1
        while(j > 0 and word[j-1] >= word[j]):
            j -= 1
        
        if j <= 0:
            return ([], word)
        
        return (word[:j], word[j:])
    
    def _find_smallest_bigger(self, word, val):
        j = len(word) - 1
        while(word[j] <= val):
            j -= 1
        
        return j
    
    def next_perm(self, word):
        word = list(word)
        
        pref, post = self._split(word)
        
        if len(pref) == 0:
            return "no answer"
        
        min_ind = self._find_smallest_bigger(post, pref[-1])
        
        tmp = pref[-1]
        pref[-1] = post[min_ind]
        post[min_ind] = tmp
        
        post.reverse()
        
        return "".join(pref + post)
        

solution = Solution()
        
cases = int(raw_input())

for i in xrange(cases):
    word = raw_input()
    print solution.next_perm(word)

    
