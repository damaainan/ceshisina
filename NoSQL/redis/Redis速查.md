这是Redis速查系列的开篇，如有不当之处欢迎指正

操作 | 操作类型  |  操作说明  |  结果返回  |  示例
-|-|-|-|-
set | 新增:创建指定key对应的值 |  创建一条String类型记录(相当于MySQL insert操作)  |  成功：'ok' | ① set name 'wuliuqing'
setnx |   新增:检测是否存在，不存在则新增   |  创建一条String类型记录前，检测指定名称的key是否已创建过，未创建则执行set操作；setnx = set not exists | 成功:1；失败:0  |  ① setnx name 'zhangsanfeng';return 0 <br/> ② setnx age 16;return 1
setex  |  新增:创建key对应值为value，并设置有效期  |   创建一条String类型记录，并设置值的有效期为N秒：setex = set expire  |  成功:'ok' | ① setex name 10 'wuliuqing'  设置name值为'wuliuqing',10秒后过期，过期后执行get name操作 返回nil
setrange  |   更新:设置key对应value的子字符串值 |   替换一条key对应value子字符串的值，注意字符串下表从0开始    | 成功:value值的新长度   前提:set name 'wu liuqing' | ① setrange name 2 'qing';执行get name操作返回'wuqingqing'，即替换掉值' liu'; <br/>② setrange name 2 'fei good boy';执行get name操作返回'hufei good boy'，即替换值'qingqing'
mset | 新增:执行多个 key:value操作 新增多条key:value记录，相当于多次执行set操作 | mset = multiple set |  成功:'ok' | ① mset name 'wuliuqing' age 18  执行get name操作返回'wuliuqing'，执行get age操作返回'18'
msetnx |  新增:检测是否存在，不存在则新增  |   在mset基础上执行检测对应的key是否已创建，未创建则执行新增  |   成功:1 失败:0    前提:mset name 'wu liuqing' age 18 | ① msetnx name 'zhang sanfeng' nickname 'zhang zhenren';响应结果为0；执行get name操作返回'wu liuqing'，执行get nickname操作返回nil <br/>② msetnx nickname 'liu gongzi' job 'php engineer'; 响应结果为1；执行get nickname操作返回'liu gongzi'，执行get job操作返回'engineer'
del | 删除  | 相当于MySQL delete操作   | 成功:1 失败:0   |  前提:set name 'wu liuqing' | del name
get | 查询：查询指定key设置的值  | 查询一条String类型key对应记录(相当于MySQL select操作)  | 成功:返回key对应value 失败:nil  | 略
getset  | 查询并更新  |  设置指定key的新值，并返回key对应的旧值 |  成功:key对应旧值 失败:nil  | 前提:set name 'wu liuqing'<br/>①getset name 'zhangsanfeng';响应结果'wuliuqing',执行get name响应结果为'zhangsanfeng'<br/>②getset age 16;响应结果nil
getrange   |  查询字串   |  查询key对应位置的字串 | 语法：getrange key stratpos endpos | 成功:字串  |  前提:set name 'wu liuqing'<br/>① getrange name 3 -1；响应liuqing<br/>② getrange name 3 5; 响应liu
mget |    查询 |  查询多个key对应的value | 成功:返回key对应value 失败:nil |  前提:set name 'wu liuqing' age 18<br/> ① mget name age;响应<br/> 1> 'wuliuqing'<br/> 2> '18'<br/> ① mget name nickname;响应<br/> 1> 'wuliuqing'<br/> 2> nil<br/>
incr | 自增  | 对数字类型value执行自增操作  |   成功:返回自增后value  |  前提:set age 18 <br/> ① incr age；响应结果:19 <br/> 前提:set name 'wuliuqing' <br/> ② incr name;响应： <br/> ERR value is not an integer or out of range
incrby |  按指定值执行加法操作 |  对数字类型value执行+N 操作 | 语法：incrby key stepvalue(当stepvalue为负值时执行减法操作 = decrby操作)  |   成功:返回key执行+N后的value | 前提:set age 18 <br/> ① incrby age 2;响应20 <br/> ② incrby age -5; 响应15
decr  |   自减  | 略 |   略 |   略
decrby  | 按指定值执行减法操作  | 对数字类型value执行-N 操作 | 语法：decrby key stepvalue(当stepvalue为负值时执行加法操作 = incrby操作) |    略  |  前提:set age 18 <br/> ① decrby age 2;响应16 <br/> ② incrby age -4; 响应20
append  | 拼接操作   |  在字符串结果拼接新字符串   |  成功:返回拼接后字符长度    | 前提:set name 'hu'<br/> ① append name ' liuqing';响应10<br/> 前提:set age 18<br/> ② append age ' year's old';响应13
strlen  | 获取长度    | 获取key对应value字符串长度   | 略 |   前提:set name 'hu' | ① strlen name;响应9