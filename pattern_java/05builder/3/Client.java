public class Client {

	public static void main(String[] args) {
		Director director = new Director();
		
		//1W辆A类型的奔驰车
		for(int i=0;i<10;i++){
			director.getABenzModel().run();
		}
		
		//100W辆B类型的奔驰车
		for(int i=0;i<10;i++){
			director.getBBenzModel().run();
		}
		
		//1000W量C类型的宝马车
		for(int i=0;i<10;i++){
			director.getCBMWModel().run();
		}
	}

}