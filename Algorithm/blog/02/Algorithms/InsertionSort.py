def insertionSort(a):
    
    for i in xrange(len(a)):
        tmp = a[i]
        j = i - 1
        while j >= 0 and a[j] > tmp:
            a[j+1] = a[j]
            j -= 1
        a[j+1] = tmp
        
    return a
