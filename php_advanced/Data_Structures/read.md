PHP 扩展 Data Structures 的基本使用和解读

**不能在命令行使用？需要修改 php-cgi.ini ，添加该扩展**

直接修改 php.ini 即可

包含的类如下：  

Collection---  
Deque---双端队列  
Hashable---  
Map---映射  
Pair---对  
PriorityQueue---优先队列  
Queue---队列  
Sequence---序列  
Set---集合  
Stack---栈  
Vector---向量  


`Collection`、`Sequence` 没有自身的类，使用时都是实例化 `Vector`


1. Collection — The Collection interface
    * Ds\Collection::clear — Removes all values.
    * Ds\Collection::copy — Returns a shallow copy of the collection.
    * Ds\Collection::isEmpty — Returns whether the collection is empty
    * Ds\Collection::toArray — Converts the collection to an array.

1. Hashable — The Hashable interface
    * Ds\Hashable::equals — Determines whether an object is equal to the current instance.
    * Ds\Hashable::hash — Returns a scalar value to be used as a hash value.

1. Sequence — The Sequence interface
    * Ds\Sequence::allocate — Allocates enough memory for a required capacity.
    * Ds\Sequence::apply — Updates all values by applying a callback function to each value.
    * Ds\Sequence::capacity — Returns the current capacity.
    * Ds\Sequence::contains — Determines if the sequence contains given values.
    * Ds\Sequence::filter — Creates a new sequence using a callable to determine which values to include.
    * Ds\Sequence::find — Attempts to find a value's index.
    * Ds\Sequence::first — Returns the first value in the sequence.
    * Ds\Sequence::get — Returns the value at a given index.
    * Ds\Sequence::insert — Inserts values at a given index.
    * Ds\Sequence::join — Joins all values together as a string.
    * Ds\Sequence::last — Returns the last value.
    * Ds\Sequence::map — Returns the result of applying a callback to each value.
    * Ds\Sequence::merge — Returns the result of adding all given values to the sequence.
    * Ds\Sequence::pop — Removes and returns the last value.
    * Ds\Sequence::push — Adds values to the end of the sequence.
    * Ds\Sequence::reduce — Reduces the sequence to a single value using a callback function.
    * Ds\Sequence::remove — Removes and returns a value by index.
    * Ds\Sequence::reverse — Reverses the sequence in-place.
    * Ds\Sequence::reversed — Returns a reversed copy.
    * Ds\Sequence::rotate — Rotates the sequence by a given number of rotations.
    * Ds\Sequence::set — Updates a value at a given index.
    * Ds\Sequence::shift — Removes and returns the first value.
    * Ds\Sequence::slice — Returns a sub-sequence of a given range.
    * Ds\Sequence::sort — Sorts the sequence in-place.
    * Ds\Sequence::sorted — Returns a sorted copy.
    * Ds\Sequence::sum — Returns the sum of all values in the sequence.
    * Ds\Sequence::unshift — Adds values to the front of the sequence.

1. Vector — The Vector class
    * Ds\Vector::allocate — Allocates enough memory for a required capacity.
    * Ds\Vector::apply — Updates all values by applying a callback function to each value.
    * Ds\Vector::capacity — Returns the current capacity.
    * Ds\Vector::clear — Removes all values.
    * Ds\Vector::__construct — Creates a new instance.
    * Ds\Vector::contains — Determines if the vector contains given values.
    * Ds\Vector::copy — Returns a shallow copy of the vector.
    * Ds\Vector::count — Returns the number of values in the collection.
    * Ds\Vector::filter — Creates a new vector using a callable to determine which values to include.
    * Ds\Vector::find — Attempts to find a value's index.
    * Ds\Vector::first — Returns the first value in the vector.
    * Ds\Vector::get — Returns the value at a given index.
    * Ds\Vector::insert — Inserts values at a given index.
    * Ds\Vector::isEmpty — Returns whether the vector is empty
    * Ds\Vector::join — Joins all values together as a string.
    * Ds\Vector::jsonSerialize — Returns a representation that can be converted to JSON.
    * Ds\Vector::last — Returns the last value.
    * Ds\Vector::map — Returns the result of applying a callback to each value.
    * Ds\Vector::merge — Returns the result of adding all given values to the vector.
    * Ds\Vector::pop — Removes and returns the last value.
    * Ds\Vector::push — Adds values to the end of the vector.
    * Ds\Vector::reduce — Reduces the vector to a single value using a callback function.
    * Ds\Vector::remove — Removes and returns a value by index.
    * Ds\Vector::reverse — Reverses the vector in-place.
    * Ds\Vector::reversed — Returns a reversed copy.
    * Ds\Vector::rotate — Rotates the vector by a given number of rotations.
    * Ds\Vector::set — Updates a value at a given index.
    * Ds\Vector::shift — Removes and returns the first value.
    * Ds\Vector::slice — Returns a sub-vector of a given range.
    * Ds\Vector::sort — Sorts the vector in-place.
    * Ds\Vector::sorted — Returns a sorted copy.
    * Ds\Vector::sum — Returns the sum of all values in the vector.
    * Ds\Vector::toArray — Converts the vector to an array.
    * Ds\Vector::unshift — Adds values to the front of the vector.

