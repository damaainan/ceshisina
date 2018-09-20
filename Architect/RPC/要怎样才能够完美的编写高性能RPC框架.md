## 要怎样才能够完美的编写高性能RPC框架

来源：[https://segmentfault.com/a/1190000014480745](https://segmentfault.com/a/1190000014480745)


## RPC 的主要流程


* 客户端 获取到 UserService 接口的 Refer: userServiceRefer
* 客户端 调用 userServiceRefer.verifyUser(email, pwd)
* 客户端 获取到 请求方法 和 请求数据
* 客户端 把 请求方法 和 请求数据 序列化为 传输数据
* 进行网络传输
* 服务端 获取到 传输数据
* 服务端 反序列化获取到 请求方法 和 请求数据
* 服务端 获取到 UserService 的 Invoker: userServiceInvoker
* 服务端 userServiceInvoker 调用 userServiceImpl.verifyUser(email, pwd) 获取到响应结果
* 服务端 把 响应结果 序列化为 传输数据
* 进行网络传输
* 客户端 接收到 传输数据
* 客户端 反序列化获取到 响应结果
* 客户端 userServiceRefer.verifyUser(email, pwd) 返回 响应结果


整个流程中对性能影响比较大的环节有：序列化[4, 7, 10, 13]，方法调用[2, 3, 8, 9, 14]，网络传输[5, 6, 11, 12]。本文后续内容将着重介绍这3个部分。
## 序列化方案

Java 世界最常用的几款高性能序列化方案有 Kryo Protostuff FST Jackson Fastjson。只需要进行一次 Benchmark，然后从这5种序列化方案中选出性能最高的那个就行了。DSL-JSON 使用起来过于繁琐，不在考虑之列。Colfer Protocol Thrift 因为必须预先定义描述文件，使用起来太麻烦，所以不在考虑之列。至于 Java 自带的序列化方案，早就因为性能问题被大家所抛弃，所以也不考虑。下面的表格列出了在考虑之列的5种序列化方案的性能。

User 序列化+反序列化 性能

framework thrpt (ops/ms) size

![][0]

包含15个 User 的 Page 序列化+反序列化 性能
framework thrpt (ops/ms) size

![][1]

从这个 benchmark 中可以得出明确的结论：二进制协议的 protostuff kryo fst 要比文本协议的 jackson fastjson 有明显优势；文本协议中，jackson(开启了afterburner) 要比 fastjson 有明显的优势。
无法确定的是：3个二进制协议到底哪个更好一些，毕竟 速度 和 size 对于 RPC 都很重要。直观上 kryo 或许是最佳选择，而且 kryo 也广受各大型系统的青睐。不过最终还是决定把这3个类库都留作备选，通过集成传输模块后的 Benchmark 来决定选用哪个。

framework existUser (ops/ms) createUser (ops/ms) getUser (ops/ms) listUser (ops/ms)

![][2] 
最终的结果也还是各有千秋难以抉择，所以 Turbo 保留了 protostuff 和 kryo 的实现，并允许用户自行替换为自己的实现。
## 方法调用

可用的 动态方法调用 方案有：Reflection ClassGeneration MethodHandle。Reflection 是最古老的技术，据说性能不佳。ClassGeneration 动态类生成，从原理上说应该是跟直接调用一样的性能。MethodHandle 是从 Java 7 开始出现的技术，据说能达到跟直接调用一样的性能。实际结果如下：

type thrpt (ops/us)

![][3]

结论非常明显：使用类生成技术的 javassist 跟直接调用几乎一样的性能，就用 javassist 了。
MethodHandle 表现并没有宣传的那么好，怎么回事？原来 MethodHandle 只有在明确知道调用 参数数量 参数类型 的情况下才能调用高性能的 invokeExact(Object... args)，所以它并不适合作为动态调用的方案。

As is usual with virtual methods, source-level calls to invokeExact and invoke compile to an invokevirtual instruction. More unusually, the compiler must record the actual argument types, and may not perform method invocation conversions on the arguments. Instead, it must push them on the stack according to their own unconverted types. The method handle object itself is pushed on the stack before the arguments. The compiler then calls the method handle with a symbolic type descriptor which describes the argument and return types.
refer: [https://docs.oracle.com/javas...][10]
## 网络传输

Netty 已经成为事实上的标准，所有主流的项目现在使用的都是 Netty。Mina Grizzly 已经失去市场，所以也就不用考虑了。还好也不至于这么无聊，Aeron 的闪亮登场让 Netty 多了一个有力的竞争对手。Aeron 是一个可靠高效的 UDP 单播 UDP 多播和 IPC 消息传递工具。性能是消息传递中的关键。Aeron 的设计旨在达到 高吞吐量 低开销 和 低延迟。实际效果到底如何呢？很遗憾，在 RPC Benchmark Round 1 中的表现一般。跟他们开发团队沟通后，最终确认其无法对超过 64k 的消息进行 zero-copy 处理，我觉得这可能是 Aeron 表现不佳的一个原因。Aeron 或许更适合 微小消息 极端低延迟 的场景，而不适用于更加通用的 RPC 场景。所以暂时还没有出现能够跟 Netty 一争高下的通用网络传输框架，现阶段 Netty 依然是 RPC 系统的最佳选择。

existUser 判断某个 email 是否存在
framework thrpt (ops/ms) avgt (ms) p90 (ms) p99 (ms) p999 (ms)

![][4]
## 消息格式

我们先来看一下 Dubbo 的消息格式

![][5]

可以说是非常经典的设计，Client 必须告知 Server 要调用的 方法名称 参数类型 参数。Server 获取到这3个参数后，通过 方法名称 com.alibaba.service.auth.UserService.verifyUser 和 参数类型 (String, String) 获取到 Invoker，然后通过 Invoker 实际调用 userServiceImpl 的 verifyUser(String, String) 方法。其他的众多 RPC 框架也都采取了这一经典设计。
但是，这是正确的做法吗？当然不是，这种做法非常浪费空间，每次请求消息体的大概内存布局应该是下面的样子。 public boolean verifyUser(String email, String pwd) 大致的内存布局：

com.alibaba.service.auth.UserService.verifyUser|java.lang.String,java.lang.String|实际的参数|

啰里啰嗦的，浪费了 80 byte 来定义 方法 和 参数，并没有比 http+json 的方式高效多少。实际的 性能测试 也证明了这一点，undertow+jackson 要比 dubbo motan 的成绩都要好。
那什么才是正确的做法？Turbo 在消息格式上做出了非常大的改变。

![][6]

public boolean verifyUser(String email, String pwd) 大致的内存布局：

int|int|实际的参数|

高效多了，只用了 4 byte 就做到了 方法 和 参数 的定义。大大减小了 传输数据 的 size，同时 int 类型的 serviceId 也降低了 Invoker 的查找开销。
看到这里，有同学可能会问：那岂不是要为每个方法定义一个唯一 id ？ 答案是不需要的，Turbo 解决了这一问题，详情参考 TurboConnectService 。

推荐一个交流学习群：575745314   里面会分享一些资深架构师录制的视频录像：有Spring，MyBatis，Netty源码分析，高并发、高性能、分布式、微服务架构的原理，JVM性能优化这些成为架构师必备的知识体系。还能领取免费的学习资源，目前受益良多：

![][7]
## MethodParam 简介

MethodParam 才是 Turbo 性能炸裂的真正原因。其基本原理是利用 ClassGeneration 对每个 Method 都生成一个MethodParam 类，用于对方法参数的封装。这样做的好处有：


* 减少基本数据类型的 装箱 拆箱 开销
* 序列化时可以省略掉很多类型描述，大大减小 传输消息 的 size
* 使 Invoker 可以高效调用 被代理类 的方法
* 统一 RPC 和 REST 的数据模型，简化 序列化 反序列化 实现
* 大大加快 json 格式数据 反序列化 速度


![][8]
## 序列化的进一步优化

大部分 RPC 框架的 序列化 反序列化 过程都需要一个中间的 bytes


* 序列化过程：User > bytes > ByteBuf
* 反序列化过程：ByteBuf > bytes > User


而 Turbo 砍掉了中间的 bytes，直接操作 ByteBuf，实现了 序列化 反序列化 的 zero-copy，大大减少了 内存分配 内存复制 的开销。具体实现请参考 ProtostuffSerializer 和 Codec。
对于已知类型和已知字段，Turbo 都尽量采用 手工序列化 手工反序列化 的方式来处理，以进一步减少性能开销。
## ObjectPool

常见的几个 ObjectPool 实现性能都很差，反而很容易成为性能瓶颈。Stormpot 性能强悍，不过存在偶尔死锁的问题，而且作者也停止维护了。HikariCP 性能不错，不过其本身是一款数据库连接池，用作 ObjectPool 并不称手。我的建议是尽量避免使用 ObjectPool，转而使用替代技术。更重要的是 Netty 的 Channel 是线程安全的，并不需要使用 ObjectPool 来管理。只需要一个简单的容器来存储 Channel，用的时候使用 负载均衡策略 选出一个 Channel 出来就行了。

framework thrpt (ops/us)

![][9]
## 基础类库优化

除了上述的关键流程优化，Turbo 还做了大量基础类库的优化


* AtomicMuiltInteger 多个 int 的原子性操作
* ConcurrentArrayList 无锁并发 List 实现，比 CopyOnWriteArrayList 的写入开销低，O(1)
  vs O(n)
* ConcurrentIntToObjectArrayMap 以 int 数组为底层实现的无锁并发Map，读多写少情况下接近直接访问字段的性能，读多写多情况下是 ConcurrentHashMap 性能的 5x
* ConcurrentIntegerSequencer 快速序号生成器，并发环境下是 AtomicInteger 性能的10x
* ObjectId 全局唯一 id 生成器，是 Java 自带 UUID 性能的 200x
* HexUtils 查表 + 批量操作，是 Netty 和 Guava 实现的 2x~5x
* URLEncodeUtils 基于 HexUtils 实现，是 Java 和 Commons 实现的 2x，Guava 实现的 1.1x
  (Guava 只有 urlEncode 实现，无 urlDecode 实现)
* ByteBufUtils 实现了高效的 ZigZag 写入操作，最高可达通常实现的 4x


上面的内容仅介绍了作者认为重要的东西，更多内容请直接查看 Turbo 源码
[https://gitee.com/hank-whu/tu...][11]
[https://github.com/hank-whu/t...][12]
## 不足之处


* 有很多优化是毫无价值的，Donald Knuth 大神说得很对
* 强制必须使用 CompletableFuture 作为返回值导致了一些性能开销
* 滥用 ClassGeneration，而且并没有考虑类的卸载，这方面需要改进
* 实现了 UnsafeStringUtils，这是个危险的黑魔法实现，需要重新思考下
* 对性能的追求有点走火入魔，导致了很多地方的设计过于复杂


[10]: https://docs.oracle.com/javase/7/docs/api/java/lang/invoke/MethodHandle.html
[11]: https://gitee.com/hank-whu/turbo-rpc
[12]: https://github.com/hank-whu/turbo-rpc
[0]: ./img/bV8VaT.png
[1]: ./img/bV8VaY.png
[2]: ./img/bV8Vbj.png
[3]: ./img/bV8Vbs.png
[4]: ./img/bV8Vcc.png
[5]: ./img/bV8Vcq.png
[6]: ./img/bV8VcR.png
[7]: ./img/bV3aiy.png
[8]: ./img/bV8VdP.png
[9]: ./img/bV8Vet.png