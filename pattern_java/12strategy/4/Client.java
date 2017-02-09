public class Client {
    
    public static void main(String[] args) {
        //输入的两个参数是数字
        int a = Integer.parseInt(args[0]);
        String symbol = args[1];  //符号
        int b = Integer.parseInt(args[2]);
        //生成一个运算器
        Calculator cal = new Calculator();
        System.out.println(a + symbol + b + "=" + cal.exec(a, b, symbol));
        
    }
}