#!/bin/bash

# 使用 curl 下载需要制定来源的图片

for i in `awk -F': ' '/kaimingwan/{print $2}' urls.txt`
do
    name=`echo ${i} | awk -F'/' '{print $NF}'`
    n1=`echo ${i} | awk -F'/' '{print $(NF-1)}'`
    n3=`echo ${i} | awk -F'/' '{print $(NF-3)}'`
    n4=`echo ${i} | awk -F'/' '{print $(NF-4)}'`
    
    n1=`echo ${n1} | tr -d '\n' | od -An -tx1 | tr ' ' %`  # 结果为小写 转为大写即可
    n3=`echo ${n3} | tr -d '\n' | od -An -tx1 | tr ' ' %`
    n4=`echo ${n4} | tr -d '\n' | od -An -tx1 | tr ' ' %`

    url="http://www.kaimingwan.com/%E5%9F%BA%E7%A1%80%E7%9F%A5%E8%AF%86/%E7%BD%91%E7%BB%9C/_image/%E4%BD%BF%E7%94%A8tshark%E5%9C%A8%E5%91%BD%E4%BB%A4%E8%A1%8C%E8%BF%9B%E8%A1%8C%E7%BD%91%E7%BB%9C%E6%8A%93%E5%8C%85/"${name}
    # echo ${url}
    # echo ${i}
    # echo ${name}
    curl -e http://www.kaimingwan.com/post/ji-chu-zhi-shi/wang-luo/shi-yong-tsharkzai-ming-ling-xing-jin-xing-wang-luo-zhua-bao -A "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3251.0 Safari/537.36" ${url} -o ${name}
done

# curl -e http://www.kaimingwan.com/post/ji-chu-zhi-shi/wang-luo/shi-yong-tsharkzai-ming-ling-xing-jin-xing-wang-luo-zhua-bao -A'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3251.0 Safari/537.36' http://www.kaimingwan.com/基础知识/网络/_image/使用tshark在命令行进行网络抓包/15-03-15.jpg -o 15-03-15.jpg
# 
# http://www.kaimingwan.com/%e5%9f%ba%e7%a1%80%e7%9f%a5%e8%af%86%e7%bd%91%e7%bb%9c/_image/%e4%bd%bf%e7%94%a8%74%73%68%61%72%6b%e5%9c%a8%e5%91%bd%e4%bb%a4%e8%a1%8c%e8%bf%9b%e8%a1%8c%e7%bd %91%e7%bb%9c%e6%8a%93%e5%8c%85
# 
# 
# http://www.kaimingwan.com/%E5%9F%BA%E7%A1%80%E7%9F%A5%E8%A F%86/%E7%BD%91%E7%BB%9C/_image/%E4%BD%BF%E7%94%A8tshark%E5%9C%A8%E5%91%BD%E4%BB%A4%E8%A1%8C%E8%BF%9B%E8%A1%8C%E7%BD%91%E7%BB%9C%E6%8A%93%E5%8C%85/15- 03-29.jpg
# 
# 
# http://www.kaimingwan.com/%E5%9F%BA%E7%A1%80%E7%9F%A5%E8%AF%86/%E7%BD%91%E7%BB%9C/_image/%E4%BD%BF%E7%94%A8tshark%E5%9C%A8%E5%91%BD%E4%BB%A4%E8%A1%8C%E8%BF%9B%E8%A1%8C%E7%BD%91%E7%BB%9C%E6%8A%93%E5%8C%85