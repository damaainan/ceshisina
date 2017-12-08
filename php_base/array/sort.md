## 排序函数属性


函数名称 | 排序依据 | 数组索引键保持 | 排序的顺序 | 相关函数
- | - | - | - | -
array_multisort() | 值 | 键值关联的保持，数字类型的不保持 | 第一个数组或者由选项指定 | array_walk() 
asort() | 值 | 是 | 由低到高 | arsort() 
arsort() | 值 | 是 | 由高到低 | asort() 
krsort() | 键 | 是 | 由高到低 | ksort() 
ksort() | 键 | 是 | 由低到高 | asort() 
natcasesort() | 值 | 是 | 自然排序，大小写不敏感 | natsort() 
natsort() | 值 | 是 | 自然排序 | natcasesort() 
rsort() | 值 | 否 | 由高到低 | sort() 
shuffle() | 值 | 否 | 随机 | array_rand() 
sort() | 值 | 否 | 由高到低 | rsort() 
uasort() | 值 | 是 | 由用户定义 | uksort() 
uksort() | 键 | 是 | 由用户定义 | uasort() 
usort() | 值 | 否 | 由用户定义 | uasort() 
