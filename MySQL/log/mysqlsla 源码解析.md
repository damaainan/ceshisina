# [mysqlsla 源码解析][0]

By [兰春][1]

Jul 14 2015 Updated:Jul 15 2015

**Contents**

[1. 知识结构][2]  
[2. Packages][3]  
[3. main 主要流程图][4]  
[4. 以slow log 分析为导火线][5]  
[5. 已知的bug list 以及 Bug 修复][6]  

## 知识结构

- - -

* **目的： 通过mysqlsla的源码分析，能够更加深入理解其内部实现机制**
* **mysqlsla 主要分为三个大的packages，我们先大致了解一下package 都包含什么，都做什么**
* **分析 main 函数，一步步了解主要流程**
* **针对一条线进行分析，比如slow log 的解析**

## Packages

- - -

* **(MySQL::Log::)ParseFilter**

函数名 |功能简介 
-|-
get_meta_filter | 获取meta fileter 
get_statement_filter | 获取statement fileter 
set_save_meta_values | 设置meta 
set_save_all_values | 设置所有 
set_IN_abstraction | 设置是否IN_abstraction 
set_VALUES_abstraction | 设置是否VALUES_abstraction 
set_atomic_statements | 没用过 
set_db_inheritance | DB 继承 
set_grep | 设置是否grep 
set_meta_filter | -mf，设置过滤meta  
set_statement_filter | -sf，设置过滤sql 
set_udl_format | 设置自定义格式 
parse_binary_logs | 解析binary日志 
parse_general_logs | 解析general日志 
parse_slow_logs | 解析slow 日志 
parse_udl_logs | 解析udl 日志 
check_stmt | 检查stamt合法性，规范化stmt 
abstract_stmt | 抽象stmt 
compact_IN | 对In 做特殊处理 
compact_VALUES | 对values 做特殊处理 
passes_statement_filter | 判断是否sf 
passes_meta_filter | 判断是否mf 
calc_final_values | 计算最终的value 
apply_final_meta_filters | 应用最后的mf 
set_debug | 是否debug 
_d | debug日志 
_p | 百分比 

* **ReportFormats**

函数名 | 功能简介 
-|-
get_report_format | 获取log type 
report_formats{slow} | slow 报表 
report_formats{general} | general 报表 
report_formats{binary} | binary 报表 
report_formats{msl} | msl 报表 
report_formats{udl} | udl 报表 

* **LogType**

函数名 | 功能简介 
-|-
new | new 对象 
get_log_type | 得到日志类型 
lines_match_log_type | 得到日志类型2 
name_for | 得到日志类型3 
_d | debug 

* **QueryRewriter**

函数名 | 功能简介 
-|-
new | new 对象 
strip_comments | 没用到 
fingerprint | SQL指纹，没用到 
convert_to_select | 转换select 
convert_select_list | 转换select list 
__delete_to_select | dml -> select 
__insert_to_select | dml -> select 
__update_to_select | dml -> select 
wrap_in_derived | 没用到 
_d | debug 

* **main**

函数名 | 功能简介 
-|-
help_and_exit | 查看帮助 
read_mycnf | 读取mysql 配置文件 
read_dot_mysqlsla | 读取mysqlsla 配置文件 
connect_to_MySQL | 连接mysql 
parse_logs | 解析日志 
refilter_replay | replay 功能 
calc_nthp | 统计nthp 
calc_dist | 统计百分比 
EXPLAIN_queries | explain 功能 
get_create_table | 得到create 
table | get_row_count 得到rc 
parse_table_aliases | 解析表别名 
get_table_ref | 得到表的reference 
time_each_query | -report-file 的一种 
time_profile | 没用到 
calc_rows_read | 统计有多少行被读取过 
sort_and_prune | 排序和统计 
make_reports | 制作报表的入口函数 
standard_report | 标准report 
dump_report | dump report 
time_all_report | time_all report 
print_all_report | print——unique 的变相report 
print_unique_report | print-uniq 的report 
resolve_coded_value | 转换code value 
parse_report_format | 解析报表format 
save_replay | 保持replay结果 
beautify | 美化 
stmt_users_summary | 统计users特殊变量，主要用于标准格式报表 
EXPLAIN_summary | expplian的统计 
schema_summary | 结构的统计 
avg | 平均值 
p | 百分比 
format_u_time | 格式化u time 
make_short | 简单模式 
d | debug 
get_options | 获取命令行参数 
set_MySQL_reserved_words | 设置保留字 

