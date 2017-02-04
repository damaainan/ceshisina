package com.company.section1;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * 用户信息管理
 */
public interface IUserInfo {
	
	//设置用户的ID
	public void setUserID(String userID);
	
	//获得用户的ID
	public String getUserID();
	
	//设置用户的密码
	public void setPassword(String password);
	
	//获得用户的密码
	public String getPassword();
	
	//设置用户的名字
	public void setUserName(String userName);
	
	//获得用户的名字
	public String getUserName();
	
	//修改用户的密码
	public boolean changePassword(String oldPassword);
	
	//删除用户
	public boolean deleteUser();
	
	//用户映射
	public void mapUser();
}
