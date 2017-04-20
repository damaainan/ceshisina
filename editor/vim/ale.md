ale 作为一个刚刚完成不久的后起之秀，又有什么特别的优势呢？  

● 实时检测。为了让代码可以在编辑时进行实时的检测，ale 的运行方式是将代码做为 stdin 导入检测工具（不支持的话使用临时文件），这样做的好处是我们可以更早的发现错误。  
●并发运行。ale默认使用所有可用的检测工具并发执行检测，譬如说我们有时需要同时对 javascript 运行 eslint 以及 jscs。   

● 标识栏、状态栏以及命令行消息支持。   

----

**需要相应语言的插件支持语法的检查**


---

一些常用的设置：
● 保持侧边栏可见：

    let g:ale_sign_column_always = 1

● 改变错误和警告标识符：

    let g:ale_sign_error = '>>'
    let g:ale_sign_warning = '--'

● 添加状态栏信息：

    %{ALEGetStatusLine()}

● 改变状态栏信息格式：

    let g:ale_statusline_format = ['⨉ %d', '⚠ %d', '⬥ ok']

● 改变命令行消息：

    let g:ale_echo_msg_error_str = 'E'
    let g:ale_echo_msg_warning_str = 'W'
    let g:ale_echo_msg_format = '[%linter%] %s [%severity%]'

● 添加检测完成后回调：

    augroup YourGroup
        autocmd!
        autocmd User ALELint call YourFunction()
    augroup END

● 自定义跳转错误行快捷键：

    nmap <silent> <C-k> <Plug>(ale_previous_wrap)
    nmap <silent> <C-j> <Plug>(ale_next_wrap)


----
另一种配置 

    "ale
    "始终开启标志列
    let g:ale_sign_column_always = 1
    let g:ale_set_highlights = 0
    "自定义error和warning图标
    let g:ale_sign_error = '✗'
    let g:ale_sign_warning = '►'
    "在状态栏中整合ale
    let g:ale_statusline_format = ['✗ %d', '► %d', '✔ OK']
    "显示Linter名称,出错或警告等相关信息
    let g:ale_echo_msg_error_str = 'E'
    let g:ale_echo_msg_warning_str = 'W'
    let g:ale_echo_msg_format = '[%linter%] %s [%severity%]'
    "普通模式下，sp前往上一个错误或警告，sn前往下一个错误或警告
    nmap sp <Plug>(ale_previous_wrap)
    nmap sn <Plug>(ale_next_wrap)

需要注意的是要开启上面的状态栏整合，必须在状态栏设置%{ALEGetStatusLine()},比如我的状态栏设置为

    "设置状态栏显示的内容
    set statusline=%F%m%r%h%w\ [FORMAT=%{&ff}]\ [TYPE=%Y]\ [POS=%l,%v][%p%%]\ %{strftime(\"%d/%m/%y\ -\ %H:%M\")}\ %{ALEGetStatusLine()}


如果不想进行实时语法检查，希望在保存文件时才运行Linters，可以像下面这样设置

    let g:ale_lint_on_text_changed = 'never'
    let g:ale_lint_on_enter = 0


另外，你还可以自定义Linters，比如下面的设置把JavaScript的Linter设置成了eslint

    let g:ale_linters = {
    \   'javascript': ['eslint'],
    \}


----

space-vim 中 ale 的配置 

    " ale {
        let g:ale_linters = {
                    \   'sh' : ['shellcheck'],
                    \   'vim' : ['vint'],
                    \   'html' : ['tidy'],
                    \   'python' : ['flake8'],
                    \   'markdown' : ['mdl'],
                    \   'javascript' : ['eslint'],
                    \}
        let g:ale_set_highlights = 0
        " If emoji not loaded, use default sign
        try
            let g:ale_sign_error = emoji#for('boom')
            let g:ale_sign_warning = emoji#for('small_orange_diamond')
        catch
            " Use same sign and distinguish error and warning via different colors.
            let g:ale_sign_error = '•'
            let g:ale_sign_warning = '•'
        endtry
        let g:ale_echo_msg_format = '[#%linter%#] %s [%severity%]'
        let g:ale_statusline_format = ['E•%d', 'W•%d', 'OK']

        " For a more fancy ale statusline
        function! ALEGetError()
            let l:res = ale#statusline#Status()
            if l:res ==# 'OK'
                return ''
            else
                let l:e_w = split(l:res)
                if len(l:e_w) == 2 || match(l:e_w, 'E') > -1
                    return ' •' . matchstr(l:e_w[0], '\d\+') .' '
                endif
            endif
        endfunction

        function! ALEGetWarning()
            let l:res = ale#statusline#Status()
            if l:res ==# 'OK'
                return ''
            else
                let l:e_w = split(l:res)
                if len(l:e_w) == 2
                    return ' •' . matchstr(l:e_w[1], '\d\+')
                elseif match(l:e_w, 'W') > -1
                    return ' •' . matchstr(l:e_w[0], '\d\+')
                endif
            endif
        endfunction

        if g:spacevim_gui_running
            let g:ale_echo_msg_error_str = 'Error'
            let g:ale_echo_msg_warning_str = 'Warning'
        else
            let g:ale_echo_msg_error_str = '✹ Error'
            let g:ale_echo_msg_warning_str = '⚠ Warning'
        endif
    
    
        nmap <Leader>en <Plug>(ale_next)
        nmap <Leader>ep <Plug>(ale_previous)
        nnoremap <Leader>ts :ALEToggle<CR>
    " }

