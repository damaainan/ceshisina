<?php
header("Content-type:text/html; Charset=utf-8");
set_time_limit(0);
//文件打包
function addFileToZip($path,$path2,$zip){
	@$handler=opendir($path); //打开当前文件夹由$path指定。
	// echo $handler;
	if(!is_bool($handler)){
	while(($filename=readdir($handler))!==false){	
		// echo "*****\r\n";
		if($filename != "." && $filename != ".."){//文件夹文件名字为'.'和‘..’，不要对他们进行操作
			if($filename != "L" && $filename != "R"){
				if(is_dir($path."/".$filename)){// 如果读取的某个对象是文件夹，则递归
					// addFileToZip($path."/".$filename, $zip);
					if($path2==='.'){
						addFileToZip($path."/".$filename,$filename, $zip);
					}else{
						addFileToZip($path."/".$filename,$filename, $zip);
					}
				}else{ //将文件加入zip对象
					if($path2==='.'){
						$rst=$zip->addFile($path."/".$filename,$filename);
					}else{
						$rst=$zip->addFile($path."/".$filename,$path2."/".$filename);
					}
				}
			}
		}
	}
	}
	@closedir($path);
}

function zipFile($id,$code){
	$zip=new ZipArchive();
	$dir1="D:/wamp/www/wish/trunk/resource_demo/".$code;
	$dir1="/chroot/wish/trunk/resource_demo/".$code;
	// $dir2="D:/wamp/www/wish/trunk/resource_demo/zip/";
	// $dir2="/chroot/wish/trunk/resource_demo/zip/";
	$dir1=iconv('utf-8','gbk',$dir1);
	$dir2=iconv('utf-8','gbk',$dir2);
	if(!file_exists($dir2.$id.'.zip')){
		if($zip->open($dir2.$id.'.zip', ZipArchive::OVERWRITE)=== TRUE){
			addFileToZip($dir1,'.', $zip); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
			$zip->close(); //关闭处理的zip文件
		}
	}
}



	zipFile($va['id'],$va['code']);



