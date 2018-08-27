# SOAP与WSDL详解

 时间 2017-12-18 19:01:43  

原文[https://www.congcong.us/post/soap_wsdl.html][1]


## SOAP简单对象访问协议 

SOAP(Simple Object Access Protocol)简单对象访问协议是交换数据的一种规范，在web service中，交换带结构信息。可基于HTTP等协议，使用XML格式传输，抽象于语言实现、平台和硬件。即多语言包括PHP、Java、.Net均可支持。优点是跨语言，非常适合异步通信和针对松耦合的C/S，缺点是必须做很多运行时检查。

### 相关概念 

* SOAP封装(envelop),定义了一个框架，描述消息中的内容是什么，是谁发送的，谁应当接受并处理。
* SOAP编码规则(encoding rules),定义了一种序列化的机制，表示应用程序需要使用的数据类型的实例。
* SOAP RPC表示(RPC representation)，定义了一个协定，用于表示远程过程调用和应答。
* SOAP绑定(binding)，定义了SOAP使用哪种协议交换信息。使用HTTP/TCP/UDP协议都可以。

### 语法规则 

SOAP消息必须用XML来编码

SOAP消息必须使用SOAP Envelope命名空间

SOAP消息必须使用SOAP Encoding命名空间

SOAP消息不能包含DTD引用

SOAP消息不能包含XML处理指令

### 基本结构 

一条SOAP消息就是一个普通的XML文档，包含以下元素：

必须的Envelope元素

可选的Header元素，包含头部信息

必须的Body元素，包含所有的调用和相应信息

可选的Fault元素，提供有关在处理此消息所发生的错误的信息

```xml
    <?xml version="1.0"?>
    <soap:Envelope
        xmlns:soap="http://www.w3.org/2001/12/soap-envelope"
        soap:encodingStyle="http://www.w3.org/2001/12/soap-encoding">
     
        <soap:Header>
          ...
          ...
        </soap:Header>
     
        <soap:Body>
              ...
              ...
              <soap:Fault>
                ...
                ...
              </soap:Fault>
        </soap:Body>
    </soap:Envelope>
```
    

1、SOAP Envelope元素,SOAP消息的根元素，可把XML文档定义为SOAP消息

2、xmlns：SOAP命名空间,固定不变。

3、SOAP的encodingstyle属性用于定义在文档中使用的数据类型。此属性可出现在任何SOAP元素中，并会被应用到元素的内容及元素的所有子元素上。SOAP消息没有默认的编码方式。

4、可选的SOAP Header元素可包含有关SOAP消息的应用程序专用信息。如果Header元素被提供，则它必须是Envelope元素的第一个子元素

```xml
    <soap:Header>
       <m:Trans xmlns:m="http://www.w3schools.com/transaction/"
        soap:mustUnderstand="1">234 #表示处理此头部的接受者必须认可此元素，假如此元素接受者无法认可此元素，则在处理此头部时必须失效
       </m:Trans>
    </soap:Heaser>
```

SOAP在默认命名空间中定义了3个属性：actor，mustUnderstand，encodingStyle。这些被定义在SOAP头部的属性可定义容器如何对SOAP消息进行处理。soap:mustUnderstand=”0/1″

* mustUnderstand属性——用于标识标题项对其进行处理的接受者来说是强制的还是可选的。（0可选1强制）

* SOAP的actor属性可用于将Header元素寻址到一个特定的端点 soap:actor=”URI”

* SOAP的encodingStyle属性用于定义在文档中使用的数据类型。此属性可出现在任何SOAP元素中，并会被应用到元素的内容及元素的所有子元素上。SOAP消息没有默认的编码方式。soap:encodingstyle=”URI”

5、必须的SOAP Body元素可包含打算传送到消息最终端点的实际SOAP消息。SOAP Body元素的直接子元素可以使合格的命名空间

6、SOAP Fault元素——用于存留SOAP消息的错误和状态消息，可选的SOAP Fault元素用于指示错误消息。如果已提供了Fault元素，则它必须是Body元素的子元素，在一条SOAP消息中，Fault元素只能出现一次。

SOAP Fault子元素：

* 供识别障碍的代码

* 可供人阅读的有关障碍的说明

* 有关是谁引发故障的信息

* 存留涉及Body元素的应用程序的专用错误信息

以下定义的faultcode值必须用于描述错误时的faultcode元素中

* versionMismatch SOAP Envelope的无效命名空间被发现
* mustUnderstand Header元素的一个直接子元素(mustUnderstand=”1′)无法被理解
* Client 消息被不正确的构成，或包含不正确的信息
* Server 服务器有问题，因此无法处理进行下去

## WSDL网络服务描述语言 

WSDL： WSDL 指网络服务描述语言 (Web Services Description Language)，WSDL 是一种使用 XML 编写的文档。这种文档可描述某个 Web service。

## 基本结构 

```xml
    <definitions>
        <types>
           definition of types........
        </types>
        <message>
           definition of a message....
        </message>
        <portType>
           definition of a port.......
        </portType>
        <binding>
           definition of a binding....
        </binding>
    </definitions>
```
    

一个WSDL文档通常包含7个重要的元素，即types、import、message、portType、operation、binding、service元素。这些元素嵌套在definitions元素中，definitions是WSDL文档的根元素。

* Types – 数据类型定义的容器，它使用某种类型系统(一般地使用XML Schema中的类型系统)。
* Message – 通信消息的数据结构的抽象类型化定义。使用Types所定义的类型来定义整个消息的数据结构。
* Operation – 对服务中所支持的操作的抽象描述，一般单个Operation描述了一个访问入口的请求/响应消息对。
* PortType – 对于某个访问入口点类型所支持的操作的抽象集合，这些操作可以由一个或多个服务访问点来支持。
* Binding – 特定端口类型的具体协议和数据格式规范的绑定。
* Port – 定义为协议/数据格式绑定与具体Web访问地址组合的单个服务访问点。
* Service- 相关服务访问点的集合。

### 特定实例剖析 

http://cardpay.shengpay.com/api-acquire-channel/services/receiveOrderService?wsdl

```xml
    <?xml version="1.0" encoding="utf-8"?>
     
    <wsdl:definitions xmlns:wsdl="http://schemas.xmlsoap.org/wsdl/" xmlns:ns1="http://schemas.xmlsoap.org/soap/http" xmlns:soap="http://schemas.xmlsoap.org/wsdl/soap/" xmlns:tns="http://www.sdo.com/mas/api/receive/" xmlns:xsd="http://www.w3.org/2001/XMLSchema" name="ReceiveOrderAPIExplorterService" targetNamespace="http://www.sdo.com/mas/api/receive/">  
      <wsdl:types> 
        <xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" attributeFormDefault="unqualified" elementFormDefault="unqualified" targetNamespace="http://www.sdo.com/mas/api/receive/">  
          <xs:element name="receB2COrderRequest" type="tns:ReceB2COrderRequest"></xs:element>  
          <xs:element name="receB2COrderResponse" type="tns:ReceB2COrderResponse"></xs:element>  
          <xs:complexType name="ReceB2COrderRequest"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="buyerContact" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="buyerId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="buyerIp" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="buyerName" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="cardPayInfo" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="cardValue" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="currency" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="depositId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="depositIdType" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="expireTime" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="extension" type="tns:extension"></xs:element>  
              <xs:element minOccurs="0" name="header" type="tns:header"></xs:element>  
              <xs:element minOccurs="0" name="instCode" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="language" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="notifyUrl" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="orderAmount" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="orderNo" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="orderTime" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="pageUrl" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="payChannel" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="payType" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="payeeId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="payerAuthTicket" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="payerId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="payerMobileNo" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="productDesc" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="productId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="productName" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="productNum" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="productUrl" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="sellerId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="signature" type="tns:signature"></xs:element>  
              <xs:element minOccurs="0" name="terminalType" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="unitPrice" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="extension"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="ext1" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="ext2" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="ext3" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="header"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="charset" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="sendTime" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="sender" type="tns:sender"></xs:element>  
              <xs:element minOccurs="0" name="service" type="tns:service"></xs:element>  
              <xs:element minOccurs="0" name="traceNo" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="sender"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="senderId" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="service"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="serviceCode" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="version" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="signature"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="signMsg" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="signType" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="ReceB2COrderResponse"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="customerLogoUrl" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="customerName" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="customerNo" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="extension" type="tns:extension"></xs:element>  
              <xs:element minOccurs="0" name="header" type="tns:header"></xs:element>  
              <xs:element minOccurs="0" name="orderAmount" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="orderNo" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="orderType" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="returnInfo" type="tns:returnInfo"></xs:element>  
              <xs:element minOccurs="0" name="sessionId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="signature" type="tns:signature"></xs:element>  
              <xs:element minOccurs="0" name="tokenId" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="transNo" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="transStatus" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="transTime" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:complexType name="returnInfo"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="errorCode" type="xs:string"></xs:element>  
              <xs:element minOccurs="0" name="errorMsg" type="xs:string"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:element name="MasAPIException" type="tns:MasAPIException"></xs:element>  
          <xs:complexType name="MasAPIException"> 
            <xs:sequence></xs:sequence> 
          </xs:complexType>  
          <xs:element name="receiveB2COrder" type="tns:receiveB2COrder"></xs:element>  
          <xs:complexType name="receiveB2COrder"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="arg0" type="tns:ReceB2COrderRequest"></xs:element> 
            </xs:sequence> 
          </xs:complexType>  
          <xs:element name="receiveB2COrderResponse" type="tns:receiveB2COrderResponse"></xs:element>  
          <xs:complexType name="receiveB2COrderResponse"> 
            <xs:sequence> 
              <xs:element minOccurs="0" name="return" type="tns:ReceB2COrderResponse"></xs:element> 
            </xs:sequence> 
          </xs:complexType> 
        </xs:schema> 
      </wsdl:types>  
      <wsdl:message name="receiveB2COrder"> 
        <wsdl:part element="tns:receiveB2COrder" name="parameters"></wsdl:part> 
      </wsdl:message>  
      <wsdl:message name="receiveB2COrderResponse"> 
        <wsdl:part element="tns:receiveB2COrderResponse" name="parameters"></wsdl:part> 
      </wsdl:message>  
      <wsdl:message name="MasAPIException"> 
        <wsdl:part element="tns:MasAPIException" name="MasAPIException"></wsdl:part> 
      </wsdl:message>  
      <wsdl:portType name="ReceiveOrderAPI"> 
        <wsdl:operation name="receiveB2COrder"> 
          <wsdl:input message="tns:receiveB2COrder" name="receiveB2COrder"></wsdl:input>  
          <wsdl:output message="tns:receiveB2COrderResponse" name="receiveB2COrderResponse"></wsdl:output>  
          <wsdl:fault message="tns:MasAPIException" name="MasAPIException"></wsdl:fault> 
        </wsdl:operation> 
      </wsdl:portType>  
      <wsdl:binding name="ReceiveOrderAPIExplorterServiceSoapBinding" type="tns:ReceiveOrderAPI"> 
        <soap:binding style="document" transport="http://schemas.xmlsoap.org/soap/http"></soap:binding>  
        <wsdl:operation name="receiveB2COrder"> 
          <soap:operation soapAction="" style="document"></soap:operation>  
          <wsdl:input name="receiveB2COrder"> 
            <soap:body use="literal"></soap:body> 
          </wsdl:input>  
          <wsdl:output name="receiveB2COrderResponse"> 
            <soap:body use="literal"></soap:body> 
          </wsdl:output>  
          <wsdl:fault name="MasAPIException"> 
            <soap:fault name="MasAPIException" use="literal"></soap:fault> 
          </wsdl:fault> 
        </wsdl:operation> 
      </wsdl:binding>  
      <wsdl:service name="ReceiveOrderAPIExplorterService"> 
        <wsdl:port binding="tns:ReceiveOrderAPIExplorterServiceSoapBinding" name="ReceiveOrderAPIExplorterPort"> 
          <soap:address location="http://cardpay.shengpay.com/api-acquire-channel/services/receiveOrderService"></soap:address> 
        </wsdl:port> 
      </wsdl:service> 
    </wsdl:definitions>
```

[1]: https://www.congcong.us/post/soap_wsdl.html
