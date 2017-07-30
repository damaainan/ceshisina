//  ## 字符的左右移动

// > 给定一个字符串，这个字符串为 * 号和26个字母的任意组合。现在需要把字符串中的 * 号都移动到最左侧，而把字符串中的字母移到最右侧并保持相对顺序不变，要求时间复杂度和空间复杂度最小。

    var stars = 'sosunn**afns*repsni*';
    
    var rs = stars.split('');
    var flag = 0;
    for(var i=rs.length -1; i>=0; i--){
        if(rs[i] == '*'){
            flag++;
        }else{
            if(flag == 0)
                continue;
            else{
                rs[i+flag] = rs[i];
                rs[i] = '*';
            }
        }
    }
    console.log(rs.join(''))