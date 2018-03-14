## .「VIM」基于项目的 VIM 配置

来源：[http://hbliu.coding.me/2018/02/28/vim-project-configuration/](http://hbliu.coding.me/2018/02/28/vim-project-configuration/)

时间 2018-02-28 11:04:17

 
有时在特定的项目中我们需要有特定的 VIM 配置, 这些配置和全局配置是不一致的 或者这个配置是只在这个项目中才能生效的, 我们可以借助插件 [PorjectConfig][1] 来实现 Per Project 的 VIM 配置. 
 
## 如何使用 
 
利用 vim-plug 安装:

```
Plug 'https://github.com/hiberabyss/ProjectConfig'

```
 
我们便可直接调用 VIM 命令`ProjectConfig`来添加当前项目的配置: 
 

![][0]
 
## 原理 
 
ProjectConfig 插件会基于`.git``.hg`等特殊文件夹来识别当前项目的根目录, 再在这些文件夹内 保存 VIM 配置文件`project_config`, 当 VIM 启动时如果检查到这个文件的存在, 便会自动加载它. 
 


[1]: https://github.com/hiberabyss/ProjectConfig
[0]: https://img2.tuicool.com/aYNVjuZ.png