
public class Client {
    
    public static void main(String[] args) {
        Context context = new Context();
        context.setLiftState(new ClosingState());
        
        context.open();
        context.close();
        context.run();
        context.stop();
        context.open();
    }
}