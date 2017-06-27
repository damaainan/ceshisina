Collection is the base interface which covers functionality common to all the data structures in this library. It guarantees that all structures are traversable, countable, and can be converted to json using `json_encode()`. 

不能在命令行使用？需要修改 php-cgi.ini ，添加该扩展

一共有四个方法：  
•Ds\Collection::clear — Removes all values.  
•Ds\Collection::copy — Returns a shallow copy of the collection.  
•Ds\Collection::isEmpty — Returns whether the collection is empty  
•Ds\Collection::toArray — Converts the collection to an array.  


