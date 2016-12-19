<?php
/**
 * LRU Cache 缓存器


Design and implement a data structure for Least Recently Used (LRU) cache. It should support the following operations: get and set.

get(key) - Get the value (will always be positive) of the key if the key exists in the cache, otherwise return -1.
set(key, value) - Set or insert the value if the key is not already present. When the cache reached its capacity, it should invalidate the least recently used item before inserting a new item.



这道题让我们实现一个LRU缓存器，LRU是Least Recently Used的简写，就是最近最少使用的意思。那么这个缓存器主要有两个成员函数，get和set，其中get函数是通过输入key来获得value，如果成功获得后，这对(key, value)升至缓存器中最常用的位置（顶部），如果key不存在，则返回-1。而set函数是插入一对新的(key, value)，如果原缓存器中有该key，则需要先删除掉原有的，将新的插入到缓存器的顶部。如果不存在，则直接插入到顶部。若加入新的值后缓存器超过了容量，则需要删掉一个最不常用的值，也就是底部的值。具体实现时我们需要三个私有变量，cap, l和m，其中cap是缓存器的容量大小，l是保存缓存器内容的列表，m是哈希表，保存关键值key和缓存器各项的迭代器之间映射，方便我们以O(1)的时间内找到目标项。

然后我们再来看get和set如何实现，get相对简单些，我们在m中查找给定的key，如果存在则将此项移到顶部，并返回value，若不存在返回-1。对于set，我们也是现在m中查找给定的key，如果存在就删掉原有项，并在顶部插入新来项，然后判断是否溢出，若溢出则删掉底部项(最不常用项)。
 */