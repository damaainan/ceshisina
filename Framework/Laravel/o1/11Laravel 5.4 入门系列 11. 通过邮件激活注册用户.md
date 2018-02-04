## Laravel 5.4 入门系列 11. 通过邮件激活注册用户

## 使用 Mailtrap 测试邮件功能

Mailtrap 提供了简单的测试邮件的服务，步骤如下：

1. 登录网站 Mailtrap
1. 注册用户
1. 注册成功之后，会自动创建一个 demo，点进去之后就可以看到配置信息


![][0]

只需要把上面的信息配置到对应的 .env 中即可:

    MAIL_DRIVER=smtp
    MAIL_HOST=smtp.mailtrap.io
    MAIL_PORT=2525
    MAIL_USERNAME=fc0ad24e593f34
    MAIL_PASSWORD=aef271516bc810
    MAIL_ENCRYPTION=null

这样，我们在应用里面发出去的邮件你都能在网站的 demo 中看到了。

## 用户激活功能实现

### 用户激活功能实现思路

接下来谈谈要实现的用户激活功能的基本过程：

1. 创建数据表，保存用户激活信息；
1. 提供激活选项给登录其未激活的用户；
1. 用户点击激活之后，生成一个唯一的秘钥，保存到数据库；同时将秘钥以参数形式附在超链接的后面显示在用户邮箱正文中；
1. 用户在邮箱中点击超链接，返回网站；
1. 网站根据参数是否匹配以及是否过期（24小时）来判断用户是否激活成功；

### 创建数据表

因为激活的功能并不常用，将其单独放置于一张表中:

    $ php artisan make:model UserActivation -m

