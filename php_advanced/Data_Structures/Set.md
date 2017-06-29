
### 简介

A Set is a sequence of unique values. This implementation uses the same hash table as Ds\Map, where values are used as keys and the mapped value is ignored. 


#### Strengths


• Values can be any type, including objects.  
• Supports array syntax (square brackets).  
• Insertion order is preserved.  
• Automatically frees allocated memory when its size drops low enough.  
• add(), remove() and contains() are all O(1).   



#### Weaknesses


• Doesn't support push(), pop(), insert(), shift(), or unshift().   
• get() is O(n) if there are deleted values in the buffer before the accessed index, O(1) otherwise.   


### Set — The Set class

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