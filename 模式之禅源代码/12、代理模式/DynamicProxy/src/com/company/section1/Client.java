package com.company.section1;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 */
public class Client {

	
	public static void main(String[] args) {
		DynamicProxy proxy = new DynamicProxy(new RealSubject());
		String[] str = {"1111"};
		proxy.exec("doSomething",str);
			
	}
}
