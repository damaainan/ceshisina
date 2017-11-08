# UMS&WMS 如何修改数据库配置？ #

> 提醒：<br/>
>　　1、此秘籍只针对UMS,WMS线上&测试环境，数据库配置修改。  <br/>
>　　2、记得发邮件哟（邮件抄送相关领导）。

## 一、线上环境配置 ##

>  注意：<br/>
>　a、由于线上环境数据库配置使用项目管理，需要修改项目配置，才会生效。<br/>
>　b、UMS数据库配置对应UMS_CONF项目。<br/>
>　c、WMS数据库配置对应WMS_CONF项目。<br/>

　　**1、找到相关配置管理人员，申请修改（申请人无修改权限时）。** <br/>
　　**2、申请UMS_CONF或WMS_CONF的配置项目权限。** <br/>
　　**3、创建GIT分支进行修改。** <br/>
　　**4、修改UMS_CONF或WMS_CONF项目下的database配置文件 。**<br/>
　　 　(参考路径：E:\www\ums_conf.git\Deploy\prod.ci\database.php，E:\www\wms_conf.git\Deploy\prod.ci\database.php )<br/>
　　**5、修改完成后，采用WALLE工具进行上线。**<br/>

## 二、测试环境配置 ##

>注意：<br/>
>　a、勇敢并果断拒绝非test数据库的配置，单元测试只用test数据库。(目前情况)<br/>
>　b、测试环境配置由于会影响单元测试，修改后需要相关上线群里吼一吼。<br/>
>　c、UMS&WMS数据库配置对应 local.ci\database.php，htdocs\www\application\config\database.php。<br/>
>　　　UMS参考路径：E:\www\ums\Deploy\local.ci\database.php，E:\www\ums\Server\htdocs\www\application\config\database.php。<br/>
>　　　WMS参考路径：E:\www\wms\Deploy\local.ci\database.php，E:\www\wms\Server\htdocs\www\application\config\database.php。<br/>

 　　 **1、使用GIT分支进行修改，修改相关项目local.ci下的 database.php（受GIT版本控制）。**<br/>
 　　 **2、修改Server\htdocs\www\application\config\database.php（不受GIT版本控制）。**<br/>
　 　**3、执行./sync 进行测试。**<br/>
　 　**4、合并分支到当天发布分支，如果不是版本发布日，请创建hotfix分支进行修改。**<br/>

##  三、踩过的坑##
　　 **1、测试环境修改配置后，没有通知发布人员，导致发布人员上线时，单元测试出错。**<br/>
　　 **2、测试环境新增加的配置，不是使用的test数据库，导致test测试库没有数据，单元测试出错。**     