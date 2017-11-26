<?php 

use UI\Menu;
use UI\MenuItem;

use UI\Size;
use UI\Window;
use UI\Controls\Tab;
use UI\Controls\Box;
use UI\Controls\Group;
use UI\Controls\Form;
use UI\Controls\Button;
use UI\Controls\Entry;
use UI\Controls\MultilineEntry;
use UI\Controls\Spin;
use UI\Controls\Slider;
use UI\Controls\Progress;
use UI\Controls\Combo;
use UI\Controls\EditableCombo;
use UI\Controls\Radio;
use UI\Controls\Grid;
use UI\Controls\Picker;
use UI\Controls\Check;
use UI\Controls\Label;
use UI\Controls\Separator;
use UI\Controls\ColorButton;

$window = new Window("窗口测试", new Size(640, 480), true);
$window->setMargin(true);


$tab = new Tab();
$window->add($tab);


$numbersHbox = new Box(Box::Horizontal);
$numbersHbox->setPadded(true);

//--------------- numbers -------------------//

$numbersGroup = new Group("数字");// numbers 组
$numbersGroup->setMargin(true);
$numbersHbox->append($numbersGroup, true);// 将 numbers 组 追加到 盒子上

$numbersVbox = new Box(Box::Vertical);
$numbersVbox->setPadded(true);
$numbersGroup->append($numbersVbox);// 数字框

//+++++++++++++++++
// spin  slider
$progress = new Progress();
$spin = new class(0, 100) extends Spin {
	public function setSlider(Slider $slider) {
		$this->slider = $slider;
	}
	public function setProgress(Progress $progress) {
		$this->progress = $progress;	
	}
	protected function onChange() {
		$this->slider->setValue($this->getValue());
		$this->progress->setValue($this->getValue());
	}
	private $slider;
	private $progress;
};
$spin->setProgress($progress);

$slider = new class(0, 100) extends Slider {//滑动器
	public function setSpin(Spin $spin) {
		$this->spin = $spin;
	}
	public function setProgress(Progress $progress) {
		$this->progress = $progress;
	}
	protected function onChange() {
		$this->spin->setValue($this->getValue());
		$this->progress->setValue($this->getValue());
	}
	private $spin;
	private $progress;
};
$slider->setProgress($progress);

$slider->setSpin($spin);  // 互相取值
$spin->setSlider($slider);
//+++++++++++++++++

$numbersVbox->append($spin);
$numbersVbox->append($slider);


$numbersVbox->append($progress);//  加入进度条  值取自 滑动器

//----------------------------------------------------------------------------//

$ip = new Progress();//一个一直运行的进度条
$ip->setValue(1);
$numbersVbox->append($ip);
//----------------------------------//

// $listsGroup = new Group("Lists");
// $listsGroup->setMargin(true);
// $numbersHbox->append($listsGroup);
// $otherBox = new Box(Box::Vertical);
// $otherBox->setPadded(true);
// $listsGroup->append($otherBox);

// $combo = new Combo();
// $combo->append("Item 1");
// $combo->append("Item 2");
// $combo->append("Item 3");
// $otherBox->append($combo);

// $ecombo = new EditableCombo();
// $ecombo->append("Editable Item 1");
// $ecombo->append("Editable Item 2");
// $ecombo->append("Editable Item 3");
// $otherBox->append($ecombo);

// $radio = new Radio();
// $radio->append("Radio Button 1");
// $radio->append("Radio Button 2");
// $radio->append("Radio Button 3");
// $otherBox->append($radio);

$tab->append("Numbers and Lists", $numbersHbox);
$tab->setMargin(0, true);// 参数意义  第 0 个，真
 



// 显示窗口 运行
$window->show();
UI\run();