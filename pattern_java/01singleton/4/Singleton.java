public final class Singleton {
	private static Singleton singleton = null;
	
	//限制产生多个对象
	private Singleton(){
		
	}
	
	//通过该方法获得实例对象
	public synchronized static Singleton getSingleton(){
		if(singleton == null){
			singleton = new Singleton();
		}
		return singleton;		
	}
}

/*
该单例模式在低并发的情况下尚不会出现问题， 若系统压力增大， 并发量增加时则可能
在内存中出现多个实例， 破坏了最初的预期。 为什么会出现这种情况呢？ 如一个线程A执行
到singleton = new Singleton()， 但还没有获得对象（ 对象初始化是需要时间的） ， 第二个线程
B也在执行， 执行到（ singleton == null） 判断， 那么线程B获得判断条件也是为真， 于是继续
运行下去， 线程A获得了一个对象， 线程B也获得了一个对象， 在内存中就出现两个对象！
 */
/**
 * 解决线程不安全的方法很有多， 可以在getSingleton方法前加synchronized关键字， 也可以
在getSingleton方法内增加synchronized来实现， 但都不是最优秀的单例模式， 建议读者使用如
代码清单7-3所示的方式（ 有的书上把代码清单 3 中的单例称为饿汉式单例， 在代码清单 4 
中增加了synchronized的单例称为懒汉式单例） 
 */