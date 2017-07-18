# 【nginx运维基础(7)】常用PHP开源程序的NginxRewrite示例


> 在写伪静态的时候,可以先用一个打印$_GET的PHP文件来测试,并且一定注意浏览器缓存

## dedecms

```nginx

location / {
  rewrite "^/index.html$" /index.php last;
  rewrite "^/list-([0-9]+)\.html$" /plus/list.php?tid=$1 last;
  rewrite "^/list-([0-9]+)-([0-9]+)-([0-9]+)\.html$" /plus/list.php?tid=$1&totalresult=$2&PageNo=$3 last;
  rewrite "^/view-([0-9]+)-1\.html$" /plus/view.php?arcID=$1 last;
  rewrite "^/view-([0-9]+)-([0-9]+)\.html$" /plus/view.php?aid=$1&pageno=$2 last;
  rewrite "^/tags.html$" /tags.php last;
  rewrite "^/tag-([0-9]+)-([0-9]+)\.html$" /tags.php?/$1/$2/ last;
  break;
}
```

## discuz

    
```nginx
location / {
    rewrite ^/archiver/((fid|tid)-[\w\-]+\.html)$ /archiver/index.php?$1 last;
    rewrite ^/forum-([0-9]+)-([0-9]+)\.html$ /forumdisplay.php?fid=$1&page=$2 last;
    rewrite ^/thread-([0-9]+)-([0-9]+)-([0-9]+)\.html$ /viewthread.php?tid=$1&extra=page%3D$3&page=$2 last;
    rewrite ^/space-(username|uid)-(.+)\.html$ /space.php?$1=$2 last;
    rewrite ^/tag-(.+)\.html$ /tag.php?name=$1 last;
}

```

## discuzx

    
```nginx
location / {
    rewrite ^([^\.]*)/topic-(.+)\.html$ $1/portal.php?mod=topic&topic=$2 last;
    rewrite ^([^\.]*)/article-([0-9]+)-([0-9]+)\.html$ $1/portal.php?mod=view&aid=$2&page=$3 last;
    rewrite ^([^\.]*)/forum-(\w+)-([0-9]+)\.html$ $1/forum.php?mod=forumdisplay&fid=$2&page=$3 last;
    rewrite ^([^\.]*)/thread-([0-9]+)-([0-9]+)-([0-9]+)\.html$ $1/forum.php?mod=viewthread&tid=$2&extra=page%3D$4&page=$3 last;
    rewrite ^([^\.]*)/group-([0-9]+)-([0-9]+)\.html$ $1/forum.php?mod=group&fid=$2&page=$3 last;
    rewrite ^([^\.]*)/space-(username|uid)-(.+)\.html$ $1/home.php?mod=space&$2=$3 last;
    rewrite ^([^\.]*)/blog-([0-9]+)-([0-9]+)\.html$ $1/home.php?mod=space&uid=$2&do=blog&id=$3 last;
    rewrite ^([^\.]*)/(fid|tid)-([0-9]+)\.html$ $1/index.php?action=$2&value=$3 last;
    rewrite ^([^\.]*)/([a-z]+[a-z0-9_]*)-([a-z0-9_\-]+)\.html$ $1/plugin.php?id=$2:$3 last;
    if (!-e $request_filename) {
        return 404;
    }
}

```

## drupal
```nginx

if (!-e $request_filename) {
  rewrite ^/(.*)$ /index.php?q=$1 last;
}
```

## ecshop

