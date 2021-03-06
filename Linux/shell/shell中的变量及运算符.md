 **变量赋值与环境**

语法

     export name[=word]

     export –p

     readonly name[=word]

     readonly –p

`export` 用于修改或打印环境变量， readonly 使得变量不能修改

`export` 命令仅将变量添加到环境中，如果要从程序的环境中删除变量，要用 env 命令

`env` 的选项有

     -i: 表示用来初始化环境变量，即丢弃任何的继承值，仅传递命令行上指定的变量给程序使用。

     -u varname: 表示删除 varname 环境变量

     -0( 是 zero): 表示输出行是以 NUL 结尾，而不是换行

`unset` 命令从执行中的 shell 中删除变量与函数

`unset –f function` 用来删除函数

`unset [-v] variable`: 不加 `-v` 表示删除 `variable`, 加 `-v` 表示删除除 `variable` 的变量

**替换运算符**

`${varname:-word}`: 如果 varname 存在且非 null, 则返回其值，否则返回 word ，用途：如果变量未定义，则返回默认值

`${varname:=word}`: 如果 varname 存在且非 null ，则返回其值，否则设置它为 word ，并返回其值，用途是如果变量未定义，设置变量为默认值

`${varname:?word}`: 如果 varname 存在且非 null ，则返回它的值，否则，显示 varname:message ，并退出当前的命令或脚本。用途是为了捕捉由于变量未定义所导致的错误

`${varname:+word}`: 如果 varname 存在且非 null, 则返回 word, 否则，返回 null, 用途是测试变量的存在

上述每个运算符内的冒号都是可选的。如果省略冒号，则将每个定义中的存在且非 null 部分改成存在，也就是说，运算符仅用于测试变量是否存在。

**模式匹配运算符**

`${variable#pattern}`: 如果模式匹配于变量值的开头处，则删除匹配的最短部分，并返回剩下的部分

`${variable##pattern}`: 如果模式匹配于变量值的开头处，则删除匹配的最长部分，并返回剩下的部分

`${variable%pattern}`: 如果模式匹配变量值的结尾处，则删除匹配的最短部分，并返回剩下的部分

`${variable%%pattern}`: 如果模式匹配变量值的结尾处，则删除匹配的最长部分，并返回剩下的部分

**字符串长度运算符**

`${#variable}` 返回 `$variable` 值里的字符长度

**位置参数**

位置参数指的是 shell 脚本的命令行参数，同时也表示在 shell 函数内的函数参数。它们的名称以单个的整数来命名，当这个整数大于 9 时，就应该以花括号 {} 括起来。

`$#`: 表示传递到 shell 脚本或函数的参数总数

`$*,$@`: 表示所有的命令行参数，这两个参数可用来把命令行参数传递给脚本或函数所执行的程序

`"$*"`: 表示将所有的命令行参数视为单个字符串。等同于 "$1 $2…"

`"$@"`: 表示将所有命令行参数视为单独的个体，也就是单独字符串。等同于 "$1" "$2" …

`shift` 命令用来截去来自列表的位置参数，由左开始，一旦执行 `shift`,$1 的初始值会永远消失，取而代之的是 $2 的旧值， $2 的值变为 $3 的值，以此类推

