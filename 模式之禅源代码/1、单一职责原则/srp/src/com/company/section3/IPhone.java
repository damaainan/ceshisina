package com.company.section3;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * 电话的接口
 */
public interface IPhone {
	
	//拨通电话
	public void dial(String phoneNumber);
	
	//通话
	public void call(Object o);
	
	//回应，只有自己说话而没有回应，那算啥？！
	public void answer(Object o);
	
	//通话完毕，挂电话
	public void huangup();
}
