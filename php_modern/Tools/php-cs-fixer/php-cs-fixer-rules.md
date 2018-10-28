# PHP 基本编码规范

**本规范参考 [PSR-1](http://www.php-fig.org/psr/psr-1/) 与 [PSR-2](http://www.php-fig.org/psr/psr-2/)，在此标准之上做了更严格的限制和扩充。**

本规范仅适用于 **PHP 5.4** 以上版本。

## 1.文件与行

- PHP 代码文件**必须**以 `<?php`；
- PHP 代码文件**必须**以 `不带 BOM 的 UTF-8` 编码；
- 代码**必须**使用4个空格符而不是 tab 键进行缩进；
- 所有 PHP 文件**必须**使用`Unix LF (linefeed)`作为行的结束符；
- 所有 PHP 文件**必须**以一个空白行作为结束；
- 纯 PHP 代码文件**必须**省略最后的 `?>` 结束标签；
- 视图文件中的 PHP 代码（模板代码除外）**必须**使用 `<?php ?>` 长标签 或 `<?= ?>` 短输出标签；**一定不可**使用其它自定义标签。
- 每行的字符数**应该**保持在80个之内， **不应该**多于120个；
- 非空行后**一定不能**有多余的空格符；
- 每行**一定不能**存在多于一条语句。

## 2.类、方法与属性

- PHP代码中**应该**只定义类、函数、常量等声明或只定义其他会产生 `从属效应` 的操作（如：生成文件输出、引入文件以及修改 ini 配置文件等），二者只能选其一；
- 命名空间以及类**必须**符合 PSR 的自动加载规范：PSR-4；
- 类的命名**必须**遵循 `StudlyCaps` 大写开头的驼峰命名规范；
- 类中的常量所有字母都**必须**大写，单词间用下划线分隔；
- 方法名称**必须**符合 `camelCase` 式的小写开头驼峰命名规范；
- `use`声明**必须**位于`namespace`声明之后， 每个 `namespace` 命名空间声明语句块和 `use` 声明语句块后面，**必须**插入一个空白行；
- 类与方法的开始花括号(`{`)**必须**写在函数声明后自成一行，结束花括号(`}`)也**必须**写在函数主体后自成一行；
- 关键词 `extends` 和 `implements`**必须**写在类名称的同一行；
- 需要添加 `abstract` 或 `final` 声明时， **必须**写在访问修饰符前，而 `static` 则**必须**写在其后；
- 类的属性和方法**必须**添加访问修饰符（`private`、`protected` 以及 `public`），**不要**使用下划线`_`作为前缀来区分属性是 protected 或 private；
- 每条语句**一定不能**定义超过一个属性，**一定不可**使用关键字 `var` 声明一个属性；
- 方法及函数的声明和调用时，方法名或函数名与参数左括号之间**一定不能**有空格，参数左括号后与右括号前也**一定不能**有空格，参数列表中每个逗号后面**必须**要有一个空格，而逗号前面**一定不能**有空格。

## 3.控制结构

- 控制结构的关键字后**必须**要有一个空格，右括号 `)` 与开始花括号 `{` 间也**一定**有一个空格；
- 控制结构的开始花括号(`{`)**必须**写在声明的同一行，而结束花括号(`}`)**必须**写在主体后自成一行；
- 控制结构转折（`else`、`else if`、`catch` 以及 `do ... while` 结构中的 `while`）关键字与上一结构体的结束花括号(`}`)**必须**写在同一行中，两者之间**一定**有一个空格；
- 条件括号左括号后与右括号前**一定不能**有空格，两个子句句之间（分号之后）一定有一个空格；
- 每个结构体的主体都**必须**被包含在成对的花括号之中。

## 4.闭包

- 闭包声明时，关键词 `function` 后以及关键词 `use` 的前后都**必须**要有一个空格；
- 开始花括号**必须**写在声明的同一行，结束花括号**必须**紧跟主体结束的下一行；
- 参数列表和变量列表的左括号后以及右括号前，**必须不能**有空格；
- 参数和变量列表中，逗号前**必须不能**有空格，而逗号后**必须**要有空格。

## 5.变量与关键字

- PHP所有 [关键字](http://php.net/manual/en/reserved.keywords.php) **必须**全部小写，常量 `true` 、`false` 和 `null` 也**必须**全部小写；
- `array`、`emtpy`、`isset`、`unset` 等关键字括号内的变量两端**必须不能**有多余的空格；
- 变量声明**应该**使用下划线分隔的小写字母，**不应该**使用非通用的单词简写，**不应该**出现英文以外的拼写或简写；
- 变量赋值和比较（包括控制结构条件语句和数组中）的操作符（`=`、`>`、`<` 以及 `=>`）两端**必须**各有一个空格，但自增（`++`）自减（`--`）操作变量与符号之间**一定不能**有空格；
- 数组声明和使用都**应该**使用`[]` 代替 `array()`；
- 需要换行数组变量的声明，数组开始符号（`[` 或 `array(`）**应该**和操作符在同一行，数组的第一个元素**应该**在新的一行开始，且与上一行之间**必须**保持一个缩进，数组的结束符号（`[` 或 `]`）必须新起一行，且**应该**与变量的第一个字符对齐。


## Basic php-cs-fixer rules

```php
<?php

$finder = Symfony\Component\Finder\Finder::create()
    ->files()
    ->name('*.php')
    ->exclude('vendor')
    ->exclude('tests')
    ->in(__DIR__)
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$fixers = [
    '-psr0',
    '-php_closing_tag',
    'duplicate_semicolon',
    'empty_return',
    'extra_empty_lines',
    'include',
    'list_commas',
    'namespace_no_leading_whitespace',
    'no_blank_lines_after_class_opening',
    'no_empty_lines_after_phpdocs',
    'object_operator',
    'operators_spaces',
    'phpdoc_indent',
    'phpdoc_no_access',
    'phpdoc_no_package',
    'phpdoc_scalar',
    'phpdoc_to_comment',
    'phpdoc_trim',
    'phpdoc_type_to_var',
    'phpdoc_var_without_name',
    'remove_leading_slash_use',
    'remove_lines_between_uses',
    'self_accessor',
    'single_array_no_trailing_comma',
    'single_blank_line_before_namespace',
    'single_quote',
    'spaces_before_semicolon',
    'spaces_cast',
    'standardize_not_equal',
    'ternary_spaces',
    'trim_array_spaces',
    'unary_operators_spaces',
    'whitespacy_lines',
    'multiline_spaces_before_semicolon',
    'short_array_syntax',
];

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->fixers($fixers)
    ->finder($finder)
    ->setUsingCache(true);
```