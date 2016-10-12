<?php 
header('content-type:text/html;charset=utf-8');
  $all=$_POST['all'];
  $page=$_POST['page'];
  $list=explode('*@*',$all);
  $page=explode('*@*',$page);
  $len1=count($list);
  $len2=count($page);

  $pdo=new PDO('mysql:host=localhost;dbname=caiji;charset=utf8','root','');
  $pdo->exec('set names utf8');

  for($i=0;$i<$len1;$i++){
    $lista=explode('*',$list[$i]);
  	if($lista[0]!=''){
  	  //查询数据库 检查是否重复
  	  $stmt=$pdo->prepare("SELECT * FROM hj_list WHERE list=?;");
	  $stmt->bindParam(1,$lista[0]);
	  $stmt->execute();
	  if(!$row=$stmt->rowCount()){	//写入数据库
	    $stmt=$pdo->prepare("INSERT INTO hj_list SET id=null, list=? ,qihao=?, name=?, book=?;");
      $stmt->bindParam(1,$lista[0]);
      $stmt->bindParam(2,$lista[2]);
      $stmt->bindParam(3,$lista[1]);
	    $stmt->bindParam(4,$lista[3]);
	    $stmt->execute();	
	  }
  	  
  	}
  }
  for($i=0;$i<$len2;$i++){
  	if($page[$i]!=''){
  		//写入数据库
  	  $stmt=$pdo->prepare("SELECT * FROM hj_page WHERE page=?;");
	  $stmt->bindParam(1,$page[$i]);
	  $stmt->execute();
	  if(!$row=$stmt->rowCount()){	
  		$stmt=$pdo->prepare("INSERT INTO hj_page SET id=null, page=?;");
	    $stmt->bindParam(1,$page[$i]);
	    $stmt->execute();
	  }
  	}
  }
