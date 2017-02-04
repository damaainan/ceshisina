package com.company.section3;

import junit.framework.TestCase;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 */
public class OffNovelBookTest extends TestCase {
	
	private IBook below40NovelBook = new OffNovelBook("平凡的世界",3000,"路遥");
	private IBook above40NovelBook = new OffNovelBook("平凡的世界",6000,"路遥");
	
	//测试低于40元的数据是否是打8折
	public void testGetPriceBelow40() {
		super.assertEquals(2400, this.below40NovelBook.getPrice());
	}
	
	//测试大于40的书籍是否是打9折
	public void testGetPriceAbove40(){
		super.assertEquals(5400, this.above40NovelBook.getPrice());
	}

}
