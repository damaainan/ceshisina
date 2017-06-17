#!/bin/bash

for i in `ls | grep md `
do
    s=`sed -n '1p' $i `
    echo $s
    mv $i "$s.md"
done
