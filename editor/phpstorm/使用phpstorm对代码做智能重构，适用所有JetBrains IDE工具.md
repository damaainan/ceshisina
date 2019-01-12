## 使用phpstorm对代码做智能重构，适用所有JetBrains IDE工具

时间：2017-12-08

来源：<https://juejin.im/entry/5a2a6bd45188253d6817802b>

PhpStorm 最強悍的就是 Refactoring，這也是文字編輯器無法達到的，善用 Refactoring 將可大幅增加 code review 之後重構 PHP 的速度。
 [][71]
## Version

-----

PhpStorm 2017.1.2
## Extraction

-----

### Extract Method

最需要被重構的程式碼，首推`Long Method`，一旦一個 method 的程式碼的行數過多，就會難以閱讀、難以維護、難以單元測試、也違反物件導向的`單一職責原則`，建議使用`Extract Method`將`Long Method`拆成多個小 method。

何時該使用`Extract Method`呢？根據 Martin Fowler 在`重構`這本書的建議 :


* 當一段程式碼需要被重複使用時，就該獨立抽成 method。
* 當一段程式碼需要寫註解才能讓人理解時，就該獨立抽成 method。
* 當一段程式碼抽成 method 後， **`語意`** 更清楚 ，就該獨立抽成 method。
    

 重構前 

```php
namespace App\Services;

class ExtractMethod
{
    public function printOwing(string $name)
    {
        $this->printBanner();

        // print details
        print("name:  " . $name);
        print("amount " . $this->getOutstanding());
    }
}

```
 重構後 

```php
namespace App\Services;

class ExtractMethod
{
    public function printOwing(string $name)
    {
        $this->printBanner();

        // print details
        $this->printDetails($name);
    }

    /**
     * @param string $name
     */
    private function printDetails(string $name)
    {
        print("name:  " . $name);
        print("amount " . $this->getOutstanding());
    }
}

```

[][72]

選擇要重構成 method 的程式碼，按熱鍵跳出`Refactor This`選單，選擇`Method`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][73]

輸入欲建立的 method 名稱，並選擇`public`、`protected`或`private`，一般來說重構出來的 method 選`private`。

[][74]

PhpStorm 會自動幫我們將所選的程式碼抽成`printDetails()`，連參數、型別與 PHPDoc 都會幫我們加上。
### Extract Field

實務上有些在 method 內的 local 變數，原本只有單一 method 使用，若有其他 method 也使用相同變數時，建議使用`Extract Field`將此變數重構成 field。
 重構前 

```php
namespace App\Services;

class ExtractField
{
    public function print1()
    {
        $name = 'Hello World';

        echo($name);
    }

    public function print2()
    {
        $name = 'Hello World';

        echo($name);
    }
}

```
 重構後 

```php
namespace App\Services;

class ExtractField
{
    private $name = 'Hello World';

    public function print1()
    {
        echo($this->name);
    }

    public function print2()
    {
        echo($this->name);
    }
}

```

[][75]

將滑鼠游標放在變數名稱上，按熱鍵跳出`Refactor This`選單，選擇`Field`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][76]

可選擇要將 **`單一變數`** 或者將 **`整個 expression`**  重構成 field，這裡選擇`$name`即可，因為我們想將`$name`變數重構成 field。

[][77]

輸入欲建立的 field 名稱，並選擇`public`、`protected`或`private`，為了實現物件導向資料 **`封裝`** ，建議重構出來的 field 選`private`。

在`Initialize in`選擇 field 初始化的方式，若是 PHP 原生型別，如`int`/`string`，則選擇`Field declaration`，若是物件，則必須選擇`Class constructor`。

[][78]

PhpStorm 會自動幫我們將變數重構成 field，並將原來引用變數之處重構成引用 field。
### Extract Variable

實務上有些原本在 method 內的固定值，想要變成變數，建議使用`Extract Variable`將固定值重構成變數。
 重構前 

```php
namespace App\Services;

class ExtractVariable
{
    public function Calculate(int $i)
    {
        while ($i < 10) {
            $i = $i + 1;
            return $i;
        };
    }

    public function DisplaySum()
    {
        $a = 1;
        $result = $this->Calculate($a);
        
        echo "The final result is " . $result;
    }
}

```
 重構後 

