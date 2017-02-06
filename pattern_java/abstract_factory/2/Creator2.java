/**
 * 工厂2，只生产跳线为2的产品
 */
public class Creator2 extends AbstractCreator {
    
    //只生产产品等级为2的A产品
    public AbstractProductA createProductA() {  
        return new ProductA2();
    }

    //只生产铲平等级为2的B产品
    public AbstractProductB createProductB() {
        return new ProductB2();
    }

}
