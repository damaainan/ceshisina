## (PHP7内核剖析-8) 类

来源：[https://segmentfault.com/a/1190000014336209](https://segmentfault.com/a/1190000014336209)

 **`1.类的结构`** 

类是编译阶段的产物，而对象是运行时产生的，它们归属于不同阶段。编译完成后我们定义的每个类都会生成一个zend_class_entry，它保存着类的全部信息，在执行阶段所有类相关的操作都是用的这个结构,
```c
struct _zend_class_entry {
    char type;          //类的类型：内部类ZEND_INTERNAL_CLASS(1)、用户自定义类ZEND_USER_CLASS(2)
    zend_string *name;  //类名，PHP类不区分大小写，统一为小写
    struct _zend_class_entry *parent; //父类
    uint32_t ce_flags;  //类掩码，如普通类、抽象类、接口，

    int default_properties_count;        //普通属性数，包括public、private
    int default_static_members_count;    //静态属性数，static
    HashTable properties_info; //成员属性基本信息哈希表，key为成员名，value为zend_property_info
    zval *default_properties_table;      //普通属性值数组
    zval *default_static_members_table;  //静态属性值数组
    HashTable function_table;  //成员方法哈希表
    HashTable constants_table; //常量哈希表，通过constant定义的

    //以下是构造函授、析构函数、魔术方法的指针
    union _zend_function *constructor;
    union _zend_function *destructor;
    union _zend_function *clone;
    union _zend_function *__get;
    union _zend_function *__set;
    union _zend_function *__unset;
    union _zend_function *__isset;
    union _zend_function *__call;
    union _zend_function *__callstatic;
    union _zend_function *__tostring;
    union _zend_function *__debugInfo;
    union _zend_function *serialize_func;
    union _zend_function *unserialize_func;
}
```

类的编译:首先为类分配一个zend_class_entry结构，如果没有继承类则生成一条类声明的opcode(ZEND_DECLARE_CLASS)，有继承类则生成两条opcode(ZEND_FETCH_CLASS、ZEND_DECLARE_INHERITED_CLASS)，然后再继续编译常量、成员属性、成员方法注册到zend_class_entry中，最后编译完成后调用zend_do_early_binding()进行 父子类关联 以及 注册到EG(class_table)符号表。

 **`2.类常量`** 

PHP中可以把在类中始终保持不变的值定义为常量，在定义和使用常量的时候不需要使用 $ 符号，常量的值必须是一个定值,它们通过zend_class_entry.constants_table进行存储，这是一个哈希结构
```c
//常量的读取:

class my_class {
    const A1 = "hi";
}
echo my_class::A1;

//编译到echo my_class::A1这行时首先会尝试检索下是否已经编译了my_class，如果能在CG(class_table)中找到，则进一步从类的contants_table查找对应的常量，找到的话则会复制其value替换常量，简单的讲就是类似C语言中的宏，编译时替换为实际的值了，而不是在运行时再去检索。

echo my_class::A1;

class my_class {
    const A1 = "hi";
}

//在运行时再去检索。替换成为实际的值
```


 **`3.成员属性`** 

属性中的变量可以初始化，但是初始化的值必须是常数，这里的常数是指PHP脚本在编译阶段时就可以得到其值，而不依赖于运行时的信息才能求值，比如public $time = time();这样定义一个属性就会触发语法错误。成员属性又分为两类：普通属性、静态属性,与常量的存储方式不同，成员属性的初始化值并不是直接用以"属性名"作为索引的哈希表存储的，而是通过数组保存的


![][0]

实际只是成员属性的VALUE通过数组存储的，访问时仍然是根据以"属性名"为索引的散列表查找具体VALUE的,而这个散列表是zend_class_entry.properties_info
```c
typedef struct _zend_property_info {
    uint32_t offset; //普通成员变量的内存偏移值,静态成员变量的数组索引
    uint32_t flags;  //属性掩码，如public、private、protected及是否为静态属性
    zend_string *name; //属性名:并不是原始属性名,private会在原始属性名前加上类名，protected则会加上*作为前缀
    zend_class_entry *ce; //所属类
} zend_property_info;
```

![][1]

成员属性在类编译阶段就已经分配了zval，静态与普通的区别在于普通属性在创建一个对象时还会重新分配zval,对象对普通属性的操作都是在其自己的空间进行的，各对象隔离，而静态属性的操作始终是在类的空间内，各对象共享。

 **`4.成员方法`** 

每个类可以定义若干属于本类的函数(称之为成员方法)，这种函数与普通的function相同，只是以类的维度进行管理，不是全局性的，所以成员方法保存在类中而不是EG(function_table)
![][2]

成员方法也有静态、非静态之分，静态方法中不能使用$this，因为其操作的作用域全部都是类的而不是对象的，而非静态方法中可以通过$this访问属于本对象的成员属性

 **`5.对象的数据结构`** 

```c
typedef struct _zend_object     zend_object;

struct _zend_object {
    zend_refcounted_h gc; //引用计数
    uint32_t          handle; //对象编号
    zend_class_entry *ce; //所属类
    const zend_object_handlers *handlers; //对象操作处理函数
    HashTable        *properties; //普通成员属性哈希表,用于动态属性
    zval              properties_table[1]; //普通属性值数组
};
```

对象的创建:首先是根据类名在EG(class_table)中查找对应zend_class_entry、然后是创建并初始化一个对象、最后是初始化调用构造函数的zend_execute_data
```
实例化一个对象:
step1: 首先根据类名去EG(class_table)中找到具体的类，即zend_class_entry
step2: 分配zend_object结构，一起分配的还有普通非静态属性值的内存
step3: 初始化对象的非静态属性，将属性值从zend_class_entry浅复制(写时分离)到对象中
step4: 查找当前类是否定义了构造函数，如果没有定义则跳过执行构造函数的opcode，否则为调用构造函数的执行进行一些准备工作(分配zend_execute_data)
step5: 实例化完成，返回新实例化的对象(如果返回的对象没有变量使用则直接释放掉了)
```


 **`6.继承`** 

(a).继承属性

属性从父类复制到子类 。子类会将父类的公共、受保护的属性值数组全部合并到子类中，然后将全部属性的zend_property_info哈希表也合并到子类中
![][3]

(b).继承常量

常量的合并策略比较简单，如果父类与子类冲突时用子类的，不冲突时则将父类的常量合并到子类。
(c).继承方法

与属性一样，子类可以继承父类的公有、受保护的方法，方法的继承比较复杂，因为会有访问控制、抽象类、接口、Trait等多种限制条件。实现上与前面几种相同，即父类的function_table合并到子类的function_table中。
如果父类是用户自定义的类，且继承的方法没有静态变量则不会硬拷贝，而是增加zend_function的引用计数(zend_op_array.refcount)。
```
子类重写了父类的方法的检查规则
(1)抽象子类的抽象方法与抽象父类的抽象方法冲突: 无法重写，Fatal错误。
(2)父类方法为final: Fatal错误，final成员方法不得被重写。
(3)父子类方法静态属性不一致: 父类方法为非静态而子类的是静态(或相反)，Fatal错误。
(4)抽象子类的抽象方法覆盖父类非抽象方法: Fatal错误。
(5)子类方法限制父类方法访问权限: Fatal错误，不允许派生类限制父类方法的访问权限，如父类方法为public，
而子类试图重写为protected/private。
6)剩余检查情况: 除了上面5中情形下无法重写方法，剩下还有一步对函数参数的检查
```


 **`7. 动态属性`** 

```c
class my_class {
    public $id = 123;
    public function test($name, $value){
        $this->$name = $value;
    }
}
```

非静态成员属性值在实例化时保存到了对象中，属性的操作按照编译时按顺序编好的序号操作，各对象对其非静态成员属性的操作互不干扰，而动态属性是在运行时创建的，动态创建的属性保存在zend_object->properties哈希表中
属性查找:首先按照普通属性在zend_class_entry.properties_info找，没有找到再去zend_object->properties继续查找
首次创建动态属性将通过rebuild_object_properties()初始化zend_object->properties哈希表，后面再创建动态属性直接插入此哈希表，rebuild_object_properties()过程并不仅仅是创建一个HashTable，还会将普通成员属性值插入到这个数组中，与动态属性不同，这里的插入并不是增加原zend_value的refcount，而是创建了一个IS_INDIRECT类型的zval，指向原属性值zval
![][4]

[0]: ./img/bV8jxP.png
[1]: ./img/bV8tpc.png
[2]: ./img/bV8jDs.png
[3]: ./img/bV8ttD.png
[4]: ./img/bV8tw8.png