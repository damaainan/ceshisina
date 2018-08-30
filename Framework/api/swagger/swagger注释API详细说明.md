## swagger注释API详细说明

来源：[https://blog.csdn.net/youngqj/article/details/82022119](https://blog.csdn.net/youngqj/article/details/82022119)

时间 2018-08-24 17:20:20



## API详细说明

注释汇总

| 作用范围 | API | 使用位置 |
| - | - | - |
| 对象属性 | @ApiModelProperty | 用在出入参数对象的字段上 |
| 协议集描述 | @Api | 用于controller类上 |
| 协议描述 | @ApiOperation | 用在controller的方法上 |
| Response集 | @ApiResponses | 用在controller的方法上 |
| Response | @ApiResponse | 用在 @ApiResponses里边 |
| 非对象参数集 | @ApiImplicitParams | 用在controller的方法上 |
| 非对象参数描述 | @ApiImplicitParam | 用在@ApiImplicitParams的方法里边 |
| 描述返回对象的意义 | @ApiModel | 用在返回对象类上 |
  


@RequestMapping此注解的推荐配置

value 

method 

produces

```
@ApiOperation("信息软删除")
    @ApiResponses({ @ApiResponse(code = CommonStatus.OK, message = "操作成功"),
            @ApiResponse(code = CommonStatus.EXCEPTION, message = "服务器内部异常"),
            @ApiResponse(code = CommonStatus.FORBIDDEN, message = "权限不足") })
    @ApiImplicitParams({ @ApiImplicitParam(paramType = "query", dataType = "Long", name = "id", value = "信息id", required = true) })
    @RequestMapping(value = "/remove.json", method = RequestMethod.GET, produces = MediaType.APPLICATION_JSON_UTF8_VALUE)
    public RestfulProtocol remove(Long id) {
```

```
@ApiModelProperty(value = "标题")
    private String  title;
```


## @ApiImplicitParam

| 属性 | 取值 | 作用 |
| - | - | - |
| paramType |  | 查询参数类型 |
| | path | 以地址的形式提交数据 |
| | query | 直接跟参数完成自动映射赋值 |
| | body | 以流的形式提交 仅支持POST |
| | header | 参数在request headers 里边提交 |
| | form | 以form表单的形式提交 仅支持POST |
| dataType |  | 参数的数据类型 只作为标志说明，并没有实际验证 |
| | Long | |
| | String | |
| name |  | 接收参数名 |
| value |  | 接收参数的意义描述 |
| required |  | 参数是否必填 |
| | true | 必填 |
| | false | 非必填 |
| defaultValue |  | 默认值 |
  


## paramType 示例详解  

path

```
@RequestMapping(value = "/findById1/{id}", method = RequestMethod.GET, produces = MediaType.APPLICATION_JSON_UTF8_VALUE)

 @PathVariable(name = "id") Long id
```

body

```
@ApiImplicitParams({ @ApiImplicitParam(paramType = "body", dataType = "MessageParam", name = "param", value = "信息参数", required = true) })
  @RequestMapping(value = "/findById3", method = RequestMethod.POST, produces = MediaType.APPLICATION_JSON_UTF8_VALUE, consumes = MediaType.APPLICATION_JSON_VALUE)

  @RequestBody MessageParam param

  提交的参数是这个对象的一个json，然后会自动解析到对应的字段上去，也可以通过流的形式接收当前的请求数据，但是这个和上面的接收方式仅能使用一个（用@RequestBody之后流就会关闭了）
```

header

```
@ApiImplicitParams({ @ApiImplicitParam(paramType = "header", dataType = "Long", name = "id", value = "信息id", required = true) }) 

   String idstr = request.getHeader("id");
        if (StringUtils.isNumeric(idstr)) {
            id = Long.parseLong(idstr);
        }
```

Form

```
@ApiImplicitParams({ @ApiImplicitParam(paramType = "form", dataType = "Long", name = "id", value = "信息id", required = true) })
 @RequestMapping(value = "/findById5", method = RequestMethod.POST, produces = MediaType.APPLICATION_JSON_UTF8_VALUE, consumes = MediaType.APPLICATION_FORM_URLENCODED_VALUE)
```