```php
namespace App\Services;

class ExtractVariable
{
    public function Calculate(int $i)
    {
        $c = 10;
        while ($i < $c) {
            $i = $i + 1;
            return $i;
        };
    }

    public function DisplaySum()
    {
        $a = 1;
        $result = $this->Calculate($a);

        echo "The final result is " . $result;
    }
}

```

[][79]

將滑鼠游標放在`10`上，按熱鍵跳出`Refactor This`選單，選擇`Variable`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][80]

可以將 **`固定值`** 或 **`expression`**  抽成變數，這裡選擇`10`將固定值重構成變數。

[][81]

輸入欲建立的變數名稱。

[][82]

PhpStorm 會自動幫我們加上重構過的變數，並將原有的值都以變數取代。
### Extract Parameter

實務上有些原本在 method 內的固定值，想要變成參數可由外部帶入，建議使用`Extract Parameter`將固定值重構成參數。
 重構前 

```php
namespace App\Services;

class ExtractParameter
{
    public function Calculate(int $i)
    {
        while ($i < 10) {
            $i = $i + 1;
            return $i;
        };
    }

    public function DisplaySum()
    {
        $a = 1;
        $result = $this->Calculate($a);
        
        echo "The final result is " . $result;
    }
}

```
 重構後 

```php
namespace App\Services;

class ExtractParameter
{
    public function Calculate(int $i, int $c)
    {
        while ($i < $c) {
            $i = $i + 1;
            return $i;
        };
    }

    public function DisplaySum()
    {
        $a = 1;
        $result = $this->Calculate($a, 10);

        echo "The final result is " . $result;
    }
}

```

[][83]

將滑鼠游標放在固定值上，按熱鍵跳出`Refactor This`選單，選擇`Parameter`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][84]

輸入欲建立的 parameter 名稱。

[][85]

PhpStorm 會自動幫我們重構成參數，將原有的固定值都以參數取代，並在 method 呼叫 的地方重構成原來的值。
### Extract Constant

實務上不建議將 **`字串`** 或 **`數字`** 直接 hardcode 在程式碼中 :


* 日後難以閱讀與維護
* 若字串與數字需要變動，需要改很多地方


建議將這類 Magic Number 使用`Extract Constant`重構成 constant。
 重構前 

```php
namespace App\Services;

class ExtractConstant
{
    public function potentialEnergy(int $mass, int $height): float
    {
        return $mass * $height * 9.81;
    }
}

```
 重構後 

```php
namespace App\Services;

class ExtractConstant
{
    const GRAVITATIONAL_CONSTANT = 9.81;

    public function potentialEnergy(int $mass, int $height): float
    {
        return $mass * $height * self::GRAVITATIONAL_CONSTANT;
    }
}

```

[][86]

將滑鼠游標放在數值上，按熱鍵跳出`Refactor This`選單，選擇`Constant`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][87]

輸入欲建立的 constant 名稱。

[][88]

PhpStorm 會自動幫我們重構成`const`，並將原有的值都以 constant 取代。
### Extract Interface

為了讓 class 實現不同的角色，且讓 class 與 class 之間的耦合降低，讓物件不要直接相依某個物件，而是僅相依於 interface，建議使用`Extract Interface`重構出 interface。
 重構前 

```php
namespace App\Services;

class SMSService
{
    public function printMessage()
    {
        echo('Print Message');
    }

    public function sendMessage() : string
    {
        return 'Send Message';
    }
}

```

```php
namespace App\Services;

use App\Post;

class PostService
{
    /** @var SMSService */
    private $SMSService;

    /**
     * PostService constructor.
     * @param SMSService $SMSService
     */
    public function __construct(SMSService $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```
 重構後 

```php
namespace App\Services;

interface Sendable
{
    public function sendMessage(): string;
}

```

```php
namespace App\Services;

interface Printable
{
    public function printMessage();
}

```

```php
class SMSService implements Sendable, Printable
{
    public function printMessage()
    {
        echo('Print Message');
    }

    public function sendMessage() : string
    {
        return 'Send Message';
    }
}

```

