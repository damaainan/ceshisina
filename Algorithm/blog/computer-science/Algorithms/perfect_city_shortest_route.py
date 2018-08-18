def perfectCity(departure, destination):
    dist = 0
    
    ar = [departure[:], destination[:]]
    
    for i in xrange(2):
        for j in xrange(2):
            if ar[i][j] % 1 > 0:
                rounded = ar[i][j] // 1
                r = ar[i][j] - rounded

                # move right
                arr = ar[:]
                arr[i][j] = rounded + 1
                opt1 = dist + 1 - r + perfectCity(arr[0], arr[1])

                # move left
                arr = ar[:]
                arr[i][j] = rounded
                opt2 = dist + r + perfectCity(arr[0], arr[1])
                
                return min(opt1, opt2)
        
    return abs(departure[0] - destination[0]) + abs(departure[1] - destination[1])


perfectCity([0.4, 1], [0.9, 3])
