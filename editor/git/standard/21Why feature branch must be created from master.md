`发生过程`
=====

### 发生情况
创建feature分支时未从最新mater中开出，而是从当前release分支开出。

### 发生描述
* 在当前release分支查看文件内容；
* 当要创建feature分支时，`未切换到master`；
* 从当前release分支开出feature分支；
* 开发完提交、合并到当天上线release分支。

 
`危害`
=====
1.危害范围
* release分支稳定性
* 上线时间
* 分支图形阅读困难

2.危害认识
#####  1) 影响release分支稳定性
* (1) 如果当前release分支存在功能异常或bug；
* (2) 在此异常release分支创建了feature分支；
* (3) 则基于此feature分支进行的开发，也存在功能异常或bug；
* (4) 当该feature分支合并到release时：
   * 功能异常或bug在release分支线上表现为多处;
   * 危害在release分支向前扩散,`引发release不稳定`；
   * 此时回滚，需在release分支线上最早异常点进行；
   * 操作难度，回滚成本较大。

#####  2) 影响上线时间
* (1) 如果从存在异常的release分支创建feature分支；
* (2) 当feature分支合并到release后；
* (3) release异常，需重新创建release分支；
* (4) 所有当天已合并完feature分支需重新合并；
* (5) 影响整体上线时间。
 
#####  3) 分支图形阅读困难
* (1) 正常分支(master开出)路线方向为master->feature->release；
* (2) 当按以下操作(`不正常`)进行时:
   *  从release创建分支，合并回release；
   *  分支路线方向为release->feature->release；
   *  会造成feature与release有两处交点，形成闭环；
   *  不利于分支图形阅读。

`解决方案`
=====

* 在创建feature分支时，严格按照| :one: - :one:  | [创建分支(功能、发布、紧急修复、集成, etc.)](How-To-Create-Branch) `执行`


`注意`
=====   

* "分支的上游只能是 master, 分支的下游只能是 release"