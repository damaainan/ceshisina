# 合并日志说明

至少包括以下内容

* 原始分支名
* 目标分支名
* 是否发生冲突
* 冲突范围 (文件列表)

# 如果发现冲突，需要手动合并

* 请在本地按照文档 [:one: - :three: - :two: 手动合并](How-To-Merge-Code-For-QA-Testing) 进行操作
* 如果有注释，请保留系统提示部分


# `良好`的合并日志

## ums/ums@c46d528fb

```
commit c46d528fbc0dd00153d5c53f48b8707849061bd7
Merge: a481fc9 681c097e
Author: 渠涛涛 <qutaotao@miyabaobei.com>
Date:   Fri May 6 16:31:09 2016 +0800

    Merge branch 'master' into hotfix-20160506-2
```

## ums/ums@03864a7

```
commit 03864a7e82c52ad7cfcf054cab929ceb80cdf922
Author: 张久通 <zhangjiutong@mia.com>
Date:   Thu May 12 12:01:59 2016 +0800

    Merge branch 'master' into zhangjiutong_update_sku

    # Conflicts:
    #   Server/htdocs/www/application/models/operation/promotion_new_model.php

 create mode 100644 Server/htdocs/www/application/models/operation/promotion_new_model.php
```

## ums/ums@6abf05a

```
commit 6abf05a005f873c761f7fee090cc5a158e4841f0
Merge: c6e8c38 f1d0273
Author: 郑强强 <zhengqiangqiang@miyabaobei.com>
Date:   Wed May 4 16:26:23 2016 +0800

    Merge branch 'master' into zqq_NBOrder_process

    # Conflicts:
    #   Server/htdocs/www/application/config/customer_menu.php
    #   Server/htdocs/www/application/config/ums_permissions.php
    #   Server/htdocs/www/application/models/include/Database/OrderDscrpLogDb.class.php
    #   Server/htdocs/www/application/models/include/Database/OrderExceptionDB.class.php

```

## ums/ums@b3abc8e55

```
commit b3abc8e55558e5abe7b41712ff5c29577a6d6e6c
Merge: b3ecd79 681c097e
Author: xiaohaijie <xiaohaijie@miyabaobei.com>
Date:   Fri May 6 16:13:14 2016 +0800

    Merge remote-tracking branch 'origin/master' into xiaohaijie_contract_add_oem

    # Conflicts:
    #   Server/htdocs/www/application/config/sensitive_words_config.php
    #   Server/htdocs/www/application/views/purchase/plan_target/marketing_plan_target.php
    #   Server/htdocs/www/application/views/purchase/plan_target/operation_plan_target.php
    #   Server/htdocs/www/application/views/purchase/plan_target/production_plan_target.php
    #   Server/htdocs/www/resources/js/special_system/main.js

```


# `不良`的合并日志

## ums/ums@bc2cb2f
```
commit bc2cb2f662129c6d2a1e5654f1a0f3a3205e63d9
Merge: 4240e67 2b34c16
Author: 渠涛涛 <qutaotao@miyabaobei.com>
Date:   Mon May 23 16:41:23 2016 +0800

    合并MASTER

```

## ums/ums@4ae77c1 

```
commit 4ae77c17738dd888c72b82dd5e5e74028b985693
Merge: 2efead8 2269ced
Author: 张博文 <zhangbowen@miyabaobei.com>
Date:   Thu May 12 10:37:43 2016 +0800

    conflict
```

## ums/ums@3a1a39a

```
commit 3a1a39af23d57e90bee6311d5a9edec9f804437e
Merge: 9f4b053 0f7f6cc
Author: xiaohaijie <xiaohaijie@miyabaobei.com>
Date:   Thu May 12 16:32:51 2016 +0800

    提交修改
```

## ums/ums@19d9e2a

```
commit 19d9e2a06c819e3e3cee3ade70755e1e5040d37f
Merge: 60161ce 612aae4
Author: 苏良 <suliang@mia.com>
Date:   Thu May 12 14:48:19 2016 +0800

    merge master

```

## ums/ums@d06df147
```
commit d06df14740d9ed47628d1fb77ed1657592c9554c
Merge: 672f62f 9489968
Author: 张久通 <zhangjiutong@mia.com>
Date:   Tue May 17 14:27:53 2016 +0800

    合并
```

## ums/ums@d51ceab6
```
commit d51ceab624dd56a5c3d758079fb2d386efb373e5
Merge: e5fc747 080f328
Author: wangwei <wangwei1@mia.com>
Date:   Thu Sep 1 16:44:43 2016 +0800

    冲突修改
```

## ums/ums@6bb03d5c
```
commit 6bb03d5cade66b258f7992b1f0c0df391ce4d785
Merge: 10c5280 7f58358
Author: wangwei <wangwei1@mia.com>
Date:   Thu Sep 1 16:19:14 2016 +0800

    冲突解决

```

## ums/ums@b0d4539
```
commit b0d45399c8b0fe93770465a348b123e49e86dc42
Merge: 2c7a8d1 2646443
Author: 赵娜 <zhaona@mia.com>
Date:   Tue Nov 1 11:13:54 2016 +0800

    解决冲突

```

## ums/ums@4d4c285
```
commit 4d4c2859d7e10584de73e041cdb67f99e1d0176f
Merge: ff669a0 e40107e
Author: 杨圆 <yangyuan@mia.com>
Date:   Thu Oct 27 17:33:20 2016 +0800

    活动异步操作脚本

```

## ums/ums@62f78e6
```
commit 62f78e612e8952a6b06f2853b3a42eed874452c4
Merge: 7485301 f65b875
Author: 赵娜 <zhaona@mia.com>
Date:   Wed Oct 26 10:43:52 2016 +0800

    解决冲突

```