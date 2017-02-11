
public class LiuSi implements Observer{
    
    //刘斯，观察到韩非子活动后，自己也做一定得事情
    public void update(String str){
        System.out.println("刘斯：观察到韩非子活动，开始动作了...");
        this.happy(str);
        System.out.println("刘斯：真被乐死了\n");
    }
    
    //一看韩非子有变化，他就快乐
    private void happy(String context){
        System.out.println("刘斯：因为" +context+",——所以我快乐呀！" );
    }
}