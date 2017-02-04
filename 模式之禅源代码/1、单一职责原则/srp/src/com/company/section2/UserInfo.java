package com.company.section2;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * 用户管理的实现类
 */
public class UserInfo implements IUserInfo {
	private String userName;
	private String userID;
	private String password;

	public String getUserName() {
		return userName;
	}

	public void setUserName(String userName) {
		this.userName = userName;
	}
	
	public String getUserID() {
		return userID;
	}
	
	public void setUserID(String userID) {
		this.userID = userID;
	}
	
	public String getPassword() {
		return password;
	}
	
	public void setPassword(String password) {
		this.password = password;
	}
	
	//修改用户密码
	public boolean changePassword(String oldPassword){
		System.out.println("密码修改成功...");
		return true;
	}
	
	//删除用户
	public boolean deleteUser(){
		System.out.println("删除用户成功...");
		return true;
	}
	
	//用户映射
	public void mapUser(){
		System.out.println("用户映射成功...");
	}
	
	//增加一个组织
	public void addOrg(IUserBO userBO,int orgID){
		System.out.println("增加组织成功...");
	}
	
	//增加一个角色
	public void addRole(IUserBO userBO,int roleID){
		System.out.println("增加角色成功...");
	}
}
