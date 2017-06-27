## 1.Introduction to Data Structures and Algorithms
* #### Importance of data structures and algorithms
* #### Understanding Abstract Data Type (ADT)
* #### Different data structures
    * ##### Struct
    * ##### Array
    * ##### Linked list
    * ##### Doubly linked list
    * ##### Stack
    * ##### Queue
    * ##### Set
    * ##### Map
    * ##### Tree
    * ##### Graph
    * ##### Heap
* #### Solving a problem - algorithmic approach
* #### Writing pseudocode
    * ##### Converting pseudocode to actual code
* #### Algorithm analysis
    * ##### Calculating the complexity
* #### Understanding the big O (big oh) notation
* #### Standard PHP Library (SPL) and data structures
* #### Summary


## 2.Understanding PHP Arrays
* #### Understanding PHP arrays in a better way
    * ##### Numeric array
    * ##### Associative array
    * ##### Multidimensional array
* #### Using an array as flexible storage
* #### Use of multi-dimensional arrays to represent data structures
    * ##### Creating fixed size arrays with the SplFixedArray method
* #### Performance comparison between a regular PHP array and SplFixedArray
    * ##### More examples using SplFixedArray
        * ###### Changing from a PHP array to SplFixedArray
        * ###### Converting a SplFixedArray to a PHP array
        * ###### Changing the SplFixedArray size after declaration
        * ###### Creating a multidimensional array using SplFixedArray
* #### Understanding hash tables
* #### Implementing struct using a PHP array
* #### Implementing sets using a PHP array
* #### Best usage of a PHP array
* #### PHP array, is it a performance killer?
* #### Summary


## 3.Using Linked Lists
* #### What is a linked list?
* #### Different types of linked list
    * ##### Doubly linked lists
    * ##### Circular linked lists
    * ##### Multi-linked lists
* #### Inserting, deleting, and searching for an item
    * ##### Inserting at the first node
    * ##### Searching for a node
    * ##### Inserting before a specific node
    * ##### Inserting after a specific node
    * ##### Deleting the first node
    * ##### Deleting the last node
    * ##### Searching for and deleting a node
    * ##### Reversing a list
    * ##### Getting the Nth position element
* #### Understanding complexity for linked lists
* #### Making the linked list iterable
* #### Building circular linked list
* #### Implementing a doubly linked list in PHP
* #### Doubly linked list operations
    * ##### Inserting at first the node
    * ##### Inserting at the last node
    * ##### Inserting before a specific node
    * ##### Inserting after a specific node
    * ##### Deleting the first node
    * ##### Deleting the last node
    * ##### Searching for and deleting one node
    * ##### Displaying the list forward
    * ##### Displaying the list backward
* #### Complexity for doubly linked lists
* #### Using PHP SplDoublyLinkedList
* #### Summary

## 4.Constructing Stacks and Queues
* #### Understanding stack
* #### Implementing a stack using PHP array
* #### Understanding complexity of stack operations
* #### Implementing stack using linked list
* #### Using SplStack class from SPL
* #### Real life usage of stack
    * ##### Nested parentheses matching
* #### Understanding queue
    * ##### Implementing a queue using PHP array
    * ##### Implementing a queue using linked list
* #### Using SplQueue class from SPL
* #### Understanding priority queue
    * ##### Ordered sequence
    * ##### Unordered sequence
* #### Implementing priority queue using linked list
* #### Implement a priority queue using SplPriorityQueue
* #### Implementing a circular queue
* #### Creating a double - ended queue (deque)
* #### Summary


## 5.Applying Recursive Algorithms - Recursion
* #### Understanding recursion
    * ##### Properties of recursive algorithms
    * ##### Recursion versus iterative algorithms
    * ##### Implementing Fibonacci numbers using recursion
    * ##### Implementing GCD calculation using recursion
* #### Different types of recursions
    * ##### Linear recursion
    * ##### Binary recursion
    * ##### Tail recursion
    * ##### Mutual recursion
    * ##### Nested recursion
* #### Building an N-level category tree using recursion
    * ##### Building a nested comment reply system
* #### Finding files and directories using recursion
* #### Analyzing recursive algorithms
* #### Maximum recursion depth in PHP
* #### Using SPL recursive iterators
* #### Using the PHP built-in function array_walk_recursive
* #### Summary

## 6.Understanding and Implementing Trees
* #### Tree definition and properties
* #### Implementing a tree using PHP
* #### Different types of tree structures
    * ##### Binary tree
    * ##### Binary search tree
    * ##### Self-balanced binary search tree
        * ###### AVL tree
        * ###### Red-black tree
    * ##### B-tree
    * ##### N-ary Tree
