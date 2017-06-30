 spl_autoload_unregister — 注销已注册的__autoload()函数

### 说明

> bool  **spl_autoload_unregister** ( mixed $autoload_function )

从spl提供的自动装载函数队列中注销某一函数。如果该函数队列处于激活状态，并且在给定函数注销后该队列变为空，则该函数队列将会变为无效。 

如果该函数注销后使得自动装载函数队列无效，即使存在有__autoload函数它也不会自动激活。 

### 参数

- autoload_function   
要注销的自动装载函数。 

### 返回值

成功时返回 **TRUE**， 或者在失败时返回 **FALSE**。

