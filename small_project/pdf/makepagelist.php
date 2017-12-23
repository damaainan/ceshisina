<?php 
header('content-type:text/html;charset=utf-8');
  $book=$_GET['book'];
  $pdo=new PDO('mysql:host=localhost;dbname=caiji;charset=utf8','root','');
  $pdo->exec('set names utf8');
  $stmt=$pdo->prepare("SELECT * FROM hj_list WHERE book=? ;");
  $stmt->bindParam(1,$book);
  $stmt->execute();
  $result=$stmt->fetchAll(PDO::FETCH_ASSOC);
  ?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>生成页面</title>
	<script src="jquery-1.8.3.min.js"></script>
</head>
<body>
<!-- 在这之前我需要一个页面选择打印那一个  社刊   所以我需要存储所有的社刊名字 -->
	<!-- 需要先列出所有的页面链接  找到名字  日期  期号  排序  再根据这些生成PDF -->
      <button type="button" id="makepdf">生成PDF</button>
	<button type="button" id="selectall">全选</button>

	<ol>
		<?php 
              $len=count($result);
            for($i=0;$i<$len;$i++){
                echo '<li><input type="checkbox"> <span class="book">'.$result[$i]['book'].'</span><span class="list">'.$result[$i]['list'].'</span><span class="qihao">'.$result[$i]['qihao'].'</span><span class="name">'.$result[$i]['name'].'</span><b class="progress" style="color:red;"></b></li>';
              }
            ?>
	</ol>
	
	<input type="hidden" value="<?php  echo $_GET['book'];  ?>" id="book">
	<script type="text/javascript">
	  // var book=$("#book").val();
      // $(document).ready(function(){
      // 	$.post('malist.php',{book:book},function(data){
      // 		//得到的data是所有的链接
      // 		data=JSON.parse(data);
      // 		var len=data.length;
      // 		var html=$('ol').html();
      //       for(var i=0;i<len;i++){
      //           html+='<li><span class="book">'+data[i]['book']+'</span><span class="list">'+data[i]['list']+'</span><span class="qihao">'+data[i]['qihao']+'</span><span class="name">'+data[i]['name']+'</span><b class="progress" style="color:red;"></b></li>';
      //       }
      //       $('ol').html(html);
      // 	})
      // })
      $("#makepdf").click(function(){
      	//每个请求一次   生成状态显示
      	var len=$("ol").children('li').length;
      	for(var i=0;i<len;i++){
                  var check=$("ol").children('li').eq(i).children(":checkbox").attr("checked");
                  if(!check){
                        continue;//跳出循环
                  }
      		var list=$("ol").children('li').eq(i).find('.list').text();
      		var book=$("ol").children('li').eq(i).find('.book').text();
      		var qihao=$("ol").children('li').eq(i).find('.qihao').text();
      		var name=$("ol").children('li').eq(i).find('.name').text();
      		$.ajax({
      			url:'makepage.php',
      			type:'POST',
      			async:false,
      			data:"list="+list+"&book="+book+"&qihao="+qihao+"&name="+name,
      			success:function(data){
      			   if(data==1){
      	               	$("ol").children('li').eq(i).find('.progress').text('完成');
      	               }else if(data==2){
      	               	$("ol").children('li').eq(i).find('.progress').text('已存在');
      	               }else{
      	               	$("ol").children('li').eq(i).find('.progress').text('未完成');
      	               }
      			}

      		})
      	}
      })
      $("#selectall").click(function(){
            var str=$("#selectall").text();
            if(str=="全选"){
                  $(':checkbox').attr("checked","checked");
                  $("#selectall").text("全不选");
            }else{
                  $(':checkbox').removeAttr("checked");
                  $("#selectall").text("全选");
            }
      })
	</script>
</body>
</html>