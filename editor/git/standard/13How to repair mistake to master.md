`发生过程`
=====
* 直接提交到master
* 未通过测试的分支直接合并到master 

`危害`
=====

* 危害范围
 * 创建新功能分支
 * 日常开发
 * 日常发布
 * 紧急修复

* 范围细节
 * 开发工程师创建新功能new_feature分支，将把不安全代码带到new_feature分支，造成new_feature分支不可用
 * 开发工程师日常开发进行merge master之后，将把不安全代码合到已有分支feature分支，如果在继续开发之前发现，还可以回滚代码，如果未发现继续正常开发，该分支将需要重构，给开发工程师带来很大的工作量
 * 发布工程师日常发布merge master的时候，将把不安全代码合到release分支，如果之后没有开发人员合并到release，还可以回滚代码，如果有开发人员在之后合并了代码，则需要重新创建release重新合并，对开发工程师和发布工程师都会带来额外的工作量
 * 如果此时有人进行紧急修复，将造成紧急修复不可用，需待master修复后重新走紧急修复流程

* 核心认识
 * 将稳定分支master变成不稳定分支，对后续开发、发布都将造成根基不牢的影响
 * 给发布工程师和开发工程师增添额外工作量，如回滚、重新合并、重新修改、提交代码
 * 为团队开发、测试、上线流程造成不必要的麻烦，影响正常开发、测试、上线流程

`解决方案`
=====

## 日常开发合并master，视情况选择以下两种方案

### `方案一` 及时发现，修复成本低

* :loudspeaker: 通知大家不要进行创建新分支，不要合并master

* 如果有人按照 [1-1 创建分支](How-To-Create-Branch)，则删除新创建分支，等master修复后再重新创建

* 如果有人进行 [1-2 日常开发](how-to-merge-master-to-feature-branch)，并合并master到feature分支

 * :loudspeaker: 通知该同学将合并了master的feature分支回滚到合并之前

    ```sh
    $ git checkout feature_branch
    $ git checkout -b feature_branch_2 a2b43f83
    $ git push origin feature_branch_2
    $ git branch -D feature_branch
    ```

 * 确认所有同学没有再应用合并后的分支，对master进行回滚

    ```sh
     检出到master
     将master回滚到正确节点
     推送远端

    此处操作属于 '高风险操作'，仅限管理员按流程操作
    ```
* :loudspeaker: 通知大家master已修复，可以正常走流程了

### `方案二` 发现不及时，已经很多人应用了合并后的master，修复成本很高

 * 开启一个紧急修复分支，对之前的提交或者合并的代码进行修复 (参照 [紧急修复上线流程](how-to-deploy-release-branch))