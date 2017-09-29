# [MySQL 分区建索引][0]

# 介绍 

mysql分区后每个分区成了独立的文件，虽然从逻辑上还是一张表其实已经分成了多张独立的表，从“information_schema.INNODB_SYS_TABLES”系统表可以看到每个分区都存在独立的TABLE_ID,由于Innodb数据和索引都是保存在".ibd" 文件当中（从INNODB_SYS_INDEXES系统表中也可以得到每个索引都是对应各自的分区(primary key和unique也不例外）），所以分区表的索引也是随着各个分区单独存储。

在INNODB_SYS_INDEXES系统表中type代表索引的类型;  
0:一般的索引,  
1:(GEN_CLUST_INDEX)不存在主键索引的表,会自动生成一个6个字节的标示值，  
2:unique索引,  
3 :primary索引;  
所以当我们在分区表中创建索引时其实也是在每个分区中创建索引，每个分区维护各自的索引（其实也就是local index）；对于一般的索引(非主键或者唯一)没什么问题由于索引树中只保留了索引key和主键key(如果存在主键则是主键的key否则就是系统自动生成的6个的key)不受分区的影响；但是如果表中存在主键就不一样了，虽然在每个分区文件中都存在主键索引但是主键索引需要保证全局的唯一性就是所有分区中的主键的值都必须唯一（唯一键也是一样的道理），所以在创建分区时如果表中存在主键或者唯一键那么分区列必须包含主键或者唯一键的部分或者全部列（全部列还好理解，部分列也可以个人猜测是为了各个分区和主键建立关系），由于需要保证全局性又要保证插入数据更新数据到具体的分区所以就需要将分区和主键建立关系,由于通过一般的索引进行查找其它非索引字段需要通过主键如果主键不能保证全局唯一性的话那么就需要去每个分区查找了，这样性能可想而知。

To enforce the uniqueness we only allow mapping of each unique/primary key value to one partition.If we removed this limitation it would mean that for every insert/update we need to check in every partition to verify that it is unique. Also PK-only lookups would need to look into every partition.

**索引方式：**

性能依次降低

**1.主键分区**

主键分区即字段是主键同时也是分区字段，性能最好

**2. 部分主键+分区索引**

使用组合主键里面的部分字段作为分区字段，同时将分区字段建索引

**3.分区索引**

没有主键，只有分区字段且分区字段建索引

**4.分区+分区字段没有索引**

只建了分区，但是分区字段没有建索引

**参考：**



# **总结** 

因为每一个表都需要有主键这样可以减少很多锁的问题，由于上面讲过主键需要解决全局唯一性并且在插入和更新时可以不需要去扫描全部分区，造成主键和分区列必须存在关系；所以最好的分区效果是使用主键作为分区字段其次是使用部分主键作为分区字段且创建分区字段的索引，其它分区方式都建议不采取。

[0]: http://www.cnblogs.com/chenmh/p/5761995.html
