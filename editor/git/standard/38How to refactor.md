# 什么是好代码

ARTHUR J.RIEL,《OOD启思录》

> “你不必严格遵守这些原则，违背它也不会被处以宗教刑罚。

> 但你应当把这些原则看做警铃，若违背了其中的一条，那么警铃就会响起。”

Bjarne Stroustrup，C++之父：

> * 逻辑应该是清晰的，bug难以隐藏；
> * 依赖最少，易于维护；
> * 错误处理完全根据一个明确的策略；
> * 性能接近最佳化，避免代码混乱和无原则的优化；
> * 整洁的代码只做一件事。

Grady Booch，《面向对象分析与设计》作者：

> * 整洁的代码是简单、直接的；
> * 整洁的代码，读起来像是一篇写得很好的散文；
> * 整洁的代码永远不会掩盖设计者的意图，而是具有少量的抽象和清晰的控制行。

Michael Feathers，《修改代码的艺术》作者：

> * 整洁的代码看起来总是像很在乎代码质量的人写的；
> * 没有明显的需要改善的地方；
> * 代码的作者似乎考虑到了所有的事情。

### 推荐阅读

> * 《重构-改善既有代码的设计》
> * 《设计模式-可复用面向对象软件的基础》
> * 《Software Architecture Patterns-Understanding Common Architecture Patterns and When to Use Them》

### Duplicate Code

#### 同一个类，两个方法含有相同表达式。

* 解决方法：
 * 你可以Extract Method提炼重复代码，
 * 然后让这两个方法都调用这个Extract Method。

#### 两个类，有相似的方法。

* 解决方法：
 * 把两个类的方法提出来，共同构造一个父类。
 * 把其中一个类的方法删除，调用另一个类的方法。

### 晦涩的if条件 [Consolidate Conditional Expression](http://refactoring.com/catalog/consolidateConditionalExpression.html)

#### `bad`

```
double disabilityAmount() {
  if (_seniority < 2) return 0;
  if (_monthsDisabled > 12) return 0;
  if (_isPartTime) return 0;
  // compute the disability amount
```

#### `good`

```
double disabilityAmount() {
  if (isNotEligableForDisability()) return 0;
  // compute the disability amount
```


#### `bad`

```
if ( a && b && c || ( e && f ) )
```

#### `good`

```
if ( a
  && b
  && c
  || ( e
     && f ) )
```

### 流程控制 卫语句（[Guard Clauses](http://refactoring.com/catalog/replaceNestedConditionalWithGuardClauses.html)）

#### `bad`

```
if () {
}
else {
    if () {
    }
    else {
        if () {
        }
        else {
            if () {
            }
            else {

            }
        }
    }
}
```



####  `good`

```
if () {
return
}

if () {
return
}

if () {
return
}

if () {
return
}

return

```


#### `bad`
```
double getPayAmount() {
  double result;
  if (_isDead) result = deadAmount();
  else {
    if (_isSeparated) result = separatedAmount();
    else {
      if (_isRetired) result = retiredAmount();
      else result = normalPayAmount();
    };
  }
  return result;
};
```

#### `good`
```
double getPayAmount() {
  if (_isDead) return deadAmount();
  if (_isSeparated) return separatedAmount();
  if (_isRetired) return retiredAmount();
  return normalPayAmount();
};
```

### Consolidate Duplicate Conditional Fragments

#### `bad`

```
if (a) {
  total = price * 1.1
  send()
}else {
  total = price * 0.5
  send()
}
```

#### `good`
```
if (a) {
  total = price * 1.1
}else {
  total = price * 0.5
}
send()
```

### [Decompose Conditional](http://refactoring.com/catalog/decomposeConditional.html)

#### `bad`

```
if (date.before (SUMMER_START) || date.after(SUMMER_END))
  charge = quantity * _winterRate + _winterServiceCharge;
else charge = quantity * _summerRate;
```

#### `good`
```
if (notSummer(date))
  charge = winterCharge(quantity);
else charge = summerCharge (quantity);
```

### [Pull Up Constructor Body](http://refactoring.com/catalog/pullUpConstructorBody.html)
#### `bad`

