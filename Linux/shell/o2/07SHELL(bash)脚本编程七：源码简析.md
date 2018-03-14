## SHELL(bash)脚本编程七：源码简析

来源：[https://segmentfault.com/a/1190000008355803](https://segmentfault.com/a/1190000008355803)

本文对bash的源码(版本：`4.2.46(1)-release`)进行简要分析。
## 数据结构

bash是用C语言写成的，其源码中只使用了少量的数据结构：`数组`，`树`，`单向链表`，`双向链表`和`哈希表`。几乎所有的bash结构都是用这些基本结构实现的。

源码中最主要的结构都定义在根目录下头文件`command.h`中。
### 单词

bash在不同阶段传输信息并处理数据单元的数据结构是`WORD_DESC`：

```c
typedef struct word_desc {
  char *word;       /* Zero terminated string. */
  int flags;        /* Flags associated with this word. */
} WORD_DESC;
```
`WORD_DESC`表示一个单词，字符指针`word`指向一个以\0结尾的字符串，整型成员`flags`定义了该单词的类型。
当前源码中定义了二十多种单词类型，如`W_HASDOLLAR`表示该单词包含扩展字符`$`，`W_ASSIGNMENT`表示该单词是一个赋值语句，`W_GLOBEXP`表示该单词是路径扩展(通配符扩展)之后的结果等等。

单词被组合为简单的链表`WORD_LIST`：

```c
typedef struct word_list {
  struct word_list *next;
  WORD_DESC *word;
} WORD_LIST;
```
`WORD_LIST`在shell中无处不在。一个简单的命令就是一个单词列表，展开结果同样是一个单词列表，内置命令的参数还是一个单词列表。
### 重定向

结构`REDIRECT`描述了一条命令的重定向链表，包含指向下一个REDIRECT对象的next指针：

```c
typedef struct redirect {
  struct redirect *next;    /* Next element, or NULL. */
  REDIRECTEE redirector;    /* Descriptor or varname to be redirected. */
  int rflags;           /* Private flags for this redirection */
  int flags;            /* Flag value for `open'. */
  enum r_instruction  instruction; /* What to do with the information. */
  REDIRECTEE redirectee;    /* File descriptor or filename */
  char *here_doc_eof;       /* The word that appeared in <<foo. */
} REDIRECT;
```

整型成员`flags`定义了目标文件打开方式。
重定向描述符`redirector`的类型是一个联合体`REDIRECTEE`：

```c
typedef union {
  int dest;         /* Place to redirect REDIRECTOR to, or ... */
  WORD_DESC *filename;      /* filename to redirect to. */
} REDIRECTEE;
```
`instruction`是枚举型变量`r_instruction`，它定义了一个重定向的类型：

```c
enum r_instruction {
  r_output_direction, r_input_direction, r_inputa_direction,
  r_appending_to, r_reading_until, r_reading_string,
  r_duplicating_input, r_duplicating_output, r_deblank_reading_until,
  r_close_this, r_err_and_out, r_input_output, r_output_force,
  r_duplicating_input_word, r_duplicating_output_word,
  r_move_input, r_move_output, r_move_input_word, r_move_output_word,
  r_append_err_and_out
};
```

在`REDIRECTEE`中，如果重定向类型是`ri_duplicating_input`或者`ri_duplicating_output`则使用整型成员`dest`(如果其值为负则表示错误的重定向)，否则使用结构指针成员`filename`。
REDIRECT结构中的字符指针成员`here_doc_eof`，指定了重定向类型为`Here Document`(见[这里][0])。
### 命令

命令`COMMAND`结构描述一条bash命令，对于`复合命令`，其内部可能还包含有其他命令：

```c
typedef struct command {
  enum command_type type;   /* FOR CASE WHILE IF CONNECTION or SIMPLE. */
  int flags;            /* Flags controlling execution environment. */
  int line;         /* line number the command starts on */
  REDIRECT *redirects;      /* Special redirects for FOR CASE, etc. */
  union {
    struct for_com *For;
    struct case_com *Case;
    struct while_com *While;
    struct if_com *If;
    struct connection *Connection;
    struct simple_com *Simple;
    struct function_def *Function_def;
    struct group_com *Group;
#if defined (SELECT_COMMAND)
    struct select_com *Select;
#endif
#if defined (DPAREN_ARITHMETIC)
    struct arith_com *Arith;
#endif
#if defined (COND_COMMAND)
    struct cond_com *Cond;
#endif
#if defined (ARITH_FOR_COMMAND)
    struct arith_for_com *ArithFor;
#endif
    struct subshell_com *Subshell;
    struct coproc_com *Coproc;
  } value;
} COMMAND;
```

枚举型成员`type`定义了命令类型：

```c
/* Command Types: */
enum command_type { cm_for, cm_case, cm_while, cm_if, cm_simple, cm_select,
            cm_connection, cm_function_def, cm_until, cm_group,
            cm_arith, cm_cond, cm_arith_for, cm_subshell, cm_coproc };
