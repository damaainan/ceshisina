public class Facade {
    //被委托的对象
    private ClassA a = new ClassA();
    private ClassB b = new ClassB();
    private Context context = new Context();
    
    //提供给外部访问的方法
    public void methodA(){
        this.a.doSomethingA();
    }
    
    public void methodB(){
        this.b.doSomethingB();
    }
    
    public void methodC(){
        this.context.complexMethod();
    }
}
/*
通过这样一次封装后，门面对象又不参与业务逻辑了，在门面模式中，门面角色应该是
稳定，它不应该经常变化，一个系统一旦投入运行它就不应该被改变，它是一个系统对外的
接口，你变来变去还怎么保证其他模块的稳定运行呢？但是，业务逻辑是会经常变化的，我
们已经把它的变化封装在子系统内部，无论你如何变化，对外界的访问者来说，都还是同一
个门面，同样的方法——这才是架构师最希望看到的结构。
*/