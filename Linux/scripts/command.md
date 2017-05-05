windows下打印目录树 

     tree /f

Linux 下目录 
tree --help
其中常用参数：
-a：打印全部文件。
-A：使用ASNI绘图字符显示树状图而非以ASCII字符组合（显示的树状图为实线不是虚线）
-C：在文件和目录清单加上色彩，便于区分各种类型。
-d：只打印目录。
-L level：指定打印目录的深度（层级）。
-f：打印出每个文件、目录的绝对路径。

---

删除行尾的^M：

    %s/\r//g


---

统计符合要求的字符串的长度

    awk -F': ' '/images/{print $2}' *.md | awk '{print length($0)}'