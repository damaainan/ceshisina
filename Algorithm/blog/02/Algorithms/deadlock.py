import threading

# if we acquire all locks simultaneously (in one construction), we can avoid deadlocks
# deadlocks occure only when there's a possibility to lock one resource and not lock another and let another process to clock it
class acquire(object):
    def __init__(self,*locks):
        self.locks = sorted(locks, key=lambda x: id(x))
    def __enter__(self):
        for lock in self.locks:
            lock.acquire()
    def __exit__(self,ty,val,tb):
        for lock in reversed(self.locks):
            lock.release()
        return False

# The philosopher thread
def philosopher(left, right):
    while True:
        # This code will cause a deadlock
        with left:
             with right:
                 print threading.currentThread(), "eating"
        # this code instead will allow to avoid it
        # with acquire(left,right):
        #      print threading.currentThread(), "eating"

# The chopsticks
NSTICKS = 5
chopsticks = [threading.Lock() 
              for n in xrange(NSTICKS)]

# Create all of the philosophers
phils = [threading.Thread(target=philosopher,
                          args=(chopsticks[n],chopsticks[(n+1) % NSTICKS]))
         for n in xrange(NSTICKS)]

# Run all of the philosophers
for p in phils:
    p.start()
