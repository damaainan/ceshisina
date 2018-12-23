### 下载整个网站


你甚至可以用wget下载完整的站点, 然后进行离线浏览. 方法是使用如下命令:

```sh
wget --mirror --convert-links --page-requisites --no-parent -P /path/to/download https://example-domain.com

wget --mirror --convert-links --page-requisites --no-parent -P ./ https://www.c82.net/euclid/
```


**`—mirror`** 会开启镜像所需要的所有选项.


**`–convert-links`** 会将所有链接转换成本地链接以便离线浏览.


**`–page-requisites`** 表示下载包括CSS样式文件，图片等所有所需的文件，以便离线时能正确地现实页面.


`–no-parent` 用于限制只下载网站的某一部分内容.


此外, 你可以使用 =P= 设置下载路径.