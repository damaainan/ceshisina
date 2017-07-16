## 1. 查找字符位置函数

strpos($str,search,[int]): 查找search在$str中的第一次位置从int开始

stripos($str,search,[int]): 函数返回字符串在另一个字符串中第一次出现的位置

strrpos($str,search,[int]): 查找search在$str中的最后一次出现的位置从int

## 2. 提取子字符函数（双字节）

submit($str,int start[,int length]): 从$str中strat位置开始提取[length长度的字符串]。

strstr($str1,$str2): 从$str1(第一个的位置)搜索$str2并从它开始截取到结束字符串;若没有则返回FALSE。

stristr() 功能同strstr，只是不区分大小写。

strrchr() 从最后一次搜索到的字符处返回；用处：取路径中文件名

## 3. 替换字符串的PHP字符串函数

str_replace(search,replace,$str): 从$str中查找search用replace来替换

str_irreplace(search,replace,$str):

strtr($str,search,replace): 这个函数中replace不能为”";

substr_replace($Str,$rep,$start[,length]) $str原始字符串,$rep替换后的新字符串,$start起始位置,$length替换的长度，该项可选

## 4. 字符长度

int strlen($str)

## 5. 比较字符函数

int strcmp($str1,$str2): $str1>=<$str2分别为正1,0,-1（字符串比较）

strcasecmp() 同上（不分大小写）

strnatcmp(“4″,”14″) 按自然排序比较字符串

strnatcasecmp() 同上，（区分大小写）

## 6. 分割成数组的PHP字符串函数

str_split($str,len): 把$str按len长度进行分割返回数组

split(search,$str[,int]): 把$str按search字符进行分割返回数组int是分割几次，后面的将不分割

expload(search,$str[,int])

## 7. 去除空格

ltrim、rtrim、trim

## 8. HTML代码有关函数

nl2br()： 使n转换为  
  
strip_tags($str[,''])： 去除HTML和PHP标记.在$str中所有HTML和PHP代码将被去除，可选参数为html和PHP代码作用是将保留

如：echo strip_tags($text, ”);

htmlspecialchars($str[,参数]):页面正常输出HTML代码参数是转换方式

## 9. 字符大小写转换的PHP字符串函数

strtolower($str): 字符串转换为小写

strtoupper($str): 字符串转换为大写

ucfirst($str): 将函数的第一个字符转换为大写

ucwords($str): 将每个单词的首字母转换为大写

## 附：

PHP字符串函数库，收集了51个PHP处理字符串的函数。包括计算字符串长度、分割字符串、查找字符串等等各个方面。

AddSlashes: 字符串加入斜线。  
bin2hex: 二进位转成十六进位。  
Chop: 去除连续空白。  
Chr: 返回序数值的字符。  
chunk_split: 将字符串分成小段。  
convert_cyr_string: 转换成其它字符串。  
crypt: 将字符串用 DES 编码加密。  
echo: 输出字符串。  
explode: 切开字符串。  
flush: 清出输出缓冲区。  
get_meta_tags: 抽出文件所有 meta 标记的资料。  
htmlspecialchars: 将特殊字符转成 HTML 格式。  
htmlentities: 将所有的字符都转成 HTML 字符串。  
implode: 将数组变成字符串。  
join: 将数组变成字符串。  
ltrim: 去除连续空白。  
md5: 计算字符串的 MD5 哈稀。  
nl2br: 将换行字符转成。  
Ord: 返回字符的序数值。  
parse_str: 解析 query 字符串成变量。  
print: 输出字符串。  
printf: 输出格式化字符串。  
quoted_printable_decode 将 qp 编码字符串转成 8 位字符串。  
QuoteMeta: 加入引用符号。  
rawurldecode: 从 URL 专用格式字符串还原成普通字符串。  
rawurlencode: 将字符串编码成 URL 专用格式。  
setlocale: 配置地域化信息。  
similar_text: 计算字符串相似度。  
soundex: 计算字符串的读音值  
sprintf: 将字符串格式化。  
strchr: 寻找第一个出现的字符。  
strcmp: 字符串比较。  
strcspn: 不同字符串的长度。  
strip_tags: 去掉 HTML 及 PHP 的标记。  
StripSlashes: 去掉反斜线字符。  
strlen: 取得字符串长度。  
strrpos: 寻找字符串中某字符最后出现处。  
strpos: 寻找字符串中某字符最先出现处。  
strrchr: 取得某字符最后出现处起的字符串。  
strrev: 颠倒字符串。  
strspn: 找出某字符串落在另一字符串遮罩的数目。  
strstr: 返回字符串中某字符串开始处至结束的字符串。  
strtok: 切开字符串。  
strtolower: 字符串全转为小写。  
strtoupper: 字符串全转为大写。  
str_replace: 字符串取代。  
strtr: 转换某些字符。  
substr: 取部份字符串。  
trim: 截去字符串首尾的空格。  
ucfirst: 将字符串第一个字符改大写。  
ucwords: 将字符串每个字第一个字母改大写。

