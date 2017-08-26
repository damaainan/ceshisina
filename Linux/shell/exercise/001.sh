#!/bin/bash

echo "hello bash"
for i in {1..10}
do
    echo ${i}
done

for i in `seq 1 2 10`
do
    echo ${i}
done