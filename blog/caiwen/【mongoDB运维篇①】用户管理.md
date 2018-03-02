## 【mongoDB运维篇①】用户管理

来源：[https://segmentfault.com/a/1190000004263255](https://segmentfault.com/a/1190000004263255)


## 3.0版本以前

在mongodb3.0版本以前中,有一个admin数据库, 牵涉到服务器配置层面的操作,需要先切换到admin数据库.即 use admin , 相当于进入超级用户管理模式,mongo的用户是以数据库为单位来建立的, 每个数据库有自己的管理员.我们在设置用户时,需要先在admin数据库下建立管理员---这个管理员登陆后,相当于超级管理员.

命令:db.addUser();
简单参数: db.addUser(用户名,密码,是否只读)

注意: 添加用户后,我们再次退出并登陆,发现依然可以直接读数据库?
原因: mongodb服务器启动时, 默认不是需要认证的.
要让用户生效, 需要启动服务器时,就指定`--auth`选项.这样, 操作时,就需要认证了.

```LANG
# 添加用户
> use admin
> db.addUser('admin','admin',false); # 3.0版本更改为createUser();

# 删除用户
> use test
> db.removeUser(用户名); # 3.0版本更改为dropUser();
```
## 3.0版本以后
### 创建管理员

在3.0版本以后,mongodb默认是没有admin这个数据库的,并且创建管理员不再用addUser,而用createUser;
#### 语法说明

```LANG
{ user: "<name>",  
  pwd: "<cleartext password>",
  customData: { <any information> }, # 任意的数据,一般是用于描述用户管理员的信息
  roles: [
    { role: "<role>", db: "<database>" } | "<role>", # 如果是role就是直接指定了角色,并作用于当前的数据库
    ...
  ] # roles是必传项,但是可以指定空数组,为空就是不指定任何权限
}
```

Built-In Roles（[内置角色][0]）：

* 数据库用户角色：read、readWrite;

* 数据库管理角色：dbAdmin、dbOwner、userAdmin；

* 集群管理角色：clusterAdmin、clusterManager、clusterMonitor、hostManager；

* 备份恢复角色：backup、restore；

* 所有数据库角色：readAnyDatabase、readWriteAnyDatabase、userAdminAnyDatabase、dbAdminAnyDatabase

* 超级用户角色：root 
 这里还有几个角色间接或直接提供了系统超级用户的访问（dbOwner 、userAdmin、userAdminAnyDatabase）

* 内部角色：__system
PS：关于每个角色所拥有的操作权限可以点击上面的内置角色链接查看详情。



**`官方Example`** 

```LANG
use products # mongoDB的权限设置是以库为单位的,必选要先选择库
db.createUser( 
{ "user" : "accountAdmin01", 
 "pwd": "cleartext password",
 "customData" : { employeeId: 12345 },
 "roles" : [ { role: "clusterAdmin", db: "admin" }, 
             { role: "readAnyDatabase", db: "admin" },
             "readWrite" 
             ] },
{ w: "majority" , wtimeout: 5000 } ) # readWrite 适用于products库,clusterAdmin与readAnyDatabase角色适用于admin库
```

writeConcern文档（[官方说明][1]）

* w选项：允许的值分别是 1、0、大于1的值、"majority"、<tag set>；

* j选项：确保mongod实例写数据到磁盘上的journal（日志），这可以确保mongd以外关闭不会丢失数据。设置true启用。

* wtimeout：指定一个时间限制,以毫秒为单位。wtimeout只适用于w值大于1。



```LANG
use shop;
db.createUser({
    user:'admin',
    pwd:'zhouzhou123',
    roles:['dbOwner']
})
```

只要新加了一个用户,admin数据库就会重新存在;

```LANG
mongo --host xxx -u admin -p zhouzhou123 --authenticationDatabase shop # 用新创建的用户登录

# 查看当前用户在shop数据库的权限
use shop;
db.runCommand(
  {
    usersInfo:"shopzhouzhou",
    showPrivileges:true
  }
)

# 查看用户信息
db.runCommand({usersInfo:"userName"})

# 创建一个不受访问限制的超级用户
use admin
db.createUser(
  {
    user:"superuser",
    pwd:"pwd",
    roles:["root"]
  }
)
```
### 认证用户

```LANG
> use test
> db.auth(用户名,密码); #注意是以库为单位,必须先选择库;
```
### 删除用户

```LANG
# 删除用户
> use test
> db.dropUser('用户名');
```
### 修改用户密码

```LANG
> use test
> db.changeUserPassword(用户名, 新密码);

# 修改密码和用户信息
db.runCommand(
  {
    updateUser:"username",
    pwd:"xxx",
    customData:{title:"xxx"}
  }
)
```
## 权限规则

参考: [http://blog.csdn.net/kk185800961/article/details/45619863][2]

[0]: http://docs.mongodb.org/manual/reference/built-in-roles/#built-in-roles
[1]: http://docs.mongodb.org/manual/reference/write-concern/
[2]: http://blog.csdn.net/kk185800961/article/details/45619863