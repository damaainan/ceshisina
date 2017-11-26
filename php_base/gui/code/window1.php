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


$basicControlsVbox = new Box(Box::Vertical);
$basicControlsVbox->setPadded(true);
$basicControlsHbox = new Box(Box::Horizontal);
$basicControlsHbox->setPadded(true);
$basicControlsVbox->append($basicControlsHbox);
$basicControlsHbox->append(new Button("按钮"));
$basicControlsHbox->append(new Check("选择框"));
$basicControlsVbox->append(new Label("This is a label. Right now, labels can only span one line."));// label  只能在 一行 内
$basicControlsVbox->append(new Separator(Separator::Horizontal));//水平分割线
$entriesGroup = new Group("输入");
$entriesGroup->setMargin(true);
$basicControlsVbox->append($entriesGroup, true);
$entryForm = new Form();
$entryForm->setPadded(true);
$entryForm->append("Entry", new Entry(Entry::Normal), false);
$entryForm->append("密码输入", new Entry(Entry::Password), false);
$entryForm->append("Search Entry", new Entry(Entry::Search), false);
$entryForm->append("多行输入", new MultilineEntry(MultilineEntry::Wrap), true);
$entryForm->append("多行输入不换行", new MultilineEntry(MultilineEntry::NoWrap), true);
$entriesGroup->append($entryForm);
$tab->append("tab1 名", $basicControlsVbox);
$tab->setMargin(0, true);


// 显示窗口 运行
$window->show();
UI\run();