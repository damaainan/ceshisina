# [PHPUnit-断言][0]

### 1. 布尔类型

assertTrue 断言为真  
assertFalse 断言为假

### 2. NULL类型

assertNull 断言为NULL  
assertNotNull 断言非NULL

### 3. 数字类型

assertEquals 断言等于  
assertNotEquals 断言不等于  
assertGreaterThan 断言大于  
assertGreaterThanOrEqual 断言大于等于  
assertLessThan 断言小于  
assertLessThanOrEqual 断言小于等于

### 4. 字符类型

assertEquals 断言等于  
assertNotEquals 断言不等于  
assertContains 断言包含  
assertNotContains 断言不包含  
assertContainsOnly 断言只包含  
assertNotContainsOnly 断言不只包含

### 5. 数组类型

assertEquals 断言等于  
assertNotEquals 断言不等于  
assertArrayHasKey 断言有键  
assertArrayNotHasKey 断言没有键  
assertContains 断言包含  
assertNotContains 断言不包含  
assertContainsOnly 断言只包含  
assertNotContainsOnly 断言不只包含

### 6. 对象类型

assertAttributeContains 断言属性包含  
assertAttributeContainsOnly 断言属性只包含  
assertAttributeEquals 断言属性等于  
assertAttributeGreaterThan 断言属性大于  
assertAttributeGreaterThanOrEqual 断言属性大于等于  
assertAttributeLessThan 断言属性小于  
assertAttributeLessThanOrEqual 断言属性小于等于  
assertAttributeNotContains 断言不包含  
assertAttributeNotContainsOnly 断言属性不只包含  
assertAttributeNotEquals 断言属性不等于  
assertAttributeNotSame 断言属性不相同  
assertAttributeSame 断言属性相同  
assertSame 断言类型和值都相同  
assertNotSame 断言类型或值不相同  
assertObjectHasAttribute 断言对象有某属性  
assertObjectNotHasAttribute 断言对象没有某属性

### 7. class类型

class类型包含对象类型的所有断言，还有  
assertClassHasAttribute 断言类有某属性  
assertClassHasStaticAttribute 断言类有某静态属性  
assertClassNotHasAttribute 断言类没有某属性  
assertClassNotHasStaticAttribute 断言类没有某静态属性

### 8. 文件相关

assertFileEquals 断言文件内容等于  
assertFileExists 断言文件存在  
assertFileNotEquals 断言文件内容不等于  
assertFileNotExists 断言文件不存在

### 9. XML相关

assertXmlFileEqualsXmlFile 断言XML文件内容相等  
assertXmlFileNotEqualsXmlFile 断言XML文件内容不相等  
assertXmlStringEqualsXmlFile 断言XML字符串等于XML文件内容  
assertXmlStringEqualsXmlString 断言XML字符串相等  
assertXmlStringNotEqualsXmlFile 断言XML字符串不等于XML文件内容  
assertXmlStringNotEqualsXmlString 断言XML字符串不相等

[0]: http://www.cnblogs.com/bndong/p/6633766.html