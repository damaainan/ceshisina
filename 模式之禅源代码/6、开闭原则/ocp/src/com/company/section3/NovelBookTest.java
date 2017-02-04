package com.company.section3;

import junit.framework.TestCase;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * NovelBook的测试类
 */
public class NovelBookTest extends TestCase {
	private String name = "平凡的世界";
	private int price = 6000;
	private String author = "路遥";
	
	private IBook novelBook = new NovelBook(name,price,author);

	//测试getPrice方法
	public void testGetPrice() {
		//原价销售，判断输入和输出的值是否相等进行断言
		super.assertEquals(this.price, this.novelBook.getPrice());
	}

}
