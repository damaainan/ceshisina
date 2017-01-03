<?php
/**
 * 备忘录模式
 * 理解：就是外部存储对象的状态，以提供后退/恢复/复原
 * 使用场景：编辑器后退操作/数据库事物/存档

备忘录模式又叫做快照模式或Token模式，在不破坏封闭的前提下，捕获一个对象的内部状态，并在该对象之外保存这个状态。这样以后就可将该对象恢复到原先保存的状态。

角色：

1.发起人（GameRole）：负责创建一个备忘录，用以记录当前时刻自身的内部状态，并可使用备忘录恢复内部状态。发起人可以根据需要决定备忘录存储自己的哪些内部状态。

2.备忘录（RoleStateSaveBox)：负责存储发起人对象的内部状态，并可以防止发起人以外的其他对象访问备忘录。备忘录有两个接口：管理者只能看到备忘录的窄接口，他只能将备忘录传递给其他对象。发起人却可看到备忘录的宽接口，允许它访问返回到先前状态所需要的所有数据。

3.管理者(GameRoleStateManager):负责存取备忘录，不能对的内容进行访问或者操作。
 */

class Originator {
	// 发起人(Originator)角色
	private $_state;
	public function __construct() {
		$this->_state = '';
	}
	public function createMemento() {
		// 创建备忘录
		return new Memento($this->_state);
	}
	public function restoreMemento(Memento $memento) {
		// 将发起人恢复到备忘录对象记录的状态上
		$this->_state = $memento->getState();
	}
	public function setState($state) {$this->_state = $state;}
	public function getState() {return $this->_state;}
	public function showState() {
		echo $this->_state;
		echo "<br>";
	}

}
class Memento {
	// 备忘录(Memento)角色
	private $_state;
	public function __construct($state) {
		$this->setState($state);
	}
	public function getState() {return $this->_state;}
	public function setState($state) {$this->_state = $state;}
}
class Caretaker {
	// 负责人(Caretaker)角色
	private $_memento;
	public function getMemento() {return $this->_memento;}
	public function setMemento(Memento $memento) {$this->_memento = $memento;}
}

// client
/* 创建目标对象 */
$org = new Originator();
$org->setState('open');
$org->showState();
/* 创建备忘 */
$memento = $org->createMemento();
/* 通过Caretaker保存此备忘 */
$caretaker = new Caretaker();
$caretaker->setMemento($memento);
/* 改变目标对象的状态 */
$org->setState('close');
$org->showState();
$org->restoreMemento($memento);
$org->showState();
/* 改变目标对象的状态 */
$org->setState('close');
$org->showState();
/* 还原操作 */
$org->restoreMemento($caretaker->getMemento());
$org->showState();