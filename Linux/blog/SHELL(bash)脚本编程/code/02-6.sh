#!/bin/bash
echo "系统信息："
select item in "host_name" "user_name" "shell_name" "quit"
do
    case $item in
     host*) hostname;;
     user*) echo $USER;;
     shell*) echo $SHELL;;
     quit) break;;
    esac
done