## main 主要流程图

- - -

![test][7]

## 以slow log 分析为导火线

- - -

* **先设置各种变量，判断操作系统，读取mysql 配置文件**
```
use strict;

use English qw(-no_match_vars);

use Time::HiRes qw(gettimeofday tv_interval);

use File::Temp qw(tempfile);

use Data::Dumper;

use DBI;

use Getopt::Long;

use Storable;

eval { require Term::ReadKey; };

my $RK = ($@ ? 0 : 1);

our $VERSION = '2.03';

my $WIN = ($^O eq 'MSWin32' ? 1 : 0);

my %op;

my %mycnf; # ~/.my.cnf

my ($dbh, $query, $MySQL_connected);

my ($q_h, %queries, %u_h, $q_a, @all_queries, %g_t);

my $total_queries;

my $total_unique_queries;

my $total_unique_users;

my $u = chr(($WIN ? 230 : 181)) . 's'; # micro symbol

my %params;

my %MySQL_reserved_words; # used by beautify()

my %db; # --databases

my %af; # --analysis-filter

my %r;  # --reports

my (@headers, @header_vals);  #

my (@formats, @format_vals);  # standard report

my %conditional_formats;      #
```
* **读取mysqlsla 自己的内部初始化文件 ./mysqlsla**
```
read_dot_mysqlsla();

  open MYSQLSLA, "< $ENV{HOME}/.mysqlsla" or return;

   while(<MYSQLSLA>)

   {

      next if /^$/;

      next if /^#/;

      $op{$1} = $2, next if /^(\S+)\s*=\s*(\S+)/;

      $op{$1} = 1,  next if /^(\S+)/;

   }

   close MYSQLSLA;
* 根据代码可以看到，配置文件中不能对参数加前导符号，如： -， -- 等，这里是没有办法匹配的。
```

* **接下来就是将命令行的参数读取进来，并且覆盖掉~/.mysqlsla中的参数**
```
$ops_ok = GetOptions(

      \%op,

      "user=s",

      "password:s",

      "host=s",

      "port=s",

      "socket=s",

      "no-mycnf",

      "mycnf=s",

      "db|D|databases=s",

      "help|?",

      "lt|log-type=s", 

      "uf|udl-format=s",

      "sort=s",

      "flush-qc",

      "avg|n=i",

      "percent",

      "top=n",

      "mf|meta-filter=s",

      "sf|statement-filter=s",

      "grep=s",

      "dist",

      "dmin|dist-min-percent=i",

      "dtop|dist-top=i",

      "nthp|nth-percent:i",

      "nthpm|nthp-min-values=i",

      "ex|explain",

      "te|time-each-query",

      "rf|report-format=s",

      "reports|R=s",

      "silent",

      "post-parse-replay=s",

      "post-analyses-replay=s",

      "post-sort-replay=s",

      "replay=s",

      "Av|abstract-values",

      "Ai|abstract-in=i",

      "atomic-statements",

      "dont-save-meta-values",

      "save-all-values",

      "db-inheritance",

      "microsecond-symbol|us=s",

      "debug",

      "extra|x=s",

   );
 * 这基本上就是所有的命令行对应的参数。
```

