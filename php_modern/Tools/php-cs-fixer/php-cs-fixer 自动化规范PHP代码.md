# [php-cs-fixer 自动化规范PHP代码][0]

* 叶子坑 发布于 2018-05-13
* 分类：[未分类][1]
* 阅读(628)
* 评论(0)

php-cs-fixer是一个支持自定义，自动化规范PHP代码的组件，

使用教程参考官网：[http://cs.sensiolabs.org/][2]

### 参考命令

    php-cs-fixer fix --config=.php_cs -v --using-cache=no --path-mode=intersection -- PHP文件路径
    

> 大部分编辑器可安装相关扩展，以支持快捷键执行格式化。如Sublime：> PHP CS Fixer

### 参考配置

> 配置存项目根目录 `.php_cs` 即可 

```php
<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@Symfony' => true,
        'array_syntax' => array('syntax' => 'short'),
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'php_unit_construct' => true,
        'php_unit_strict' => true,
        'yoda_style' => false,
        'phpdoc_summary' => false,
        'not_operator_with_successor_space' => true,
        'no_extra_consecutive_blank_lines' => true,
        'general_phpdoc_annotation_remove' => true,
        // 'ordered_class_elements' => true,
        'binary_operator_spaces' => array(
            // 'align_double_arrow' => true,
            // 'align_equals' => true,
            'default' => 'align_single_space_minimal',
        ),
    ))
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('_*')
            ->exclude('vendor')
            ->exclude('storage')
            ->exclude('resources')
            ->exclude('public')
            ->in(__DIR__)
    )
;
```
[0]: http://flc.io/2018/05/746.html
[1]: http://flc.io/category/uncategorized
[2]: http://cs.sensiolabs.org/