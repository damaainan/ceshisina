### 简介

A Queue is a "first in, first out" or "FIFO" collection that only allows access to the value at the front of the queue and iterates in that order, destructively. 


### Queue — The Queue class

* Ds\Queue::allocate — Allocates enough memory for a required capacity.
* Ds\Queue::capacity — Returns the current capacity.
* Ds\Queue::clear — Removes all values.
* Ds\Queue::__construct — Creates a new instance.
* Ds\Queue::copy — Returns a shallow copy of the queue.
* Ds\Queue::count — Returns the number of values in the queue.
* Ds\Queue::isEmpty — Returns whether the queue is empty
* Ds\Queue::jsonSerialize — Returns a representation that can be converted to JSON.
* Ds\Queue::peek — Returns the value at the front of the queue.
* Ds\Queue::pop — Removes and returns the value at the front of the queue.
* Ds\Queue::push — Pushes values into the queue.
* Ds\Queue::toArray — Converts the queue to an array.