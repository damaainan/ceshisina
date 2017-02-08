
public class ConcreteCommand1 extends Command {
	//也对那个Receiver类进行命令处理
	private Receiver receiver;
	
	//构造函数传递接收者
	public ConcreteCommand1(Receiver _receiver){
		this.receiver = _receiver;
	}
	
	//每个具体的命令都必须实现一个命令
	public void execute() {
		//业务处理
		this.receiver.doSomething();
	}

}
