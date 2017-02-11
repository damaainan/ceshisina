public class ConcreteSubject extends Subject {
    
    //具体的业务
    public void doSomething(){
        /*
         * do something
         */
        super.notifyObserver();
    }
}