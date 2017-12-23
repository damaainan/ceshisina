<?php 
header('content-type:text/html;charset=utf-8');
  $pdo=new PDO('mysql:host=localhost;dbname=caiji;charset=utf8','root','');
  $pdo->exec('set names utf8');
  $stmt=$pdo->prepare("SELECT DISTINCT book FROM hj_list;");
  $stmt->execute();
  $result=$stmt->fetchAll(PDO::FETCH_ASSOC);

  ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>所有社刊</title>

	<script src="jquery-1.8.3.min.js"></script>
</head>
<body>
    <ul>
    <?php
     $len=count($result);
     for($i=0;$i<$len;$i++){
        echo '<li>'.$result[$i]['book'].'<a href="makepagelist.php?book='.$result[$i]['book'].'">生成PDF</a></li>';
      }
    ?>
    </ul>
</body>
</html>