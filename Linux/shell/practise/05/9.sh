#!/bin/bash

for ((i=1;i<=9;i++)); do
   for ((j=1;j<=i;j++)); do
     result=$(($i*$j))
     echo -n "$j*$i=$result "
   done
   echo
done