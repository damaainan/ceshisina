#!/bin/bash
#########################################################################
# File Name: tsk.dcc
# Author: zioer
# mail: next4nextjob@gmail.com
# Created Time: 2018年01月09日 星期二 23时14分40秒
#########################################################################

usage()
{
cat <<END
功能介绍:
        DCC消息抓包文件分析工具, tshark命令分析脚本 
		`basename $0` [ -s session_id ]  [-t oper_type ] [ -o out_file] [-f filter ] cap_file ....
			-s session_id : 会话ID，确保用双引号或者单引号括起来，因为session_id经常会带分号";"等特殊符号。
			-t oper_type  : 操作类型, 默认为show,取值有: show:展示关键dcc字段信息/sum: 汇总每个session_id流量使用总和/detail:比show展示的消息更详细，按照AVP列模式展示，通常用来展示单个消息明细
			-o out_file   : 设置输出文件名,不携带cap文件名后缀
			-f filter     : 设置过滤条件, 比如 : -f 'Origin-Host == \"abc.com\" ' 等。
END
}

str_session_id=""
str_oper_type="show"
str_out_file=""
out_flag=0
## tshark 参数选项列表
str_options=""
## 协议消息过滤规则列表
str_filter=""
while getopts eqb:s:t:f:o: arg_val
do
        case $arg_val  in
        q)
                ## 安静模式，不输出任何无关信息 ###
                quite_flag="1"
        ;;
        b)
                ## background模式，后台运行每一个ssh服务，最后wait执行结束 ###
                bg_flag="1"
        ;;
        s)
                str_session_id="$OPTARG"
                str_filter="diameter.Session-Id == \"$str_session_id\" "
        ;;
		f)
				str_filter="$OPTARG"
		;;
        t)
                str_oper_type="$OPTARG"
        ;;
        o)
                str_out_file="$OPTARG"
                out_flag=1
                str_out_file_tmp="${str_out_file}.tmp." ; ## 处理多文件时，合并前的临时文件前缀.
        ;;
        ?)
                usage
        ;;
        esac
done

shift $(($OPTIND - 1 ))

if [ "$#" = "0" ] ; then
        usage
        exit 2
fi

##识别diameter协议的端口范围
port_address="1024-20000"
dcc_field_list="272,Session-Id,Origin-Host,3GPP-Charging-Id,CC-Request-Type,CC-Request-Number,Subscription-Id-Data,Event-Timestamp,Result-Code,Rating-Group,CC-Total-Octets,CC-Input-Octets,CC-Output-Octets,3GPP-Reporting-Reason,3GPP-Charging-Id,3GPP-Charging-Characteristics,3GPP-RAT-Type,3GPP-User-Location-Info"

i=1
for cap_file in $@
do
	if [ "$out_flag" = "1" ] ; then
		str_options=" -w ${str_out_file_tmp}.${i}.cap"
	fi
	run_cmd="tshark -r $cap_file ${str_options} -d tcp.port==${port_address},diameter -q -z diameter,avp,${dcc_field_list}  ${str_filter}"
#	[[ ! -z "$str_session_id" ]]  && run_cmd="${run_cmd} \| grep \"$str_session_id\""

	case "${str_oper_type}" in 
		show )
		{
			if [ ! -z "$str_session_id" ]  ; then
				${run_cmd} |  grep "${str_session_id}"
			else
				${run_cmd}
			fi
		}
		;;
		sum )
		{
			if [ ! -z "$str_session_id" ]  ; then
				${run_cmd} |  grep "${str_session_id}"
			else
				${run_cmd}
			fi
		} | grep "is_request='1'" | awk '{
                gsub("'\''","");
		out_str=""
		for( i = 1; i <=NF; i++){ 
                	split($i,arr,"="); 
	                if( $i~/Session-Id/)  out_str=arr[2];
	                if( $i~/Subscription-Id-Data/)  out_str=out_str" "arr[2]
       		         if( $i~/CC-Total-Octets/) {     dataflow=arr[2]; out_flag=1;  } 
       		         if( $i~/Rating-Group/ && out_flag == 1) { 
       		                 out_flag=0; 
       		                 printf("%s %s %.0f\n", out_str, arr[2], dataflow ); 
       		         }
       		 }
        }' | awk '{
		idx=$1
                for( i = 2; i < NF; i++) { idx = idx" "$i }
	        flow=$NF;
		tot[idx] += flow;
		total  += flow;
        }END{
                for( i in tot){
                        printf("%s %15.0f %10.2f\n", i , tot[i], tot[i]/1024/1024 );
                }
                printf("total: %.2f MB\n", total/1024/1024 );
        }'
		;;
		detail)
			tshark -r $cap_file -d tcp.port==${port_address},diameter -V ${str_filter} | grep 'AVP: '
		;;
		*)
		usage ;;
	esac
	i=$(( $i + 1 ))
done

if [ "$out_flag" = "1" ] ; then
        mergecap -w ${str_out_file}.cap ${str_out_file_tmp}.*.cap
        [[ "$?" = "0" ]] && rm -f ${str_out_file_tmp}.*.cap || echo "merge error!file:[${str_out_file}.cap],error_return [ $? ] "
fi
