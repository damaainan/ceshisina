package com.company.section2;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * 业务类调用
 */
public class Client {
	
	public static void main(String[] args) {
		IUserBiz userInfo = new UserInfo();
		
		//我要复制了，我就认为它是一个纯粹的BO
		IUserBO userBO = (IUserBO)userInfo;
		userBO.setPassword("abc");
		
		//我要执行动作了，我就认为是一个业务逻辑类
		IUserBiz userBiz = (IUserBiz)userInfo;
		userBiz.deleteUser();
				
	}
}
