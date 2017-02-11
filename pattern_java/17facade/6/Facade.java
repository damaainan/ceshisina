public class Facade {
    //被委托的对象
    private ClassA a = new ClassA();
    private ClassB b = new ClassB();
    private ClassC c = new ClassC();
    
    //提供给外部访问的方法
    public void methodA(){
        this.a.doSomethingA();
    }
    
    public void methodB(){
        this.b.doSomethingB();
    }
    
    public void methodC(){
        this.a.doSomethingA();
        this.c.doSomethingC();
    }
    /*
在methodC方法中增加了doSomethingA()方法的调用，可以这样做
吗？这样设计是非常不靠谱的，为什么呢？因为你已经让门面对象参与了业务逻辑，门
面对象只是提供一个访问子系统的一个路径而已，它不应该也不能参与具体的业务逻辑，否
则就会产生一个倒依赖的问题：子系统必须依赖门面才能被访问，这是设计上一个严重错
误，不仅违反了单一职责原则，同时也破坏了系统的封装性。
    */
}