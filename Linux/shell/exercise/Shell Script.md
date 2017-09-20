# Linux的Shell Script

 时间 2017-09-15 16:27:2  [简书][0]

原文[http://www.jianshu.com/p/1f478f36b006][1]


### 起步

#### 注意事项 :

1. 命令，参数间的多个空白都会被忽略掉
1. 空白行也会被忽略掉，[tab]所得空白等于[space]所得空白
1. 如果得到一个Enter符号(CR),就尝试执行该命令
1. "#"号后面为注释

#### 执行方法 :

1. 直接命令执行(.sh文件必须具有rx权限)
1. 以bash进程来执行(有r权限即可执行),该种执行方式在子进程中执行，所以声明的变量在父进程中访问不到
```
    eg: bash shell.sh
    或
    eg: sh shell.sh
```
1. 用source执行,该种执行方式是使用父进程执行，故声明的变量可以访问
```
    eg: source shell.sh
    或
    eg: . shell.sh
```
### 判断符号:
```
    判断result变量是否等于Y
    [ "$result" == "Y" ]
```
### shell script默认变量:
```
    eg: shell.sh opt1 opt2
           $0    $1   $2
```
* **$#** : 后接参数的个数
* **$@** : 代表"$1","$2"...
```
    eg: echo "All parameters are '$@'"
```
### shift变量偏移:
```
    从前往后偏移number个变量
    shift [number]
```
### if...then...
```
    if [条件判断式] ; then
      ...
    else
      ...
    fi
```

多重条件判断

    if [条件判断式] ; then
      ...
    elif [条件判断式] ; then
      ...
    else
      ...
    fi

举个例子:

    if [ "$result" == "Y" ] || [ "$result" == "y" ] ; then
      echo "ok"
    fi

### case...esac

    case $变量名称 in
      "变量内容1")
       ...
       ;;
    
       "变量内容2")
       ...
       ;;
    
       *)
       ...
       ;;
    esac

### function

function拥有内置变量$0,$1,$2...,与shell script一样 

    function name () {
    
    }

举个例子:

    function printit() {
      echo "Your choice is $1"
    }
    
    #调用
    printit 1

### 循环loop:

* while循环
```
    while [condition]
    do
    ...
    done
```

举个例子

    while [ "$yn" != "yes" -a "$yn" != "YES" ]
    do
      echo "Input yes/YES"
    done

* for循环(第一种形式)
```
    for var in con1 con2 con3
    do
    ...
    done
```


举个例子

    users=$(last | cut -d ' ' -f1)
    for username in $users
    do
     echo "$username"
    done

    for i in $(seq 1 100)
    do
     echo "$i"
    done

* for循环(第二种形式)
```
    for ((...;...;...))
    do
    ...
    done
```

举个例子

    read -p "Input a num: " num
    s=0
    for ((i=1;i<=$num;i=i+1))
    do
      s=$(($s+$i))
    done
    echo "the sum is $s"


[1]: http://www.jianshu.com/p/1f478f36b006
