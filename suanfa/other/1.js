/**
 *
 * 三门问题和 JavaScript 仿真实验
 *
 * 
 * 1990 年 9 月美国《广场杂志》的「请教玛丽琳」专栏，曾刊登如下逻辑题：

假设你在进行一个游戏节目。现给三扇门供你选择：一扇门后面是一辆轿车，另两扇门后面分别都是一头山羊。你的目的当然是要想得到比较值钱的轿车，但你却并不能看到门后面的真实情况。主持人先让你作第一次选择。在你选择了一扇门后，知道其余两扇门后面是什么的主持人，打开了另一扇门给你看，而且，当然，那里有一头山羊。现在主持人告诉你，你还有一次选择的机会。那么，请你考虑一下，你是坚持第一次的选择不变，还是改变第一次的选择，更有可能得到轿车？

这个问题即著名的 [蒙提霍尔问题][0] ，也叫三门问题。 

梳理一下它的流程如下：

1. 玩家从三个门中选择一个门，记为 **门a** 。
1. 主持人选择另一个门，记为 **门b** ， **门b** 里面是一头山羊，并且问玩家是否变更选择。
1. 玩家如果变更选择，即选了 **门a** 、 **门b** 以外的第三个门，记为 **门c** ；如果不变更选择，玩家仍然选 **门a** 。

问题是：选 **门a** 和 **门c** ，哪个更大的概率得到轿车？ 

## 答案及解释 

很早看到过这题，一直都觉得没有差别，选哪个门的概率都是 1/3 ，最近同事间又在讨论起这个话题，让我改变了主意。 

正确的答案是：变更选择后，命中轿车的概率是 2/3 ，命中羊的概率是 1/3 。 

原因是，主持人打开 **门b** 之后，剩下的两个门（ **门a** 和 **门c** ），正好是一个后面是羊，另一个后面是轿车。也就是说，变更选择会导致： 

#### 玩家本来选到羊会变成选到轿车，而本来选到轿车变成选到羊。

三扇门其中有两扇门是羊，玩家做第一次选择时：

* 命中羊的概率是 2/3
* 命中轿车的概率是 1/3

变更选择之后：

* 命中轿车的概率是 2/3 （第一次命中羊）
* 命中羊的概率是 1/3 （第一次命中轿车）

## JavaScript 实验 

为了验证，我用 JS 写了个仿真程序，计算不变选择与变更选择的分别概率。

戳这里 [直接测试][1] ，具体代码如下：

[0]: https://en.wikipedia.org/wiki/Monty_Hall_problem
[1]: https://jsbin.com/vakume/edit?js,console
 */

/**
 * JS test for the monty hall problem
 * @author dron (http://ucren.com)
 */

const random1 = length => Math.random() * length | 0;

const guess = function( reselect ){
  const carInDoor = random1( 3 );
  let playerSelects = random1( 3 );

  if( reselect ){
    let presenterSelects;

    if( carInDoor === playerSelects ){
      return false;
    }else{
      presenterSelects = carInDoor ^ playerSelects ^ 3;
      playerSelects = playerSelects ^ presenterSelects ^ 3;
    }
  }

  return carInDoor === playerSelects;
}

const hitRate = function( times, reselect ){
  let count = 0;

  for( let i = 0; i < times; i ++ ){
    if( guess( reselect ) )
      count ++;
  }

  return count / times;
}

const times = 10000;

console.log( '玩家不变更选择，命中轿车的概率是：', hitRate( times, false ) ); 
console.log( '玩家变更选择，命中轿车的概率是：', hitRate( times, true ) );