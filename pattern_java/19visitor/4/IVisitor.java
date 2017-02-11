public interface IVisitor {
    
    //首先定义我可以访问普通员工
    public void visit(CommonEmployee commonEmployee);
    
    //其次定义，我还可以访问部门经理
    public void visit(Manager manager);
    
    //统计所有员工工资总和
    public int getTotalSalary();
}