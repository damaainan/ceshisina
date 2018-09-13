<?php
/**
 * 学生类
 *
 * 描述信息
 */
class Student
{
    const NORMAL = 1;
    const FORBIDDEN = 2;
    /**
     * 用户ID
     * @var 类型
     */
    public $id;
    /**
     * 获取id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    public function setId($id = 1)
    {
        $this->id = $id;
    }
}
$ref = new ReflectionClass('Student');
$doc = $ref->getDocComment();
echo $ref->getName() . ':' . getComment($ref) , "\n";
echo "属性列表：\n";
printf("%-15s%-10s%-40s\n", 'Name', 'Access', 'Comment');
$attr = $ref->getProperties();
foreach ($attr as $row) {
    printf("%-15s%-10s%-40s\n", $row->getName(), getAccess($row), getComment($row));
}
echo "常量列表：\n";
printf("%-15s%-10s\n", 'Name', 'Value');
$const = $ref->getConstants();
foreach ($const as $key => $val) {
    printf("%-15s%-10s\n", $key, $val);
}
echo "\n\n";
echo "方法列表\n";
printf("%-15s%-10s%-30s%-40s\n", 'Name', 'Access', 'Params', 'Comment');
$methods = $ref->getMethods();
foreach ($methods as $row) {
    printf("%-15s%-10s%-30s%-40s\n", $row->getName(), getAccess($row), getParams($row), getComment($row));
}
// 获取权限
function getAccess($method)
{
    if ($method->isPublic()) {
        return 'Public';
    }
    if ($method->isProtected()) {
        return 'Protected';
    }
    if ($method->isPrivate()) {
        return 'Private';
    }
}
// 获取方法参数信息
function getParams($method)
{
    $str = '';
    $parameters = $method->getParameters();
    foreach ($parameters as $row) {
        $str .= $row->getName() . ',';
        if ($row->isDefaultValueAvailable()) {
            $str .= "Default: {$row->getDefaultValue()}";
        }
    }
    return $str ? $str : '';
}
// 获取注释
function getComment($var)
{
    $comment = $var->getDocComment();
    // 简单的获取了第一行的信息，这里可以自行扩展
    preg_match('/\* (.*) *?/', $comment, $res);
    return isset($res[1]) ? $res[1] : '';
}