* **然后，参数读取到之后，就开始解析参数。 当然，第一个肯定就是判断日志类型了。**
```
if ( !$op{lt} ) {

   my $lt = new LogType;

   my $log_type = $lt->get_log_type($ARGV[0]);

   die 'Cannot auto-detect log type. Use option --log-type.' if !$log_type;

   $op{lt} = $lt->name_for($log_type);

   print "Auto-detected logs as $op{lt} logs\n";

}

sub get_log_type {

   my ( $self, $log_file ) = @_;

   $log_file ||= '';

   MKDEBUG && _d("Detecting log type for $log_file");

   return LOG_TYPE_UNKNOWN if !$log_file;

   my $log_fh;

   if ( !open $log_fh, '<', $log_file ) {

      MKDEBUG && _d("Failed to open $log_file: $OS_ERROR");

      return LOG_TYPE_UNKNOWN;

   }

   my @lines   = ();

   my $n_lines = 0;

   while ( ($n_lines++ < $self->{sample_size}) && (my $line = <$log_fh>) ) {

      push @lines, $line;

   }

   close $log_fh;

   foreach my $log_type ( @{ $self->{detection_order} } ) {

      if ( $self->lines_match_log_type(\@lines, $log_type) ) {

         MKDEBUG && _d("Log is type $log_type");

         return $log_type;

      }

   }

   MKDEBUG && _d("Log type is unknown");

   return LOG_TYPE_UNKNOWN;

}

sub lines_match_log_type {

   my ( $self, $lines, $log_type ) = @_;

   return 0 if ( !ref $lines || scalar @$lines == 0 );

   foreach my $pattern ( @{ $patterns_for{$log_type} } ) {

      foreach my $line  ( @$lines ) {

         if ( $line =~ m/$pattern/ ) {

            MKDEBUG && _d("Log type $log_type pattern $pattern matches $line");

            return 1;

         }

      }

   }

   return 0;

}

my %patterns_for = (

   LogType::LOG_TYPE_SLOW    => [

      qr/^# User\@Host:/,

   ],

   LogType::LOG_TYPE_GENERAL => [

      qr/^\d{6}\s+\d\d:\d\d:\d\d/,

      qr/^\s+\d+\s+[A-Z][a-z]+\s+/,

   ],

   LogType::LOG_TYPE_BINARY  => [

      qr/^.*?server id \d+\s+end_log_pos/,

   ],

);
```
* 通过以上代码，发现如果有指定日志类型，mysqlsla就会用指定类型的日志去解析。

* 如果没有指定类型，那么会自动判断。它究竟是如何自动判断呢？根据文件名？扩展名？还是？

get_log_type -> lines_match_log_type -> patterns_for , 代码结构是这样。

匹配模式分为几种： 判断slow，主要是这个正则表达式qr/^# User\@Host:/。  如果遇到 # User@Host ，那么程序会判断为slow类型

* **接下来开始判断 数据格式**
```
parse_report_format($op{rf},

                    \@headers, \@header_vals,

                    \@formats, \@format_vals,

                    \%conditional_formats) if exists $r{standard};

* 根据指定的rf 文件来report，如果没有，则认为是标准模式standard。
```
* **接下来读取 mycnf 文件，并且用 命令行参数接收到的value 直接覆盖掉 mycnf变量**
```
read_mycnf() unless $op{'no-mycnf'};  # read ~/.my.cnf or $op{mycnf}

# Command line options override ~/.my.cnf

$mycnf{host}   = $op{host}   if $op{host};

$mycnf{port}   = $op{port}   if $op{port};

$mycnf{socket} = $op{socket} if $op{socket};

$mycnf{user}   = $op{user}   if $op{user};

$mycnf{user} ||= $ENV{USER};
```
* **设置一些重要的meta 值**
```

ParseFilter::set_save_meta_values(0)      if $op{'dont-save-meta-values'};

ParseFilter::set_save_all_values(1)       if $op{'save-all-values'};

ParseFilter::set_IN_abstraction($op{Ai})  if $op{Ai};

ParseFilter::set_VALUES_abstraction(1)    if $op{Av};

ParseFilter::set_atomic_statements(1)     if $op{'atomic-statements'};

ParseFilter::set_db_inheritance(1)        if $op{'db-inheritance'};

ParseFilter::set_grep($op{grep})          if $op{grep};

ParseFilter::set_debug(1)                 if $op{debug};
```
* **当我们调用set_statement_filter前，必须检查的一个参数 -r，即 —reports**
``` 

if(($op{te} || exists $r{'time-all'}))

{

   if(!$op{sf})

   {

      print STDERR "Safety for time-each/time-all is enabled (statement-filter='+SELECT,USE')\n";

      $op{sf} = "+SELECT,USE";

   }

   else

   {

      print STDERR "Safety for time-each/time-all is DISABLED!\n";

   }

}
```
* 意思就是：如果设置了--reports=‘time-all’，且没有设置-sf参数，那么mysqlsla会自动设置sf值为：$op{sf} = "+SELECT,USE"; 如果自己显示的指定了-sf参数，那么mysqlsla会告诉用户Safety for time-each/time-all is DISABLED!

