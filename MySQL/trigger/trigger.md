**Mysql 触发器**

**1. 创建触发器**

**1）创建只有一个执行语句的触发器：**
```
    CREATE  TRIGGER   触发器名   BEFORE | AFTER   触发事件  
                  ON   表名   FOR  EACH  ROW   执行语句
```
FOR EACH ROW表示任何一条记录上的操作满足触发事件都会触发触发器，执行最后的执行语句。

**2）创建有多个执行语句的触发器**
```
    CREATE  TRIGGER   触发器名   BEFORE | AFTER   触发事件  
                  ON   表名   FOR  EACH  ROW    
                  BEGIN  
                        执行语句列表  
                  END
```
**2. 查看触发器**

    SHOW  TRIGGERS;  
    SELECT * FROM  information_schema.triggers;

**3. 使用触发器**

**1）INSERT触发器**

在 INSERT 触发器代码内，可引用一个名为 NEW 的虚拟表，访问被插入的行；

在 BEFORE INSERT 触发器中， NEW 中的值也可以被更新（允许更改被插入的值）；

对于 AUTO_INCREMENT 列， NEW 在 INSERT 执行之前包含 0 ，在 INSERT 执行之后包含新的自动生成值。

**2）UPDATE触发器**

在 UPDATE 触发器代码中，你可以引用一个名为 OLD 的虚拟表访问以前（ UPDATE 语句前）的值，引用一个名为 NEW 的虚拟表访问新更新的值；

在 BEFORE UPDATE 触发器中， NEW 中的值可能也被更新（允许更改将要用于 UPDATE 语句中的值）；

OLD 中的值全都是只读的，不能更新。

**3）DELETE触发器**

在 DELETE 触发器代码内，你可以引用一个名为 OLD 的虚拟表，访问被删除的行；

OLD 中的值全都是只读的，不能更新。

**4. 删除触发器**

    DROP  TRIGGER   触发器名;

