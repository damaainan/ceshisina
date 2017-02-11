import java.util.Vector;

public class Client {
    
    public static void main(String[] args) {
        for(int i=0;i<10;i++){
            //获得元素对象
            Element el = ObjectStruture.createElement();
            //接受访问者访问
            el.accept(new Visitor());
        }       
    }
}