###  
# @usage   数字的中文写法转化为数字  
###  

def chineseToNumber(s):  
    chineseOfNumber=['零','一', '二', '三', '四', '五', '六','七', '八', '九', '十','百','千','万','亿'];  
    result = 0;  
    #每一组两个数，比如九百，一万，都是由一个值数和一个倍数组成。  
    #不可能两个以上的值在一块，但可能两个以上的倍数在一块，比如九九不合法，但四百万合法。  
      
    #合法表达为0，不合法为其它值  
    illegal = 0;  
    #两个长度  
    lengthOfStr = len(s);  
    lengthOfChs = len(chineseOfNumber);  
    #合法性判断  
    for i in range(lengthOfStr):  
        if illegal == 1:  
            break;  
  
        for j in range(lengthOfChs):  
            if s[i] == chineseOfNumber[j]:  
                break;  
            else:  
                if j >= lengthOfChs-1:  
                    print('含有非中文数字的字符，表达式不合法');  
                    illegal = 1;  
  
    for i in range(lengthOfStr-1):  
        if illegal == 1:  
            break;  
        for j in range(10):  
            if s[i] == chineseOfNumber[j]:  
                if j>0:  
                    for k in range(10):  
                        if s[i+1] == chineseOfNumber[k]:  
                            print('连续两个本数相连而没有倍数，表达式不合法。');  
                            illegal = 1;  
                            break;  
                #当这个数是零时，它后面跟零或倍数都不合法  
                else:  
                    if s[i+1] == chineseOfNumber[0]:  
                        print('连续两个零相连，表达式不合法。');  
                        illegal = 1;  
                        break;  
                      
                    for k in range(10, lengthOfChs):  
                        if s[i+1] == chineseOfNumber[k]:  
                            print('零后面跟上倍数，表达式不合法。');  
                            illegal = 1;  
                            break;  
                      
      
    for i in range(lengthOfStr-1):  
        if illegal == 1:  
            if (i > 0):  
                print('表达式的倍数排序不符合规范，不合法。');  
            break;                      
     
        if s[i] == '十':  
            if s[i+1] == '十' or s[i+1] == '百' or s[i+1] == '千':  
                illegal = 1;  
        elif s[i] == '百':  
            if s[i+1] == '十' or s[i+1] == '百' or s[i+1] == '千':  
                illegal = 1;  
        elif s[i] == '千':  
            if s[i+1] == '十' or s[i+1] == '百' or s[i+1] == '千':  
                illegal = 1;  
        elif s[i] == '万':  
            if s[i+1] == '十' or s[i+1] == '百' or s[i+1] == '千':  
                illegal = 1;  
        elif s[i] == '亿':  
            if s[i+1] == '十' or s[i+1] == '百' or s[i+1] == '千' or s[i+1] == '万':  
                illegal = 1;  
        else:  
            pass;  
  
    #合法则计算        
    if illegal!=0:  
        print('输入不合法。');  
    else:  
        value = 0;  
        multiple = 1;  
        result = 0;  
        #超过亿的部分，单独分出来的原因是避免再和万的倍数相乘  
        yiPart = 0;  
        #超过万的部分  
        wanPart = 0;  
        for i in range(lengthOfStr):  
            if s[i] == '亿':  
                result += value+wanPart+yiPart;  
                multiple = 100000000;  
                value = result;  
                result = value*multiple;  
                if (i < lengthOfStr-1 and s[i+1] == '亿'):  
                    value = 0;  
                else:  
                    yiPart = result;  
                    result = 0;  
                multiple = 1;  
            elif s[i] == '万':  
                result += value+wanPart;  
                multiple = 10000;  
                value = result;  
                result = value*multiple;  
                if (i < lengthOfStr-1 and (s[i+1] == '亿' or s[i+1] == '万')):  
                    value = 0;  
                else:  
                    if (result > 100000000):  
                        yiPart = result;  
                    else:  
                        wanPart = result;  
                    result = 0;  
                multiple = 1;  
            elif s[i] == '千':  
                multiple = 1000;  
                result += value*multiple;  
                value = 0;  
                multiple = 1;  
            elif s[i] == '百':  
                multiple = 100;  
                result += value*multiple;  
                value = 0;  
                multiple = 1;  
            #十这个数字，即可以作为本数，也可以作为倍数  
            elif s[i] == '十':  
                if value == 0:  
                    value = 10;  
                    multiple = 1;  
                    result += value*multiple;  
                    value = 0;  
                else:  
                    multiple = 10;  
                    result += value*multiple;  
                    value = 0;  
                    multiple = 1;  
            else:  
                for j in range(10):  
                    if s[i] == chineseOfNumber[j]:  
                        value = j;  
                        multiple = 1;  
                if i >= lengthOfStr-1:  
                    result += value * multiple;  
  
        result += wanPart + yiPart;  
    print('{0} {1}'.format(s, result));  
    #return result;  



###  
# @usage   数字的中文写法   
###  

def numberToChinese(num, s):  
    if (num < 0):  
        num = abs(num);  
    chineseOfNumber=['零','一', '二', '三', '四', '五', '六','七', '八', '九', '十','百','千','万','亿'];  
    bit = 0;  
    tmp = num;  
    if (tmp == 0):  
        s = chineseOfNumber[0];  
    while (tmp > 0):  
        tmp = tmp//10;  
        bit+=1;  
    tmp = num;  
    while (tmp > 0):  
        if (tmp < 10):  
            s += chineseOfNumber[tmp];  
            tmp -= 10;  
        elif (tmp < 100):  
            s += chineseOfNumber[tmp//10];  
            s += '十';  
            tmp = tmp%10;   
        elif (tmp < 1000):  
            s += chineseOfNumber[tmp//100];  
            s += '百';  
            tmp = tmp%100;  
            if tmp < 10 and tmp > 0:  
                s += '零';  
        elif (tmp < 10000):  
            s += chineseOfNumber[tmp//1000];  
            s += '千';  
            tmp = tmp%1000;  
            if tmp < 100 and tmp > 0:  
                s += '零';  
        elif (tmp < 100000000):  
            s1 = '';  
            s += numberToChinese(tmp//10000, s1);  
            s += '万';  
            tmp =tmp%10000;  
            if tmp < 1000 and tmp > 0:  
                s += '零';  
        elif (tmp >= 100000000):  
            s1 = '';  
            s += numberToChinese(tmp//100000000, s1);  
            s += '亿';  
            tmp = tmp%100000000;  
            if tmp < 10000000 and tmp > 0:  
                s += '零';  
        else:  
            pass;  
    return s;