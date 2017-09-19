# if和case应用

关注 2017.07.04 23:04  字数 175  

1、编写脚本/root/bin/createuser.sh，实现如下功能：使用一个用户名做为参数，如果指定参数的用户存在，就显示其存在，否则添加之；显示添加的用户的id号等信息

    #!/bin/bash
    useradd $1 &> /dev/null
    if [ $? -eq 0 ] ;then 
        echo "user  $1 is created"
        id $1
    else
        echo "user exist already or no argument "
    fi

2、编写脚本/root/bin/yesorno.sh，提示用户输入yes或no,并判断用户输入的是yes还是no,或是其它信息

    #!/bin/bash
    read -p "please input yes or no: " ans
    case $ans in
    [Yy]|[Yy][Ee][Ss])
            echo "your answer is yes"
            ;;
    [Nn]|[Nn][Oo])
             echo "your answer is no" 
             ;;
    *)
             echo "Error,please input again" 
             ;;
    esac

3、编写脚本/root/bin/filetype.sh,判断用户输入文件路径，显示其文件类型（普通，目录，链接，其它文件类型）

    #!/bin/bash
    if [[ $# -lt 1 ]] ;then
        echo -e "Error: No argument.\n\tUsage: $0 FILENAME"
        exit 1
    else
        if [[ -e $1 ]] ;then
            FileType=`ls -ld $1 | cut -c1`
            case $FileType in
                l)
                    echo "$1 is a link file"
                    ;;
                d)
                    echo "$1 is a diretory"
                    ;;
                -)
                    echo "$1 is a common file"
                    ;;
                *)
                    echo "$1 is other file"
                    ;;
            esac
        else
            echo "$1: no such file or diretory."
        fi
    fi
    unset FileType

4、编写脚本/root/bin/checkint.sh,判断用户输入的参数是否为正整数

    #!/bin/bash
    read -p "请入一个数字: " num
    [ -z "$num" ] && echo "请输入一个数字" && exit 1
    NUM=$(echo $num | egrep -o "\-?[[:digit:]]+")
    if [ "$NUM" == "$num" ];then
             if [ "$NUM" -lt "0" ];then
                    echo "您输入的是一个负整数"
             elif [ "$NUM" -eq "0" ];then
                    echo "您输入的是一个0"
             else
                    echo "您输入的是一个正整数"
             fi
    else
            echo "您输入的不是一个整数,请重新运行脚本"
    fi
    unset NUM

