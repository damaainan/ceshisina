/** 
* @usage   把一个数字按照基准点右对齐绘制 
* @author  mw 
* @date    2016年01月01日  星期五  13:59:42  
* @param 
* @return 
* 
*/  
function rightAlign(num, x, y, r) {  
    var s = num.toFixed(0);  
    var digitBit = s.length;  
    var digit = new Digit();  
    var xpos=0, ypos=0;  
      
    for (var i = digitBit-1; i > -1; i--) {  
        xpos = x - r * (digitBit - i);  
        ypos = y;  
        digit.number(s.charAt(i), xpos, ypos, r);  
    }  
}

/** 
* @usage   加法竖式 
* @author  mw 
* @date    2016年01月01日  星期五  13:59:42  
* @param 
* @return 
* 
*/  
  
/* 
    算术竖式 
    Vertical arithmetic 
    加数 
    addend 
    被加数 
    Augend 
    加号 
    Plus 
*/  
function verticalAdd(augend, addend, x, y, r) {  
    var result = addend + augend;  
    var xBeg = x ? x : 300, yBeg = y ? y :100, large = r ? r : 20;  
      
    var maxBit = Math.max(addend, augend).toFixed(0).length;  
    var plusPos = x - (maxBit+2) * r;  
    rightAlign(augend, x, y, r);  
    rightAlign(addend, x, y+1.5*r, r);  
    plot.setFillStyle('black')  
        .fillText('+', plusPos, y+1.5*r, r);  
          
    plot.beginPath()  
        .moveTo(plusPos - 1 * r, y + 2.5*r)  
        .lineTo(x + 1 * r, y + 2.5 * r)  
        .closePath()  
        .stroke();  
          
    rightAlign(result, x, y + 3.5 * r, r);  
}

function myDraw() {  
    plot.init();  
    setPreference();  
      
    var r = 20;  
      
    var row = 0, col = 4;  
    var task = [32,6,24,3,5,43,21,3,4,33];  
      
    var len = task.length / 2;  
    row = Math.ceil(len/(col-1));  
    //document.body.appendChild(document.createTextNode(len.toFixed(0) + ', ' + row.toFixed(0)));  
    var rowCount = 1, colCount = 2;  
      
    for (var i = 0; i < len; i++) {  
        setSector(row, col, rowCount, colCount++);  
        if (colCount > col) {  
            colCount = 2;  
            rowCount++;  
        }  
        verticalAdd(task[2*i], task[2*i+1],0, 0, r);  
    }  
}

/** 
* @usage   减法竖式 
* @author  mw 
* @date    2016年01月01日  星期五  13:59:42  
* @param 
* @return 
* 
*/  
/* 
    被减数 
    Minuend 
    减数 
    subtrahend 
    减号 
    Minus sign 
*/  
function verticalSub(minuend, subtrahend, x, y, r) {  
    var result = minuend - subtrahend;  
    var xBeg = x ? x : 300, yBeg = y ? y :100, large = r ? r : 20;  
      
    var maxBit = Math.max(minuend, subtrahend).toFixed(0).length;  
    var minusPos = x - (maxBit+2) * r;  
    rightAlign(minuend, x, y, r);  
    rightAlign(subtrahend, x, y+1.5*r, r);  
    plot.setFillStyle('black')  
        .fillText('-', minusPos, y+1.5*r, r);  
          
    plot.beginPath()  
        .moveTo(minusPos - 1 * r, y + 2.5*r)  
        .lineTo(x + 1 * r, y + 2.5 * r)  
        .closePath()  
        .stroke();  
          
    rightAlign(result, x, y + 3.5 * r, r);  
}


function myDraw() {  
    plot.init();  
    setPreference();  
      
    var r = 20;  
      
    var row = 0, col = 3;  
    var task = [45,3,64,42,48,18,25,21];  
      
    var len = task.length / 2;  
    row = Math.ceil(len/(col-1));  
    //document.body.appendChild(document.createTextNode(len.toFixed(0) + ', ' + row.toFixed(0)));  
    var rowCount = 1, colCount = 2;  
      
    for (var i = 0; i < len; i++) {  
        setSector(row, col, rowCount, colCount++);  
        if (colCount > col) {  
            colCount = 2;  
            rowCount++;  
        }  
        verticalSub(task[2*i], task[2*i+1],0, 0, r);  
    }  
}


/** 
* @usage   连续加法竖式 
* @author  mw 
* @date    2016年01月01日  星期五  13:59:42  
* @param 
* @return 
* 
*/  
  
/* 
    算术竖式 
    Vertical arithmetic 
    加数 
    addend 
    被加数 
    Augend 
    加号 
    Plus 
*/  
function verticalAdd2(arr, x, y, r) {  
    var array = new Array();  
    array = arr;  
    var len = array.length;  
      
    if (len < 2) return;  
      
      
    var result = array[0] + array[1];  
      
    var xBeg = x ? x : 300, yBeg = y ? y :100, large = r ? r : 20;  
      
    var maxBit = Math.max(array[0], array[1]).toFixed(0).length;  
      
    var plusPos = x - (maxBit+2) * r;  
    rightAlign(array[0], x, y, r);  
      
    y += 1.5 * r;  
      
    rightAlign(array[1], x, y, r);  
    plot.setFillStyle('black')  
        .fillText('+', plusPos, y, r);  
      
    y += 1.0 * r;  
      
    plot.beginPath()  
        .moveTo(plusPos - 1 * r, y)  
        .lineTo(x + 1 * r, y)  
        .closePath()  
        .stroke();  
      
    y += 1.5 * r;  
      
    rightAlign(result, x, y, r);  
      
    if (array.length > 2) {  
        for (var i = 2; i < array.length; i++) {           
            maxBit = Math.max(result, array[i]).toFixed(0).length;            
            plusPos = x - (maxBit+2) * r;  
            result += array[i];   
              
            y += 1.5 * r;  
            rightAlign(array[i], x, y, r);  
            plot.fillText('+', plusPos, y, r);  
              
            y += 1.0 * r;  
          
            plot.beginPath()  
                .moveTo(plusPos - 1 * r, y)  
                .lineTo(x + 1 * r, y)  
                .closePath()  
                .stroke();  
      
            y += 1.5 * r;  
              
            rightAlign(result, x, y, r);  
        }  
      
    }  
}