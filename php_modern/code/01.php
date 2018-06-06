<?php 
interface Printable{
	public function print(string $name);
}

(function (Printable $printer, string $name){
	$printer->print($name);
})(new class("Hello") implements Printable{
	private $greeting;

	public function __construct(string $greeting){
		$this->greeting = $greeting;
	}

	public function print(string $name){
		print("{$this->greeting}, $name!" . PHP_EOL);
	}
}, "Gua");
