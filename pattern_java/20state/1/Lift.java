public class Lift implements ILift {

    //电梯门关闭
    public void close() {
        System.out.println("电梯门关闭...");
    }
        
    //电梯门开启
    public void open() {
        System.out.println("电梯门开启...");
    }
    
    //电梯开始跑起来
    public void run() {
        System.out.println("电梯上下跑起来...");

    }

    //电梯停止
    public void stop() {
        System.out.println("电梯停止了...");
    }
}
    