1. Deque — The Deque class
    * Ds\Deque::allocate — Allocates enough memory for a required capacity.
    * Ds\Deque::apply — Updates all values by applying a callback function to each value.
    * Ds\Deque::capacity — Returns the current capacity.
    * Ds\Deque::clear — Removes all values from the deque.
    * Ds\Deque::__construct — Creates a new instance.
    * Ds\Deque::contains — Determines if the deque contains given values.
    * Ds\Deque::copy — Returns a shallow copy of the deque.
    * Ds\Deque::count — Returns the number of values in the collection.
    * Ds\Deque::filter — Creates a new deque using a callable to determine which values to include.
    * Ds\Deque::find — Attempts to find a value's index.
    * Ds\Deque::first — Returns the first value in the deque.
    * Ds\Deque::get — Returns the value at a given index.
    * Ds\Deque::insert — Inserts values at a given index.
    * Ds\Deque::isEmpty — Returns whether the deque is empty
    * Ds\Deque::join — Joins all values together as a string.
    * Ds\Deque::jsonSerialize — Returns a representation that can be converted to JSON.
    * Ds\Deque::last — Returns the last value.
    * Ds\Deque::map — Returns the result of applying a callback to each value.
    * Ds\Deque::merge — Returns the result of adding all given values to the deque.
    * Ds\Deque::pop — Removes and returns the last value.
    * Ds\Deque::push — Adds values to the end of the deque.
    * Ds\Deque::reduce — Reduces the deque to a single value using a callback function.
    * Ds\Deque::remove — Removes and returns a value by index.
    * Ds\Deque::reverse — Reverses the deque in-place.
    * Ds\Deque::reversed — Returns a reversed copy.
    * Ds\Deque::rotate — Rotates the deque by a given number of rotations.
    * Ds\Deque::set — Updates a value at a given index.
    * Ds\Deque::shift — Removes and returns the first value.
    * Ds\Deque::slice — Returns a sub-deque of a given range.
    * Ds\Deque::sort — Sorts the deque in-place.
    * Ds\Deque::sorted — Returns a sorted copy.
    * Ds\Deque::sum — Returns the sum of all values in the deque.
    * Ds\Deque::toArray — Converts the deque to an array.
    * Ds\Deque::unshift — Adds values to the front of the deque.

1. Map — The Map class
    * Ds\Map::allocate — Allocates enough memory for a required capacity.
    * Ds\Map::apply — Updates all values by applying a callback function to each value.
    * Ds\Map::capacity — Returns the current capacity.
    * Ds\Map::clear — Removes all values.
    * Ds\Map::__construct — Creates a new instance.
    * Ds\Map::copy — Returns a shallow copy of the map.
    * Ds\Map::count — Returns the number of values in the map.
    * Ds\Map::diff — Creates a new map using keys that aren't in another map.
    * Ds\Map::filter — Creates a new map using a callable to determine which pairs to include.
    * Ds\Map::first — Returns the first pair in the map.
    * Ds\Map::get — Returns the value for a given key.
    * Ds\Map::hasKey — Determines whether the map contains a given key.
    * Ds\Map::hasValue — Determines whether the map contains a given value.
    * Ds\Map::intersect — Creates a new map by intersecting keys with another map.
    * Ds\Map::isEmpty — Returns whether the map is empty
    * Ds\Map::jsonSerialize — Returns a representation that can be converted to JSON.
    * Ds\Map::keys — Returns a set of the map's keys.
    * Ds\Map::ksort — Sorts the map in-place by key.
    * Ds\Map::ksorted — Returns a copy, sorted by key.
    * Ds\Map::last — Returns the last pair of the map.
    * Ds\Map::map — Returns the result of applying a callback to each value.
    * Ds\Map::merge — Returns the result of adding all given associations.
    * Ds\Map::pairs — Returns a sequence containing all the pairs of the map.
    * Ds\Map::put — Associates a key with a value.
    * Ds\Map::putAll — Associates all key-value pairs of a traversable object or array.
    * Ds\Map::reduce — Reduces the map to a single value using a callback function.
    * Ds\Map::remove — Removes and returns a value by key.
    * Ds\Map::reverse — Reverses the map in-place.
    * Ds\Map::reversed — Returns a reversed copy.
    * Ds\Map::skip — Returns the pair at a given positional index.
    * Ds\Map::slice — Returns a subset of the map defined by a starting index and length.
    * Ds\Map::sort — Sorts the map in-place by value.
    * Ds\Map::sorted — Returns a copy, sorted by value.
    * Ds\Map::sum — Returns the sum of all values in the map.
    * Ds\Map::toArray — Converts the map to an array.
    * Ds\Map::union — Creates a new map using values from the current instance and another map.
    * Ds\Map::values — Returns a sequence of the map's values.
    * Ds\Map::xor — Creates a new map using keys of either the current instance or of another map, but not of both.

