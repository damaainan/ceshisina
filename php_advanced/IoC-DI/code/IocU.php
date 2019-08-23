<?php 
require_once("Ioc.php");
require_once("IocT.php");

$book = Ioc::register('book', function () {
    // $book = new Book;
    Book::setdb('db');
    Book::setfile('file');
    Book::get();
    // return $book;
});

//注入依赖
$book = Ioc::resolve('book');
// var_dump($book);