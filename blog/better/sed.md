玩转 js 系列 图片替换脚本


    sed -i 's@\[\!\[.*\]@@'  [0-4].md

    awk '/images2015/{sub(/\(/,"");sub(/\)/,"");print $0}' [0-4].md  | xargs -i[ wget [


    sed -i 's/http:\/\/images2015.cnblogs.com\/blog\/341820\/201606/.\/img/g' [0-4].md

    awk '/.\/img/{print $1}' [0-4].md  | xargs -i} sed  -i "s@}$@\![]}@" [0-4].md