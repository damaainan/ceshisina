import threading
import random

class Account():
	def __init__(self, balance):
		self.balance = balance
		self.lock = threading.Lock()

total_money = 0

# seed accounts
accounts = []
for i in xrange(2):
	n = random.randrange(5000, 100000)
	total_money += n
	accounts.append(Account(n))

class acquire(object):
	def __init__(self, *locks):
		self.locks = sorted(locks, key=lambda x: id(x))
	def __enter__(self):
		for lock in self.locks:
			lock.acquire()
	def __exit__(self,ty,val,tb):
		for lock in reversed(self.locks):
			lock.release()
		return False

def transaction(ac1, ac2, amount):
	# print "* transfering $" + str(amount)	

	# naive, causing race conditions:
	b1 = ac1.balance
	b2 = ac2.balance
	ac1.balance = b1 - amount
	ac2.balance = b2 + amount		

	# using simple locks, causing deadlocks, because we're locking > 1 resource:
	# with ac1.lock:
	# 	with ac2.lock:
	# 		b1 = ac1.balance
	# 		b2 = ac2.balance
	# 		ac1.balance = b1 - amount
	# 		ac2.balance = b2 + amount	

	# #smart, no deadlocks
	# with acquire(ac1.lock, ac2.lock):
	# 	b1 = ac1.balance
	# 	b2 = ac2.balance
	# 	ac1.balance = b1 - amount
	# 	ac2.balance = b2 + amount


NACC = len(accounts)

# seed transactions
transactions = []
for i in xrange(1000):
	a1 = random.randrange(0, NACC)
	a2 = a1
	while a2 == a1:
		a2 = random.randrange(0, NACC)

	n = random.randrange(10, 100)	

	transactions.append(
			threading.Thread(
				target=transaction,
				args=(accounts[a1], accounts[a2], n)
			)
		)

for t in transactions:
	# print "starting transaction"
	t.start()

for t in transactions:
	t.join()


new_money = 0
for i in xrange(NACC):
	new_money += accounts[i].balance

print "was $%s, became $%s" % (total_money, new_money)


