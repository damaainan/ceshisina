

///融合备忘录的发起人角色
//增加了clone方法，产生了一个备份对象，需要使用的时候再还原
public class Originator implements Cloneable{
    
    //内部状态
    private String state = "";
    
    public String getState() {
        return state;
    }

    public void setState(String state) {
        this.state = state;
    }

    //创建一个备忘录
    public Originator createMemento(){
        return this.clone();
    }
    
    //恢复一个备忘录
    public void restoreMemento(Originator _originator){
        this.setState(_originator.getState());
    }
    
    //克隆当前对象
    @Override
    protected Originator clone(){
        
        try {
            return (Originator)super.clone();
        } catch (CloneNotSupportedException e) {
            e.printStackTrace();
        }
        return null;
    }
}