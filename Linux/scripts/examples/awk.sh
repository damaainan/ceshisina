awk '/^Service/{++i}{print >"file"i}' file
# 按标识分割文件


awk '/^<\?php/{++i}{print >"01-functions.php"i}' 01-functions.php

awk '/^<\?php/{++i}{print i"*"$0}' 01-functions.php


将 php\d 后缀的文件取出即可