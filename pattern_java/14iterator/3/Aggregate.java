/**
 * 容器类
 */
public interface Aggregate {
    
    //是容器必然有元素的增加
    public void add(Object object);
    
    //减少元素
    public void remove(Object object);
    
    //由迭代器来遍历所有的元素
    public Iterator iterator();
}