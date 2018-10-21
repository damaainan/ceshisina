### 命令行 alias 设置 

修改 `/etc/bash.bashrc`

```
alias ..="cd .."
alias ...="cd .. && cd .."
alias ....="cd .. && cd .. && cd .."
alias ll="ls -l --color --show-control-chars -F  $*"
alias la="ls -al --color"
alias gs="git status"
alias gcm="git checkout master"
alias gc="git checkout $*"
alias gb="git branch"
alias gba="git branch -a"
alias gfop="git fetch origin -p"
alias gpfo="git pull --ff-only"

alias gu="git fetch origin -p && git pull --ff-only"

alias gbd="git branch -d $*"
alias gbD="git branch -D $*"
alias gcb="git checkout -b $*"

alias gstash="git stash $*"
alias gspop="git stash pop"

alias gpo="git push origin -u $*"


alias cat="ccat"
```

`source bash.bashrc`