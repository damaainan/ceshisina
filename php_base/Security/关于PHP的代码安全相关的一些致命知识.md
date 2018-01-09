# 谈谈关于PHP的代码安全相关的一些致命知识

 时间 2017-08-18 14:16:05 

原文[https://yq.aliyun.com/articles/123421][1]

<font face=微软雅黑>

## 目标 

本教程讲解如何防御最常见的安全威胁：**SQL 注入**、 **操纵 GET 和 POST 变量**、 **缓冲区溢出攻击**、 **跨站点脚本攻击**、 **浏览器内的数据操纵和远程表单提交**。

## 前提条件 

本教程是为至少有一年编程经验的 PHP 开发人员编写的。您应该了解 PHP 的语法和约定；这里不解释这些内容。有使用其他语言（比如 Ruby、Python 和 Perl）的经验的开发人员也能够从本教程中受益，因为这里讨论的许多规则也适用于其他语言和环境。

## 安全性快速简介 

Web 应用程序最重要的部分是什么？根据回答问题的人不同，对这个问题的答案可能是五花八门。业务人员需要可靠性和可伸缩性。IT 支持团队需要健壮的可维护的代码。最终用户需要漂亮的用户界面和执行任务时的高性能。但是，如果回答 "安全性"，那么每个人都会同意这对 Web 应用程序很重要。

但是，大多数讨论到此就打住了。尽管安全性在项目的检查表中，但是往往到了项目交付之前才开始考虑解决安全性问题。采用这种方式的 Web 应用程序项目的数量多得惊人。开发人员工作几个月，只在最后才添加安全特性，从而让 Web 应用程序能够向公众开放。

结果往往是一片混乱，甚至需要返工，因为代码已经经过检验、单元测试并集成为更大的框架，之后才在其中添加安全特性。添加安全性之后，主要组件可能会停止工作。安全性的集成使得原本顺畅（但不安全）的过程增加额外负担或步骤。

本教程提供一种将安全性集成到 PHP Web 应用程序中的好方法。它讨论几个一般性安全主题，然后深入讨论主要的安全漏洞以及如何堵住它们。在学完本教程之后，您会对安全性有更好的理解。

#### 主题包括：

    SQL 注入攻击
    操纵 GET 字符串
    缓冲区溢出攻击
    跨站点脚本攻击（XSS）
    浏览器内的数据操纵
    远程表单提交

#### Web 安全性 101

在讨论实现安全性的细节之前，最好从比较高的角度讨论 Web 应用程序安全性。本节介绍安全哲学的一些基本信条，无论正在创建何种 Web 应用程序，都应该牢记这些信条。这些思想的一部分来自 Chris Shiflett（他关于 PHP 安全性的书是无价的宝库），一些来自 Simson Garfinkel（参见 参考资料），还有一些来自多年积累的知识。

### 规则 1：绝不要信任外部数据或输入 

关于 Web 应用程序安全性，必须认识到的第一件事是不应该信任外部数据。外部数据（outside data） 包括不是由程序员在 PHP 代码中直接输入的任何数据。在采取措施确保安全之前，来自任何其他来源（比如 GET 变量、表单 POST、数据库、配置文件、会话变量或 cookie）的任何数据都是不可信任的。

例如，下面的数据元素可以被认为是安全的，因为它们是在 PHP 中设置的。

清单 1. 安全无暇的代码

    $myUsername = 'tmyer';
    $arrayUsers = array('tmyer', 'tom', 'tommy');
    define("GREETING", 'hello there' . $myUsername);

但是，下面的数据元素都是有瑕疵的。

清单 2. 不安全、有瑕疵的代码

    $myUsername = $_POST['username']; //tainted!
    $arrayUsers = array($myUsername, 'tom', 'tommy'); //tainted!
    define("GREETING", 'hello there' . $myUsername); //tainted!

为什么第一个变量 `$myUsername` 是有瑕疵的？因为它直接来自表单 POST。用户可以在这个输入域中输入任何字符串，包括用来清除文件或运行以前上传的文件的恶意命令。您可能会问，"难道不能使用只接受字母 A-Z 的客户端（JavaScript）表单检验脚本来避免这种危险吗？"是的，这总是一个有好处的步骤，但是正如在后面会看到的，任何人都可以将任何表单下载到自己的机器上，修改它，然后重新提交他们需要的任何内容。

解决方案很简单：必须对 `$_POST['username']` 运行清理代码。如果不这么做，那么在使用 `$myUsername` 的任何其他时候（比如在数组或常量中），就可能污染这些对象。

对用户输入进行清理的一个简单方法是，使用正则表达式来处理它。在这个示例中，只希望接受字母。将字符串限制为特定数量的字符，或者要求所有字母都是小写的，这可能也是个好主意。

清单 3. 使用户输入变得安全

    $myUsername = cleanInput($_POST['username']); //clean!
    $arrayUsers = array($myUsername, 'tom', 'tommy'); //clean!
    define("GREETING", 'hello there' . $myUsername); //clean!
    function cleanInput($input){
    $clean = strtolower($input);
    $clean = preg_replace("/[^a-z]/", "", $clean);
    $clean = substr($clean,0,12);
    return $clean;
    }

### 规则 2：禁用那些使安全性难以实施的 PHP 设置 

已经知道了不能信任用户输入，还应该知道不应该信任机器上配置 PHP 的方式。例如，要确保禁用 `register_globals`。如果启用了 `register_globals`，就可能做一些粗心的事情，比如使用 `$variable` 替换同名的 `GET` 或 `POST` 字符串。通过禁用这个设置，PHP 强迫您在正确的名称空间中引用正确的变量。要使用来自表单 `POST` 的变量，应该引用 `$_POST['variable']`。这样就不会将这个特定变量误会成 `cookie`、会话或 `GET` 变量。

要检查的第二个设置是错误报告级别。在开发期间，希望获得尽可能多的错误报告，但是在交付项目时，希望将错误记录到日志文件中，而不是显示在屏幕上。为什么呢？因为恶意的黑客会使用错误报告信息（比如 SQL 错误）来猜测应用程序正在做什么。这种侦察可以帮助黑客突破应用程序。为了堵住这个漏洞，需要编辑 [php][4] .ini 文件，为 error_log 条目提供合适的目的地，并将 `display_errors` 设置为 Off。 

### 规则 3：如果不能理解它，就不能保护它 

一些开发人员使用奇怪的语法，或者将语句组织得很紧凑，形成简短但是含义模糊的代码。这种方式可能效率高，但是如果您不理解代码正在做什么，那么就无法决定如何保护它。

例如，您喜欢下面两段代码中的哪一段？

清单 4. 使代码容易得到保护

    //obfuscated code
    $input = (isset($_POST['username']) ? $_POST['username']:");
    //unobfuscated code
    $input = ";
    if (isset($_POST['username'])){
    $input = $_POST['username'];
    }else{
    $input = ";
    }

在第二个比较清晰的代码段中，很容易看出 `$input` 是有瑕疵的，需要进行清理，然后才能安全地处理。

### 规则 4："纵深防御" 是新的法宝 

本教程将用示例来说明如何保护在线表单，同时在处理表单的 PHP 代码中采用必要的措施。同样，即使使用 PHP regex 来确保 GET 变量完全是数字的，仍然可以采取措施确保 SQL 查询使用转义的用户输入。

纵深防御不只是一种好思想，它可以确保您不会陷入严重的麻烦。

既然已经讨论了基本规则，现在就来研究第一种威胁：SQL 注入攻击。

#### 防止 SQL 注入攻击

在 SQL 注入攻击 中，用户通过操纵表单或 GET 查询字符串，将信息添加到数99一系列操作有点儿太严格了。毕竟，十六进制串有合法的用途，比如输出外语中的字符。如何部署十六进制 regex 由您自己决定。比较好的策略是，只有在一行中包含过多十六进制串时，或者字符串的字符超过特定数量（比如 128 或 255）时，才删除十六进制串。

#### 跨站点脚本攻击

在跨站点脚本（XSS）攻击中，往往有一个恶意用户在表单中（或通过其他用户输入方式）输入信息，这些输入将恶意的客户端标记插入过程或数据库中。例如，假设站点上有一个简单的来客登记簿程序，让访问者能够留下姓名、电子邮件地址和简短的消息。恶意用户可以利用这个机会插入简短消息之外的东西，比如对于其他用户不合适的图片或将用户重定向到另一个站点的 JavaScript，或者窃取 cookie 信息。

幸运的是，PHP 提供了 `strip_tags()` 函数，这个函数可以清除任何包围在 HTML 标记中的内容。`strip_tags()` 函数还允许提供允许标记的列表，比如 或 。

清单 16 给出一个示例，这个示例是在前一个示例的基础上构建的。

清单 16. 从用户输入中清除 HTML 标记

    if ($_POST['submit'] == "go"){
    //strip_tags
    $name = strip_tags($_POST['name']);
    $name = substr($name,0,40);
    //clean out any potential hexadecimal characters
    $name = cleanHex($name);
    //continue processing….
    }
    function cleanHex($input){
    $clean = preg_replace\
    ("![\][xX]([A-Fa-f0-9]{1,3})!", "",$input);
    return $clean;
    }
    "" method="post"
    Name
    "text" name="name" id="name" size="20″ maxlength="40″/>

从安全的角度来看，对公共用户输入使用 `strip_tags()` 是必要的。如果表单在受保护区域（比如内容管理系统）中，而且您相信用户会正确地执行他们的任务（比如为 Web 站点创建 HTML 内容），那么使用 `strip_tags()` 可能是不必要的，会影响工作效率。

还有一个问题：如果要接受用户输入，比如对贴子的评论或来客登记项，并需要将这个输入向其他用户显示，那么一定要将响应放在 PHP 的 `htmlspecialchars()` 函数中。这个函数将与符号、`<` 和 `>` 符号转换为 HTML 实体。例如，与符号`（&）`变成 `&`。这样的话，即使恶意内容躲开了前端 `strip_tags()` 的处理，也会在后端被 `htmlspecialchars()` 处理掉。

#### 浏览器内的数据操纵

有一类浏览器插件允许用户篡改页面上的头部元素和表单元素。使用 Tamper Data（一个 Mozilla 插件），可以很容易地操纵包含许多隐藏文本字段的简单表单，从而向 PHP 和 MySQL 发送指令。

用户在点击表单上的 Submit 之前，他可以启动 Tamper Data。在提交表单时，他会看到表单数据字段的列表。Tamper Data 允许用户篡改这些数据，然后浏览器完成表单提交。

让我们回到前面建立的示例。已经检查了字符串长度、清除了 HTML 标记并删除了十六进制字符。但是，添加了一些隐藏的文本字段，如下所示：

清单 17. 隐藏变量

    if ($_POST['submit'] == "go"){
    //strip_tags
    $name = strip_tags($_POST['name']);
    $name = substr($name,0,40);
    //clean out any potential hexadecimal characters
    $name = cleanHex($name);
    //continue processing….
    }
    function cleanHex($input){
    $clean = \
    preg_replace("![\][xX]([A-Fa-f0-9]{1,3})!", "",$input);
    return $clean;
    }
    " method="post"
    Name
    "text" name="name" id="name" size="20″ maxlength="40″/>

注意，隐藏变量之一暴露了表名：users。还会看到一个值为 create 的 action 字段。只要有基本的 SQL 经验，就能够看出这些命令可能控制着中间件中的一个 SQL 引擎。想搞大破坏的人只需改变表名或提供另一个选项，比如 delete。

图 1 说明了 Tamper Data 能够提供的破坏范围。注意，Tamper Data 不但允许用户访问表单数据元素，还允许访问 HTTP 头和 cookie。

要防御这种工具，最简单的方法是假设任何用户都可能使用 Tamper Data（或类似的工具）。只提供系统处理表单所需的最少量的信息，并把表单提交给一些专用的逻辑。例如，注册表单应该只提交给注册逻辑。

如果已经建立了一个通用表单处理函数，有许多页面都使用这个通用逻辑，那该怎么办？如果使用隐藏变量来控制流向，那该怎么办？例如，可能在隐藏表单变量中指定写哪个数据库表或使用哪个文件存储库。有 4 种选择：

不改变任何东西，暗自祈祷系统上没有任何恶意用户。

重写功能，使用更安全的专用表单处理函数，避免使用隐藏表单变量。

使用 md5() 或其他加密机制对隐藏表单变量中的表名或其他敏感信息进行加密。在 PHP 端不要忘记对它们进行解密。

通过使用缩写或昵称让值的含义模糊，在 PHP 表单处理函数中再对这些值进行转换。例如，如果要引用 users 表，可以用 u 或任意字符串（比如 `u8y90×0jkL`）来引用它。

后两个选项并不完美，但是与让用户轻松地猜出中间件逻辑或数据模型相比，它们要好得多了。

现在还剩下什么问题呢？远程表单提交。

#### 远程表单提交

Web 的好处是可以分享信息和服务。坏处也是可以分享信息和服务，因为有些人做事毫无顾忌。

以表单为例。任何人都能够访问一个 Web 站点，并使用浏览器上的 `File > Save As` 建立表单的本地副本。然后，他可以修改 action 参数来指向一个完全限定的 URL（不指向 formHandler.php，而是指向`http://www.yoursite.com/formHandler.php`，因为表单在这个站点上），做他希望的任何修改，点击 Submit，服务器会把这个表单数据作为合法通信流接收。

首先可能考虑检查 `$_SERVER['HTTP_REFERER']`，从而判断请求是否来自自己的服务器，这种方法可以挡住大多数恶意用户，但是挡不住最高明的黑客。这些人足够聪明，能够篡改头部中的引用者信息，使表单的远程副本看起来像是从您的服务器提交的。

处理远程表单提交更好的方式是，根据一个惟一的字符串或时间戳生成一个令牌，并将这个令牌放在会话变量和表单中。提交表单之后，检查两个令牌是否匹配。如果不匹配，就知道有人试图从表单的远程副本发送数据。

要创建随机的令牌，可以使用 PHP 内置的 `md5()`、`uniqid()` 和 `rand()` 函数，如下所示：

清单 18. 防御远程表单提交

```
    session_start();
    if ($_POST['submit'] == "go"){
    //check token
    if ($_POST['token'] == $_SESSION['token']){
    //strip_tags
    $name = strip_tags($_POST['name']);
    $name = substr($name,0,40);
    //clean out any potential hexadecimal characters
    $name = cleanHex($name);
    //continue processing….
    }else{
    //stop all processing! remote form posting attempt!
    }
    }
    $token = md5(uniqid(rand(), true));
    $_SESSION['token']= $token;
    function cleanHex($input){
    $clean = preg_replace("![\][xX]([A-Fa-f0-9]{1,3})!", "",$input);
    return $clean;
    }
    " method="post"
    Name
```
这种技术是有效的，这是因为在 PHP 中会话数据无法在服务器之间迁移。即使有人获得了您的 PHP 源代码，将它转移到自己的服务器上，并向您的服务器提交信息，您的服务器接收的也只是空的或畸形的会话令牌和原来提供的表单令牌。它们不匹配，远程表单提交就失败了。

### 结束语

本教程讨论了许多问题：

使用` mysql_real_escape_string()` 防止 SQL 注入问题。

使用正则表达式和 `strlen()` 来确保 `GET` 数据未被篡改。

使用正则表达式和 `strlen()` 来确保用户提交的数据不会使内存缓冲区溢出。

使用 `strip_tags()` 和 `htmlspecialchars()` 防止用户提交可能有害的 HTML 标记。

避免系统被 Tamper Data 这样的工具突破。

使用惟一的令牌防止用户向服务器远程提交表单。

本教程没有涉及更高级的主题，比如文件注入、HTTP 头欺骗和其他漏洞。但是，您学到的知识可以帮助您马上增加足够的安全性，使当前项目更安全。

</font> 

[1]: https://yq.aliyun.com/articles/123421

[4]: http://www.lucktribe.com/tag/php/