# PHP设计模式(一)：基础编程模式

<font face=黑体>
> 原文地址：[PHP设计模式(一)：基础编程模式][0]

## Introduction

俗话说，“PHP是世界上最好的语言”，因为PHP什么都能干。但是在PHP编程中，你是否会遇到这样的困惑：明明是相同的需求，但是之前写的代码却并不能重用，稍微修改不满足需求，大改又会让页面变样。  
是的，由于PHP什么都能干，但是高度灵活性降低了代码的结构性。虽然可以利用三方框架来解决问题，但问题的根本在于缺乏设计模式。  
本系列文章将由浅入深的介绍各种设计模式。

## 面向对象编程

面向对象编程，Object-Oriented Programming(OOP)作为最基本的设计模式并不是什么新鲜的话题，但是大部分新手的PHP编程都是在写流水账，各种拼接字符串，所以这里还是要提一下。  
[Object-Oriented Programming][1]的概念这里就不说了，毕竟很多人都明白，但是如何在PHP中使用？  
假设你需要在页面上显示不同的用户类型，如电脑用户、手机用户等，那么你可以将“显示”这件事抽象为一个类，如：

```php
    <?php
    class ShowAgent {
      private $agent;
      public function __construct() {
        $this->agent = $_SERVER['HTTP_USER_AGENT'];
        echo $this->agent;
      }
    }
    $showAgent = new ShowAgent();
    ?>
```
## 调试技巧

在很多PHP默认环境中，调试功能是关闭的。打开调试功能又需要配置php.ini文件，其实有一个简单的方法：

```php
    <?php
    ini_set("display_errors", "1");
    ERROR_REPORTING(E_ALL);
    ?>
```
将这段代码加入到你的代码中，甚至可以require或者include进去，方便调试。

## 流水账编程

这里列出流水账编程，并不是让你学习，而是指出何种编程不推荐使用：

```php
    <?php
    $total = "Total number is ";
    $number = "6";
    $totalNumber = $total.$number;
    echo $totalNumber;
    ?>
```
这段代码并没有错，但是以后再也无法重用了，对吧？每次遇到相同问题，你都需要反复拼接。

## 面向过程编程

面向过程编程曾经很流行，缺点也是无法维护，例如：

```php
    <?php
    function showTotal($total, $number) {
      $totalNumber = $total.$number;
      echo $totalNumber;
    }
    showTotal("Total number is", "6");
    ?>
```
这段代码同样没有错，但是时间久了，由于缺乏类的概念，showTotal在各种应用场景缺乏灵活性，你还是需要重写代码。

## Summary

转变编程的思维需要花费的时间是很长的，但是记住：算法提高程序运行的速度，而设计模式提高编程的速度。

</font>

[0]: http://csprojectedu.com/2016/02/22/PHPDesignPatterns-1/
[1]: https://en.wikipedia.org/wiki/Object-oriented_programming