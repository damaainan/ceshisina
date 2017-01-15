/** 
* @usage   绘制矩阵 
* @author  mw 
* @date    2016年01月10日  星期日  14:59:38  
* @param 
* @return 
* 
*/  
function drawMatrix(matrix, row, col) {  
    plot.save();  
    var digit = new Digit();  
    var r = 30;  
      
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            plot.setStrokeStyle('black');  
            digit.number(matrix[j][i], 2 * r * (i+1-2.5), 2 * r * (j+1-2.5), r);  
            plot.setStrokeStyle('red');  
            shape.strokeRect(2 * r * (i+1-2.5), 2 * r * (j+1-2.5), 2 * r, 2 * r);  
        }  
    }  
    plot.restore();  
  
}

function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    config.setSector(1,1,1,1);  
      
    var matrix =   
[[1, 3, 4, 2],  
[2, 4, 3, 1],  
[3, 2, 1, 4],  
[4, 1, 2, 3]];  
      
      
    drawMatrix(matrix, 4, 4);  
  
}




function myDraw() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    //config.setSector(1,1,1,1);  
    //config.axis2D(0, 0, 180);  
      
    var digit = new Digit();  
    var r = 50;  
    var row = 7;  
    var col = 9;  
    plot.translate(r, r);  
      
    var array = new Array(row * col);  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            array[i * col + j] = 0;  
        }  
    }  
      
    //设置笑脸  
    for (var x = 1; x < row; x+= 2) {  
        for (var y = 1; y<col; y+=3) {  
  
            array[x * col + y] = 'f';  
        }  
    }  
      
    //计算数字  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            if (array[i * col + j] == 'f') {  
                if (i -1 >= 0) {  
                    if (j - 1 >= 0) {  
                        if (array[(i-1)*col + j-1] != 'f')   
                            array[(i-1)*col + j-1]++;  
                    }  
                      
                    if (j+1 < col) {  
                        if (array[(i-1)*col + j+1] != 'f')   
                            array[(i-1)*col + j+1]++;  
                    }  
                      
                    if (array[(i-1)*col + j] != 'f')   
                        array[(i-1)*col+j]++;  
                }  
                  
                if (i + 1 < row) {  
                    if (j - 1 >= 0) {  
                        if (array[(i+1)*col + j-1] != 'f')   
                            array[(i+1)*col + j-1]++;  
                    }  
                      
                    if (j+1 < col) {  
                        if (array[(i+1)*col + j+1] != 'f')   
                            array[(i+1)*col + j+1]++;  
                    }  
                      
                    if (array[(i+1)*col + j] != 'f')   
                        array[(i+1)*col+j]++;  
                }  
                  
                if (j - 1 >= 0) {  
                    if (array[i*col + j-1] != 'f')   
                        array[i*col + j-1]++;  
                }  
                  
                if (j+1 < col) {  
                    if (array[i*col + j+1] != 'f')   
                        array[i*col + j+1]++;  
                }  
            }  
        }  
    }  
                  
  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            shape.strokeRect(j * r, i * r, r, r);  
            if (array[i * col + j] == 'f') {  
                plot.setFillStyle('red');  
                shape.fillCircle(j * r, i * r, 20);  
                plot.setFillStyle('yellow');  
                shape.fillEllipse(j * r, i * r, 15, 10);  
  
            }  
            else if (array[i * col + j] > 0) {  
                plot.setFillStyle('#880000');  
                digit.number(array[i * col + j], j * r, i * r, r);  
            }  
        }  
    }  
      
  
  
  
} 



/** 
* @usage   找笑脸 
* @author  mw 
* @date    2016年01月10日  星期日  14:59:38  
* @param 
* @return 
* 
*/  
function myDraw_1() {  
    var config = new PlotConfiguration();  
    config.init();  
    config.setPreference();  
    //config.setSector(1,1,1,1);  
    //config.axis2D(0, 0, 180);  
      
    var digit = new Digit();  
    var r = 50;  
    var row = 7;  
    var col = 9;  
    plot.translate(r, r);  
      
    var array = new Array(row * col);  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            array[i * col + j] = 0;  
        }  
    }  
      
    //设置笑脸  
    var a = [0,2, 0,7, 1,5, 1,8, 3,2, 4,7];  
    len = a.length / 2;  
      
    for (var i = 0; i < len; i++) {  
        array[a[2*i] * col + a[2*i+1]] = 'f';  
    }  
  
      
    //计算数字  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            if (array[i * col + j] == 'f') {  
                if (i -1 >= 0) {  
                    if (j - 1 >= 0) {  
                        if (array[(i-1)*col + j-1] != 'f')   
                            array[(i-1)*col + j-1]++;  
                    }  
                      
                    if (j+1 < col) {  
                        if (array[(i-1)*col + j+1] != 'f')   
                            array[(i-1)*col + j+1]++;  
                    }  
                      
                    if (array[(i-1)*col + j] != 'f')   
                        array[(i-1)*col+j]++;  
                }  
                  
                if (i + 1 < row) {  
                    if (j - 1 >= 0) {  
                        if (array[(i+1)*col + j-1] != 'f')   
                            array[(i+1)*col + j-1]++;  
                    }  
                      
                    if (j+1 < col) {  
                        if (array[(i+1)*col + j+1] != 'f')   
                            array[(i+1)*col + j+1]++;  
                    }  
                      
                    if (array[(i+1)*col + j] != 'f')   
                        array[(i+1)*col+j]++;  
                }  
                  
                if (j - 1 >= 0) {  
                    if (array[i*col + j-1] != 'f')   
                        array[i*col + j-1]++;  
                }  
                  
                if (j+1 < col) {  
                    if (array[i*col + j+1] != 'f')   
                        array[i*col + j+1]++;  
                }  
            }  
        }  
    }  
                  
  
    for (var i = 0; i < row; i++) {  
        for (var j = 0; j < col; j++) {  
            shape.strokeRect(j * r, i * r, r, r);  
            if (array[i * col + j] == 'f') {  
                plot.setFillStyle('red');  
                shape.fillCircle(j * r, i * r, 20);  
                plot.setFillStyle('yellow');  
                shape.fillEllipse(j * r, i * r, 15, 10);  
  
            }  
            else if (array[i * col + j] > 0) {  
                plot.setFillStyle('#880000');  
                digit.number(array[i * col + j], j * r, i * r, r);  
            }  
        }  
    }  
      
  
  
  
}