* 为什么会这样呢？因为这个参数会真正的在mysql中执行，也就是说如果是DML语句，一不小心会污染线上环境，导致不可预期的错误。所以，在使用--reports = ‘-time-all’的时候，要特别特别小心，这种参数，我基本上不会使用。但是特殊情况下，我们还是会使用的。比如：压力测试，还原线上执行过的SQL 等等。

* **接下来开始日志parsing 阶段,调用的是：parse_slow_logs 这个函数。 这里重点讲解slow 是如何解析的**
```
parse_logs() if @ARGV;
```
由于代码比较多，这里我拿重点的出来讲：

```
foreach $log (@$logs)  # 由于参数中可以接很多日志文件，所以日志必须循环，然后一个个日志解析。

next until $line =~ /^# User/;  # 这是第一个头，当mysqlsla 碰到 # User ，那么一个日志解析开始。

($user, $host, $IP) = $line =~

            /^# User\@Host: (.+?) \@ (.*?) \[(.*?)\]/ ? ($1,$2,$3) : ('','',''); # 获取user，host，ip

  next if (exists $mf{user} && !passes_meta_filter('user', $user, 's'));

         next if (exists $mf{host} && !passes_meta_filter('host', $host, 's'));

         next if (exists $mf{ip}   && !passes_meta_filter('ip',   $IP,   's')); ## 还记得我们有mf参数么，如果mf没有，及可以跳过。
```

接下来就是read statament的部分代码：

```
         READ_STATEMENTS:

         while($line = <LOG>)

         {

            last if $line =~ /^#(?! administrator )/; # stop at next stmt but not administrator commands

            last if $line =~ /^\/(?![\*\/]+)/;        # stop at log header lines but not SQL comment lines

            next if $line =~ /^\s*$/;

            $stmt .= $line;

         }

         $valid_stmt = check_stmt(\$stmt, \$use_db);  # 这里是用于拼接statment 语句，判断其合法性

        $q = abstract_stmt($stmt);                  #  这里是重点，抽象valid SQL。
```

* **check_stmt: 用于检测statment合法性，并且合并SQL**
  
```
比如：slow 日志如下

# Time: 141128 23:59:22

# User@Host: readonly_v2[readonly_v2] @  [10.10.3.139]

# Query_time: 0.636455  Lock_time: 0.000064 Rows_sent: 3  Rows_examined: 184547

use anjuke_db;

SET timestamp=1417190361;

SELECT    md5,    Width,    Height,    Size,    UploadTime   FROM    commpic_base_info   WHERE md5 in   ('421a656739c63f2c7e1587d213e7585a','8a3ed632211cf72bb3a9720f5c025ce9','c6fd1d939f0640d70cb3fec46da1fa2b') ;

check_stmt 会将下面三种语句进行合并，然后变成一条。
```
* **abstract_stmt: 抽象化SQL，我个人感觉这是slow 分析的最最最重要的地方**   
```
sub abstract_stmt

{

   my $q = lc shift;  # scalar having statement to abstract

   my $t;  # position in q while compacting IN and VALUES

   # --- Regex copied from mysqldumpslow

   $q =~ s/\b\d+\b/N/go;

   $q =~ s/\b0x[0-9A-Fa-f]+\b/N/go;

   $q =~ s/''/'S'/go;

   $q =~ s/""/"S"/go;

   $q =~ s/(\\')//go;

   $q =~ s/(\\")//go;

   $q =~ s/'[^']+'/'S'/go;

   $q =~ s/"[^"]+"/"S"/go;

   # ---

   $q =~ s/^\s+//go;      # remove leading blank space

   $q =~ s/\s{2,}/ /go;   # compact 2 or more blank spaces to 1

   $q =~ s/\n/ /go;       # remove newlines

   $q =~ s/`//go;         # remove graves/backticks

   # compact IN clauses: (N, N, N) --> (N3)

   while ($q =~ m/( in\s?)/go)

   {

      $t = pos($q);

      $q =~ s/\G\((?=(?:N|'S'))(.+?)\)/compact_IN($1)/e;

      pos($q) = $t;

   }

   # compact VALUES clauses: (NULL, 'S'), (NULL, 'S') --> (NULL, 'S')2

   while ($q =~ m/( values\s?)/go)

   {

      $t = pos($q);

      $q =~ s/\G(.+?)(\s?)(;|on|\z)/compact_VALUES($1)."$2$3"/e;

      pos($q) = $t;

   }

   return $q;  # abstracted form of stmt

}

