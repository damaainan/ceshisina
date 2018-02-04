## Laravel 5.4 入门系列 12. 使用请求类进行字段验证

本节内容比较简单，之前我们使用 validator 方法来进行字段验证，这样做有一个不好的地方就是，如果你要在很多地方使用同样的验证，就需要重复编写代码。因此，Laravel 提供另外一种方式来进行字段验证，即「请求类」。

首先，创建请求类:

    $ php artisan make:request RegistrationForm

将注册相关信息转移到该类中:

    /app/Http/Requests/RegistrationForm.php
    
    use App\Mail\Welcome;
    use App\User;
    
    class RegistrationForm extends FormRequest
    {
    
        public function authorize()
        {
            return true;
        }
    
     
        public function rules()
        {
            return [
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed',
            ];
        }
    
        public function persist()
        {
    
            $user = User::create(
                $this->only(['name','password','email'])
            );
    
            auth()->login($user);
    
            \Mail::to($user)->send(new Welcome($user));
        }
    }

注解：

* 任何人都有权利做出该请求(即注册），所以 authorize() 应该返回 true；
* rule() 里面定义验证规则
* 我们将注册表的创建用户以及发送邮件功能也封装到了请求类中

控制器的代码可以简化成：

    use App\Http\Requests\RegistrationForm;
    
    public function store(RegistrationForm $request)
    {
        $request->persist();
    
        return redirect()->home();
    }

如果我们在其他地方也要使用该请求，直接传入 RegistrationForm 就可以了，是不是方便多了 :)

