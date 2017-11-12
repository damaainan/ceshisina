# JSHint 配置浅析 

 发表于 2014-10-25   

[JSHint][1]，发现错误和潜在问题的社区驱动的工具  
[JSLint 错误解析][2]

## 单独安装

    $ npm install jshint -g

    
    $ jshint myfile.js

    myfile.js: line 10, col 39, Octal literals are not allowed in strict mode.

    1 error

## 编辑器和IDE插件

[Sublime-JSHint][3]

### 安装

Ctrl+Shift+P 或 Cmd+Shift+P  
输入 install，选择 Package Control: Install Package  
输入 js gutter，选择 JSHint Gutter

### 使用方法：

Tools -> Command Palette (Ctrl+Shift+P 或者 Cmd+Shift+P) 然后输入 jshint– 或者 –

Ctrl+Shift+J (或者 Mac 使用 Cmd+Shift+J)

– 或者 –

当前文件右键选择 JSHint -> Lint Code

– 或者 –

打开 JavaScript 文件，菜单 View -> Show Console，然后输入 view.run_command("jshint")

### 编辑，加载或保存时自动检查

右键 -> JSHint -> Set Plugin Options

三项设置为 true

   

    {
    
        "lint_on_edit": true,
    
        "lint_on_load": true,
    
        "lint_on_save": true
    
    }

## 三种配置方式：

通过 --config 标记手动配置

使用 **.jshintrc** 文件

配置放到项目的 **package.json** 文件里面， **jshintConfig** 下面

[自定义 **.jshintrc** 配置文件][4]

## JSHint 设置

### 强制选项

禁用位运算符，位运算符在 JavaScript 中使用较少，经常是把 && 错输成 &

    bitwise: true

循环或者条件语句必须使用花括号包围

    
    curly: true

强制使用三等号

    eqeqeq: true

兼容低级浏览器 IE 6/7/8/9

    es3: true

禁止重写原生对象的原型，比如 Array，Date

    freeze: true

代码缩进

    indent: true

禁止定义之前使用变量，忽略 function 函数声明

    latedef: "nofunc"

构造器函数首字母大写

    newcap: true

禁止使用 arguments.caller 和 arguments.callee，未来会被弃用， ECMAScript 5 禁止使用 arguments.callee

    noarg:true

为 true 时，禁止单引号和双引号混用

    
    "quotmark": false

变量未定义

    "undef": true

变量未使用

    "unused": true

严格模式

    
    strict:true

最多参数个数

    maxparams: 4

最大嵌套深度

    maxdepth: 4

复杂度检测

    maxcomplexity:true

最大行数

    maxlen: 600

### 宽松选项

控制“缺少分号”的警告

    "asi": true

    "boss": true

忽略 debugger

    "debug": true

控制 eval 使用警告

    "evil": true

检查一行代码最后声明后面的分号是否遗漏

    "lastsemic": true

检查不安全的折行，忽略逗号在最前面的编程风格

    "laxcomma": true

检查循环内嵌套 function

    "loopfunc": true

检查多行字符串

    "multistr": true

检查无效的 typeof 操作符值

    "notypeof": true

person['name'] vs. person.name

    "sub": true

new function () { ... } 和 new Object;

    "supernew": true

在非构造器函数中使用 this

    "validthis": true

### 环境

预定义一些全局变量

预定义全局变量 document，navigator，FileReader 等

    "browser": true

定义用于调试的全局变量：console，alert

    "devel": true

定义全局变量

    "jquery": true,

    "node": true


[1]: http://www.jshint.com/
[2]: http://jslinterrors.com/
[3]: https://github.com/victorporof/Sublime-JSHint
[4]: http://www.jshint.com/docs/options/