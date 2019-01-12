## centos为用户增加ssh key

来源：<https://www.cnblogs.com/juandx/p/5790758.html>

时间：2016-08-20 16:48

 **`linux增加用户，为用户增加key`** 
 **`可以用 `** 

```LANG
ssh-keygen -t rsa

```
 **`添加ssh的key，会得到public_key和自己的private_key`** 
 **`然后这个key可以用在任何用户上`** 

```LANG
    adduser wenbin

    cd /home/wenbin

    mkdir .ssh

    echo public_key >> /home/wenbin/.ssh/authorized_keys

    chown wenbin.wenbin /home/wenbin/.ssh -R
```

权限很重要！

.ssh权限为755

authorized_keys权限为644
