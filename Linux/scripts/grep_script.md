
过滤文件夹，删除文件夹中的某类文件

    ls | grep -E '[0-9]{4}' | xargs -I[ find [ -name "*-ruby.md" | xargs rm -f

删除

    ls | grep -E '2[0-9]{3}' | xargs -I[ find [ -name "*-ruby.md" -print0 | xargs -0 rm -f

`-print0 | xargs -0 rm -f` 为了删除带空格的文件名