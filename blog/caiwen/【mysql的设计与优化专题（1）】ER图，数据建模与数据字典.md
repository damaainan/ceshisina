## 【mysql的设计与优化专题(1)】ER图，数据建模与数据字典

来源：[https://segmentfault.com/a/1190000004053409](https://segmentfault.com/a/1190000004053409)

需求分析是做项目中的极为重要的一环,而作为整个项目中的'血液'--数据,更是重中之重。viso，workbench，phpmyadmin等软件可以帮我们更好的处理数据分析问题。

## ER图


![][0]
E-R方法是“实体-联系方法”（Entity-Relationship Approach）的简称。它是描述现实世界概念结构模型的有效方法。是表示概念模型的一种方式， **`用矩形表示实体型，矩形框内写明实体名；用椭圆表示实体的属性，并用无向边将其与相应的实体型连接起来,属性如果有下划线的话,就表示该属性为主键属性；用菱形表示实体型之间的联系，在菱形框内写明联系名(实体和实体之间的关系)，并用无向边分别与有关实体型连接起来，同时在无向边旁标上联系的类型（1:1,1:n或m:n）`** 


![][1]
### 实体之间联系

联系可分为以下 3 种类型：
(1) 一对一联系(1 ∶1)
例如，一个部门有一个经理，而每个经理只在一个部门任职，则部门与经理的联系是一对一的。
(2) 一对多联系(1 ∶N)
例如，某校教师与课程之间存在一对多的联系“教”，即每位教师可以教多门课程，但是每门课程只能由一位教师来教
(3) 多对多联系(M ∶N)
例如，图1表示学生与课程间的联系(“学”)是多对多的，即一个学生可以学多门课程，而每门课程可以有多个学生来学。联系也可能有属性。例如，学生“ 学” 某门课程所取得的成绩，既不是学生的属性也不是课程的属性。由于“成绩” 既依赖于某名特定的学生又依赖于某门特定的课程，所以它是学生与课程之间的联系“学”的属性。

推荐使 **`亿图图示专家或viso`**  来画ER图

## 数据建模


![][2]

使用workbench软件可以很方便的建立数据模型,当然workbench不仅仅可以用来建模,还可以用来管理数据库.但通常我们只用来建模,管理数据库用navcate等更为方便的工具;
软件很简单,只不过是英文版本的,貌似市面上还没有出现中文版的,其实软件能用英文版的尽量使用英文版的

### 简单的图示使用说明


![][3]


![][4]


![][5]


![][6]


![][7]


![][8]


![][9]
#### 中文乱码的问题


![][10]
### 数据字典

在使用数据字典前,要保证sql的注释务必要详情

```LANG
CREATE TABLE `sc_role` (
  `id` smallint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL DEFAULT '角色名称',
  `parentid` smallint(6) NOT NULL COMMENT '父角色ID',
  `status` tinyint(1) unsigned NOT NULL COMMENT '状态',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `create_time` int(11) unsigned NOT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned NOT NULL COMMENT '更新时间',
  `listorder` int(3) NOT NULL DEFAULT '0' COMMENT '排序字段',
  PRIMARY KEY (`id`),
  KEY `parentId` (`parentid`),
  KEY `status` (`status`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='角色信息列表';
```

然后可以通过phpmyadmin来导出数据字典
phpAdmin是一个用php语言写的B/S架构,其配置文件在其应用的根目录config.inc.php;在该文件中可以设置数据库链接的一些信息


![][11]

ER图,数据模型,数据字典是对分析数据结构和维护数据库非常有帮助的,千万不要怕麻烦

[0]: http://images0.cnblogs.com/blog2015/487276/201505/181929117298239.jpg
[1]: http://images0.cnblogs.com/blog2015/487276/201505/182132486197443.png
[2]: http://images0.cnblogs.com/blog2015/487276/201505/190926015419954.png
[3]: http://images0.cnblogs.com/blog2015/487276/201505/190930499797139.png
[4]: http://images0.cnblogs.com/blog2015/487276/201505/190930570252359.png
[5]: http://images0.cnblogs.com/blog2015/487276/201505/190931041827079.png
[6]: http://images0.cnblogs.com/blog2015/487276/201505/190931094329482.png
[7]: http://images0.cnblogs.com/blog2015/487276/201505/190931158222101.png
[8]: http://images0.cnblogs.com/blog2015/487276/201505/190931219791649.png
[9]: http://images0.cnblogs.com/blog2015/487276/201505/190931279477438.png
[10]: http://static.zybuluo.com/a5635268/rxwl36lc0mnhojdpthr4w4e8/1348760556_1222.png
[11]: http://images0.cnblogs.com/blog2015/487276/201505/190954064631808.png