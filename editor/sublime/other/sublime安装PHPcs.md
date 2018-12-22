## sublime安装PHPcs（PHPcodesniffer）代码规范提示插件

来源：[https://blog.csdn.net/he426100/article/details/76573038](https://blog.csdn.net/he426100/article/details/76573038)

时间：

人生最痛苦的事莫过于在度娘寻找答案结果出来的是清一色的错误答案，本文的目的就是让大家正确的装好php code sniffer （格式检查和格式化）

首先按教程 [http://blog.csdn.net/cyaspnet/article/details/51773331][0] 装好phpcs和phpmd

然后在sublime 安装php code sniffer （网上教程说要去github下载其实是不需要的，sublime直接可以搜索到）

下面是我的配置

```
{
    // Example for:
    // - Windows 7
    // - With phpcs and php-cs-fixer support

    // We want debugging on
    "show_debug": true,

    // Only execute the plugin for php files
    "extensions_to_execute": ["php"],

    // Do not execute for twig files
    "extensions_to_blacklist": ["twig.php"],

    // Execute the sniffer on file save
    "phpcs_execute_on_save": true,

    // Show the error list after save.
    "phpcs_show_errors_on_save": true,

    // Show the errors in the gutter
    "phpcs_show_gutter_marks": true,

    // Show outline for errors
    "phpcs_outline_for_errors": true,

    // Show the errors in the status bar
    "phpcs_show_errors_in_status": true,

    // Show the errors in the quick panel so you can then goto line
    "phpcs_show_quick_panel": true,

    // Path to php on windows installation
    // This is needed as we cannot run phars on windows, so we run it through php
    "phpcs_php_prefix_path": "D:\\xampp\\php\\php.exe",

    // We want the fixer to be run through the php application
    "phpcs_commands_to_php_prefix": ["Fixer"],


    // PHP_CodeSniffer settings
    // Yes, run the phpcs command
    "phpcs_sniffer_run": true,

    // And execute it on save
    "phpcs_command_on_save": true,

    // This is the path to the bat file when we installed PHP_CodeSniffer
    "phpcs_executable_path": "D:\\xampp\\php\\phpcs.bat",

    // I want to run the PSR2 standard, and ignore warnings
    "phpcs_additional_args": {
        "--standard": "PSR2",
        "-n": ""
    },


    // PHP-CS-Fixer settings
    // Don't want to auto fix issue with php-cs-fixer
    "php_cs_fixer_on_save": false,

    // Show the quick panel
    "php_cs_fixer_show_quick_panel": true,

    // The fixer phar file is stored here:
    "php_cs_fixer_executable_path": "D:\\xampp\\php\\php-cs-fixer.phar",

    // Additional arguments, run all levels of fixing
    "php_cs_fixer_additional_args": {
    },


    // PHP Linter settings
    // Yes, lets lint the files
    "phpcs_linter_run": true,

    // And execute that on each file when saved (php only as per extensions_to_execute)
    "phpcs_linter_command_on_save": true,

    // Path to php
    "phpcs_php_path": "D:\\xampp\\php\\php.exe",

    // This is the regex format of the errors
    "phpcs_linter_regex": "(?P<message>.*) on line (?P* \\d+)",


    // PHP Mess Detector settings
    // Not turning on the mess detector here
    "phpmd_run": false,
    "phpmd_command_on_save": false,
    "phpmd_executable_path": "",
    "phpmd_additional_args": {
        "codesize,naming,unusedcode": ""
    },

    "phpcbf_executable_path": "D:\\xampp\\php\\phpcbf.bat"
}

```


按这个配好了就可以用了， 顺便说下， 不要装phpfmt这个插件，这个是不符合规范的。
            

[0]: http://blog.csdn.net/cyaspnet/article/details/51773331