```php
namespace App\Services;

use App\Post;

class PostService
{
    /** @var Sendable */
    private $SMSService;

    /**
     * PostService constructor.
     * @param ISendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showPost()
    {
        return $this->SMSService->sendMessage();
    }
}

```

[][89]

欲從 class 抽出 interface，將滑鼠游標放在 class 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Interface`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][90]

輸入欲建立的 interface 名稱，選擇欲抽出的 method。

[][91]

PhpStorm 會幫我們重構出 interface。

[][92]

原 class 也會自動加上`implements`interface。

[][93]

繼續抽出第 2 個 interface，將滑鼠游標放在 class 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Interface`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][94]

輸入欲建立的 interface 名稱，選擇欲抽出的 method。

並勾選`Replace class references with interface where possible`，PhpStorm 會自動搜尋所有使用 class 的地方，以 interface 取代。

[][95]

重構前的預覽，PhpStorm 告知即將對以下檔案進行重構，按`Do Refactor`繼續。

[][96]

PhpStorm 會幫我們產生 interface。

[][97]

原 class 也會自動加上`implements`interface。

[][98]

原來 constructor 的參數型別，也從 class 變成 interface，field 的型別宣告也變成了 interface。

如此`PostService`與`SMSService`的相依僅限於`Sendable`interface，大大降低`PostService`與`SMService`之間的耦合，也就是`設計模式`一書所說的：

根據 interface 寫程式，不要根據 class 寫程式

白話就是

若要降低物件之間的耦合程度，讓物件之間方便抽換與組合，就讓物件與物件之間僅相依於 interface，而不要直接相依於 class

更白話就是

黑貓白貓，能抓老鼠的就是好貓

黑貓白貓就是 class，能抓老鼠就是 interface

## Rename

-----

實務上常會遇到 variable 名稱、method 名稱、class 名稱…的命名不當，導致程式碼難以閱讀，在 code review 後須加以重構，PhpStorm 支援`Rename Class`、`Rename Method`,`Rename Field`、`Rename Variable`、`Rename Parameter`，以下僅對最常用的`Rename Variable`、`Rename Method`、`Change Signature`、`Rename Class`加以介紹。
### Rename Variable

當遇到命名不當的變數名稱時，建議使用`Rename Variable`將變數名稱加以重構。
 重構前 

```php
namespace App\Services;

class RenameVariable
{
    public function print()
    {
        $address = 'Sam';

        echo($address);
    }
}

```
 重構後 

```php
namespace App\Services;

class RenameVariable
{
    public function print()
    {
        $name = 'Sam';

        echo($name);
    }
}

```

[][99]

將滑鼠游標放在變數名稱上，按熱鍵跳出`Refactor This`選單，選擇`Rename`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][100]

從`$address`重構成`$name`之後，PhpStorm 會將所有原本使用`$address`變數的地方都重構成使用`$name`。
### Rename Method

當遇到命名不當的 method 名稱時，建議使用`Rename Method`將 method 名稱加以重構。
 重構前 

```php
namespace App\Services;

class RenameMethod
{
    public function print()
    {
        $this->printOutline();
    }

    public function printOutline()
    {
        echo('Detail');
    }
}

```
 重構後 

```php
namespace App\Services;

class RenameMethod
{
    public function print()
    {
        $this->printDetail();
    }

    public function printDetail()
    {
        echo('Detail');
    }
}

```

[][101]

將滑鼠游標放在 method 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Rename`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][102]

將`printOutline()`重構成`printDetail()`，PhpStorm 會即時顯示修改後的結果。

[][103]

重構前的預覽，PhpStorm 告知即將對以下檔案進行重構，按`Do Refactor`繼續。

[][104]

除了原來的 method 名稱變更外，PhpStorm 會將所有引用該 method 的地方加以修改。
### Change Signature

實務上因需求改變，可能必須增加參數，也可能必須刪除參數，建議使用`Change Signature`來重構參數。
#### Change Method Signature

修改一般 method 的參數。
 重構前 

```php
namespace App\Services;

