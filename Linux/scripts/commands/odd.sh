#!/bin/bash
# echo $IFS

MY_SAVEIFS=$IFS  # 改变分隔符
# IFS=$(echo -en "\n\b")  
IFS=$'\n'  
# echo $IFS
for i in `ls *.txt`
do
    # echo $i
    name=$i
    echo $name
    awk '/```/{i++;if(i%2==1)print NR}' "${name}" | xargs -I[ sed -i '[s@```@```php@' "${name}"
done

IFS=$MY_SAVEIFS  
# echo $IFS