/**
 * 客户就是甲方，给我们钱的一方，是老大
 */
public class Client {
	
	public static void main(String[] args) {		
		//首先客户找到需求组说，过来谈需求，并修改
		System.out.println("-------------客户要求增加一个需求-----------------");
		Group rg = new RequirementGroup();
		//找到需求组
		rg.find();
		//增加一个需求
		rg.add();
		//要求变更计划
		rg.plan();
		//首先客户找到美工组说，过来谈页面，并修改
		System.out.println("----------客户要求删除一个页面--------------");
		Group pg = new PageGroup();
		//找到需求组
		pg.find();
		//删除一项需求
		pg.delete();
		//要求变更计划
		pg.plan();
	}
}