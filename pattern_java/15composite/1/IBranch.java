import java.util.ArrayList;

/**
 * 树枝节点，也就是各个部门经理和组长的角色
 */
public interface IBranch {
    
    //获得信息
    public String getInfo();
    
    //增加数据节点，例如研发部下的研发一组
    public void add(IBranch branch);
    
    //增加叶子节点
    public void add(ILeaf leaf);
    
    //获得下级信息
    public ArrayList getSubordinateInfo();
}