# 如何使用PHP快速构建命令行应用程序

 时间 2017-12-19 10:36:05 

原文[https://juejin.im/post/5a387ad56fb9a0451543fefe][1]

原文地址： [How to build a Command Line Application using PHP?][3]

如果你是一名 Web 开发工程师，那么你一定使用 PHP 开发过很多 Web 应用程序。但是你知道如何使用 PHP 快速构建一个命令行应用程序(工具)吗?下面我将向您展示如何使用 PHP 和一个著名的的 Composer 扩展包--Symphony/Console构建一个命令行应用。 

Symphony/Console是一个使用 Composer 管理的 PHP 扩展包，它简化了创建一个漂亮的、可测试的 PHP 命令行应用的过程，它提供了开箱即用的诸如(可选/必选的)参数规范和选项规范(使用 - 符号)等功能。那么，我们来一起开始构建我们的应用。 

按照惯例，我们将构建一个“Hello World”的控制台应用程序，但是要稍微修改一下它，让它支持自定义问候语（代替Hello）,并且可以随意的去问候一个人（代替world)。

### 这个Hello World应用程序将会有如下功能：

1. 为我们提供一个单独的 greet (问候)命令，我们将使用它来与应用程序交互。
1. greet 可以接受一个可选的参数( name )来打印出一个被问候的人(默认是World)。
1. greet 可以接受一个选项( --say )来更改问候语(默认是Hello)。
1. 如果我们么样给定参数或者选项，程序将默认输出一个 Hello World 消息。

### 如何使用PHP构建命令行应用程序

* 为我们的项目创建新的目录并 cd 进入它： 
```
    mkdir hello-world-app && cd hello-world-app
```
* 使用 [Composer][4] 将控制台组件引入我们项目 
```
    composer require symfony/console
```
* 然后为你的应用程序创建一个入口点，PHP扩展不是必需的，因为我们要使这个文件成为可执行文件，并在文件本身中指定环境。
```
    touch HelloWorld
    chmod +X HelloWorld
```
* 将下面的代码添加到 HelloWorld 文件中（后面我将为每一行做注解），并在你的终端中执行 HelloWorld 这个应用程序. 
```php
    #!/usr/bin/env php
    <?php
    require __DIR__.'/vendor/autoload.php';
    
    use Symfony\Component\Console\Application;
    use Symfony\Component\Console\Input\InputArgument;
    use Symfony\Component\Console\Input\InputInterface;
    use Symfony\Component\Console\Input\InputOption;
    use Symfony\Component\Console\Output\OutputInterface;
    
    (new Application('Hello World', '1.0.0'))
          ->register('greet')
          ->addArgument('name', InputArgument::OPTIONAL, 'Name of the person')
          ->addOption('say', , InputOption::VALUE_REQUIRED, 'Custom greeting')
          ->setCode(function (InputInterface $input, OutputInterface $output) {
                  
            $name = $input->getArgument('name');
            $greeting = $input->getOption('say');
    
            if (!empty($name) && !empty($greeting)) {
                return $output->writeln("<info>$greeting $name!</info>");
            } else if (!empty($name)) {
                return $output->writeln("<info>Hello $name!</info>");
            } else if (!empty($greeting)) {
                return $output->writeln("<info>$greeting World!</info>");
            } else {
                return $output->writeln("<info>Hello World!</info>");
            }
          })
          ->getApplication()
          ->run();
```
看，就这样，你拥有了自己的 HelloWorld 控制台程序 

![][5]

当没有指定命令时，HelloWorld默认输出一屏信息提示

Symfony Console 组件给我们提供的应用程序有几个开箱可用的选项的和命令，比如 help ， list 和 --version### 解释这个神奇的文件内容

OK，让我们来看看我们的 HelloWorld 文件中的代码。 

1. 我们引入 autoload.php 以使用由 composer 提供的自动加载以及控制台组件提供的各功能。 InputInterface 和 OutputInterface 将使应用程序的输入和输出功能变得简单， InputArgument 和 InputOption 将帮助我们处理传递给我们HelloWorld应用程序的选项和参数。
```
    require __DIR__.'/vendor/autoload.php'; 
    
    use Symfony\Component\Console\Application; 
    use Symfony\Component\Console\Input\InputArgument; 
    use Symfony\Component\Console\Input\InputInterface; 
    use Symfony\Component\Console\Input\InputOption; 
    use Symfony\Component\Console\Output\OutputInterface;
```
1. symphony/console 通过名称实例化一个新的应用程序 HelloWorld (v1.0.0) ,并注册我们的 greet 命令。
```
    (new Application('Hello World', '1.0.0'))
        ->register('greet')
```
1. 我们添加一个可选的 name 参数（ addArgument() ），并提供参数的简短描述。然后,我们使用这个 addOption() 方法添加一个 say 选项。请注意，选项始终是可选的，但您可以指定要传递的值，也可以仅仅将其用作指boolean标识。
```
    ->addArgument('name', InputArgument::OPTIONAL, 'Name of the person') 
    ->addOption('say', , InputOption::VALUE_REQUIRED, 'Custom greeting')
```
1. setCode() 方法中的代码会包含我们应用程序的主逻辑，它会根据传递的参数和选项打印一个问候语到终端。我们监听 $input 对象，使用 getArgument() 和 getOption() 辅助方法获取传递给 greet 的选项和参数，然后，我们只需要检查传递了哪些参数或者选项，并相应的（使用 $output 对象)向控制台输出打印问候语。这个 writeln() 方法可以根据标签格式化文本，比如输出不同颜色的 info , error 和 warning 。
```
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        $name = $input->getArgument('name');
        $greeting = $input->getOption('say');
    
        if (!empty($name) && !empty($greeting)) {
            return $output->writeln("<info>$greeting $name!</info>");
        } else if (!empty($name)) {
            return $output->writeln("<info>Hello $name!</info>");
        } else if (!empty($greeting)) {
            return $output->writeln("<info>$greeting World!</info>");
        } else {
            return $output->writeln("<info>Hello World!</info>");
        }
      })
```
1. 最后我们引导应用程序 并调用他的 方法，以便他做好随时接收和处理 greet 命令。

```
    ->getApplication()
    ->run();
```

[1]: https://juejin.im/post/5a387ad56fb9a0451543fefe
[3]: https://link.juejin.im?target=https%3A%2F%2Fwww.kerneldev.com%2F2017%2F12%2F16%2Fhow-to-build-a-command-line-application-using-php%2F
[4]: https://link.juejin.im?target=https%3A%2F%2Fpkg.phpcomposer.com%2F
[5]: ../img/ziaYjqf.png