```nginx
if (!-e $request_filename){
  rewrite "^/index\.html" /index.php last;
  rewrite "^/category$" /index.php last;
  rewrite "^/feed-c([0-9]+)\.xml$" /feed.php?cat=$1 last;
  rewrite "^/feed-b([0-9]+)\.xml$" /feed.php?brand=$1 last;
  rewrite "^/feed\.xml$" /feed.php last;
  rewrite "^/category-([0-9]+)-b([0-9]+)-min([0-9]+)-max([0-9]+)-attr([^-]*)-([0-9]+)-(.+)-([a-zA-Z]+)(.*)\.html$" /category.php?id=$1&brand=$2&price_min=$3&price_max=$4&filter_attr=$5&page=$6&sort=$7&order=$8 last;
  rewrite "^/category-([0-9]+)-b([0-9]+)-min([0-9]+)-max([0-9]+)-attr([^-]*)(.*)\.html$" /category.php?id=$1&brand=$2&price_min=$3&price_max=$4&filter_attr=$5 last;
  rewrite "^/category-([0-9]+)-b([0-9]+)-([0-9]+)-(.+)-([a-zA-Z]+)(.*)\.html$" /category.php?id=$1&brand=$2&page=$3&sort=$4&order=$5 last;
  rewrite "^/category-([0-9]+)-b([0-9]+)-([0-9]+)(.*)\.html$" /category.php?id=$1&brand=$2&page=$3 last;
  rewrite "^/category-([0-9]+)-b([0-9]+)(.*)\.html$" /category.php?id=$1&brand=$2 last;
  rewrite "^/category-([0-9]+)(.*)\.html$" /category.php?id=$1 last;
  rewrite "^/goods-([0-9]+)(.*)\.html" /goods.php?id=$1 last;
  rewrite "^/article_cat-([0-9]+)-([0-9]+)-(.+)-([a-zA-Z]+)(.*)\.html$" /article_cat.php?id=$1&page=$2&sort=$3&order=$4 last;
  rewrite "^/article_cat-([0-9]+)-([0-9]+)(.*)\.html$" /article_cat.php?id=$1&page=$2 last;
  rewrite "^/article_cat-([0-9]+)(.*)\.html$" /article_cat.php?id=$1 last;
  rewrite "^/article-([0-9]+)(.*)\.html$" /article.php?id=$1 last;
  rewrite "^/brand-([0-9]+)-c([0-9]+)-([0-9]+)-(.+)-([a-zA-Z]+)\.html" /brand.php?id=$1&cat=$2&page=$3&sort=$4&order=$5 last;
  rewrite "^/brand-([0-9]+)-c([0-9]+)-([0-9]+)(.*)\.html" /brand.php?id=$1&cat=$2&page=$3 last;
  rewrite "^/brand-([0-9]+)-c([0-9]+)(.*)\.html" /brand.php?id=$1&cat=$2 last;
  rewrite "^/brand-([0-9]+)(.*)\.html" /brand.php?id=$1 last;
  rewrite "^/tag-(.*)\.html" /search.php?keywords=$1 last;
  rewrite "^/snatch-([0-9]+)\.html$" /snatch.php?id=$1 last;
  rewrite "^/group_buy-([0-9]+)\.html$" /group_buy.php?act=view&id=$1 last;
  rewrite "^/auction-([0-9]+)\.html$" /auction.php?act=view&id=$1 last;
  rewrite "^/exchange-id([0-9]+)(.*)\.html$" /exchange.php?id=$1&act=view last;
  rewrite "^/exchange-([0-9]+)-min([0-9]+)-max([0-9]+)-([0-9]+)-(.+)-([a-zA-Z]+)(.*)\.html$" /exchange.php?cat_id=$1&integral_min=$2&integral_max=$3&page=$4&sort=$5&order=$6 last;
  rewrite ^/exchange-([0-9]+)-([0-9]+)-(.+)-([a-zA-Z]+)(.*)\.html$" /exchange.php?cat_id=$1&page=$2&sort=$3&order=$4 last;
  rewrite "^/exchange-([0-9]+)-([0-9]+)(.*)\.html$" /exchange.php?cat_id=$1&page=$2 last;
  rewrite "^/exchange-([0-9]+)(.*)\.html$" /exchange.php?cat_id=$1 last;
}

```

## phpwind

```nginx
location / {
  rewrite ^(.*)-htm-(.*)$ $1.php?$2 last;
  rewrite ^(.*)/simple/([a-z0-9\_]+\.html)$ $1/simple/index.php?$2 last;
}

```


## wordpress

```nginx
location / {
    try_files $uri $uri/ /index.php?$args;
}
# Add trailing slash to */wp-admin requests.
rewrite /wp-admin$ $scheme://$host$uri/ permanent;

```

## spf

    
```nginx
location / {
    rewrite "^/list-([0-9]+)-?([0-9]*)\.html$" /index.php?a=lists&catid=$1&page=$2 last;
    rewrite "^/shows_([0-9]+)_?([0-9]*)\.html$" /index.php?a=shows&catid=$1&id=$2 last;
    if (-f $request_filename) { 
       expires max;
       break;
    }
    if (!-e $request_filename) {
       rewrite ^/(.*)$ /index.php/$1 last;
    }
}
```