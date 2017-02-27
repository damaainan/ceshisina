<?php 

//如果两个 trait 使用了同一个方法，没有明确解决会发生报错， 为了解决多个 trait 在同一个类的命名冲突,需要使用 insteadof 操作符明确指定使用冲突方法的哪一个, as 操作符可以将其中的一个冲突的方法以另一个名称来引入。

trait A {
    public function smallTalk() {
        echo 'a';
    }
    public function bigTalk() {
        echo 'A';
    }
}

trait B {
    public function smallTalk() {
        echo 'b';
    }
    public function bigTalk() {
        echo 'B';
    }
}

// class Talker {
//     use A, B {
//         B::smallTalk insteadof A;
//         A::bigTalk insteadof B;
//          A::bigTalk insteadof S;
//     }
// }

class Aliased_Talker {
    use A, B {
        B::smallTalk as A;
        A::bigTalk insteadof B;
        B::bigTalk as talk;
    }
}
$o = new Aliased_Talker();
$o->A();
// $o->bigTalk();
$o->talk();