#批量转换文件编码 必须保证文件编码正确，没有目标类型

for i in `find ./  -type f -name '*.java'` ;
do
        echo $i
        echo ${i}.tmp
        iconv -f cp936 -t utf-8  $i>${i}.tmp
        mv ${i}.tmp $i;
done