public class MultiThread extends Thread {
    private SignInfo signInfo;
    public MultiThread(SignInfo _signInfo){
            this.signInfo = _signInfo;

    }
    
    public void run(){
        if(!signInfo.getId().equals(signInfo.getLocation())){
            System.out.println("编号："+signInfo.getId());
            System.out.println("考试地址："+signInfo.getLocation());
            System.out.println("线程不安全了！");
        }
        
    }
}