编辑字段:

    /database/migrations/2017_04_21_071142_create_user_activations_table.php
     public function up()
    {
        Schema::create('user_activations', function (Blueprint $table) {
    
            $table->integer('user_id')->unsigned()->primary();
            $table->string('token')->unique();
            $table->boolean('active')->default(0);
            $table->timestamps();
    
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

执行:

    $ php artisan migrate

### 添加用户表与用户激活表的一对一关系

用户表与用户激活表为一对一关系:

    /app/User.php
    public function activations()
    {
        return $this->hasOne(\App\UserActivation::class);
    }

允许用户激活表批量赋值:

    /app/UserActivation.php
    protected $fillable= ['user_id','active','token'];

### 添加激活选项

首先，让登录且未激活的用户可以点击「激活」按钮：

    /resources/views/layouts/nav.blade.php
    @if (Auth::check())
        <a class="dropdown-item" href="#">{{ Auth::user()->name }}</a>
        <a class="dropdown-item" href="/logout">登出</a>
        @if (is_null(Auth::user()->activations) || Auth::user()->activations->active == 0)
            <a class="dropdown-item" href="/sendActiveMail">发送激活邮件</a>
        @endif
    @else            

路由指派该请求:

    Route::get('/sendActivationMail','RegistrationController@send');

在实现具体的方法之前，我们先来介绍下如何快速发送邮件。

### 快速发送邮件

先来看看 Laravel 提供的选项：

    $message->from($address, $name = ); # 邮件的所有者
    $message->sender($address, $name = ); # 邮件的实际传输人，若所有者与发送者为同一人，可省略
    $message->replyTo($address, $name = ); # 作者建议回复的地址
    $message->to($address, $name = ); # 收件人的地址
    $message->cc($address, $name = ); # 收件人外，想让其他知道该事的人的地址
    $message->bcc($address, $name = ); # 不想让 cc 的人看到你发送给了谁，那么就用 bcc 
    
    $message->subject($subject); #主题
    $message->priority($level);
    $message->attach($pathToFile, array $options = []);

注意，邮件的格式要遵守 [RFC2822][1] 规范，否则可能报错。

    from            =       "From:" mailbox-list CRLF
    sender          =       "Sender:" mailbox CRLF
    reply-to        =       "Reply-To:" address-list CRLF
    to              =       "To:" address-list CRLF
    cc              =       "Cc:" address-list CRLF
    bcc             =       "Bcc:" (address-list / [CFWS]) CRLF

我们举一个简单的例子：

    public function send()
    {   
        
        \Mail::raw('test laravel blog email function',function ($message){
            $from = ['From: from@qq.com','fromman'];
            $to = ['To: to@qq.com','toman'];
            $cc = ['Cc: cc@qq.com','ccman'];
            $bcc = ['Bcc: bcc@qq.com','bccman'];
            $replyTo = ['Reply-To: replyto@qq.com','replytoman'];
            $sender = ['Sender: sender@qq.com','senderman'];
            $subject = 'Subject Subject';
            $message->to($to)
                    ->from($from)
                    ->sender($sender)
                    ->cc($cc)
                    ->bcc($bcc)
                    ->replyTo($replyTo)
                    ->subject($subject);
        });
    
        return redirect('/');
    }

raw 方法发送纯文本给对方。同时，我们在传入的闭包函数中进行配置。在本例子中，将会给 toman、ccman 和 bccman 三个人一人发送一封纯文本邮件，因此，我们在 Mailtrap 中会收到三封：

![][2]

因为 bcc 是密送，所以发送给 ccman 的邮件中看不到 bccman ：

![][3]

在上述的配置字段中， from 字段以预先定义好，这样就不需要每次都填写了。当然，如果使用了 $message->from 方法，就会覆盖配置。

    /config/mail.php
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', '"From: "demo@blog.com'),
        'name' => env('MAIL_FROM_NAME', 'Zen'),
    ],

### 激活功能实现

现在，我们实现可以发送激活邮件的功能了：

    use App\UserActivation;
    
    public function send()
    {    
        // 生成唯一 token
        $token = bcrypt(auth()->user()->email.time());
        $user = auth()->user();
        
         // 发送邮件
        \Mail::send('emails.activation', compact('user', 'token'), function ($message) {
            $to = ['To: '.auth()->user()->email, auth()->user()->name];
            $subject = 'blog demo 请您激活账户';
    
            $message->to($to)
                    ->subject($subject);
        });
        
        
         // 数据库保存 token
        if ($user->activations){
            $user->activations()->update(['token'=>$token]);
        } else {
            $user->activations()->save(new UserActivation([
                'token' => $token
            ]));
        }
       
        // 发送并保存成功，跳转到主页
        return redirect('/');
    }

这次，我们使用的是 Mail::send 方法，该方法可以传入视图作为邮件内容，同时第二个参数里传入给视图的数据，接下来定义视图:

    /resources/views/emails/activation.blade.php
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        <p>您好, {{ $user->name }} ！ 请点击下面链接完成注册:</p>
        <a href="http://localhost:8000/activeAccount/?verify={{$token}}">激活链接</a>
        
    </body>
    </html>

用户将会收到这样的邮件:

![][4]

点击链接之后，会向网站发送请求，路由进行指派:

    Route::get('/activeAccount','RegistrationController@active');

最后，判断该 token 是否过期或者是否匹配，匹配则跳转到登录页面，否则跳转到主页。

    use Carbon\Carbon;
    public function active()
    {
        $token = request('verify');
        $rs = UserActivation::where('token', $token)
                            ->whereBetween('updated_at', [Carbon::now()->subDay(), Carbon::now()]);
        if ($rs->exists()) {
            $rs->update(['active'=>true]);
            return redirect('/login');
        }
    
        return redirect('/');
    }

功能已经实现了。我们稍微封装下代码吧:

    /app/Http/Controllers/RegistrationController.php
    public function send()
    {
        $token = bcrypt(auth()->user()->email.time());
        $user = auth()->user();
    
        \Mail::send('emails.activation', compact('user', 'token'), function ($message) {
        
            $to = ['To: '.auth()->user()->email, auth()->user()->name];
            $subject = 'blog demo 请您激活账户';
    
            $message->to($to)
                    ->subject($subject);
        });
    
    
        $user->addActivationsData($token);
       
       
        return redirect('/');
    }
    
    public function active()
    {
        $token = request('verify');
        $rs = UserActivation::where('token', $token)
                            ->notExpired();
    
        if ($rs->exists()) {
            $rs->update(['active'=>true]);
            return redirect('/login');
        }
    
        return redirect('/');
    }

addActivationsData 方法用于保存或更新 $token 字段:

    /app/User.php
    public function addActivationsData($token)
    {   
        if ($this->activations) {
            $this->activations()->update(['token'=>$token]);
        } else {
            $this->activations()->save(new \App\UserActivation([
                'token' => $token
            ]));
        }
    }

notExpired 方法用于判断 token 是否在 24 小时之内:

    /app/UserActivation.php
    use Carbon\Carbon;
    public function scopeNotExpired($query)
    {
        return $query->whereBetween('updated_at', [Carbon::now()->subDay(), Carbon::now()]);
    }

## 使用 mailables 管理邮件

在刚才的例子中，我们使用的是 Mail 提供的方法来快速创建和发送邮件。实际上，Laravel 提供了管理不同类型邮件的方法。我们来快速了解下。

### 创建「欢迎」邮件类型

首先，我们来创建一个用于欢迎新用户的 mailables:

    $ php artisan make:mail Welcome

首次使用时，会创建 app/Mail 目录。接下来，我们就可以实现给用户发送欢迎邮件的功能了。

首先，用户注册成功之后，发送一封欢迎邮件:

    /app/Http/Controllers/RegistrationController.php
    use App\Mail\Welcome;
    public function store()
    {
        ...
        
        auth()->login($user);
    
        \Mail::to($user)->send(new Welcome);
    
        return redirect()->home();
    }

之前我们通过闭包的方式传递 from 、to 等字段，实际上，也可以直接将 $user 实例直接传递给 to，这样会自动去识别 name 和 from 字段。然后，send 方法传入 mailables 类即可。

> 如果使用 > to($user)>  可能会报错，因为不符合我们之前说的 RFC2822 规范。

### 配置 mailables

接下来，发送邮件的主要功能都可以在定义的 Welcome 类中实现了。

可以定义邮件的 blade 视图：

    /app/Mail/Welcome.php
    public function build()
    {   
        return $this->view('emails.welcome');
    }

显示结果为:

![][5]

也可以定义纯文本视图:

    /app/Mail/Welcome.php
    public function build()
    {   
        
        return $this->text('emails.welcome');
    }

![][6]

对应的视图文件如下:

    /resources/views/emails/welcome.blade.php
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Document</title>
    </head>
    <body>
        <h1>{{$name}}, 欢迎成为 Blog Demo 的会员</h1>
    </body>
    </html>

可以配置邮件的各种信息，跟之前的方式类似：

    public function build()
    {   
        $from = ['From: from@qq.com','fromman'];
        $to = ['To: to@qq.com','toman'];
        $subject = 'Subject Subject';
    
        return $this->text('emails.welcome')
                    ->to($to)
                    ->from($from)
                    ->subject($subject);
    
    }

可以传递变量给视图，第一种是直接在 view 里面传递（也可以使用 with):

    /app/Mail/Welcome.php
    public function build()
    {   
        $from = ['From: from@qq.com','fromman'];
        $to = ['To: to@qq.com','toman'];
        $subject = 'Subject Subject';
        $user = auth()->user();
        return $this->view('emails.welcome',compact('user'))
                    ->to($to)
                    ->from($from)
                    ->subject($subject);
    
    
    }
    
    /resources/views/emails/welcome.blade.php
    <h1> {{$user->name }}, 欢迎成为 Blog Demo 的会员</h1>

另外一种方法是，在 Welcome 类中定义属性类型为「公共的」。这样该属性就会自动传递给视图：

    /app/Http/Controllers/RegistrationController.php
    \Mail::send(new Welcome);
    
    /app/Mail/Welcome.php
    public $user;
    
    public function __construct(User $user)
    {
        $this->user = $user;
    }

最后，我们还可以使用 Markdown 的语法来写邮件:

    $ php artisan make:mail MDWelcome --markdown="emails.md-welcome"

创建使用使用 --markdown 参数来生成对应的视图即可。具体使用方法可以查看文档。

- - -

参考资料：

* [RFC2822 中文文档][1]
* [Laravel 的 邮件发送功能 | Laravel 5.4 中文文档][7]

[0]: /img/bVMz4T?w=1084&h=1178
[1]: https://wenku.baidu.com/view/467971f6988fcc22bcd126fff705cc1755275f26.html
[2]: /img/bVMz4V?w=1604&h=394
[3]: /img/bVMz4W?w=600&h=582
[4]: /img/bVMz4Y?w=1020&h=490
[5]: /img/bVMz40?w=996&h=482
[6]: /img/bVMz41?w=918&h=622
[7]: http://d.laravel-china.org/docs/5.4/mail