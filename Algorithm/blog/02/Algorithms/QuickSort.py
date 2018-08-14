def quickSort(a):
    #base case:
    if len(a) <= 1:
        return a
    
    pivot = a[len(a)-1]
    
    pt1 = 0
    pt2 = 0
    while pt1 < len(a):
        if a[pt1] <= pivot:
            #swap
            tmp = a[pt2]
            a[pt2] = a[pt1]
            a[pt1] = tmp
            pt2 += 1
        pt1 += 1
        
    return quickSort(a[:pt2-1]) + [a[pt2-1]] + quickSort(a[pt2:])
