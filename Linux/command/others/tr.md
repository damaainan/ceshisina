tr命令是从标准输入中替换、缩减或删除字符，并将结果写到标准输出，当然可以通过重定向来改变输入输出

用法:tr [选项]... SET1 [SET2]

`-c`, `-C` 将输入中不在SET1中的替换成set2 中的

如
    
    echo "abc" | tr -c "a" "A" #将abc中的不是a的字符替换成A ,不带选项-c时表示将a 替换成A

输出为:aAAA 

`-d`将输入中包含set1的字符删除

如

    echo "abc" | tr -d "a" #将a字符删除

输出为:bc

`-s`表示浓缩重复的字符

如

    echo "aabb" | tr -s "ab" #去重a和去重b

输出为:ab

`-t`表示先将SET1的长度截取为与set2相等

如

    echo "abc" | tr -t "ab" "o" #实际上就变成tr "a" "o"

输出为:obc ，在不加-t选项时，输出为ooc

