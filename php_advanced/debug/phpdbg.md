[https://phpdbg.room11.org/getting-started.html](https://phpdbg.room11.org/getting-started.html)


Information   
  list      list PHP source   
  info      displays information on the debug session   
  print     show opcodes   
  frame     select a stack frame and print a stack frame summary   
  generator show active generators or select a generator frame   
  back      shows the current backtrace   
  help      provide help on a topic   
   
Starting and Stopping Execution   
  exec      set execution context   
  stdin     set executing script from stdin   
  run       attempt execution   
  step      continue execution until other line is reached   
  continue  continue execution   
  until     continue execution up to the given location   
  next      continue execution up to the given location and halt on the first line after it   
  finish    continue up to end of the current execution frame   
  leave     continue up to end of the current execution frame and halt after the calling instruction   
  break     set a breakpoint at the specified target   
  watch     set a watchpoint on $variable   
  clear     clear one or all breakpoints   
  clean     clean the execution environment   
   
Miscellaneous   
  set       set the phpdbg configuration   
  source    execute a phpdbginit script   
  register  register a phpdbginit function as a command alias   
  sh        shell a command   
  ev        evaluate some code   
  quit      exit phpdbg   