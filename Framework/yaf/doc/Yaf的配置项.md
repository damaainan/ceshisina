### Yaf的配置项

选项名称 | 默认值 | 可修改范围 | 更新记录
-|-|-|-
yaf.environ | product | PHP_INI_ALL | 环境名称, 当用INI作为Yaf的配置文件时, 这个指明了Yaf将要在INI配置中读取的节的名字
yaf.library | NULL | PHP_INI_ALL | 全局类库的目录路径
yaf.cache_config | 0 | PHP_INI_SYSTEM | 是否缓存配置文件(只针对INI配置文件生效), 打开此选项可在复杂配置的情况下提高性能
yaf.name_suffix | 1 | PHP_INI_ALL | 在处理Controller, Action, Plugin, Model的时候, 类名中关键信息是否是后缀式, 比如UserModel, 而在前缀模式下则是ModelUser
yaf.name_separator | " " | PHP_INI_ALL | 在处理Controller, Action, Plugin, Model的时候, 前缀和名字之间的分隔符, 默认为空, 也就是UserPlugin, 加入设置为"_", 则判断的依据就会变成:"User_Plugin", 这个主要是为了兼容ST已有的命名规范
yaf.forward_limit | 5 | PHP_INI_ALL | forward最大嵌套深度
yaf.use_namespace | 0 | PHP_INI_SYSTEM | 开启的情况下, Yaf将会使用命名空间方式注册自己的类, 比如Yaf_Application将会变成Yaf\Application
yaf.use_spl_autoload | 0 | PHP_INI_ALL | 开启的情况下, Yaf在加载不成功的情况下, 会继续让PHP的自动加载函数加载, 从性能考虑, 除非特殊情况, 否则保持这个选项关闭