class ChangeSignature
{
    public function print()
    {
        echo($this->sum(1, 2));
    }

    public function sum(int $num1, int $num2) : int
    {
        return $num1 + $num2;
    }
}

```
 重構後 

```php
namespace App\Services;

class ChangeSignature
{
    public function print()
    {
        echo($this->sum(1, 2, 10));
    }

    public function sum(int $num1, int $num2, int $num3 = 0) : int
    {
        return $num1 + $num2;
    }
}

```

[][105]

將滑鼠游標放在 method 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Change Signature`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][106]

按`+`新增參數，Default 則為其他 mehtod 呼叫參數時，所提供的預設值。

[][107]

PhpStorm 除了在 method 增加參數外，其他呼叫 method 的地方都會加上預設值。
#### Change Constructor Signature

修改 constructor 的參數。
 重構前 

```php
namespace App\Services;

class ChangeConstructorSignature
{
    /** @var PostService */
    private $postService;

    /**
     * ChangeConstructorSignature constructor.
     * @param PostService $postService
    */
    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }
}

```
 重構後 

```php
namespace App\Services;

class ChangeConstructorSignature
{
    /** @var PostService */
    private $postService;
    /** @var SMSService */
    private $SMSService;

    /**
     * ChangeConstructorSignature constructor.
     * @param PostService $postService
     * @param SMSService $SMSService
     */
    public function __construct(PostService $postService, SMSService $SMSService)
    {
        $this->postService = $postService;
        $this->SMSService = $SMSService;
    }
}

```

[][108]

將滑鼠游標放在`__construct()`上，按熱鍵跳出`Refactor This`選單，選擇`Change Signature`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][109]

若是對 constructor 使用`Change Signature`，會出現`Create and initialize class properties`，預設會打勾。

[][110]

PhpStorm 除了自動幫我們在 constructor 加上參數外，還會對 field 加以初始化。
### Rename Class

當遇到命名不當的 class 名稱時，建議使用`Rename Class`將 class 名稱加以重構。
 重構前 

```php
namespace App\Services;

class RenameClass
{
    public function print()
    {
        echo('Hello World');
    }
}

$obj = new RenameClass();
$obj->print();

```
 重構後 

```php
namespace App\Services;

class MyRenameClass
{
    public function print()
    {
        echo('Hello World');
    }
}

$obj = new MyRenameClass();
$obj->print();

```

[][111]

將滑鼠游標放在 class 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Rename`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][112]

直接修改 class 名稱，PhpStorm 會即時顯示修改後的結果。

[][113]

重構前的預覽，PhpStorm 告知即將對以下檔案進行重構，按`OK`繼續。

[][114]

class 名稱重構後，PhpStorm 將所有原本 new 之處都改用新的 class 名稱。

除此之外，檔案名稱也從重構成新的名稱。
## Movement

-----

### Move Static Member

實務上因需求改變，原本的 field 與 method 可能不再適合目前的 class，建議使用`Move Static Member`搬移 field 與 method 到適當的 class。
 重構前 

```php
namespace App\Services;

class MoveStaticMember1
{
    public static $var1 = 'Hello World';

    public static function print()
    {
        echo(self::$var1);
    }
}

```

```php
namespace App\Services;

class MoveStaticMember2
{

}

```

```php
namespace App\Services;

class MoveStaticMember0
{
    public function print()
    {
        MoveStaticMember1::print();
    }
}

```
 重構後 

```php
namespace App\Services;

class MoveStaticMember1
{

}

```

```php
namespace App\Services;

class MoveStaticMember2
{
    public static $var1 = 'Hello World';

    public static function print()
    {
        echo(self::$var1);
    }
}

```

```php
namespace App\Services;

class MoveStaticMember0
{
    public function print()
    {
        MoveStaticMember2::print();
    }
}