1. Pair — The Pair class
    * Ds\Pair::clear — Removes all values.
    * Ds\Pair::__construct — Creates a new instance.
    * Ds\Pair::copy — Returns a shallow copy of the pair.
    * Ds\Pair::isEmpty — Returns whether the pair is empty
    * Ds\Pair::jsonSerialize — Returns a representation that can be converted to JSON.
    * Ds\Pair::toArray — Converts the pair to an array.

1. Set — The Set class
    * Ds\Set::add — Adds values to the set.
    * Ds\Set::allocate — Allocates enough memory for a required capacity.
    * Ds\Set::capacity — Returns the current capacity.
    * Ds\Set::clear — Removes all values.
    * Ds\Set::__construct — Creates a new instance.
    * Ds\Set::contains — Determines if the set contains all values.
    * Ds\Set::copy — Returns a shallow copy of the set.
    * Ds\Set::count — Returns the number of values in the set.
    * Ds\Set::diff — Creates a new set using values that aren't in another set.
    * Ds\Set::filter — Creates a new set using a callable to determine which values to include.
    * Ds\Set::first — Returns the first value in the set.
    * Ds\Set::get — Returns the value at a given index.
    * Ds\Set::intersect — Creates a new set by intersecting values with another set.
    * Ds\Set::isEmpty — Returns whether the set is empty
    * Ds\Set::join — Joins all values together as a string.
    * Ds\Set::jsonSerialize — Returns a representation that can be converted to JSON.
    * Ds\Set::last — Returns the last value in the set.
    * Ds\Set::merge — Returns the result of adding all given values to the set.
    * Ds\Set::reduce — Reduces the set to a single value using a callback function.
    * Ds\Set::remove — Removes all given values from the set.
    * Ds\Set::reverse — Reverses the set in-place.
    * Ds\Set::reversed — Returns a reversed copy.
    * Ds\Set::slice — Returns a sub-set of a given range.
    * Ds\Set::sort — Sorts the set in-place.
    * Ds\Set::sorted — Returns a sorted copy.
    * Ds\Set::sum — Returns the sum of all values in the set.
    * Ds\Set::toArray — Converts the set to an array.
    * Ds\Set::union — Creates a new set using values from the current instance and another set.
    * Ds\Set::xor — Creates a new set using values in either the current instance or in another set, but not in both.

1. Stack — The Stack class
    * Ds\Stack::allocate — Allocates enough memory for a required capacity.
    * Ds\Stack::capacity — Returns the current capacity.
    * Ds\Stack::clear — Removes all values.
    * Ds\Stack::__construct — Creates a new instance.
    * Ds\Stack::copy — Returns a shallow copy of the stack.
    * Ds\Stack::count — Returns the number of values in the stack.
    * Ds\Stack::isEmpty — Returns whether the stack is empty
    * Ds\Stack::jsonSerialize — Returns a representation that can be converted to JSON.
    * Ds\Stack::peek — Returns the value at the top of the stack.
    * Ds\Stack::pop — Removes and returns the value at the top of the stack.
    * Ds\Stack::push — Pushes values onto the stack.
    * Ds\Stack::toArray — Converts the stack to an array.

1. Queue — The Queue class
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

1. PriorityQueue — The PriorityQueue class
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



