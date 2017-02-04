package com.company.command;

import com.company.CommandVO;
import com.company.command_name.df.AbstractDF;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 */
public class DFCommand extends Command {
	
	public String execute(CommandVO vo) {
		return super.buildChain(AbstractDF.class).get(0).handleMessage(vo);
	}

}