```
class Manager extends Employee...
  public Manager (String name, String id, int grade) {
    _name = name;
    _id = id;
    _grade = grade;
  }
```

#### `good`
```
public Manager (String name, String id, int grade) {
    super (name, id);
    _grade = grade;
  }
```

### [Pull Up Field](http://refactoring.com/catalog/pullUpField.html)
### [Pull Up Method](http://refactoring.com/catalog/pullUpMethod.html)
### [Push Down Field](http://refactoring.com/catalog/pushDownField.html)
### [Push Down Method](http://refactoring.com/catalog/pushDownMethod.html)

### [Recompose Conditional](http://refactoring.com/catalog/recomposeConditional.html)
#### `bad`

```
parameters = params ? params : []
```

#### `good`
```
parameters = params || []
```

### [Remove Assignments to Parameters](http://refactoring.com/catalog/removeAssignmentsToParameters.html)
#### `bad`

```
int discount (int inputVal, int quantity, int yearToDate) {
  if (inputVal > 50) inputVal -= 2;
```

#### `good`
```
int discount (int inputVal, int quantity, int yearToDate) {
  int result = inputVal;
  if (inputVal > 50) result -= 2;
```


### [Replace Array with Object](http://refactoring.com/catalog/replaceArrayWithObject.html)
#### `bad`

```
String[] row = new String[3];
row [0] = "Liverpool";
row [1] = "15";
```

#### `good`
```
Performance row = new Performance();
row.setName("Liverpool");
row.setWins("15");
```

### [Replace Constructor with Factory Method](http://refactoring.com/catalog/replaceConstructorWithFactoryMethod.html)
#### `bad`

```
Employee (int type) {
  _type = type;
}
```

#### `good`
```
static Employee create(int type) {
  return new Employee(type);
}

// additions
static Employee create(Class c){
  try{
    return (Employee)c.newInstance();
  }catch(Exception e){
    throw new IllegalException("Unable to instantiate" +c);
  }
}

// This would be called from this code
Employee.create(Engineer.class);
```

### [Replace Magic Number with Symbolic Constant](http://refactoring.com/catalog/replaceMagicNumberWithSymbolicConstant.html)
#### `bad`

```
double potentialEnergy(double mass, double height) {
  return mass * height * 9.81;
}
```

#### `good`
```
double potentialEnergy(double mass, double height) {
  return mass * GRAVITATIONAL_CONSTANT * height;
}
static final double GRAVITATIONAL_CONSTANT = 9.81;
```

### [Replace Parameter with Explicit Methods](http://refactoring.com/catalog/replaceParameterWithExplicitMethods.html)
#### `bad`

```
void setValue (String name, int value) {
  if (name.equals("height")) {
    _height = value;
    return;
  }
  if (name.equals("width")) {
    _width = value;
    return;
  }
  Assert.shouldNeverReachHere();
}
```

#### `good`
```
void setHeight(int arg) {
  _height = arg;
}
void setWidth (int arg) {
  _width = arg;
}
```

### [Split Temporary Variable](http://refactoring.com/catalog/splitTemporaryVariable.html)
#### `bad`

```
  double temp = 2 * (_height + _width);
  System.out.println (temp);
  temp = _height * _width;
  System.out.println (temp);
```

#### `good`
```
  final double perimeter = 2 * (_height + _width);
  System.out.println (perimeter);
  final double area = _height * _width;
  System.out.println (area);
```

### [Substitute Algorithm](http://refactoring.com/catalog/substituteAlgorithm.html)
#### `bad`

```
String foundPerson(String[] people){
  for (int i = 0; i < people.length; i++) {
    if (people[i].equals ("Don")){
      return "Don";
    }
    if (people[i].equals ("John")){
      return "John";
    }
    if (people[i].equals ("Kent")){
      return "Kent";
    }
  }
  return "";
}
```

#### `good`
```
String foundPerson(String[] people){
  List candidates = Arrays.asList(new String[] {"Don", "John", "Kent"});
  for (int i=0; i<people.length; i++)
    if (candidates.contains(people[i]))
      return people[i];
  return "";
}
```


### More...
* [refactoring.com](http://refactoring.com/)
* [XP refactoring](https://www.industriallogic.com/xp/refactoring/catalog.html)