class decorator{
	public class person{
	    //属性
	    public void description{
	        System.out.println("一个人");
	    }
	}
	/*
	现在有三个修饰条件： 
	带帽子 
	穿白色T-shirt 
	穿白色鞋子

	好了，你要用这三个条件来修饰这个person。要注意，这里的修饰词是有顺序的。

	现在这个人带上了帽子，要再穿白色T-shirt。你要得到一个

	System.out.println("一个带着帽子，穿白色T-shirt的人");
	1
	1
	怎么办？

	ok，新写一个类继承person。那如果是一个穿白色鞋子的人要穿白色T呢。实际上，如果采用继承的方式，你可能要写3！=6个类

	如果这里有10个修饰条件呢： 10！个类 
	. 
	. 
	. 
	如果这里有n个修饰条件： n！个类

	而装饰者模式

	如果这里有n个修饰条件： n个类

	这就是装饰者模式的用处。
	 */
}