/**
 * 适配器
 */
public class Adapter extends Adaptee implements Target {
    
    public void request() {
        super.doSomething();
    }

}