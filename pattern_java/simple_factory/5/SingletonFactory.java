import java.lang.reflect.Constructor;

public class SingletonFactory {
    private static Singleton singleton;
    static{ 
        try {
            Class cl= Class.forName(Singleton.class.getName());
            //获得无参构造
            Constructor constructor=cl.getDeclaredConstructor();
            //设置无参构造是可访问的
            constructor.setAccessible(true);
            //产生一个实例对象
            singleton = (Singleton)constructor.newInstance();
        } catch (Exception e) {
            //异常处理
        }       
    }
    
    public static Singleton getSingleton(){
        return singleton;   
    }
}