* 第一步就是将原始SQL做各种替换，即将参数全部替换成S，N。 并且将一些特殊空格，字符全部替换掉。替换表达式均来自官方的mysqldumpslow。

* 第二步就是将 IN clauses: (N, N, N) --> (N3) 

* 第三步就是将 VALUES clauses: (NULL, 'S'), (NULL, 'S') --> (NULL, 'S')2

这就是bug存在的地方，后续会讲如何改进。
```
* **统计grand 值，用于report**
```
$total_queries = ParseFilter::calc_final_values(%params, \%g_t);
```
* **最后作meta 过滤**
```
ParseFilter::apply_final_meta_filters(%params, \$total_queries) if $op{mf};
```
* **之后进入 分析阶段,主要是统计百分比，应用日志阶段**
```
calc_nthp($q_h)         if exists $op{nthp};

calc_dist($q_h)         if $op{dist};

time_each_query($q_h)   if $op{te};
```
* **然后进入 report 阶段，包括排序，explain，制作报表**
* **sort_and_prune()**   
```
sub sort_and_prune

{

   my $top;

   my $sort;

   $op{top}  ||= 10;

   $op{sort} ||=($op{lt} eq 'slow' || $op{lt} eq 'msl' ? 't_sum' : 'c_sum');

   $top = $op{top};

   d("sort_and_prune: top $op{top} sort $op{sort}\n") if $op{debug};

   foreach (sort { $$q_h{$b}->{$op{sort}} <=> $$q_h{$a}->{$op{sort}} } keys(%$q_h))

   {

      $$q_h{$_}->{sort_rank} = ($op{top} - $top + 1);

      last if !--$top;

   }

   foreach(keys %$q_h) { delete $$q_h{$_} if !exists $$q_h{$_}->{sort_rank}; }

}

* 默认的展示只有 $op{top}  ||= 10; 所以如果想要更多，请手动 --top=N

* 如果日志类型为slow，默认sort是根据t_sum,如果是msl，则是 c_sum

   $op{sort} ||=($op{lt} eq 'slow' || $op{lt} eq 'msl' ? 't_sum' : 'c_sum');

* 然后偶根据制定的参数，进行sort 与 合并。
```
* **EXPLAIN_queries**
    
```
sub EXPLAIN_queries

