<?php 
/**
 * 责任链模式
-----------------------

现实例子
> 比如，有三个支付方式 (`A`, `B` 和 `C`) 安装在你的账户里；每种方式都有不同额度。`A` 有 100 元， `B` 有 300 元，以及 `C` 有 1000 元，选择支付方式的顺序是 `A` 然后 `B` 然后 `C`。你要买一些价值 210 元的东西。使用责任链模式，首先账户 `A` 会被检查是否能够支付，如果是，支付会被执行而链子终止。如果否，请求会转移到账户 `B`，检查额度，如果是，链子终止，否则请求继续转移直到找到合适的执行者。这里 `A`，`B` 和 `C` 是链接里的环节，它们合起来就是责任链。

白话
> 它构造了一个对象的链。请求进入一端，然后从一个对象到另一个对象直到找到合适的执行者。


 */


abstract class Account {
    protected $successor;
    protected $balance;

    public function setNext(Account $account) {
        $this->successor = $account;
    }
    
    public function pay(float $amountToPay) {
        if ($this->canPay($amountToPay)) {
            echo sprintf('Paid %s using %s' . PHP_EOL, $amountToPay, get_called_class());
        } else if ($this->successor) {
            echo sprintf('Cannot pay using %s. Proceeding ..' . PHP_EOL, get_called_class());
            $this->successor->pay($amountToPay);
        } else {
            throw Exception('None of the accounts have enough balance');
        }
    }
    
    public function canPay($amount) : bool {
        return $this->balance >= $amount;
    }
}

class Bank extends Account {
    protected $balance;

    public function __construct(float $balance) {
        $this->balance = $balance;
    }
}

class Paypal extends Account {
    protected $balance;

    public function __construct(float $balance) {
        $this->balance = $balance;
    }
}

class Bitcoin extends Account {
    protected $balance;

    public function __construct(float $balance) {
        $this->balance = $balance;
    }
}




// 我们准备下面这样的链
//      $bank->$paypal->$bitcoin
//
// 首选银行 bank
//      如果银行 bank 不能支付则选择贝宝 paypal
//      如果贝宝 paypal 不能支付则选择比特币 bit coin

$bank = new Bank(100);          // 银行 Bank 有余额 100
$paypal = new Paypal(200);      // 贝宝 Paypal 有余额 200
$bitcoin = new Bitcoin(300);    // 比特币 Bitcoin 有余额 300

$bank->setNext($paypal);
$paypal->setNext($bitcoin);

// 我们尝试用首选项支付，即银行 bank
$bank->pay(259);

// 输出将会是
// ==============
// Cannot pay using bank. Proceeding ..
// Cannot pay using paypal. Proceeding ..: 
// Paid 259 using Bitcoin!