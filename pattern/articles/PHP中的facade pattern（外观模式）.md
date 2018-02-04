# PHP中的facade pattern（外观模式）

 时间 2018-02-03 22:50:48  

原文[https://juejin.im/post/5a744dde5188257a71684294][1]


本文来自pilishen.com----原文链接 

该篇属于 [《Laravel底层核心技术实战揭秘》][4] 这一课程《laravel底层核心概念解析》这一章的扩展阅读。考虑到学员们的基础差异，为了避免视频当中过于详细而连篇累牍，故将一些laravel底层实现相关的PHP知识点以文章形式呈现，供大家预习和随时查阅。 

## 关于facade这个词的翻译

facade这个词，原意指的是一个建筑物的表面、外观，在建筑学中被翻译为“立面”这个术语，国内对facade这个词的关注，可能更多要依赖于laravel的流行，似乎都一致把laravel里的facade翻译作“门面”。说实在的，当第一次看到翻译文档里提什么“门面”的时候，我想你跟我的内心一样：“这是在说什么玩意呢？你是在讲商店、店铺的门面吗？”直到现在，如果非得用中文说facade，非得用“门面”这个词，我的心里还是不自觉地会“咯噔”那么一下，我知道这里是有问题的。

facade到底翻译作啥好呢？倒是也有的人群干脆提倡不翻译，遇到它就直接英文单词拿过来，这也不是个长远办法，终归是要为了新入门的人铺平理解的道路才好。后来偶然看到台湾的学者，确切说是台湾的维基百科，将facade pattern译作“外观模式”，考虑到该模式的实际作用，方才感觉瞬间释然。即使laravel里的facade，严格上并不是facade pattern，很多人到现在依然在批评laravel在facade这个词语上的滥用和误导，但它终归也是在借用或模仿facade pattern，所以laravel里的facade，本文也认为同样翻译成“外观”比较好，当然，为了更好理解，可以是“服务外观”。即使如此，从私人角度，我更希望将其直呼为“服务定位器”、“服务代理”或者“服务别名”，实际上国外的很多人也是建议如此更名，只是Taylor在这件事上态度一反往常地强硬，所以也暂且不必强求。

通过下文，待实际了解了facade pattern具体是啥后，我想你会更好地理解为什么翻译为“外观模式”更贴切。

## 什么是facade pattern（“外观模式”的定义）

不论在现实世界还是编程世界，facade（外观）的目的就是给一个可能原本丑的、杂乱的东西，“披上”一个优美的、吸引人的外观、或者说面具，用中国的俗话就是：什么是外观？“人靠衣装马靠鞍”。基于此，facade pattern就是将一个或多个杂乱的、复杂的、不容易重构的class，添加上（或转换成）一个漂亮优雅的对接入口（interface），这样呢好让你更乐意、更方便地去操作它，从而间接地操作了背后的实际逻辑。

## 什么时候需要用facade pattern

1. facade pattern（“外观模式”）经常是用来给一个或多个子系统，来提供统一的入口界面（interface），或者说操作界面。
1. 当你需要操作别人遗留下来的项目，或者说第三方的代码的时候。尤其是通常情况下，这些代码你不容易去重构它们，也没有提供测试（tests）。这个时候，你就可以创建一个facade(“外观”),去将原来的代码“包裹”起来，以此来简化或优化其使用场景。

说得再多，不如来几个例子直观：

## 示例一：在java中，通过facade操作计算机内部复杂的系统信息

假设我们有这么一些复杂的子系统逻辑：

    class CPU {
        public void freeze() { ... }
        public void jump(long position) { ... }
        public void execute() { ... }
    }
    
    class Memory {
        public void load(long position, byte[] data) {
            ...
        }
    }
    
    class HardDrive {
        public byte[] read(long lba, int size) {
            ...
        }
    }

为了更方便地操作它们，我们可以来创建一个外观类（facade）：

    class Computer {
        public void startComputer() {
            cpu.freeze();
            memory.load(BOOT_ADDRESS, hardDrive.read(BOOT_SECTOR, SECTOR_SIZE));
            cpu.jump(BOOT_ADDRESS);
            cpu.execute();
        }
    }

然后我们的客户，就可以很方便地来这样调用了：

    class You {
        public static void main(String[] args) {
            Computer facade = new Computer();
            facade.startComputer();
        }
    }

## 示例二：一个糟糕的第三方邮件类

假设你不得不用下面这个看上去很糟糕的第三方邮件类，尤其是里面每个方法名你都得停留个好几秒才能看懂：

    interface SendMailInterface
    {
        public function setSendToEmailAddress($emailAddress);
        public function setSubjectName($subject);
        public function setTheEmailContents($body);
        public function setTheHeaders($headers);
        public function getTheHeaders();
        public function getTheHeadersText();
        public function sendTheEmailNow();
    }
     
    class SendMail implements SendMailInterface
    {
        public $to, $subject, $body;
        public $headers = array();
     
        public function setSendToEmailAddress($emailAddress)
        {
            $this->to = $emailAddress;
        }
        
        public function setSubjectName($subject)
        {
            $this->subject = $subject;
        }
     
        public function setTheEmailContents($body)
        {
            $this->body = $body;
        }
     
        public function setTheHeaders($headers)
        {
            $this->headers = $headers;
        }
     
        public function getTheHeaders()
        {
            return $this->headers;
        }
     
        public function getTheHeadersText()
        {
            $headers = "";
            foreach ($this->getTheHeaders() as $header) {
                $headers .= $header . "\r\n";
            }
        }
     
        public function sendTheEmailNow()
        {
            mail($this->to, $this->subject, $this->body, $this->getTheHeadersText());
        }
    }

这个时候你又不好直接改源码，没办法，来一个facade吧

    class SendMailFacade
    {
        private $sendMail;
     
        public function __construct(SendMailInterface $sendMail)
        {
            $this->sendMail = $sendMail;
        }
     
        public function setTo($to)
        {
            $this->sendMail->setSendToEmailAddress($to);
            return $this;
        }
        
        public function setSubject($subject)
        {
            $this->sendMail->setSubjectName($subject);
            return $this;
        }
     
        public function setBody($body)
        {
            $this->sendMail->setTheEmailContents($body);
            return $this;
        }
     
        public function setHeaders($headers)
        {
            $this->sendMail->setTheHeaders($headers);
            return $this;
        }
     
        public function send()
        {
            $this->sendMail->sendTheEmailNow();
        }
    }

然后原来不加优化的终端调用可能是这样的：

    $sendMail = new SendMail();
    $sendMail->setSendToEmailAddress($to);
    $sendMail->setSubjectName($subject);
    $sendMail->setTheEmailContents($body);
    $sendMail->setTheHeaders($headers);
    $sendMail->sendTheEmailNow();

现在有了外观类，就可以这样了：

    $sendMail       = new SendMail();
    $sendMailFacade = new sendMailFacade($sendMail);
    $sendMailFacade->setTo($to)->setSubject($subject)->setBody($body)->setHeaders($headers)->send();

## 示例三：完成一个商品交易的复杂流程

假设呢，一个商品交易环节需要有这么几步：

    $productID = $_GET['productId']; 
    $qtyCheck = new productQty();
    
     // 检查库存
    if($qtyCheck->checkQty($productID) > 0) {
         
        // 添加商品到购物车
        $addToCart = new addToCart($productID);
         
        // 计算运费
        $shipping = new shippingCharge();
        $shipping->updateCharge();
         
        // 计算打折
        $discount = new discount();
        $discount->applyDiscount();
         
        $order = new order();
        $order->generateOrder();
    }

可以看到，一个流程呢包含了很多步骤，涉及到了很多Object，一旦类似环节要用在多个地方，可能就会导致问题，所以可以先创建一个外观类：

    class productOrderFacade {
             
        public $productID = '';
         
        public function __construct($pID) {
            $this->productID = $pID;
        }
         
        public function generateOrder() {
             
            if($this->qtyCheck()) {
                 
                $this->addToCart();
                
                $this->calulateShipping();
                            
                $this->applyDiscount();
                            
                $this->placeOrder();
                 
            }
             
        }
         
        private function addToCart () {
            /* .. add product to cart ..  */
        }
         
        private function qtyCheck() {
             
            $qty = 'get product quantity from database';
             
            if($qty > 0) {
                return true;
            } else {
                return true;
            }
        }
         
         
        private function calulateShipping() {
            $shipping = new shippingCharge();
            $shipping->calculateCharge();
        }
         
        private function applyDiscount() {
            $discount = new discount();
            $discount->applyDiscount();
        }
         
        private function placeOrder() {
            $order = new order();
            $order->generateOrder();
        }
    }

这样呢，我们的终端调用就可以两行解决：

    $order = new productOrderFacade($productID);
    $order->generateOrder();

## 示例四：往多个社交媒体同步消息的流程

    // 发Twitter消息
    class CodeTwit {
      function tweet($status, $url)
      {
        var_dump('Tweeted:'.$status.' from:'.$url);
      }
    }
    
    // 分享到Google plus上
    class Googlize {
      function share($url)
      {
        var_dump('Shared on Google plus:'.$url);
      }
    }
    
    //分享到Reddit上
    class Reddiator {
      function reddit($url, $title)
      {
        var_dump('Reddit! url:'.$url.' title:'.$title);
      }
    }

如果每次我们写了一篇文章，想着转发到其他平台，都得分别去调用相应方法，这工作量就太大了，后期平台数量往往只增不减呢。这个时候借助于facade class：

    class shareFacade {
      
      protected $twitter;    
      protected $google;   
      protected $reddit;    
        
     function __construct($twitterObj,$gooleObj,$redditObj)
      {
        $this->twitter = $twitterObj;
        $this->google  = $gooleObj;
        $this->reddit  = $redditObj;
      }  
      
      function share($url,$title,$status)
      {
        $this->twitter->tweet($status, $url);
        $this->google->share($url);
        $this->reddit->reddit($url, $title);
      }
    }

这样终端调用就可以：

    $shareObj = new shareFacade($twitterObj,$gooleObj,$redditObj);
    $shareObj->share('//myBlog.com/post-awsome','My greatest post','Read my greatest post ever.');

## facade pattern的优劣势

### 优势

能够使你的终端调用与背后的子系统逻辑解耦，这往往发生在你的controller里，就意味着你的controller可以有更少的依赖，controller关注的更少了，从而责任和逻辑也更明确了，同时也意味着你子系统里的逻辑更改，并不会影响到你的controller里终端调用。

### 劣势

虽然特别有用，但是一个常见的陷阱就是，过度使用这个模式，明明可能那个时候你并不需要，这个往往注意即可。当然也有人争论说，明明我原来的代码都能用，干嘛费这个劲，那么同样是房子，你是喜欢住在精致的屋子里呢，还是说有四面墙就行了呢？

## 感觉facade pattern与其他的设计模式似曾相识？

认真学过我们 [《Laravel底层核心技术实战揭秘》][5] 这一课程的同学，可能到这里就会尤其觉得这个facade pattern好像在哪里见过？可能你会脱口而出：“这货跟之前咱们学的decorator pattern有啥区别呢？为啥不直接说成修饰者模式呢？” 

确实，在“包装”逻辑方面，它们确实类似，但是：

修饰者模式（Decorator） ——用来给一个Object添加、包裹上新的行为、逻辑，而不需要改动原来的代码 

外观模式（facade pattern） ——用来给一个或多个复杂的子系统、或者第三方库，提供统一的入口，或者说统一的终端调用方式 

还是有一定差别的~

## 参考文章：

1. [//zh.wikipedia.org/wiki/%E5%A4%96%E8%A7%80%E6%A8%A1%E5%BC%8F][6]
1. [//www.jakowicz.com/facade-pattern-in-php/][7]
1. [//code.tutsplus.com/tutorials/design-patterns-the-facade-pattern--cms-22238][8]
1. [//phpenthusiast.com/blog/simplify-your-php-code-with-facade-class][9]

[1]: https://juejin.im/post/5a744dde5188257a71684294
[4]: https://link.juejin.im?target=%2F%2Fstudy.163.com%2Fcourse%2FcourseMain.htm%3FcourseId%3D1003575006
[5]: https://link.juejin.im?target=%2F%2Fstudy.163.com%2Fcourse%2FcourseMain.htm%3FcourseId%3D1003575006%26amp%3Butm_campaign%3Dcommission%26amp%3Butm_source%3Dcp-1018568251%26amp%3Butm_medium%3Dshare
[6]: https://link.juejin.im?target=%2F%2Fzh.wikipedia.org%2Fwiki%2F%25E5%25A4%2596%25E8%25A7%2580%25E6%25A8%25A1%25E5%25BC%258F
[7]: https://link.juejin.im?target=%2F%2Fwww.jakowicz.com%2Ffacade-pattern-in-php%2F
[8]: https://link.juejin.im?target=%2F%2Fcode.tutsplus.com%2Ftutorials%2Fdesign-patterns-the-facade-pattern--cms-22238
[9]: https://link.juejin.im?target=%2F%2Fphpenthusiast.com%2Fblog%2Fsimplify-your-php-code-with-facade-class