数据库相关 [https://segmentfault.com/blog/nixi8?page=1](https://segmentfault.com/blog/nixi8?page=1)


`awk -F': ' '/^\[[0-9]{1,2}\]:[ ]http/{print $2}'`


`awk -F': ' '/^\[[0-9]{1,2}\]:[ ]http/{print $2}' segmentfault*.md | grep -E "png|jpeg|jpg|gif" > 1.txt`


awk 解析 `(` `)`  报错 部分更名未解决

`ls segmentfault119000000*.md | xargs -I[  awk 'NR==1{print $2"**["}' [ | awk -F'**' '{system("mv "$2" "$1)}'`