```

整型成员`flags`定义了命令的执行环境，比如是否在子shell中执行，是否在后台执行等等。
联合成员`value`指明了命令值的结构指针，各个不同的命令对应于不同的结构体。
如`if`命令结构：

```c
/* IF command. */
typedef struct if_com {
  int flags;            /* See description of CMD flags. */
  COMMAND *test;        /* Thing to test. */
  COMMAND *true_case;       /* What to do if the test returned non-zero. */
  COMMAND *false_case;      /* What to do if the test returned zero. */
} IF_COM;
```

简单命令`simple`结构：

```c
typedef struct simple_com {
  int flags;            /* See description of CMD flags. */
  int line;         /* line number the command starts on */
  WORD_LIST *words;     /* The program name, the arguments,
                   variable assignments, etc. */
  REDIRECT *redirects;      /* Redirections to perform. */
} SIMPLE_COM;
```
`while`命令结构：

```c
/* WHILE command. */
typedef struct while_com {
  int flags;            /* See description of CMD flags. */
  COMMAND *test;        /* Thing to test. */
  COMMAND *action;      /* Thing to do while test is non-zero. */
} WHILE_COM;
```

等等。
## 主要流程

以下所涉及文件如无特殊说明均处于bash源码的根目录下。
对于一行bash命令的执行流程分为两大步骤：`解析`和`执行`(注意和上一篇中的解析和执行的区别)。
`解析`的作用是获得用于执行的命令结构体：`COMMAND *global_command``执行`主要是针对特定类型的命令进行执行和结果处理。
### 解析

bash的入口函数`main()`位于文件`shell.c`中：

```c
int
main (argc, argv, env)
     int argc;
     char **argv, **env;
{
    ....
    shell_initialize ();
    ....
    run_startup_files ();
    ....
    shell_initialized = 1;

    /* Read commands until exit condition. */
    reader_loop ();
    exit_shell (last_command_exit_value);
}
```

函数定义了shell启动和运行过程中的一些状态变量，依据不同的参数初始化shell：`shell_initialize ()`初始化了shell变量和参数，`run_startup_files ()`执行需要的配置文件(`/etc/profile`和`~/.bashrc`等)。

初始化完成之后，进入`eval.c`中的交互循环函数`reader_loop()`。该函数不断读取和执行命令，直到遇到EOF。
此时函数调用关系为：`main()-->reader_loop()`。

```c
/* Read and execute commands until EOF is reached.  This assumes that
   the input source has already been initialized. */
int
reader_loop ()
{
    ....
    if (read_command () == 0)
    {
      ....
    }
    else if (current_command = global_command)
    {
      ....
      execute_command (current_command);
    }
    ....
    return (last_command_exit_value);
}
```

`reader_loop()`函数中调用`read_command()`取得命令结构体`global_command`，然后赋值给`current_command`并交给`execute_command ()`去执行。
`read_command ()`调用`parse_command ()`，此时函数调用关系为：`main()-->reader_loop()-->read_command()-->parse_command()`

```c
/* Read and parse a command, returning the status of the parse.  The command
   is left in the globval variable GLOBAL_COMMAND for use by reader_loop.
   This is where the shell timeout code is executed. */
int
read_command ()
{
    ....
    result = parse_command ();
    ....
    return (result);
}
....
/* Call the YACC-generated parser and return the status of the parse.
   Input is read from the current input stream (bash_input).  yyparse
   leaves the parsed command in the global variable GLOBAL_COMMAND.
   This is where PROMPT_COMMAND is executed. */
