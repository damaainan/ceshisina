# PHP过滤器(Filter)的用法总结

Feb 1, 2015 • hoohack

在使用PHP进行开发时，很多时候需要判断邮箱、URL或IP地址等数据是否符合都是使用正则表达式。还有些时候需要将某些字符转换成其他字符时需要编写函数。如果在大型项目时会有很多这样的需要，对于开发和维护来说难免过于复杂。庆幸的是，现在有了filter函数库做这样的工作。

## 什么是PHP过滤器

PHP过滤器用于验证和过滤来自非安全来源的数据，比如用户的输入。验证和过滤用户输入或自定义数据是任何Web应用程序的重要部分。

## 为什么使用过滤器

> * 几乎所有Web应用程序都依赖外部外部的输入。这些数据通常来自用户或其他应用程序（比如web服务）。通过使用过滤器，你能够确保应用程序获得正确的 输入类型。
> * 程序员应该始终对外部数据进行过滤。
> * 避免编写繁杂的正则表达式/函数对数据进行验证

## 什么是外部数据

> * 来自表单的输入数据
> * Cookies
> * 服务器变量
> * 数据库查询结果

## filter函数

**filter_var()** - 通过一个指定的过滤器来过滤单一的变量

    mixed filter_var ( mixed $variable [, int $filter = FILTER_DEFAULT [, mixed $options ]] )
    

**filter_var_array()** - 通过相同的或不同的过滤器来过滤多个变量

    mixed filter_var_array ( array $data [, mixed $definition [, bool $add_empty = true ]] )
    

**filter_input** - 获取一个输入变量，并对它进行过滤

    mixed filter_input ( int $type , string $variable_name [, int $filter = FILTER_DEFAULT [, mixed $options ]] )
    

**filter_input_array** - 获取多个输入变量，并通过相同的或不同的过滤器对它们进行过滤

    mixed filter_input_array ( int $type [, mixed $definition [, bool $add_empty = true ]] )
    

## 过滤器类型

Filter函数有第二个参数是过滤器类型，有两种过滤器：`Validating`和`Sanitizing`

### Validating过滤器

> * 用户验证用户输入
> * 严格的格式规则（比如URL或EMAIL验证）
> * 如果成功则返回预期类型的数据，如果失败则返回FALSE

### Sanitizing过滤器

> * 用于允许或禁止字符串中指定的字符
> * 无数据格式规则
> * 始终返回字符串

## 选项或标志

Filter函数的第三个参数是选项或标志，用于向指定的过滤器添加额外的过滤选项。

不同的过滤器有不同的选项和标志。

## 使用Validating过滤器过滤输入

当验证来自表单的输入时，我们需要做的是首先确认是否存在我们正在查找的输入数据。然后我们用`filter_input()`函数过滤输入的数据。如：

```php
    <?php
        if(!filter_has_var(INPUT_GET, "email")) {
            echo("Input type does not exist");
        } else {
            if (!filter_input(INPUT_GET, "email", FILTER_VALIDATE_EMAIL)) {
                echo "E-Mail is not valid";
            } else {
                echo "E-Mail is valid";
            }
         }
    ?>
```

### 解释：

> 上面的例子有一个通过 “GET” 方法传送的输入变量 (email)： 检测是否存在 “GET” 类型的 “email” 输入变量 如果存在输入变量，检测它是否是有效的邮件地址

## 使用Sanitizing过滤器过滤输入

首先，我们要确认是否存在我们正在查找的输入数据。 然后，我们用 `filter_input()` 函数来净化输入数据。 在下面的例子中，输入变量 “url” 被传到 PHP 页面：

```php
    <?php
        if(!filter_has_var(INPUT_POST, "url")) {
            echo("Input type does not exist");
        } else {
            $url = filter_input(INPUT_POST, "url", FILTER_SANITIZE_URL);
        }
    ?>
```

### 解释：

> 上面的例子有一个通过 “POST” 方法传送的输入变量 (url)： 检测是否存在 “POST” 类型的 “url” 输入变量 如果存在此输入变量，对其进行净化（删除非法字符），并将其存储在 $url 变量中 假如输入变量类似这样：”http://www.W3非o法ol.com.c字符n/”，则净化后的 $url 变量应该是这样的：  http://www.W3School.com.cn/
 
## 过滤多个输入

表单通常由多个输入字段组成。为了避免对 `filter_var` 或 `filter_input` 重复调用，我们可以使用 `filter_var_array` 或 `filter_input_array` 函数。

```php
    <?php
        $filters = array(
            "name" => array(
                "filter"=>FILTER_SANITIZE_STRING
            ),
            "age" => array(
              "filter"=>FILTER_VALIDATE_INT,
              "options"=>array(
                   "min_range"=>1,
                   "max_range"=>120
              )
            ),
            "email"=> FILTER_VALIDATE_EMAIL,
         );
        
        $result = filter_input_array(INPUT_GET, $filters);
        
        if (!$result["age"]) {
            echo("Age must be a number between 1 and 120.<br />");
        } elseif(!$result["email"]) {
            echo("E-Mail is not valid.<br />");
        } else {
            echo("User input is valid");
        }
    ?>
```

### 解释

上面的例子有三个通过 “GET” 方法传送的输入变量 (name, age and email)

> * 设置一个数组，其中包含了输入变量的名称，以及用于指定的输入变量的过滤器
> * 调用 `filter_input_array` 函数，参数包括 GET 输入变量及刚才设置的数组
> * 检测 $result 变量中的 “age” 和 “email” 变量是否有非法的输入。（如果存在非法输入，）

`filter_input_array()` 函数的第二个参数可以是数组或单一过滤器的 ID。

如果该参数是单一过滤器的 ID，那么这个指定的过滤器会过滤输入数组中所有的值。

如果该参数是一个数组，那么此数组必须遵循下面的规则：

> * 必须是一个关联数组，其中包含的输入变量是数组的键（比如 “age” 输入变量）
> * 此数组的值必须是过滤器的 ID ，或者是规定了过滤器、标志以及选项的数组

## 使用 Filter Callback

使用`FILTER_CALLBACK`过滤器，可以调用自定义的函数，把它作为一个过滤器来使用。这样，我们就可以根据自己的需求来编写数据过滤器。

在下面的例子中，我们使用一个自定义的函数把所有 “_” 转换为空格：

```php
      <?php
        function convertSpace($string) {
            return str_replace("_", " ", $string);
        }
      
        $string = "Peter_is_a_great_guy!";
      
        echo filter_var($string, FILTER_CALLBACK, array("options"=>"convertSpace"));
      ?>
```

以上代码的结果是这样的： Peter is a great guy!

### 解释

> 上面的例子把所有 “ ” 转换成空格： 创建一个把 “ ” 替换为空格的函数 调用 `filter_var()` 函数，它的参数是 `FILTER_CALLBACK` 过滤器以及包含我们的函数的数组

