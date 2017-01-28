#!/usr/bin/python
# -*- coding: UTF-8 -*-

###  
# @usage   写.wav文件，能把声波数据阵列用二进制写成.wav。  
###  
import math
import struct
def writeWav():  
      
    byteArray = [];  
    dataArray = [];  
  
    #样本数据点数  
    N = 10000;  
    times = 3;  
    dataSize = 2*N*times;  
  
    #样本数据阵列  
    sampleArray = sampleGen(N);  
    fileSize = dataSize+44; #44为格式头部分所用字节数  
      
    #RIFF WAVE CHUNK  
    RIFF_ID = [0x52, 0x49, 0x46, 0x46];  #'RIFF'  
    RIFF_Size = littleEndian(fileSize-8, 4); #文件总字节数减去8  
    RIFF_Type = [0x57, 0x41,0x56, 0x45, 0x66, 0x6D, 0x74, 0x20]; #'WAVEfat '  
  
    #Format Chunk  
    Format_10_17 = [0x10, 0x00, 0x00, 0x00, 0x01, 0x00, 0x01, 0x00];#过滤4+格式2+声道2=8个字节  
    Format_18_1B = [0x11, 0x2B, 0x00, 0x00]; #采样频率0x2B11 = 11025  
    Format_1C_1F = [0x22, 0x56, 0x00, 0x00]; #比持率 = 频率*通道*样本位 = 22050  
    Format_20_23 = [0x02, 0x00, 0x10, 0x00]; #块对齐 = 通道数* 样本位数 = 1*2 = 2      
  
    #Fact Chunk(optional)  
  
    #Data Chunk  
    Data_24_27 = [0x64, 0x61, 0x74, 0x61]; #'DATA'标记  
    Data_Size = littleEndian(fileSize-44, 4); #下面的Data部分的字节数，文件总字节数-44  
  
  
    #RIFF WAVE CHUNK  
    for i in range(4):  
        byte = struct.pack('B', RIFF_ID[i]);  
        byteArray.append(byte);  
  
    for i in range(4):  
        byte = struct.pack('B', RIFF_Size[i]);  
        byteArray.append(byte);  
  
    for i in range(8):  
        byte = struct.pack('B', RIFF_Type[i]);  
        byteArray.append(byte);  
  
    #Format Chunk  
    for i in range(8):  
        byte = struct.pack('B', Format_10_17[i]);  
        byteArray.append(byte);  
  
    for i in range(4):  
        byte = struct.pack('B', Format_18_1B[i]);  
        byteArray.append(byte);  
  
    for i in range(4):  
        byte = struct.pack('B', Format_1C_1F[i]);  
        byteArray.append(byte);  
  
    for i in range(4):  
        byte = struct.pack('B', Format_20_23[i]);  
        byteArray.append(byte);  
  
      
    #Data Chunk  
    for i in range(4):  
        byte = struct.pack('B', Data_24_27[i]);  
        byteArray.append(byte);  
  
    for i in range(4):  
        byte = struct.pack('B', Data_Size[i]);  
        byteArray.append(byte);  
  
    #以下是量化数据  
    for i in range(N):  
        value = littleEndian(sampleArray[i], 2);  
        for j in range(2):  
            byte = struct.pack('B', value[j]);  
            dataArray.append(byte);  
    size = len(byteArray);  
    print(size);  
  
    #写出到文件  
    fout= open('output.wav', 'wb');  
    for i in range(size):  
        fout.write(byteArray[i]);  
  
    for j in range(times):  
        for i in range(2*N):  
            fout.write(dataArray[i]);  
          
  
    fout.close();  
  
  
#把十进制数按照小尾字节序切割  
def littleEndian(number, byte = 4):  
    result = [0]*byte;  
  
    for i in range(byte):  
        result[i] = number%256;  
        number//=256;  
    return result;  
  
#生成声音样本，返回样本矩阵  
def sampleGen(N):  
    #设立20000个数值点，约可听2秒  
    sampleArray = [];  
    for i in range(N):  
        value = math.sin(i*10);  
        if (value < 0):  
            value *= 32768;  
        else:  
            value *= 32767;  
        sampleArray.append(round(value));  
    return sampleArray;  
      
  
#把UltraEdit中的值字串转化为hex序列组  
def hexExpr(string):  
    resultString = '';  
    size = len(string);  
    for i in range(size):  
        if (i == 0 ):  
            resultString += '0x'+string[i];  
        elif (string[i] == ' '):  
            resultString += ', 0x';  
        else:  
            resultString += string[i];  
    print(resultString);  
  
  
#调用入口  
def tmp():  
    writeWav();  

tmp();