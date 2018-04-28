;= @echo off
;= rem Call DOSKEY and use this file as the macrofile
;= %SystemRoot%\system32\doskey /listsize=1000 /macrofile=%0%
;= rem In batch mode, jump to the end of the file
;= goto:eof
;= Add aliases below here
e.=explorer .
gl=git log --oneline --all --graph --decorate  $*
ls=ls --show-control-chars -F --color $*
pwd=cd
clear=cls
history=cat "%CMDER_ROOT%\config\.history"
unalias=alias /d $1
vi=vim $*
cmderr=cd /d "%CMDER_ROOT%"



..=cd ..  
...=cd .. && cd ..   
....=cd .. && cd .. && cd ..   
ll=ls -l --color --show-control-chars -F  $*    
la=ls -al --color   
gs=git status  
gcm=git checkout master  
gc=git checkout $* 
gb=git branch  
gba=git branch -a  
gfop=git fetch origin -p  
gpfo=git pull --ff-only  

gu=git fetch origin -p && git pull --ff-only  

gbd=git branch -d $*  
gbD=git branch -D $*  
gcb=git checkout -b $*  

gstash=git stash $*
gspop=git stash pop

gpo=git push origin -u $*


subl="D:\Sublime Text 3\subl.exe" $*


vim=D:\tool\cmder\vendor\git-for-windows\usr\bin\vim.exe $*

javac=javac -encoding "utf-8" $*