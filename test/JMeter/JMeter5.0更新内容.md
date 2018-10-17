## JMeter5.0更新内容

来源：[http://shiyuanjie.cn/2018/10/17/JMeter5.0更新内容/](http://shiyuanjie.cn/2018/10/17/JMeter5.0更新内容/)

时间 2018-10-17 12:19:57

 
[JMeter][15] 官方在`20180918`发布了JMeter 5.0版本，主要涉及的更新内容如下：

 
* 新增`XPath2 extractor`元素，用于XML处理  
* 增强`Flow Control Action``Result Status Action Handler`控制器的能力  
* 强化搜索功能，便于在编写JMeter过程中进行搜索 
* 强化`录制功能`，使用顶部悬浮的特点，便于对录制过程的处理  
* UI界面中，新增`重启`功能  
* `HTML Web`报告中，新增`实时`吞吐量显示  
* `HTML Web`报告中，新增`Custom Graphs section`用于个性化图表定制  
 
 
原文链接： [http://jmeter.apache.org/changes.html][16]
 
JMeter5.0下载： [http://jmeter.apache.org/download_jmeter.cgi][17]
 
## 核心能力提升 
 
请求能力提升


- `Multipart/form-data`请求现在支持**PUT,DELETE...**
- 支持已附件的形式发送`JSON`数据文件，选择对应的文件路径配制即可
- `PUT`等请求类型支持`Multipart/form-data`选项

 
![][0]
 
![][1]
 
在分布式测试中，JMeter自动添加测试机的IP和Port作为线程的前缀名，这样就可以在HTML报告中正确的统计线程数量，而不需要做其它的配制。
 
![][2]
 
XPath 2.0新增一个元素`XPath2 extractor`，快速处理XML，与XPath语法保持一致并且有更好的性能。
 
![][3]
 
![][4]
 
已全部更新并支持HTTP模块的最新4.6API，JMeter不再支持此模块已经废弃的API。
 
现在更加容易的控制在Loop循环中的中断和跳转至下一循环。可以在`Flow Control Action`和`Result Status Action Handler`的元素中使用。
 
![][5]
 
![][6]
 `While`循环现在会抛出一个变量，包含以`__jm__<Name of your element>__idx`命名的当前索引。比如，如果你的`While`循环名为WC，那么，你可以使用`${__jm__WC__idx}`来获取循环的索引
 
## 脚本/调试增强 
 
提升搜索特性，你可以在整个树中进行搜索。可以通过使用`Next/Previous/Replace/Replace All/Replace & Find`来进行替换或搜索。
 
![][7]
 
在结果树中，请求和响应的请求头和请求体被清晰的分开，这样就可以更好的检视请求和响应。也可以在所有的Tab中搜索部分值。
 
![][8]
 
![][9]
 
录制特性增加了一个始终在顶部的弹出框，当你在浏览器中操作时，可以命名你的事务。
 
![][10]
 
现在可以通过菜单`File --> Restart`来重启JMeter。
 
![][11]
 
## 实时报告和网页报告 
 
报告功能也被增强。
 
HTML网页报告中新增图表统计每秒的总事务数量。
 
![][12]
 
现在可以通过`sample_variables`中的变量来自定义图表。这些定制图表会展示在HTML网页报告的`Custom Graphs section`中。
 
![][13]
 
每秒命中次数也被添加了进来。
 
![][14]
 
在实时报告中，发送和请求的数据被发送至后端(InfluxDB或Graphite)。


[15]: http://jmeter.apache.org/
[16]: http://jmeter.apache.org/changes.html
[17]: http://jmeter.apache.org/download_jmeter.cgi
[0]: https://img1.tuicool.com/iYBzayE.png
[1]: https://img2.tuicool.com/bQvEVjR.png
[2]: https://img1.tuicool.com/nqANFzM.png
[3]: https://img1.tuicool.com/fyY7VbM.png
[4]: https://img0.tuicool.com/muIBBnj.png
[5]: https://img1.tuicool.com/meyeYbJ.png
[6]: https://img1.tuicool.com/vuueyeI.png
[7]: https://img2.tuicool.com/Vjq6F3j.png
[8]: https://img2.tuicool.com/baIZ7nF.png
[9]: https://img1.tuicool.com/qEbemaA.png
[10]: https://img0.tuicool.com/IbMJrqj.png
[11]: https://img2.tuicool.com/2IfqI32.png
[12]: https://img2.tuicool.com/fmUrEvq.png
[13]: https://img1.tuicool.com/yqeyi2I.png
[14]: https://img0.tuicool.com/m6ZNzmN.png