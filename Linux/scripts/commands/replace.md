#### CSDN 替换行号

    sed -i 's/^\*[ ][0-9]\{1,2\}//' *.md

删除包含行号的行

    sed -i '/^\*[ ][0-9]\{1,2\}/d' *.md

下载图片

    awk -F': ' '/img.blog/{print $2}' *.md | awk -F'/' '{system("aria2c -o "$NF".png "$0)}'

添加图片后缀

     awk -F': ' '/img.blog/{print $2}' *.md | awk -F'/' '{print $NF}' | xargs -i[  sed -i 's@[@[.png@' *.md

替换前缀地址

     sed -i 's@http://img.blog.csdn.net@../img@' *.md