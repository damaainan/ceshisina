public class Context {

    
    private Calculator cal = null;
    
    public Context(Calculator _cal){
        this.cal = _cal;
    }
    
    public int exec(int a,int b,String symbol){
        return this.cal.exec(a, b);
    }
    
}