package com.company.section2;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 * 用户信息管理
 */
public interface IUserBiz {

	//修改用户的密码
	public boolean changePassword(String oldPassword);
	
	//删除用户
	public boolean deleteUser();
	
	//用户映射
	public void mapUser();
	
	//增加一个组织
	public void addOrg(IUserBO userBO,int orgID);
	
	//增加一个角色
	public void addRole(IUserBO userBO,int roleID);
}