{

   d("EXPLAIN_queries\n") if $op{debug};

   my $q_h = shift;  # reference to hash with queries

   my $row;

   my @rows;

   my $col;

   my ($x, $q);

   my ($i, $j);

   my $select_query;

   connect_to_MySQL();  # safe to call multiple times; it will just return

                        # if we're already connected to the MySQL server

   foreach $q (keys %$q_h)

   {

      $x = $$q_h{$q};

      $x->{EXPLAIN_err} = 0;

      $x->{rp} = -1;

      $x->{rr} = -1;

      if($x->{sample} !~ /^SELECT/i)

      {

         my $qr = new QueryRewriter;

         $select_query = $qr->convert_to_select($x->{sample});

         if ( $select_query !~ /^SELECT/i ) {

            $x->{EXPLAIN_err} = "Cannot convert to a SELECT statement";

            next;

         }

      }

      else {

         $select_query = $x->{sample};

      }

      if(!$x->{db})

      {

         if(!$op{db})

         {

            # See if query has qualified table names which will allow it

            # to be EXPLAINed without setting the db

            eval {

               $query = $dbh->prepare("EXPLAIN $select_query");

               $query->execute();

            };

            if ( $EVAL_ERROR ) {

               $x->{EXPLAIN_err} = "Unknown database and no qualified table names";

               next;

            }

            else {

               goto PARSE_EXPLAIN;

            }

         }

         else

         {

            foreach(keys %db)

            {

               $dbh->do("USE $_;");

               $query = $dbh->prepare("EXPLAIN $select_query");

               $query->execute();

               next if $DBI::err;

               $x->{db} = $_;

               last;

            }

            if(!$x->{db})

            {

               $x->{EXPLAIN_err} = "Unknown database and no given databases work";

               next;

            }

         }

      }

      $query = $dbh->prepare("USE $x->{db};");

      $query->execute();

      $x->{EXPLAIN_err} = $DBI::errstr and next if $DBI::err;

      $query = $dbh->prepare("EXPLAIN $select_query");

      $query->execute();

      $x->{EXPLAIN_err} = $DBI::errstr and next if $DBI::err;

      PARSE_EXPLAIN:

      $x->{EXPLAIN} = [] if $op{ex};

      $x->{tcount}  = '' if $extras{tcount};

      $x->{TSCHEMA} = [] if $extras{tschema};

      while($row = $query->fetchrow_hashref())

      {

         push @rows, ($row->{rows} ? $row->{rows} : 0)

            if $op{ex};

         for($j = 0; $j < $query->{NUM_OF_FIELDS}; $j++)

         {

            $col = $query->{NAME}->[$j];

            if ( $op{ex} ) {

               push @{$x->{EXPLAIN}}, $col;

               push @{$x->{EXPLAIN}}, ($row->{$col} ? $row->{$col} : '');

            }

         }

      }

      if ( $op{ex} ) {

         for($i = 0, $j = 1; $i < $query->rows; $i++) { $j *= $rows[$i]; }

         $x->{rp} = $j; # Rows produced

         $x->{rr} = calc_rows_read(\@rows);

      }

      if ( $extras{tcount} || $extras{tschema} ) {

         my $tbls = parse_table_aliases(get_table_ref($select_query));

         foreach my $tbl ( keys %$tbls ) {

            next if $tbl eq 'DATABASE';

            my $db = $x->{db};

            if (    exists $tbls->{DATABASE}

                 && exists $tbls->{DATABASE}->{$tbl} ) {

               $db = $tbls->{DATABASE}->{$tbl};

            }

            if ( $extras{tcount} ) {

               my $n = make_short(get_row_count($dbh, $db, $tbls->{$tbl}));

               $x->{tcount} .= "$tbls->{$tbl}:$n ";

            }

            if ( $extras{tschema} ) {

               my $ddl = get_create_table($dbh, $db, $tbls->{$tbl});

               if ( $ddl ) {

                  push @{$x->{TSCHEMA}},

                     ($ddl->[0] eq 'view' ? '(VIEW) ' : '')

                     . $ddl->[1];

               }

               else {

                  $x->{TSCHEMA} = 'Could not get table schemas';

               }

            }

         }

      }

   }

}

* 这里，如果指定了-ex，那么mysqlsla 会主动连接mysql，用户名，密码，主机当然都是你提供的。然后重写SQL语句QueryRewriter，将非select语句，全部转换成SELECT子句。并且根据explain的结果进行分析，最终report到client。
```
* **最后一步，产生报表**   
```
make_reports() ->  standard_report(\@headers, \@header_vals, \@formats, \@format_vals)

                         if exists $r{standard};

* 基本格式为：

     HEADER

    (header line format) 

    (header line values)

    REPORT

    report line format

    report line values

    主要分两部分：HEADER ， REPORT。

    根据不同的key，得到不同的value。 

这里简单介绍一下slow 的默认格式作为参考：

-nthp

HEADER

Report for %s logs: %s

lt:op logs

%s queries total, %s unique

total_queries:short total_unique_queries:short

Sorted by '%s'

sort:op

Grand Totals: Time %s s, Lock %s s, Rows sent %s, Rows Examined %s

gt_t:short gt_l:short gt_rs:short gt_re:short

REPORT

______________________________________________________________________ %03d ___

sort_rank

Count         : %s  (%.2f%%)

c_sum:short c_sum_p

Time          : %s total, %s avg, %s to %s max  (%.2f%%)

t_sum:micro t_avg:micro t_min:micro t_max:micro t_sum_p

? %3s%% of Time : %s total, %s avg, %s to %s max

