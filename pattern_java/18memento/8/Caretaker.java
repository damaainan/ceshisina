import java.util.HashMap;

public class Caretaker {
    //容纳备忘录的容器
    private HashMap<String,Memento> memMap = new HashMap<String,Memento>();

    public Memento getMemento(String idx) {
        return memMap.get(idx);
    }

    public void setMemento(String idx,Memento memento) {
        this.memMap.put(idx, memento);
    }
    
}
