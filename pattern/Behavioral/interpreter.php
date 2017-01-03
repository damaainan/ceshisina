<?php
/**
 * 解释器模式

 * 理解：就是一个上下文的连接器
 * 使用场景：构建一个编译器，SQL解析器


解释器模式：Given a language, define arepresentation for its grammar along with an interpreter that uses therepresentation to interpret sentences in the language。给定一个语言,
定义它的文法的一种表示，并定义一个解释器，该解释器使用该表示来解释语言中的句子。

角色：

环境角色(PlayContent)：定义解释规则的全局信息。

抽象解释器(Empress)：定义了部分解释具体实现，封装了一些由具体解释器实现的接口。

具体解释器(MusicNote)：实现抽象解释器的接口，进行具体的解释执行。
 */

class Expression {
	//抽象表示
	function interpreter($str) {
		return $str;
	}
}
class ExpressionNum extends Expression {
	//表示数字
	function interpreter($str) {
		switch ($str) {
		case "0":return "零";
		case "1":return "一";
		case "2":return "二";
		case "3":return "三";
		case "4":return "四";
		case "5":return "五";
		case "6":return "六";
		case "7":return "七";
		case "8":return "八";
		case "9":return "九";
		}
	}
}
class ExpressionCharater extends Expression {
	//表示字符
	function interpreter($str) {
		return strtoupper($str);
	}
}
class Interpreter {
	//解释器
	function execute($string) {
		$expression = null;
		for ($i = 0; $i < strlen($string); $i++) {
			$temp = $string[$i];
			switch (true) {
			case is_numeric($temp): $expression = new ExpressionNum();
				break;
			default:$expression = new ExpressionCharater();
			}
			echo $expression->interpreter($temp);
			echo "<br>";
		}
	}
}
//client
$obj = new Interpreter();
$obj->execute("123s45abc");