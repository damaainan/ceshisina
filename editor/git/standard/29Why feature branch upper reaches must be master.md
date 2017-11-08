`发生过程`
=====

### 发生情况
创建feature分支时未从最新mater中开出，而是从当前release分支开出。

### 发生描述
* feature  A合并到当天的release；
* release上线后，发现feature  A有bug，需要修复；
* 由于release没有合master，从master开出新分支无分支A的代码；
* 从分支release开出feature B
* 开发完提交、合并到当天上线release分支。

 
`危害`
=====
1.危害范围
* release分支稳定性
* 上线时间
* 分支图形阅读困难

2.危害认识
#####  1) 影响release分支稳定性
* 危害1
      * release a处有异常，同学A在release开出分支feature,该feature携带该异常;
      * 同学B在修复异常合并到release b处；
      * 同学A上线feature完成并上线，合并到release，再次把异常merge到release，会覆盖同学B的修复，release会复现异常情况；
* 危害2
      * 目前有release   (简称r1)  ,当前有A,B,C,D,E  5个同学的功能分支merge到r1   ,同学F从r1开启新分支feature
      * 由于r1有问题，问题比较严重，修复成本太大，需要作废r1，创建新上线分支release (简称 r2)
      * 此时同学F的feature功能完成，准备合r2
      * 由于同学F的feature是从r1创建的，r1是有异常情况，所以此时的feature可能会有异常
      * 如果此时feature  merge到r2，可能会导致r2也会出现异常。
      * feature  merge到r2的时候会把同学A,B,C,D,E功能分支一起合并到r2，此时那5个同学并不知道，上线人员也不知道
      * 导致把控不了上线分支的可知性
      * 假如B同学的需求作废，不需要上线，这样会把B同学的功能分支上线


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
* 如果情况紧急，可以让feature再次merge到release，`但是不建议`
* feature是功能分支，merge release之前，应该进行充分的测试，保证无bug,无异常情况。
* 如果feature有异常情况、bug，feature merge release会把异常情况、bug带到release，可能会把bug、异常情况带到线上
* feature分支上线的标准是，无bug，无异常情况，并且得到充分的测试
* feature没有充分测试，有异常情况、bug是达不到上限的标准，不应该merge 到release
* feature应该得到充分测试，保证(`100%没有问题`)才能merge release


`注意`
=====   

* "分支的上游只能是 master, 分支的下游只能是 release"
* "分支上线前应该进行充分的测试"