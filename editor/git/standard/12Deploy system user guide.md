# 部署系统使用说明

## 前置条件
 * 不符合`任意一条`前置条件，`均不能`使用部署系统
 * 包含但`不限于`以下条件
 * 所有代码入版本库
  * 编码规范
  * 部署配置
  * 部署脚本
  * 运维脚本
  * crontab
  * schema
  * 运行源代码
  * 代码生成器
 * `禁止`登录服务器，接触物理文件
 * 遵守开发规范
 * 单元测试覆盖、全体通过
 * 持续集成服务

## 错误处理
 * 有错误，立刻`停下后续流程`，解决问题后才能继续流程;
 * 请尝试`重新执行`
 * 如果还有问题，请记录上下文，并查看日志，`尝试解决`
 * 如果无法解决，请`联系管理员`

## 权限说明

 | 管理员 | 审核员 | 开发者
---- | ---- | ---- | ----
**用户** |  |  |
注册用户 | :negative_squared_cross_mark: | :negative_squared_cross_mark: | :white_check_mark:
用户权限管理 | :white_check_mark: | :negative_squared_cross_mark: | :negative_squared_cross_mark:
**项目** |  |  |
新建项目 | :white_check_mark: | :negative_squared_cross_mark: | :negative_squared_cross_mark:
修改项目 | :white_check_mark: | :negative_squared_cross_mark: | :negative_squared_cross_mark:
删除项目 | :negative_squared_cross_mark: | :negative_squared_cross_mark: | :negative_squared_cross_mark:
项目成员管理 | :white_check_mark: | :negative_squared_cross_mark: | :negative_squared_cross_mark:
**上线单** |  |  |
提交上线单 | :negative_squared_cross_mark: | :negative_squared_cross_mark: | :white_check_mark:
审核上线单 | :negative_squared_cross_mark: | :white_check_mark: | :negative_squared_cross_mark:
部署上线单 | :negative_squared_cross_mark: | :negative_squared_cross_mark: | :white_check_mark:
回滚上线单 | :negative_squared_cross_mark: | :white_check_mark: | :white_check_mark:

## 执行过程
 * 用户注册，默认为`开发者`
 * 项目的用户权限管理，由管理员进行
 * 所有人均可创建上线单
 * 所有上线单，禁止`创建者`审核确认
 * 所有上线单，必须经过`第三方`审核确认
  * 确认内容：
  * 上线单`标题`与`分支名`一致
  * 分支名为合法分支名,`release-xxx` 或 `hotfix-xxx`
  * `版本号`必须一致: `上线群通知`和`部署系统`的版本号必须一致
  * 全量上线
 * 部署前准备任务pre-deploy（前置检查）
 * 代码检出后处理任务post-deploy（如vendor）
  * 编译脚本
  * 生成版本号等相关信息
 * 同步后更新软链前置任务pre-release
 * 发布完毕后收尾任务post-release（如重启）

## 后置条件
 * `发布工程师`登陆并访问相关系统，确认功能正常
 * `第三方`确认相关系统`使用分支`、`版本号`和`发布时间`，和部署系统一致
 * 登陆splunk系统，观察相关错误日志情况，至少持续10分钟
 * 通知`开发工程师` 验证相关功能