int
parse_command ()
{
    ....
    r = yyparse ();

    if (need_here_doc)
      gather_here_documents ();

    return (r);
}
```
`parse_command()`调用`y.tab.c`中的`yyparse ()`函数，并使用函数`gather_here_documents ()`处理`here document`类型的输入重定向。
`yyparse ()`由YACC通过`parse.y`生成，函数内使用大量的goto语句，此文件可读性较差：

```c
int
yyparse ()
{
    ....
    yychar = YYLEX;
    ....
    yytoken = YYTRANSLATE (yychar);
    ....
    yyn += yytoken;
    ....
    switch (yyn)
    {
      case 2:
        {
        global_command = (yyvsp[(1) - (2)].command);
        ....
        }
        break;
      case 3:
        {
        global_command = (COMMAND *)NULL;
        ....
        }
        break;
      ....
      case 6:
        { (yyval.word_list) = make_word_list ((yyvsp[(1) - (1)].word), (WORD_LIST *)NULL); }
        break;
      ....
      case 8:
        {
        ....
        redir.filename = (yyvsp[(2) - (2)].word);
        (yyval.redirect) = make_redirection (source, r_output_direction, redir, 0);
        }
      ....
      case 57:
        { (yyval.command) = make_simple_command ((yyvsp[(1) - (1)].element), (COMMAND *)NULL); }
        break;
      ....
      case 107:
        { (yyval.command) = make_if_command ((yyvsp[(2) - (7)].command), (yyvsp[(4) - (7)].command), (yyvsp[(6) - (7)].command)); }
        break;
      ....
      default: break;
    }
    ....
    return YYID (yyresult);
}
```

函数内调用`yylex()`(宏定义：`#define YYLEX yylex ()`)来获得并计算出整型变量`yyn`的值，然后根据不同的`yyn`值获取具体的命令结构体。

在函数`yylex()`内部，调用`read_token()`获得各种类型的`token`并进一步调用`read_token_word()`获取具体的不同类型的单词结构`WORD_DESC`。

之后在`yyparse()`中，调用文件`make_cmd.c`中各种函数，根据`yylex()`获得的各种`token`和`word`组装成具体`command`。

其中，`make_word_list()`负责生成单词链表`WORD_LIST`；`make_redirection()`负责生成重定向链表`REDIRECT`；`command_connect()`根据一行语句中多个命令的逻辑顺序生成关系；`make_simple_command()`负责生成简单命令；以及一系列生成各种不同命令的其他函数。

此时的函数调用关系为：

```c
main()-->reader_loop()-->read_command()-->parse_command()-->yyparse()-->yylex()-->read_token()-->read_token_word()
                              |                                 |                      |                |
                        current_command  <-------------- global_command <------------token------------word
```
### 执行

在函数`reader_loop()`中，调用完`read_command()`获得`current_command`后，将调用`execute_cmd.c`中的`execute_command()`来执行命令：

```c
int
execute_command (command)
     COMMAND *command;
{
    ....
    result = execute_command_internal (command, 0, NO_PIPE, NO_PIPE, bitmap);
    ....
    return (result);
}
```
`execute_command()`调用`execute_command_internal()`函数：

```c
int
execute_command_internal (command, asynchronous, pipe_in, pipe_out,fds_to_close)
    ....
{
    ....
    switch (command->type)
    {
        case cm_simple:
        {
          ....
          exec_result = execute_simple_command (command->value.Simple, pipe_in, pipe_out, asynchronous, fds_to_close);
          ....
        }
        break;
        case cm_for:
        ....
        exec_result = execute_for_command (command->value.For);
        break;
        ....
        case cm_cond:
        ....
        exec_result = execute_cond_command (command->value.Cond);
        ....
        break;
        ....
        default: command_error ("execute_command", CMDERR_BADTYPE, command->type, 0);
    }
    ....
    last_command_exit_value = exec_result;
    ....
    return (last_command_exit_value);
}
```

在函数`execute_command_internal()`中，根据参数`command`的类型`command->type`，分别调用不同的命令执行函数，并返回命令的退出码。

此时函数的调用关系为：`main()-->reader_loop()-->execute_command()-->execute_command_internal()-->execute_xxxx_command()`。

这些命令执行函数除`execute_arith_command()`和`execute_cond_command()`之外，都将递归地调用`execute_command_internal()`并最终执行`execute_simple_command()`：

```c
static int
execute_simple_command (simple_command, pipe_in, pipe_out, async, fds_to_close)
    ....
{
    ....
    if (dofork)
    {
      ....
      if (make_child (savestring (the_printed_command_except_trap), async) == 0)
      {
        ....
      }
      else
      {
        ....
        return (result);
      }
    }
    ....
    words = expand_words (simple_command->words);
    ....
    builtin = find_special_builtin (words->word->word);
    ....
    func = find_function (words->word->word);
    ....
run_builtin:
    ....
    if (func == 0 && builtin == 0)
      builtin = find_shell_builtin (this_command_name);
    ....
    if (builtin || func)
    {
      ....
      result = execute_builtin_or_function(words, builtin, func, simple_command->redirects, fds_to_close, simple_command->flags);
      ....
      goto return_result;
    }
    ....
    result = execute_disk_command (words, simple_command->redirects, command_line, pipe_in, pipe_out, async, fds_to_close, simple_command->flags);

return_result:
    ....
    return (result);
}
```

