## Wakatime 不升级会员也能查看历史数据了！

来源：[https://chenhuichao.com/2018/08/26/wakatime-sync/](https://chenhuichao.com/2018/08/26/wakatime-sync/)

时间 2018-08-27 00:25:17

 
[WakaTime][3] 是一款优秀的编程时间管理工具，可以在各大编辑器上统计追踪你的编程时间。
 
下面的截图是 WakaTime 的 Dashboard，它统计了每天的编程总时长及对应的项目时长、编程时间段、跟前一天编程时间对比、所用的语言、编辑器。
 
  
下面的截图是我的过去7天的统计数据，通过这几个维度，基本可以很全面量化你的编程时间。

![][0]

 
强烈建议每个程序员都去使用这个工具。时间管理的第一步就是记录时间。把你每天的编程时间记录下来，这样才能更清楚的认识到自己每天都把时间花在什么项目上。以后写月报、周报啥的都不慌，打开统计看一看，一周做了啥，一目了然。
 
WakaTime 分为免费版和付费版。免费版已经满足了正常时间统计，只不过在数据统计上有所限制。只能查看过去14天的统计数据，要想再看更前面的数据，只能升级到 $9 一个月的 Premium。如果你不想花费这9美元，可以试试
 
[wakatime-sync][4] 。
 
  
[wakatime-sync][5] 可以帮助你将 WakaTime 的统计数据同步到 Gist。利用 Gist 可以无限制得创建代码片段，把它当做数据备份的地方非常完美。目前只备份该接口的数据：`https://wakatime.com/api/v1/users/current/summaries`。因为通过该接口，基本上就能获取你所需要的各类信息。
 
下面就是我备份在 Gist 上的数据。

![][1]

 
备份在 Gist 上的数据这只是第一步，最后的目的还是想通过备份的数据，查看所有的历史数据。因此便有了 [wakatime-dashboard][6] 。
 
   Wakatime Dashboard  支持从 Gist 读取数据，并以堆叠柱状图的形式可视化数据。（后续将会支持更多的功能）

![][2]

 
这样就通过 Wakatime -> Gist -> Your App  曲线救国的方式，实现备份 WakaTime 数据，然后再以图表的方式可视化所有的数据。
 
## 项目地址 

 
* [wakatime-dashboard][6]  
* [wakatime-sync][4]  
 
 
喜欢的朋友可以点波 star，支持下作者。非常感谢！
 
## 最后 
 
如果经济能力允许的话，还是希望大家能够以开通 Premium 的方式来支持开发者，让开发者能够从中获取利润，进而有更多的精力去提升工具的质量和用户体验。这是一个双赢的结局。


[3]: https://wakatime.com/
[4]: https://chenhuichao.com/2018/08/26/wakatime-sync/wakatime-sync
[5]: https://github.com/superman66/wakatime-sync
[6]: https://chenhuichao.com/2018/08/26/wakatime-sync/github.com/superman66/wakatime-dashboard
[7]: https://chenhuichao.com/2018/08/26/wakatime-sync/github.com/superman66/wakatime-dashboard
[8]: https://chenhuichao.com/2018/08/26/wakatime-sync/wakatime-sync
[0]: ../img/ZnyMrmA.png
[1]: ../img/jiieE3U.png
[2]: ../img/BfeIJza.png