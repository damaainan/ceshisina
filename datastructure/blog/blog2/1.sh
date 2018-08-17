#!/bin/bash

for i in `ls *.md`
do 
        #echo $i
        awk -F'============' 'NR==1{print $2}'
        
done
