<?php

namespace Article;

class Comment { 
	function __construct(){
		echo 111;
	}
}



namespace MessageBoard;

class Comment {
function __construct(){
		echo 22222;
	}
 }

//调用当前空间（MessageBoard）的Comment类
$comment = new Comment();

//调用Article空间的Comment类
$article_comment = new \Article\Comment();
