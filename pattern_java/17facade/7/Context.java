public class Context {
    //委托处理
    private ClassA a = new ClassA();
    private ClassC c = new ClassC();
    //复杂的计算
    public void complexMethod(){
        this.a.doSomethingA();
        this.c.doSomethingC();
    }
}
/*
该封装类的作用就是产生一个业务规则complexMethod，并且它的生存环境是在子系统
内，仅仅依赖两个相关的对象，门面对象通过对它的访问完成一个复杂的业务逻辑
*/