```

[][115]

將滑鼠游標放在`$var1`或`print()`上，按熱鍵跳出`Refactor This`選單，選擇`Move`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][116]

選擇要重構的 member，並指定要重構到的新 class。

[][117]

原 class 已經無任何 member。

[][118]

所有的 member 都已經重構到新 class。

[][119]

原來使用舊 class 之處已經重構成新 class。

目前 PhpStorm 僅支援對 static member 的重構到其他 class，對於一般的 field / const / method，則必須手動重構。

### Move Class

實務上因需求改變，原本在某一 namespace 下的 class，可能不再適合目前的 namespace，建議使用`Move Class`將 class 重構到適當的 namespace。
 重構前 

```php
namespace App\Services;

class PostService
{
    /** @var Sendable */
    private $SMSService;

    /**
     * PostService constructor.
     * @param Sendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```

```php
use App\Services\PostService;
use App\Services\SMSService;

class PostServiceIntegrationTest extends TestCase
{
    /** @var PostService */
    protected $target;

    protected function setUp()
    {
        parent::setUp();
        $SMSService = new SMSService();
        $this->target = new PostService($SMSService);
    }

    /** @test */
    public function 顯示正確簡訊()
    {
        /** arrange */

        /** act */
        $actual = $this->target->showMessage();

        /** assert */
        $expected = 'Send Message';
        $this->assertEquals($expected, $actual);
    }
}

```
 重構後 

```php
namespace App\Services\Post;

class PostService
{
    /** @var Sendable */
    private $SMSService;

    /**
     * PostService constructor.
     * @param Sendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```

```php
use App\Services\Post\PostService;
use App\Services\SMSService;

class PostServiceIntegrationTest extends TestCase
{
    /** @var PostService */
    protected $target;

    protected function setUp()
    {
        parent::setUp();
        $SMSService = new SMSService();
        $this->target = new PostService($SMSService);
    }

    /** @test */
    public function 顯示正確簡訊()
    {
        /** arrange */

        /** act */
        $actual = $this->target->showMessage();

        /** assert */
        $expected = 'Send Message';
        $this->assertEquals($expected, $actual);
    }
}

```

[][120]

將滑鼠游標放在 class 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Move`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][121]

在`Move Class PostService to namespace`填入新的 namespace 完整路徑，可以是既有 namespace，也可以是新的 namespace，若是新的 namespace，PhpStorm 會自動幫你建立目錄。

要勾選`Search in comments and strings`與`Search for text occurences`，PhpStorm 會一併將有用到此 class 的地方一起修改。

[][122]

重構前的預覽，PhpStorm 告知即將對以下檔案進行重構，按`Do Refactor`繼續。

[][123]

PhpStorm 會幫我們建立新的子目錄，且 namespace 也做了修改。

[][124]

使用到 class 的地方，`use`也跟會自動修改。
### Move Namespace

實務上因需求改變，原本在某一 namespace 下的所有 class，可能不再適合目前的 namespace，建議使用`Move Namespace`將所有 class 重構到適當的 namespace。
 重構前 

```php
namespace App\Services;

class SMSService implements Printable, Sendable
{
    public function printMessage()
    {
        echo('Print Message');
    }

    public function sendMessage() : string
    {
        return 'Send Message';
    }
}

```
 重構後 

```php
namespace App\Libs;

class SMSService implements Printable, Sendable
{
    public function printMessage()
    {
        echo('Print Message');
    }

    public function sendMessage() : string
    {
        return 'Send Message';
    }
}

```

[][125]

將滑鼠游標放在 namespace 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Move`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][126]

在`Move Namespace`填入新的 namespace 完整路徑，可以是既有 namespace，也可以是新的 namespace，若是新的 namespace，PhpStorm 會自動幫你建立目錄。

要勾選`Search in comments and strings`與`Search for text occurences`，PhpStorm 會一併將有用到原 namespace 的地方一起修改。

[][127]

PhpStorm 會列出所有即將重構的 class。

[][128]

重構前的預覽，PhpStorm 告知即將對以下檔案進行重構，按`Do Refactor`繼續。

[][129]

PhpStorm 幫我們將原來`Services`目錄下的 class 都重構到`Libs`目錄，且 namespace 也做了修改。
## Inheritance

-----

### Pull Members Up

若 class 之間有共用的邏輯，建議使用`Pull Members Up`重構到 super class，讓邏輯不再重複，符合 DRY 原則。
 重構前 

```php
namespace App\Services\Post;

