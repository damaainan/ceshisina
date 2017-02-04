package com.company.builder;

/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 */
public class BMWBuilder extends CarBuilder {
	
	@Override
	public String buildEngine() {
		return super.getBlueprint().getEngine();
	}

	@Override
	public String buildWheel() {
		return super.getBlueprint().getWheel();
	}

}
