<?php
    
namespace ChainOfResponsibilities;

/**
 * 经过责任链的Request类
 *
 * 关于请求: 有时候，不需要一个请求对象，只需一个整型数据或者一个数组即可。
 * 但是作为一个完整示例，这里我们生成了一个请求类。
 * 在实际项目中，也推荐使用请求类，即是是一个标准类\stdClass，
 * 因为这样的话代码更具扩展性，因为责任链的处理器并不了解外部世界，
 * 如果某天你想要添加其它复杂处理时不使用请求类会很麻烦
 */
class Request
{
    // getter and setter but I don't want to generate too much noise in handlers
}