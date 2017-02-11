import java.util.Random;

public class ObjectStruture {

    //对象生成器，这里通过一个工厂方法模式模拟
    public static Element createElement(){
        Random rand = new Random();
        if(rand.nextInt(100) > 50){
            return new ConcreteElement1();
        }else{
            return new ConcreteElement2();
        }
    }
}