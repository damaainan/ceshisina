package com.company.strategy;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.util.zip.ZipEntry;
import java.util.zip.ZipOutputStream;



/**
 * @author cbf4Life cbf4life@126.com
 * I'm glad to share my knowledge with you all.
 */
public class Zip implements Algorithm {

	//zip格式的压缩算法
	public boolean compress(String source, String to) {
		System.out.println(source + " --> " +to + " ZIP压缩成功!");
		return true;
	}
	
	//zip格式的解压缩算法
	public boolean uncompress(String source,String to){
		System.out.println(source + " --> " +to + " ZIP解压缩成功!");
		return true;
	}
}
