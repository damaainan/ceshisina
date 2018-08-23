## 原创 死磕GOF23之单例模式

来源：[http://www.choupangxia.com/topic/detail/66](http://www.choupangxia.com/topic/detail/66)

时间 2018-08-23 12:17:11

 
无论什么开发语言，设计模式在软件开发的过程中是必不可少的，它是软件开发过程中常见问题的成熟解决方案，也是最佳实践。本系列课程带领大家领略一下GOF23种设计模式。
 
## 什么是GOF23 
 
Erich Gamma、Richard Helm、Ralph Johnson和John Vlissides 四人合著出版了一本名为Design Patterns - Elements of Reusable Object-Oriented Software（中文译名：设计模式 - 可复用的面向对象软件元素）的书，该书首次提到了软件开发中设计模式的概念。
 
四位作者合称 GOF（四人帮，全拼 Gang of Four）。书中共提到23中设计模式，因此也称GOF23种设计模式。
 
## 设计模式分类 
 
GOF23种设计模式可分为以下三类：

 
* 创建型模式（Creational Patterns）：关注对象的实例化，不是使用new创建对象，而是隐藏创建对象的逻辑，只提供对应的方法； 
* 结构型模式（Structural Patterns）：关注类与对象的组合，从而获得新的能力； 
* 行为型模式（Behavioral Patterns）：关注对象之间的通信；
## 什么是单例模式 
 单例模式（Singleton Patterns），当在程序中需要保证某个类只存在一个实例时，往往就会用到单例设计模式。单例模式能够避免实例对象重复创建，减少创建对象的时间开销和内存的存储开销；能够避免由于操作多个实例导致的逻辑错误。  
 
 
## 单例模式特点 

 
* 单例类只能有一个实例； 
* 单例类只能自己创建自己的唯一实例； 
* 单例类必须提供给其他类获得此实例的方法； 
 
 
## 单例模式VS静态类 
 
单例模式和静态类有很多相似之处，那单例模式的优势是什么呢？
 
单例模式相对静态类的优势：

 
* 面向对象编程，可继承、可实现接口，而静态类无法做到； 
* 单例模式可被延迟加载（懒汉模式），静态类一般在第一次加载时初始化； 
* 单例类可以被集成，方法可以被覆盖； 
* 静态方法中产生的对象会在执行后被释放，进而被GC清理，不会一直存在内存中； 
* 单例模式可以附带更多的其他信息，比如读取的配置信息； 
 
 
那么，数据库链接是否能够做成单例模式呢？这里分两种情况：简单封装Connection对象和数据库连接池。
 
试想一下，如果简单封装一个Connection连接，做成单例模式，那么整个应用程序只能够使用一个数据库连接，那会死的很惨！但数据库连接池可以使用单例模式，初始化时创建指定数量的数据库连接，使用单例模式来保证只有一个对象来创建和初始化连接池。
 
## 单例模式的实现方式 
 
### 饿汉模式 

```java
package com.secbro2.gof23.singleton;

/**
 * Hungry Singleton Patterns
 *
 * @author zzs
 */
public class HungrySingleton {

    private static HungrySingleton instance = new HungrySingleton();

    private HungrySingleton() {
    }

    public static HungrySingleton getInstance() {
        return instance;
    }

    public void helloSingleton() {
        System.out.println("Hello HungrySingleton!");
    }
}
```
 
饿汉模式，类初始化时即创建对应的对象实例，保证了线程的安全性，然后提供一个静态的方法返回实例对象。但饿汉模式实例存在于整个程序的声明周期，即使单例未被使用到也会被创建，会造成一定的内存资源浪费。
 
总结一下单例模式的基本实现：第一，单例类构造参数私有化，除了单例类自身其他类不可以直接创建该类的对象；第二，提供一个私有的static变量；第三、提供获取该类实例的public的方法；
 
### 懒汉模式 
 
懒汉模式，当需要的时候才会创建对应的实例，避免内存浪费。如果某个单例使用的次数较少，并且创建单例消耗的资源较多，那么就需要按需创建，适合懒汉模式。懒汉模式分两种：线程不安全的和线程安全的。
 
先来看一下最简单的懒汉模式实现：

```java
package com.secbro2.gof23.singleton;

/**
 * Singleton Patterns

 ** Not Thread Safe;
 *
 * @author zzs
 */
public class Singleton {

    private static Singleton instance;

    private Singleton() {
    }

    public static Singleton getInstance() {
        if (instance == null) {
            instance = new Singleton();
        }

        return instance;
    }

    public void helloSingleton() {
        System.out.println("Hello Singleton!");
    }
}
```
 
上面代码实现了一个线程不安全的单例模式。
 
下面，通过添加synchronized来保证在多线程的情况下也可以正常工作。

```java
package com.secbro2.gof23.singleton;

/**
 * Singleton Patterns

 ** Thread Safe;
 *
 * @author zzs
 */
public class SingletonThreadSafe {

    private static SingletonThreadSafe instance;

    private SingletonThreadSafe() {
    }

    public static synchronized SingletonThreadSafe getInstance() {
        if (instance == null) {
            instance = new SingletonThreadSafe();
        }

        return instance;
    }

    public void helloSingleton() {
        System.out.println("Hello SingletonThreadSafe!");
    }
}
```
 
### 双重校验锁 
 
上面在获取方法上使用了synchronized关键字，将整个方法变为同步的，但如果此实例获取的次数较多，此处可能会出现瓶颈。因此，针对此项也进行进一步的优化，这便有了双重校验锁。

```java
package com.secbro2.gof23.singleton;

/**
 * Singleton Patterns

 ** Double checked lock;
 *
 * @author zzs
 */
public class SingletonThreadSafe1 {

    private static SingletonThreadSafe1 instance;

    private SingletonThreadSafe1() {
    }

    public static SingletonThreadSafe1 getInstance() {
        if (instance == null) {
            synchronized (SingletonThreadSafe1.class) {
                if (instance == null) {
                    instance = new SingletonThreadSafe1();
                }
            }
        }

        return instance;
    }

    public void helloSingleton() {
        System.out.println("Hello SingletonThreadSafe1!");
    }
}
```
 
将synchronized范围缩小到创建对象的代码，同时在同步代码外面多了一层instance为空的判断。这样既在创建时保证了线程的安全性又可以在后面使用中直接返回已经创建的对象，无需每次都对整个方法进行同步处理。双重校验锁即实现了延迟加载，又解决了线程并发问题，同时还解决了执行效率问题。
 
虽然上面的方式解决了效率问题，但又引入了其他的问题。此问题是因为JVM指令的重排优化导致的。在java中看似按照顺序执行的代码，在JVM中可能会出现编译器或者CPU对操作指令进行重新排序。
 
上面synchronized中处理如果按照代码顺序执行应该是这样：

 
* 分配内存空间； 
* 初始化对象； 
* 将对象指向刚分配的内存空间； 
 
 
但如果编译器为了性能的原因可能会将第二步和第三步进行重排：

 
* 分配内存空间； 
* 将对象指向刚分配的内存空间； 
* 初始化对象； 
 
 
这样在高并发的情况下会导致后面的线程判断instance并不为null，但又不是预期的对象。那么如何解决指令重排问题呢？在JDK1.5及以后版本引入了volitile关键字。volatile的一个语义是禁止指令重排序优化，这就保证了instance变量被赋值的时候对象已经是初始化过的，从而避免了上面说到的问题。代码如下：

```java
package com.secbro2.gof23.singleton;

/**
 * Singleton Patterns

 ** Double checked lock and volatile;
 *
 * @author zzs
 */
public class SingletonThreadSafe2 {

    private static volatile SingletonThreadSafe2 instance;

    private SingletonThreadSafe2() {}

    public static SingletonThreadSafe2 getInstance() {
        if (instance == null) {
            synchronized (SingletonThreadSafe2.class) {
                if (instance == null) {
                    instance = new SingletonThreadSafe2();
                }
            }
        }

        return instance;
    }

    public void helloSingleton() {
        System.out.println("Hello SingletonThreadSafe1!");
    }
}
```
 
### 静态内部类 
 
首先看一下代码实现：

```java
package com.secbro2.gof23.singleton;

/**
 * Singleton Patterns

 ** Inner Class Singleton;
 *
 * @author zzs
 */
public class InnerClassSingleton {

    private InnerClassSingleton() {
    }

    private static class InnerClassSingletonHolder {
        public static InnerClassSingleton instance = new InnerClassSingleton();
    }

    public static InnerClassSingleton getInstance() {
        return InnerClassSingletonHolder.instance;
    }

    public void helloSingleton() {
        System.out.println("Hello Singleton!");
    }
}
```
 
这种方式同样利用了类加载机制来保证只创建一个instance实例，不存在多线程并发问题。利用内部类去创建对象实例，只要不使用内部类，JVM就不会加载此类，因此也就不会去创建对象，从而达到延迟加载的目的。
 
### 枚举 
 
通过枚举实现单例模式，这是Effective Java作者Josh Bloch提倡的方式，不仅能避免线多线程同步问题，还自动支持序列化机制，防止反序列化重新创建新的对象，绝对防止多次实例化。支持JDK1.5及以后版本。

```java
package com.secbro2.gof23.singleton;

/**
 * Enum Singleton Patterns
 *
 * @author zzs
 */
public enum EnumSingleton {

    INSTANCE;

    public void helloSingleton() {
        System.out.println("Hello HungrySingleton!");
    }
}
```
 
单元测试方法：

```java
@Test
public void testEnumSingleton(){
    EnumSingleton enumSingleton = EnumSingleton.INSTANCE;
    enumSingleton.helloSingleton();
}
```
 
### 小结 
 
上面介绍了这么多单例模式的实现，那么在具体实践中如何选择呢。如果没有特殊要求，一般情况下可采用饿汉模式，如果需要懒加载，则可使用双重校验锁和静态内部类的方式。当然也可以尝试使用枚举的方式。
 
## Spring单例模式实践 
 
下面我们在Spring的源代码中寻找一下单例模式的应用实践。在Spring中spring-bean 4.3.13中AbstractBeanFactory类里面提供了一个getSingleton方法，源代码如下：

```java
/**
     * Return the (raw) singleton object registered under the given name.
     *Checks already instantiated singletons and also allows for an early
     * reference to a currently created singleton (resolving a circular reference).
     * @param beanName the name of the bean to look for
     * @param allowEarlyReference whether early references should be created or not
     * @return the registered singleton object, or {@code null} if none found
     */
    protected Object getSingleton(String beanName, boolean allowEarlyReference) {
        Object singletonObject = this.singletonObjects.get(beanName);
        if (singletonObject == null && isSingletonCurrentlyInCreation(beanName)) {
            synchronized (this.singletonObjects) {
                singletonObject = this.earlySingletonObjects.get(beanName);
                if (singletonObject == null && allowEarlyReference) {
                    ObjectFactory<?> singletonFactory = this.singletonFactories.get(beanName);
                    if (singletonFactory != null) {
                        singletonObject = singletonFactory.getObject();
                        this.earlySingletonObjects.put(beanName, singletonObject);
                        this.singletonFactories.remove(beanName);
                    }
                }
            }
        }
        return (singletonObject != NULL_OBJECT ? singletonObject : null);
    }
```
 
对照我们上面所讲的几种单例模式，很显然，Spring采用了双重校验锁的模式。先从缓存（ConcurrentHashMap）中获取bean实例，如果为null则对map进行加锁，然后再次从缓存中获取bean实例，如果仍然为null，则进行创建。
 
## 总结 
 
今天这篇文章带领大家学习了单例模式的集中应用场景及使用案例。源代码可访问GitHub： [https://github.com/secbr/gof23][1] ,随后会继续为大家带来更多的设计模式及实践案例。
 
### 关注微信公众 
 
  
更多技术、架构、管理等知识分享，请关注微信公众号：程序新视界（ID：ershixiong_see_world）


[1]: https://github.com/secbr/gof23
