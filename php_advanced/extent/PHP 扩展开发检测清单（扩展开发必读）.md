## PHP 扩展开发检测清单（扩展开发必读）

来源：[http://www.cnblogs.com/summerblue/p/8915303.html](http://www.cnblogs.com/summerblue/p/8915303.html)

时间 2018-04-23 08:15:00

 
想要做出一个成功的 PHP 扩展包，不仅仅是简单的将代码放进文件夹中就可以了，除此之外，还有非常多的因素来决定你的扩展是否优秀。以下清单的内容将有助于完善你的扩展，并且在 PHP 社区中得到更多的重视。
 
## 1. 为你的扩展选择一个正确合适的名字
 
 
* 确保你的命名没有被其他项目使用。 
* 扩展的名字需要和你的 PHP 命名空间保持一致。 
* 不要在你的命名空间中使用自己的姓名或者其他带有个人色彩的东西。 
 
 
## 2. 将你的扩展开源
 
 
* [GitHub][1]  可以免费管理这一类公共的项目。  
* [GitHub][1]  非常有助于你来管理这个开源项目，并且方便他人获取你的扩展。  
* 如果你不想使用，可以尝试替代品:  [Bitbucket][3] .  
 
 
## 3. 对自动加载友好一些
 
 
* 使用 [PSR-4][4]  兼容的自动加载器命名空间。  
* 请将代码放在 `src` 文件夹里。  
 
 
## 4. 通过 Composer 发布
 
 
* 确保可以通过 [Composer][5] 来找到你的类库， [Composer][5] 是PHP的一个依赖管理工具  
* 发布在  [Packagist][7] 上， [Packagist][7] 是一个主要的 Composer 包仓库。  
 
 
## 5. 不局限于框架
 
 
* 不要局限于项目只能使用在一个框架上。 
* 通过服务提供器来给框架提供特殊支持。 
 
 
## 6. 遵循一种编码风格
 
 
* 强烈建议你坚持使用  [PSR-2][9]  编码风格。  
* 使用代码自动格式化工具，比如  [PHP Coding Standards Fixer][10] 。  
* 使用代码风格检测工具，比如  [PHP Code Sniffer][11] 。  
 
 
## 7. 编写单元测试
 
 
* 覆盖大部分的代码。 
* 使用 [PHPUnit][12] ，一个常用的 PHP 单元测试框架。  
* 其他可选：  [phpspec][13] ，  [Behat][14] ， [atoum][15] ，  [Codeception][16] 。  
 
 
## 8. 为代码写注释
 
 
* 将注释当作内置文档来看待。 
* 代码注释也可以改善 IDE 的代码自动完成功能, 比如  [PhpStorm][17] 。  
* 可以自动转换成 API 文档， 查看  [phpDocumentor][18] 。  
 
 
## 9. 使用语义化版本管理
 
 
* 使用  [语义化版本号][19]  来管理版本号。  
* 遵循 主版本.次版本.补丁版本 规范。 
* 让开发人员安全的升级软件，而不用担心会产生破坏性的改动。 
* 请记得及时给发布版本打上标签！ 
 
 
## 10. 保持定期更新日志
 
 
* 明确标记并展示出版本之间显著的变化。 
* 考虑遵循  [Keep a CHANGELOG][20]  的格式进行编写。  
 
 
## 11. 使用持续集成
 
 
* 使用服务来自动检查编码是否标准并且能否通过运行测试。 
* 在多个不同的 PHP 版本都进行运行测试会是个不错的办法。 
* 确保提交或者拉取的时候都可以自动运行。 
* 参考： [Travis-CI][21] ， [Scrutinizer][22] ,  [Circle-CI][23] 。  
 
 
## 12. 编写大量的使用文档
 
 
* 一份优秀的文档对于扩展包来说至关重要。 
* 至少要确保库中有详细的 README (自述) 文件。 
* 可以尝试在  [GitHub Pages][24] 中托管文档。  
* 可用参考：  [Read the Docs][25] 。  
 
 
## 13. 包含一份授权（License）
 
 
* 包含一份授权协议，能够很有效地保护你的工作成果，并且很容易做到。 
* 参考  [choosealicense.com][26] 。 大部分PHP 开源项目使用  [MIT 协议][27] 。  
* 至少要在代码库中包含 LICENSE 文件。 
* 还可以考虑在  [Docblocks][28] 中加入你的授权协议。  
 
 
## 14. 欢迎大家的贡献
 
 
* 想要大家辅助改进项目，那一定要多多请求大家的贡献！ 
* 有一份 CONTRIBUTING 文件，列出贡献者的名单。 
* 利用这份文件解释项目环境要求，例如测试环境。 
 
 
更多现代化 PHP 知识，请前往 [Laravel / PHP 知识社区][29]
 


[1]: https://github.com/
[2]: https://github.com/
[3]: https://bitbucket.org/
[4]: http://www.php-fig.org/psr/psr-4/
[5]: https://getcomposer.org/
[6]: https://getcomposer.org/
[7]: https://packagist.org/
[8]: https://packagist.org/
[9]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md
[10]: http://cs.sensiolabs.org/
[11]: https://github.com/squizlabs/PHP_CodeSniffer
[12]: https://phpunit.de/
[13]: http://www.phpspec.net/
[14]: http://behat.org/
[15]: http://atoum.org/
[16]: http://codeception.com/
[17]: https://www.jetbrains.com/phpstorm/
[18]: http://www.phpdoc.org/
[19]: http://semver.org/
[20]: http://keepachangelog.com/
[21]: https://travis-ci.org/
[22]: https://scrutinizer-ci.com/
[23]: https://circleci.com/
[24]: https://pages.github.com/
[25]: https://readthedocs.org/
[26]: http://choosealicense.com/
[27]: http://opensource.org/licenses/MIT
[28]: http://www.phpdoc.org/docs/latest/references/phpdoc/tags/license.html
[29]: https://laravel-china.org/topics/10032
