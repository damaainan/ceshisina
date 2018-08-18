<?php

# AKA ArrayList

# Vectors are much like arrays. Operations on a vector offer the same big O as their counterparts on an array.
# Unlike arrays, which are always of a fixed size, vectors can be grown
# This can be done either explicitly or by adding more data. In order to do this efficiently, the typical vector implementation 
# grows by doubling its allocated space (rather than incrementing it) and often has more space allocated to it at any one time 
# than it needs. This is because reallocating memory can sometimes be an expensive operation.
# 
# A data structure that stores items of the same type, and is based on storage in an array
# 
# By encapsulating an array into a class (a vector class), we can
# - use dynamic allocation to allow the internal array to be flexible in size
# - handle boundary issues of the array (error checking for out-of-bounds indices).
#
# Disadvantages:  Inserts and Deletes are typically slow, since they may require shifting many elements to consecutive array slots
#
# When we're extending the Vector size, we create a new array and copy all elements over to it
# extentions happen with a frequency of power of 2