首先，对于需要在子shell中执行的命令(如管道中的命令)，先调用`job.c`中的`make_child()`，然后进一步执行系统调用`fork()`及`execve()`。

如果并不需要在子shell中执行，则将简单命令中的单词进行扩展操作，调用的函数位于`subst.c`中，包括：`expand_words()`、`expand_word_list_internal()`等等。

之后进行命令搜索，先后调用如下函数：搜索特殊内置命令`find_special_builtin()`(此版本的bash包含如下特殊内置命令：`break continue : eval exec exit return set unset export readonly shift source . times trap`)，搜索函数`find_function()`，搜索内置命令`find_shell_builtin()`。

如果搜索到结果则执行`execute_builtin_or_function()`，如果没有搜索到则执行`execute_disk_command()`：

```c
static int
execute_disk_command (words, redirects, command_line, pipe_in, pipe_out, async, fds_to_close, cmdflags)
    ....
{
    ....
    result = EXECUTION_SUCCESS;
    ....
    command = search_for_command (pathname);
    ....
    pid = make_child (savestring (command_line), async);
    if (pid == 0)
    {
      ....
      if (command == 0)
      {
        ....
        internal_error (_("%s: command not found"), pathname);
        exit (EX_NOTFOUND);
        ....
      }
      ....
      exit (shell_execve (command, args, export_env));
    }
    else
    {
parent_return:
      close_pipes (pipe_in, pipe_out);
      ....
      FREE (command);
      return (result);
    }
}
```
`execute_disk_command()`首先调用`findcmd.c`中的`search_for_command()`进行命令搜索(注意区别函数`execute_simple_command()`中的命令搜索)：

```c
char *
search_for_command (pathname)
    const char *pathname;
{
    ....
    hashed_file = phash_search (pathname);
    ....
    if (hashed_file)
      command = hashed_file;
    else if (absolute_program (pathname))
      command = savestring (pathname);
    else
    {
      ....
      command = find_user_command (pathname);
      ....
    }
    return (command);
}
```

命令搜索首先在hash缓存中进行，如果命令名包含斜线`/`，则既不在PATH中搜索，也不在hash表中进行缓存，直接返回该命令。

如果hash缓存中未找到且不包含斜线，则调用`find_user_command()`及`find_user_command_internal()`等函数继续在PATH中寻找。

然后，`execute_disk_command()`调用`job.c`中的`make_child()`，`make_child()`内部执行系统调用`fork()`并返回`pid`。在子进程中，`execute_disk_command()`判断返回的命令`command`，如果未搜索到命令，则返回报错并退出，如果找到命令，则调用`shell_execve()`并进一步执行系统调用`execve()`：

```c
int
shell_execve (command, args, env)
    ....
{
    ....
    execve (command, args, env);
    ....
    i = errno;          /* error from execve() */
    ....
    if (i != ENOEXEC)
    {
      if (file_isdir (command))
        ....
      else if (executable_file (command) == 0)
        ....
      else
        ....
    }
    ....
    return (execute_shell_script (sample, sample_len, command, args, env));
    ....
}
```

如果`execve()`失败了，则判断文件，如果文件不是目录且有可执行权限，则把它当做脚本执行`execute_shell_script()`。

至此，子进程退出，父进程关闭管道，释放命令结构体，返回至函数`execute_command_internal()`并将结果`result`赋值给全局变量`last_command_exit_value`返回。

整个流程函数调用关系为：

```c
      main()
        |
   reader_loop()       解析
        |--------------------------->read_command()-->parse_command()-->yyparse()-->yylex()-->read_token()-->read_token_word()
        |                                 |                               |                       |                 |
 execute_command() <-------------- current_command <--------------- global_command <------------token------------word
        |
execute_command_internal()
        |
 execute_xxxx_command()
        |
execute_simple_command()
        |
        |--->expand_words()-->expand_word_list_internal()
        |                                                                  子进程
        |------------------------------------->execute_disk_command()------------->shell_execve()-->execve()                
        |                  磁盘命令                       |                |                       |
        |函数及内置命令                              make_child()          |                       |FAILED
        |                                                |                |                       |
execute_builtin_or_function()                          fork()----------->pid                      ->execute_shell_script()
                                                                          |
                                                                          --------->return(result)
                                                                            父进程
```

[0]: https://segmentfault.com/a/1190000008130200#articleHeader1