use App\Services\Sendable;

class PostService extends AbstractPostService
{
    /** @var Sendable */
    private $SMSService;

    /**
     * PostService constructor.
     * @param Sendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```
 重構後 

```php
namespace App\Services\Post;

use App\Services\Sendable;

class PostService extends AbstractPostService
{

}

```

```php
namespace App\Services\Post;

use App\Services\Sendable;

class AbstractPostService
{
    /** @var Sendable */
    protected $SMSService;

    /**
     * PostService constructor.
     * @param Sendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```

[][130]

將滑鼠游標放在欲 pull up 的 member 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Pull Members Up`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][131]

選擇要 pull up 的 member，若是`private`，PhpStorm 會升格成`protected`。

[][132]

原 class 內所有 member 都被 pull up。

[][133]

所有 member 都 pull up 到 super class 了。
### Push Members Down

實務上因需求改變，原本共用的邏輯可能不再共用，建議使用`Pull Members Down`將 member 從 super class 降回 sub class。
 重構前 

```php
namespace App\Services\Post;

use App\Services\Sendable;

class AbstractPostService
{
    /** @var Sendable */
    protected $SMSService;

    /**
     * PostService constructor.
     * @param Sendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```

```php
namespace App\Services\Post;

use App\Services\Sendable;

class PostService extends AbstractPostService
{

}

```
 重構後 

```php
namespace App\Services\Post;

use App\Services\Sendable;

class PostService extends AbstractPostService
{
    /** @var Sendable */
    private $SMSService;

    /**
     * PostService constructor.
     * @param Sendable $SMSService
     */
    public function __construct(Sendable $SMSService)
    {
        $this->SMSService = $SMSService;
    }

    public function showMessage()
    {
        return $this->SMSService->sendMessage();
    }
}

```

```php
namespace App\Services\Post;

use App\Services\Sendable;

class AbstractPostService
{

}

```

[][134]

將滑鼠游標放在欲 pull down 的 member 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Push Members Down`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][135]

選擇要 push down 的 member，若是`__`的 magic method，PhpStorm 會提出警告。

[][136]

原 class 內所有 member 都被 push down。

[][137]

所有 member 都 push down 到 sub class 了。
## Others

-----

### If Else to Switch

由於`if else`較容易思考，所以很容易寫出`if else`的程式碼，PhpStorm 可以幫我們重構成可讀性較高的`switch`。
 重構前 

```php
class OrderService
{
    /**
     * @param string $type
     * @param int $days
     * @return int
     */
    public function calculatePrice(string $type, int $days): int
    {
        if ($type == 'Regular') {
            return ($days - 7) * 10;
        } elseif ($type == 'NewRelease') {
            return ($days - 3) * 30;
        } elseif ($type == 'Children') {
            return ($days - 7) * 10;
        } else {
           return ($days - 7) * 10;
        }
    }
}

```
 重構後 

```php
namespace App\Services;

class OrderService
{
    /**
     * @param string $type
     * @param int $days
     * @return int
     */
    public function calculatePrice(string $type, int $days): int
    {
        switch ($type) {
            case 'Regular':
                return ($days - 7) * 10;
            case 'NewRelease':
                return ($days - 3) * 30;
            case 'Children':
                return ($days - 7) * 10;
            default:
                return ($days - 7) * 10;
        }
    }
}

```

[][138]

將滑鼠游標放在`if`之前，按熱鍵選擇`Replace if with switch`。

 **`Windows`**  : Alt + enter
 **`macOS`**  : option + return

[][139]

PhpStorm 會幫我們將`if else`重構成`switch`。
### Inline

大部分的狀況，`Extract Variable`會讓程式碼可讀性更高，也更好維護，若發現因此造成程式碼更為複雜，或者多餘，建議使用`Inline Variable`重構刪除變數。
 重構前 

```php
namespace App\Services;

class InlineVariable
{
    public function print()
    {
        $name = 'Sam';
        
        echo($name);
    }
}

```
 重構後 

```php
namespace App\Services;

class InlineVariable
{
    public function print()
    {
        echo('Sam');
    }
}

```

