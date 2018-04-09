## PHP回顾之创建自己的Composer包

来源：[https://tlanyan.me/php-review-create-self-composer-package/](https://tlanyan.me/php-review-create-self-composer-package/)

时间 2018-04-07 20:01:22



### PHP回顾系列目录



* [PHP基础][0]
    
* [web请求][1]
    
* [cookie][2]
    
* [web响应][3]
    
* [session][4]
    
* [数据库操作][5]
    
* [加解密][6]
    
* [Composer][7]
    
  

前文PHP回顾之Composer 简要介绍了Composer的相关概念和简要用法，应付日常开发已无大碍。想要更好的利用Composer协同工作，学会创建自己的Composer包是一项必不可少的技能。本文先讲解Composer仓库的概念，再给出创建和发布Composer包的步骤。


### 仓库（Repository）

仓库是软件开发中常见的概念，与源（sources）意义相近，主要指托管资源的场所。许多软件都有仓库的概念，例如yum、npm、maven、Git，以及本文的主角Composer。仓库以中心化的方式托管资源，为软件的正常工作提供保障。

[Packagist][8]
是Composer默认的中央仓库，PHP社区的绝大部分Composer包都托管在该网站上。Packagist提供公开的、免费的托管服务，任何人均可注册、自由发布包，无需审核。Packagist由Private Packagist提供托管和维护，两者的主要区别为：Packagist的官网是    [https://packagist.org][9]
，托管开源代码，面向公众提供免费包托管服务；Private Packagist的官网是    [https://packagist.com][10]
，托管的代码无需开源，仓库服务器可位于内网，提供更快、更高效的包代码托管服务。

可以配置多个仓库，Composer会自动找出最适合项目的依赖包。搜索包的流程如下：首先检查当前项目是否配置额外仓库，有则优先在额外仓库中检索；无结果向上到全局配置中的额外仓库检索；未配置或搜索无结果的情况下，回退到默认的Packagist中央仓库检索。除非禁用了默认的仓库，Packagist中的包总会被检索到。因为这个原因，Composer推荐PHP开发人员将包托管在Packagist网站上，方便他人检索和引用。


### 配置仓库

有两种方法对Composer的仓库进行配置：命令行和编辑配置文件。`composer config`是Composer配置的命令，可以用来配置项目或全局的仓库信息，例如：

```
composer config [-g] repo.packagist composer https://packagist.phpcomposer.com

```

第二种方法是编辑配置文件。编辑项目的`composer.json`或`~/.config/composer/config.json`，增加`repositories`一项配置，例如：

```json
"repositories": {
    "packagist": {
        "type": "composer",
        "url": "https://packagist.phpcomposer.com"
    }
}

```

以上配置使用 **    [Packagist中国全量镜像][11]
** 网站作为默认中央仓库。在大陆地区部署PHP项目，建议使用该仓库目录，能加速依赖包的下载。

仓库配置最重要的两个参数是`type`和`url`。`type`指明仓库的类型，`url`则指向具体网址。根据仓库的位置，常用的`type`可选值有：



* composer，Composer包托管仓库，例如  [Packagist中国全量镜像][11]；    
* vcs，版本控制管理系统，例如Github上的项目地址；
* pear，PEAR上的包；
* package，位于互联网上包；
* artifact，代码包zip包合集；
* path，指向代码具体位置。
  

互联网上的仓库，`type`的常见值是`composer`和`vcs`；本地的项目，常见值是`artifact`和`path`。具体用例，可参考Composer官方文档。

掌握了仓库的概念和其配置，接下来我们创建自己的包。


### 创建自己的Composer包

创建一个Composer包只需两步：1. 填写包描述信息；2. 写代码。本文创建一个`hello-composer`的包来演示创建过程。该包功能只有一个：输出字符串“`Hello, Composer!`”。

Composer包的描述信息存放在`composer.json`文件中，可直接新建（或从其他项目拷贝）`composer.json`文件，手动填充必要的字段信息；也可以用`composer init`命令，交互式的输入包信息，生成`composer.json`文件后再补全其他字段信息。我们采取直接编辑文件的方式，在`composer.json`中输入如下内容：

```json
{
    "name": "tlanyan/hello-composer",
    "description": "Hello, Composer!",
    "type": "library",
    "require": {
        "php": ">=7.0"
    },
    "license": "MIT",
    "authors": [
        {
            "name": "tlanyan",
            "email": "tlanyan@hotmail.com"
        }
    ],
    "minimum-stability": "stable",
    "autoload": {
        "psr-4": {
            "tlanyan\\": "src/"
        }
    }
}

```

以上内容基本上是一个Composer包的必备字段。其他字段可参考Composer官网的    [composer.json说明][13]
。需注意标记为 **`root-only`** 的字段， **`root-only`** 表示当前包为主项目时才生效。例如`require-dev`字段，在当前项目中开发，字段内的包会下载放到`vendor`文件夹内；如果该项目被其他项目引用，则该字段的值被忽略，引用的包不会被下载。

接下来编写代码。在`src`目录下新建`HelloComposer.php`：

```php
namespace tlanyan;
 
class HelloComposer
{
    public static function greet()
    {
        echo "Hello, Composer!", PHP_EOL;
    }
}

```

代码风格建议参考    [PSR-2规范][14]
，文件命名和路径规范建议参考    [PSR-4规范][15]
。另外需注意文件的路径需与`composer.json`中`autoload`的配置一致。

通过简单两步，我们创建的自己的Composer包。接下来在其他项目中引用该包。


### 引用Composer包

新建一个test项目，引用上文创建的包并查看效果，步骤如下：



* 新建test文件夹，拷贝或者新建`composer.json`文件，配置如下：    
  

```json
{
    ....
    "require": {
        "tlanyan/hello-composer": "*"
    },
    "minimum-stability": "dev",
    "repositories": {
        "local": {
            "type": "path",
            "url": "/path/to/hello-composer"
        }
    },
    ....
}

```

配置文件需要注意两点： 1. 如果hello-composer的`composer.json`文件没有`version`字段（或不是稳定版），`minimum-stability`值要是`dev`（默认是`stable`），否则无法安装； 2. 需添加自定义仓库，`type`值为`path`。



* 执行`composer install -vvv`安装依赖包，安装完成后vendor目录下生成`tlanyan/hello-composer`目录。

    
* 在test中新建Test.php文件，引用HelloComposer类：

    
  

```php
namespace test;
 
require "vendor/autoload.php";
 
use tlanyan\HelloComposer;
 
class Test
{
    public static main()
    {
        HelloComposer::greet();
    }
}
 
Test::main();

```



* 执行Test.php：`php Test.php`，输出`Hello, Composer!`。    
  

通过配置Composer仓库，我们成功引用了创建的`hello-composer`包。测试没问题后，就可以发布到网上供其他人使用。下面简要说是发布流程。


### 发布Composer包

将Composer包发布到互联网的方式有几种：



* 打包成zip，上传到任意一个可公开访问的网站；
* 通过版本控制软件，上传到代码仓库；
* 提交到PEAR社区；
* 提交到私有的Composer仓库；
* 提交到Packagist。
  

前四种方式，需要用户配置仓库信息才能检索到包（PEAR社区几乎已死，可以忽略）。如果代码开源，建议提交到Packagist，方便全世界的PHP开发者检索和使用，为Composer生态做贡献。

提交包到Packagist，要经历以下过程：



* 在      [Github][16]
创建项目并提交代码；    
* 在      [Packagist][8]
输入项目地址提交包；    
* 在Github配置项目，触发Packagist自动更新。
  

前两步是必须的，第三步可选。本着为提交的包负责的态度，强烈建议完成第三步操作。

提交包的过程涉及到Github和Packagist两个站点，Github和Packagist之间的关系为：Github托管实际的代码和文件；Packagist托管包的作者、包名、版本号、下载量等元数据保。简要说Packagist是索引，Github是内容提供方。

详细步骤可参考官网指引或网上教程，网上相关内容太多，本文不再重复。


### 总结

本文介绍了Composer仓库的概念，创建了一个完整的Composer包，并给出提交包到Packagist的指引。用户掌握相关概念和运行机制后，可提交代码为社区做贡献，也可跳出Packagist自由的引用和安装依赖包。


### 参考



* [https://getcomposer.org/doc/][18]
    
* [https://packagist.org/about][19]
    
* [https://www.phpcomposer.com/][20]
    
  



[0]: https://tlanyan.me/php-review-php-basics/
[1]: https://tlanyan.me/php-review-web-request/
[2]: https://tlanyan.me/php-review-cookie/
[3]: https://tlanyan.me/php-review-web-response/
[4]: https://tlanyan.me/php-review-session/
[5]: https://tlanyan.me/php-review-database/
[6]: https://tlanyan.me/php-review-cipher/
[7]: https://tlanyan.me/php-review-composer/
[8]: https://packagist.org
[9]: https://packagist.org
[10]: https://packagist.com
[11]: https://www.phpcomposer.com/
[12]: https://www.phpcomposer.com/
[13]: https://getcomposer.org/doc/04-schema.md
[14]: https://www.php-fig.org/psr/psr-2/
[15]: https://www.php-fig.org/psr/psr-4/
[16]: https://github.com
[17]: https://packagist.org
[18]: https://getcomposer.org/doc/
[19]: https://packagist.org/about
[20]: https://www.phpcomposer.com/