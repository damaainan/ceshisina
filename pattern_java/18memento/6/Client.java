/*
可能要发问了，这和备忘录模式的定义不相符，它定义是“在该对象之外保存这个状
态”，而你却把这个状态保存在了发起人内部。是的，设计模式定义的诞生比Java的出世略
早，它没有想到Java程序是这么有活力，有远见，而且在面向对象的设计中，即使把一个类
封装在另一个类中也是可以做到的，何况一个小小的对象复制，这是它的设计模式完全没有
预见到的，我们把它弥补回来。
*/
/*
考虑一下原型模式深拷贝和浅拷贝的问题，在复杂的场景下它会
让你的程序逻辑异常混乱，出现错误也很难跟踪。因此Clone方式的备忘录模式适用于较简
单的场景。
*/
public class Client {
    
    public static void main(String[] args) {
        //定义发起人
        Originator originator = new Originator();
        //建立初始状态
        originator.setState("初始状态...");
        System.out.println("初始状态是："+originator.getState());
        //建立备份
        originator.createMemento();
        //修改状态
        originator.setState("修改后的状态...");
        System.out.println("修改后状态是："+originator.getState());
        //恢复原有状态
        originator.restoreMemento();
        System.out.println("恢复后状态是："+originator.getState());
    }
}