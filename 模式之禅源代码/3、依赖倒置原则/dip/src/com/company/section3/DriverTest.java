package com.company.section3;

import junit.framework.TestCase;

import org.jmock.Expectations;
import org.jmock.Mockery;
import org.jmock.integration.junit4.JUnit4Mockery;
import org.junit.Test;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * 测试类
 */
public class DriverTest extends TestCase{
    Mockery context = new JUnit4Mockery();
	@Test
	public void testDriver() {
		//根据接口虚拟一个对象
		final ICar car = context.mock(ICar.class);
		IDriver driver = new Driver();
		context.checking(new Expectations(){{
			oneOf (car).run();	
		}});
		driver.drive(car);
	}

}
