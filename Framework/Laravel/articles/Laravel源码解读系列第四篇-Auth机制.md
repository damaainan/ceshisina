# Laravel源码解读系列第四篇-Auth机制 

Published on Sep 19, 2017 in [Laravel][0][PHP][1] with [1 comment][2]

## 前言

Laravel有一个神器:

    php artisan make:auth

能够快速的帮我们完成一套注册和登录的认证机制，但是这套机制具体的是怎么跑起来的呢？我们不妨来一起看看他的源码。不过在这篇文章中，我只会阐述大致的流程，至于一些具体的细节，比如他的登录次数限制是怎么完成的之类的不妨自己去寻找答案。

## 过程

### 路由

当我们执行完命令之后，我们会发现，在`routes/web.php`中多了这样一行代码:

    Auth::routes();

结合我们在前面讲到的[Facades][3]，我们会执行Facades/Auth.php的routes方法:

    public static function routes()
        {
            static::$app->make('router')->auth();
        }

而`$app->make('router')`会返回一个`Routing/Router.php`对象的实例，而他的auth方法是:

    public function auth()
        {
            // Authentication Routes...
            $this->get('login', 'Auth\LoginController@showLoginForm')->name('login');
            $this->post('login', 'Auth\LoginController@login');
            $this->post('logout', 'Auth\LoginController@logout')->name('logout');
    
            // Registration Routes...
            $this->get('register', 'Auth\RegisterController@showRegistrationForm')->name('register');
            $this->post('register', 'Auth\RegisterController@register');
    
            // Password Reset Routes...
            $this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
            $this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
            $this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
            $this->post('password/reset', 'Auth\ResetPasswordController@reset');
        }

而这里的get和post方法，其实也就是我们通过Route::get等最终会调用的方法。

### 注册

我们直接看表单提交的方法:

            $this->post('register', 'Auth\RegisterController@register');

    //RegisterController.php
    class RegisterController extends Controller
    {
        /*
        |--------------------------------------------------------------------------
        | Register Controller
        |--------------------------------------------------------------------------
        |
        | This controller handles the registration of new users as well as their
        | validation and creation. By default this controller uses a trait to
        | provide this functionality without requiring any additional code.
        |
        */
    
        use RegistersUsers;
    
        /**
         * Where to redirect users after registration.
         *
         * @var string
         */
        protected $redirectTo = '/home';
    
        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->middleware('guest');
        }
    
        /**
         * Get a validator for an incoming registration request.
         *
         * @param  array  $data
         * @return \Illuminate\Contracts\Validation\Validator
         */
        protected function validator(array $data)
        {
            return Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
            ]);
        }
    
        /**
         * Create a new user instance after a valid registration.
         *
         * @param  array  $data
         * @return User
         */
        protected function create(array $data)
        {
            return User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['password']),
            ]);
        }
    }

里面使用到了RegistersUsers这个trait，里面就有我们需要的register方法:

    public function register(Request $request)
        {
            $this->validator($request->all())->validate();
    
            event(new Registered($user = $this->create($request->all())));
    
    //        默认情况下,$this->guard()会返回一个`SessionGuard`的对象
            $this->guard()->login($user);
    
            return $this->registered($request, $user)
                            ?: redirect($this->redirectPath());
        }
    
        protected function guard()
        {
            return Auth::guard();
        }

其中最核心的部分就是:

    $this->guard()->login($user);

而`$this->guard()`在默认配置下会返回一个`SessionGuard`的对象，具体是这样实现的:

