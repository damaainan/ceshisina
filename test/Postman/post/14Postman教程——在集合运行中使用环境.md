## Postman教程——在集合运行中使用环境

来源：[https://www.jellythink.com/archives/185](https://www.jellythink.com/archives/185)

时间 2018-03-20 00:10:51

 
## 前言
 
环境允许我们创建可重用的请求。关于环境和变量的文章，可以阅读这篇《Postman教程——变量》。
 
同样的，环境也可以在“Collection Runner”中使用，下面通过一个例子来说明在“Collection Runner”中如何使用环境。
 
为了进行说明，请下载collection.json文件，并在Postman中导入该文件。
 
导入完成后，请求信息如下图所示：
 
![][0]
 
## 使用案例
 
我们可以看到，导入的请求中，对应的请求URL和请求头中使用了`{{path}}`和`{{foo}}`环境变量。而且该请求对应的测试脚本如下：
 
```LANG
let jsonData = JSON.parse(responseBody);
tests['Correct value is returned'] = jsonData.form.foo === 'bar'

postman.setEnvironmentVariable('foo', 'bar2')
```
 
测试脚本期望响应主体中foo的值等于bar。最后，我们通过`setEnvironmentVariable`将foo的值设置成了bar2。
 
![][1]
 
要想在Collection Runner中正确运行该集合，我们需要为其提供相应的环境。下载示例环境：environment.json，并进行导入。 在Collection Runner中，如果我们从左侧的环境下拉列表中选择我们的测试环境并运行集合，则会看到测试通过。
 
![][2]
 
![][3]
 
此时我们再切换回Postman应用程序主窗口并检查变量foo的值，会看到它现在已经是bar2了。
 
这是因为在默认情况下，在Collection Runner环境（或全局变量）中的任何变量变化都将反映在Postman主应用程序窗口中，因为在选项中选中了`Persist Variables`。由于我们更改了变量foo的值，如果我们再次运行该集合，会发现它现在会失败。
 
默认情况下，`Persist Variables`在我们第一次打开Collection Runner时被选中。如果不想在运行过程中更新变量，那就取消选中`Persist Variables`复选框。在这种情况下，在Collection Runner中修改的任何变量都不会影响Postman主应用中对应的环境变量值，在Collection Runner中做的任何修改都会在运行完成后恢复它原始的值。当我们在请求中重复使用相同的变量并希望多次运行相同的集合时，这非常有用。 这也将确保环境（和全局）状态不受集合运行的影响。
 
## 总结
 
这篇文章详细的总结了在Collection Runner使用变量。希望我的这篇文章对大家有帮助。
 
节后上班，还有些不适应！！！
 
[果冻想][4] -一个原创技术文章分享网站。
 
2018年2月26日 于呼和浩特。
 


[4]: https://www.jellythink.coim
[0]: ./img/rmURvaN.png 
[1]: ./img/7v6vUvf.png 
[2]: ./img/IR7vMz3.png 
[3]: ./img/QVjm2eq.png 