* #### Understanding a binary tree
* #### Implementing a binary tree
* #### Creating a binary tree using a PHP array
* #### Understanding the binary search tree
    * ##### Inserting a new node
    * ##### Searching a node
    * ##### Finding the minimum value
    * ##### Finding the maximum value
    * ##### Deleting a node
* #### Constructing a binary search tree
* #### Tree traversal
    * ##### In-order
    * ##### Pre-order
    * ##### Post-order
* #### Complexity of different tree data structures
* #### Summary

## 7.Using Sorting Algorithms
* #### Understanding sorting and their types
* #### Understanding bubble sort
    * ##### Implementing bubble sort using PHP
    * ##### Complexity of bubble sort
    * ##### Improving bubble sort algorithm
* #### Understanding selection sort
    * ##### Implementing selection sort
    * ##### Complexity of selection sort
* #### Understanding insertion Sort
    * ##### Implementing insertion sort
    * ##### Complexity of insertion sort
* #### Understanding divide-and-conquer technique for sorting
* #### Understanding merge sort
    * ##### Implementing merge sort
    * ##### Complexity of merge sort
* #### Understanding quick sort
    * ##### Implementing quick sort
    * ##### Complexity of quick sort
* #### Understanding bucket sort
* #### Using PHP's built-in sorting function
* #### Summary


## 8.Exploring Search Options
* #### Linear searching
* #### Binary search
    * ##### Analysis of binary search algorithm
    * ##### Repetitive binary search tree algorithm
    * ##### Searching an unsorted array - should we sort first?
* #### Interpolation search
* #### Exponential search
* #### Search using hash table
* #### Tree searching
    * ##### Breadth first search
        * ###### Implementing breadth first search
    * ##### Depth first search
        * ###### Implementing depth first search
* #### Summary

## 9.Putting Graphs into Action
* #### Understanding graph properties
    * ##### Vertex
    * ##### Edge
    * ##### Adjacent
    * ##### Incident
    * ##### Indegree and outdegree
    * ##### Path
* #### Types of graphs
    * ##### Directed graphs
    * ##### Undirected graphs
    * ##### Weighted graphs
    * ##### Directed acyclic graphs (DAG)
    * ##### Representing graphs in PHP
        * ###### Adjacency lists
        * ###### Adjacency matrix
* #### Revisiting BFS and DFS for graphs
    * ##### Breadth first search
    * ##### Depth first search
* #### Topological sorting using Kahn's algorithm
* #### Shortest path using the Floyd-Warshall algorithm
* #### Single source shortest path using Dijkstra's algorithm
* #### Finding the shortest path using the Bellman-Ford algorithm
* #### Understanding the minimum spanning tree (MST)
* #### Implementing Prim's spanning tree algorithm
* #### Kruskal's algorithm for spanning tree
* #### Summary

## 10.Understanding and Using Heaps
* #### What is a heap?
* #### Heap operations
* #### Implementing a binary heap in PHP
* #### Analyzing the complexity of heap operations
* #### Using heaps as a priority queue
* #### Using heap sort
* #### Using SplHeap, SplMaxHeap, and SplMinHeap
* #### Summary


## 11.Solving Problems with Advanced Techniques
* #### Memoization
* #### Pattern matching algorithms
* #### Implementing Knuth-Morris-Pratt algorithm
* #### Greedy algorithms
* #### Implementing Huffman coding algorithm
* #### Understanding dynamic programming
* #### 0 - 1 knapsack
* #### Finding the longest common subsequence-LCS
* #### DNA sequencing using dynamic programming
* #### Backtracking to solve puzzle problem
* #### Collaborative filtering recommendation system
* #### Using bloom filters and sparse matrix
* #### Summary


## 12.PHP Built-In Support for Data Structures and Algorithms
* #### Built-in PHP features for data structure
    * ##### Using PHP array
* #### SPL classes
* #### Built-in PHP algorithms
* #### Hashing
* #### Built-in support through PECL
    * ##### Installation
        * ###### Interfaces
* #### Vector
* #### Summary


## 13.Functional Data Structures with PHP
* #### Understanding functional programming with PHP
    * ##### First class functions
    * ##### Higher order functions
    * ##### Pure functions
    * ##### Lambda functions
    * ##### Closures
    * ##### Currying
    * ##### Partial applications
* #### Getting started with Tarsana
* #### Implementing stack
* #### Implementing a queue
* #### Implementing a tree
* #### Summary