前面我们有讲过，当我们执行Facades下一个不存在的方法时，我们会调用`Facade.php`的`__callStatic`方法，这个方法会获取当前对象的getFacadeAccessor方法而返回一个对应的对象并调用他所需要调用的方法，这个详细过程在我的[第三篇][3]中都有所阐述。  
而auth的别名绑定，是在我们[初始化][4]的过程中绑定的，具体可以看我写的[第一篇][4]，所以，这里我们会调用AuthManager这个对象:

     public function guard($name = null)
        {
            $name = $name ?: $this->getDefaultDriver();
    
            return isset($this->guards[$name])
                        ? $this->guards[$name]
                        : $this->guards[$name] = $this->resolve($name);
        }
    
    public function getDefaultDriver()
        {
            return $this->app['config']['auth.defaults.guard'];
        }
    
    protected function resolve($name)
        {
            $config = $this->getConfig($name);
    
            if (is_null($config)) {
                throw new InvalidArgumentException("Auth guard [{$name}] is not defined.");
            }
    
            if (isset($this->customCreators[$config['driver']])) {
                return $this->callCustomCreator($name, $config);
            }
    
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';
    
            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($name, $config);
            }
    
            throw new InvalidArgumentException("Auth guard driver [{$name}] is not defined.");
        }

    //config/auth.php
    
    'defaults' => [
            'guard' => 'web',
            'passwords' => 'users',
        ],
    
        'guards' => [
            'web' => [
                'driver' => 'session',
                'provider' => 'users',
            ],
    
            'api' => [
                'driver' => 'token',
                'provider' => 'users',
            ],
        ],

通过调用AuthManager可以拼接得出，最终他会调用一个createSessionDriver方法:

    public function createSessionDriver($name, $config)
        {
            $provider = $this->createUserProvider($config['provider']);
    
            $guard = new SessionGuard($name, $provider, $this->app['session.store']);
    
            if (method_exists($guard, 'setCookieJar')) {
                $guard->setCookieJar($this->app['cookie']);
            }
    
            if (method_exists($guard, 'setDispatcher')) {
                $guard->setDispatcher($this->app['events']);
            }
    
            if (method_exists($guard, 'setRequest')) {
                $guard->setRequest($this->app->refresh('request', $guard, 'setRequest'));
            }
    
            return $guard;
        }

所以最终是调用了SessionGuard的login方法来完成session部分的功能。

### 登录

展示表单的部分就不赘述了，这里我们直接看:

    $this->post('login', 'Auth\LoginController@login');

    //LoginController.php
    class LoginController extends Controller
    {
        use AuthenticatesUsers;
    
        /**
         * Where to redirect users after login.
         *
         * @var string
         */
        protected $redirectTo = '/home';
    
        /**
         * Create a new controller instance.
         *
         * @return void
         */
        public function __construct()
        {
            $this->middleware('guest')->except('logout');
        }
    }

他使用到了一个AuthenticatesUserstrait，里面有一个login方法，也就是我们要使用到的login了:

    public function login(Request $request)
        {
            $this->validateLogin($request);
    
            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);
    
                return $this->sendLockoutResponse($request);
            }
    
            if ($this->attemptLogin($request)) {
                return $this->sendLoginResponse($request);
            }
    
            $this->incrementLoginAttempts($request);
    
            return $this->sendFailedLoginResponse($request);
        }

其实大部分和register的部分差不多，核心部分还是在于SessionGuard对象的获取，这里就不过多赘述了，不过值得一提的是，像`Auth::check()`等很多方法的使用，其实也会通过SessionGuard来完成，主要是通过:

    public function __call($method, $parameters)
        {
            return $this->guard()->{$method}(...$parameters);
        }

来得以完成调度。

本文由 [nine][5] 创作，采用 [知识共享署名4.0][6] 国际许可协议进行许可  
本站文章除注明转载/出处外，均为本站原创或翻译，转载前请务必署名  
最后编辑时间为: Sep 19, 2017 at 10:29 am

[0]: http://www.hellonine.top/index.php/category/laravel/
[1]: http://www.hellonine.top/index.php/category/PHP/
[2]: #comments
[3]: http://www.hellonine.top/index.php/archives/29/#directory096620026240848318
[4]: http://www.hellonine.top/index.php/archives/6/
[5]: http://www.hellonine.top/index.php/author/1/
[6]: https://creativecommons.org/licenses/by/4.0/