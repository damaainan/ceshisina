### 简介

A PriorityQueue is very similar to a Queue. Values are pushed into the queue with an assigned priority, and the value with the highest priority will always be at the front of the queue. 

Implemented using a max heap. 


Note: 

"First in, first out" ordering is preserved for values with the same priority. 



Note: 

Iterating over a PriorityQueue is destructive, equivalent to successive pop operations until the queue is empty. 

### PriorityQueue — The PriorityQueue class

* Ds\PriorityQueue::allocate — Allocates enough memory for a required capacity.
* Ds\PriorityQueue::capacity — Returns the current capacity.
* Ds\PriorityQueue::clear — Removes all values.
* Ds\PriorityQueue::__construct — Creates a new instance.
* Ds\PriorityQueue::copy — Returns a shallow copy of the queue.
* Ds\PriorityQueue::count — Returns the number of values in the queue.
* Ds\PriorityQueue::isEmpty — Returns whether the queue is empty
* Ds\PriorityQueue::jsonSerialize — Returns a representation that can be converted to JSON.
* Ds\PriorityQueue::peek — Returns the value at the front of the queue.
* Ds\PriorityQueue::pop — Removes and returns the value with the highest priority.
* Ds\PriorityQueue::push — Pushes values into the queue.
* Ds\PriorityQueue::toArray — Converts the queue to an array.