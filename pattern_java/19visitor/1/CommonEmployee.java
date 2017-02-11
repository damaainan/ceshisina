/**
 * 普通员工，也就是最小的小兵
 */
public class CommonEmployee extends Employee {
    
    //工作内容，这个非常重要，以后的职业规划就是靠这个了
    private String job;

    public String getJob() {
        return job;
    }

    public void setJob(String job) {
        this.job = job;
    }
    
    protected String getOtherInfo(){
        return "工作："+ this.job + "\t";
    }
    
}