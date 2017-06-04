# [aria2 让下载提速][0]

**1.Linux 下安装 aria2**

我们可以很容易的在所有的 Linux 发行版上安装 aria2 命令行下载器，例如 Debian、 Ubuntu、 Mint、 RHEL、 CentOS、 Fedora、 suse、 openSUSE、 Arch Linux、 Manjaro、 Mageia 等等……只需要输入下面的命令安装即可。对于 CentOS、 RHEL 系统，我们需要开启uget 或者RPMForge 库的支持。

    [对于 Debian、 Ubuntu 和 Mint]
    $ sudo apt-get install aria2
    
    [对于 CentOS、 RHEL、 Fedora 21 和更早些的操作系统]
    # yum install aria2

**2.下载单个文件**

下面的命令将会从指定的 URL 中下载一个文件，并且保存在当前目录，在下载文件的过程中，我们可以看到文件的（日期、时间、下载速度和下载进度）。

    # aria2c https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2

**3.使用不同的名字保存文件**

在初始化下载的时候，我们可以使用 -o（小写）选项在保存文件的时候使用不同的名字。这儿我们将要使用 owncloud.zip 文件名来保存文件。

    # aria2c -o owncloud.zip https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2  

**4.下载速度限制**

默认情况下，aria2 会利用全部带宽来下载文件，在文件下载完成之前，我们在服务器就什么也做不了（这将会影响其他服务访问带宽）。所以在下载大文件时最好使用

    –max-download-limit

选项来避免进一步的问题。

    # aria2c --max-download-limit=500k https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2

**5.下载多个文件**

下面的命令将会从指定位置下载超过一个的文件并保存到当前目录，在下载文件的过程中，我们可以看到文件的（日期、时间、下载速度和下载进度）。

    # aria2c -Z https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2 ftp://ftp.gnu.org/gnu/wget/wget-1.17.tar.gz

**6.续传未完成的下载**

当你遇到一些网络连接问题或者系统问题的时候，并将要下载一个大文件（例如： ISO 镜像文件），我建议你使用 -c 选项，它可以帮助我们从该状态续传未完成的下载，并且像往常一样完成。不然的话，当你再次下载，它将会初始化新的下载，并保存成一个不同的文件名（自动的在文件名后面添加 .1）。注意：如果出现了任何中断，aria2 使用 .aria2 后缀保存（未完成的）文件。

    # aria2c -c https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2 
    如果重新启动传输，aria2 将会恢复下载。 
    # aria2c -c https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2

**7.从文件获取输入**

就像 wget 可以从一个文件获取输入的 URL 列表来下载一样。我们需要创建一个文件，将每一个 URL 存储在单独的行中。ara2 命令行可以添加 -i 选项来执行此操作。

    # aria2c -i test-aria2.txt

**8.每个主机使用两个连接来下载**

默认情况，每次下载连接到一台服务器的最大数目，对于一条主机只能建立一条。我们可以通过 aria2 命令行添加 -x2（2 表示两个连接）来创建到每台主机的多个连接，以加快下载速度。

    # aria2c -x2 https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2

**9.下载 BitTorrent 种子文件**

我们可以使用 aria2 命令行直接下载一个 BitTorrent 种子文件：

    # aria2c https://torcache.net/torrent/C86F4E743253E0EBF3090CCFFCC9B56FA38451A3.torrent?title=[kat.cr]irudhi.suttru.2015.official.teaser.full.hd.1080p.pathi.team.sr


**10.下载 BitTorrent 磁力链接**

使用 aria2 我们也可以通过 BitTorrent 磁力链接直接下载一个种子文件：

    aria2c 'magnet:?xt=urn:btih:248D0A1CD08284299DE78D5C1ED359BB46717D8C'

**11.下载 BitTorrent Metalink 种子**

我们也可以通过 aria2 命令行直接下载一个 Metalink 文件。

    aria2c https://curl.haxx.se/metalink.cgi?curl=tar.bz2

**12.从密码保护的网站下载一个文件**

或者，我们也可以从一个密码保护网站下载一个文件。下面的命令行将会从一个密码保护网站中下载文件。

    aria2c --http-user=xxx --http-password=xxx https://download.owncloud.org/community/owncloud-9.0.0.tar.bz2
    aria2c --ftp-user=xxx --ftp-password=xxx ftp://ftp.gnu.org/gnu/wget/wget-1.17.tar.gz
    

**13.阅读更多关于 aria2**

如果你希望了解了解更多选项 —— 它们同时适用于 wget，可以输入下面的命令行在你自己的终端获取详细信息：

    man aria2c
    or
    aria2c --help
    



[0]: http://www.linuxprobe.com/aria2-download.html