nthp:op t_sum_nthp:micro t_avg_nthp:micro t_min_nthp:micro t_max_nthp:micro

? Distribution : %s

t_dist

Lock Time (s) : %s total, %s avg, %s to %s max  (%.2f%%)

l_sum:micro l_avg:micro l_min:micro l_max:micro l_sum_p

? %3s%% of Lock : %s total, %s avg, %s to %s max

nthp:op l_sum_nthp:micro l_avg_nthp:micro l_min_nthp:micro l_max_nthp:micro

Rows sent     : %s avg, %s to %s max  (%.2f%%)

rs_avg:short rs_min:short rs_max:short rs_sum_p

Rows examined : %s avg, %s to %s max  (%.2f%%)

re_avg:short re_min:short re_max:short re_sum_p

Database      : %s

db

Users         : %s

users

?Table:#rows   : %s

tcount

?Table schemas : %s

tschema

?EXPLAIN       : %s

explain

Query abstract:

_

%s

query:cap

Query sample:

_

%s

sample
```
* **以上，就是slow log分析的全部过程，其他的日志分析和slow 基本上差不多，所以不做多讲**

## 已知的bug list 以及 Bug 修复

- - -

* **select xx from table where id in (N,N,N) 这种类似的语句，没有办法归类，主要会影响slow的排序。**
```
症状：这种SQL语句，会严重干扰抽象语句的统计和排序，对于之后的slow 分析诸多不便。

解决方案： select xx from table where id in (N,N,N) -> select xx from table where id in (N) 即可。
```
* **特殊格式的SQL 抽象解析有误**
```
SQL1: SELECT * FROM zx_article_attributes WHERE  `column_id` IN (     '4','5','6','7','8','9','12','15','18','19')  ORDER BY created DESC LIMIT 3 OFFSET 0

SQL2: SELECT * FROM zx_article_attributes WHERE  `column_id` IN ('4','5','6','7','8','9','12','15','18','19')  ORDER BY created DESC LIMIT 3 OFFSET 0

以上这两种语句在抽象的时候，会得到不同的结果。 在IN 中，括号后面不能有空格，如果有，就不能被统一抽象化，但其实他们是同一类SQL。
```
* **insert into xx values(‘s’),(‘s’)…..(‘s’) 会导致SQL 没办法分类**
```
症状： 这种SQL语句，会严重干扰抽象语句的统计和排序，对于之后的slow 分析诸多不便。

解决方案： insert into xx values('s'),('s').....('s')  -> insert into xx values('s') 即可
```
* **原始SQL 语句的注释会干扰 slow分析**
```
症状： SQL1 #from api-lc 和 SQL1 #from api-lc  这里面，SQL1 都是同样的SQL，但是统计就会出错。

解决方案：将注释不作为统计的条件，即可。
```
* **meta-property filter 无法精准过滤**

```
症状：-mf 中的op 条件只能是三种，且>,=,< 。 字符串只能是 =

解决方案： 我个人认为在统计中 > 和 >= 没多少区别，也没必要这些统计。
```
* **新功能的添加**

```
1) 之前有的时候，发现slow 语句中并没有来自哪个DB，比较郁闷。现在源码中看到，db-inheritance 可以解决这个问题，很好用

2）修改slow standard report ，  所有slow， 新增 ip sum 这一属性，方便日后解析统计。

3) mysqlsla_lc_v2.0 新增对mysql5.6 binlog 的解析支持。
```
以上的改动，均在新的mysqlsla 版本中修复。详细内容，请看修复后的代码。

[老的mysqlsla源码][8]

[修改后的mysqlsla源码][9]

[0]: /2015/07/14/mysqlsla_source_read/
[1]: https://Keithlan.github.io
[2]: #知识结构
[3]: #Packages
[4]: #main_主要流程图
[5]: #以slow_log_分析为导火线
[6]: #已知的bug_list_以及_Bug_修复
[7]: ./img/mysqlsla_main.png
[8]: https://github.com/Keithlan/file_md/blob/master/Keithlan/mysql/SYSTEM_TOOLS/tz_slow/mysqlsla
[9]: https://github.com/Keithlan/file_md/blob/master/Keithlan/mysql/SYSTEM_TOOLS/tz_slow/mysqlsla_lc_v2.0