[][140]

將滑鼠游標放在欲 inline 的 variable 名稱上，按熱鍵跳出`Refactor This`選單，選擇`Inline`。

 **`Windows`**  : Ctrl + Alt + Shift + T
 **`macOS`**  : control + T

[][141]

PhpStorm 會列出它所找到該 inline 的變數個數，按`OK`繼續。

[][142]

PhpStorm 會自動幫我們將變數刪除，直接使用值取代變數。
## Conclusion

-----


* 所有的重構動作若不滿意，都可以取消重構，此外，每個重構步驟都該搭配 git 做版控，確保重構失敗後可以正確還原。
* 認為重構很花時間的人，是因為沒有善用工具，若能善用 PhpStorm 的重構功能，就能大幅節省重構所花的時間，減少技術債。


## Sample Code

-----

完整的範例可以在我的 [GitHub][143] 上找到。
## Reference

-----

Martin Fowler, [Refactoring: Improving The Design of Existing Code][144]
GoF, [Design Pattern][145]

[71]: #
[72]: ./img/refactor000.png 
[73]: ./img/refactor001.png 
[74]: ./img/refactor002.png 
[75]: ./img/refactor003.png 
[76]: ./img/refactor004.png 
[77]: ./img/refactor005.png 
[78]: ./img/refactor006.png 
[79]: ./img/refactor013.png 
[80]: ./img/refactor014.png 
[81]: ./img/refactor015.png 
[82]: ./img/refactor016.png 
[83]: ./img/refactor010.png 
[84]: ./img/refactor011.png 
[85]: ./img/refactor012.png 
[86]: ./img/refactor007.png 
[87]: ./img/refactor008.png 
[88]: ./img/refactor009.png 
[89]: ./img/refactor017.png 
[90]: ./img/refactor018.png 
[91]: ./img/refactor019.png 
[92]: ./img/refactor020.png 
[93]: ./img/refactor021.png 
[94]: ./img/refactor022.png 
[95]: ./img/refactor023.png 
[96]: ./img/refactor024.png 
[97]: ./img/refactor025.png 
[98]: ./img/refactor026.png 
[99]: ./img/refactor027.png 
[100]: ./img/refactor028.png 
[101]: ./img/refactor029.png 
[102]: ./img/refactor030.png 
[103]: ./img/refactor031.png 
[104]: ./img/refactor032.png 
[105]: ./img/refactor033.png 
[106]: ./img/refactor034.png 
[107]: ./img/refactor035.png 
[108]: ./img/refactor036.png 
[109]: ./img/refactor037.png 
[110]: ./img/refactor038.png 
[111]: ./img/refactor039.png 
[112]: ./img/refactor040.png 
[113]: ./img/refactor041.png 
[114]: ./img/refactor042.png 
[115]: ./img/refactor043.png 
[116]: ./img/refactor044.png 
[117]: ./img/refactor045.png 
[118]: ./img/refactor046.png 
[119]: ./img/refactor047.png 
[120]: ./img/refactor048.png 
[121]: ./img/refactor049.png 
[122]: ./img/refactor050.png 
[123]: ./img/refactor051.png 
[124]: ./img/refactor052.png 
[125]: ./img/refactor053.png 
[126]: ./img/refactor054.png 
[127]: ./img/refactor055.png 
[128]: ./img/refactor056.png 
[129]: ./img/refactor057.png 
[130]: ./img/refactor058.png 
[131]: ./img/refactor059.png 
[132]: ./img/refactor060.png 
[133]: ./img/refactor061.png 
[134]: ./img/refactor062.png 
[135]: ./img/refactor063.png 
[136]: ./img/refactor064.png 
[137]: ./img/refactor065.png 
[138]: ./img/refactor066.png 
[139]: ./img/refactor067.png 
[140]: ./img/refactor068.png 
[141]: ./img/refactor069.png 
[142]: ./img/refactor070.png 
[143]: https://github.com/oomusou/Laravel52Refactoring_demo
[144]: https://www.tenlong.com.tw/items/9861547533%3Fitem_id%3D45657
[145]: https://www.tenlong.com.tw/products/9789572054116