<?php

abstract class Duty
{

    protected $higher = null;

    abstract public function operation($lev);

}

//一级官

class Lev1 extends duty
{

    protected $higher = 'lev2';

    public function operation($lev)
    {

        if ($lev <= 1) {

            echo '村委解决';

        } else {

            $higher = new $this->higher;

            $higher->operation($lev);

        }

    }

}

//二级官

class Lev2 extends duty
{

    protected $higher = 'lev3';

    public function operation($lev)
    {

        if ($lev <= 2) {

            echo '镇委解决';

        } else {

            $higher = new $this->higher;

            $higher->operation($lev);

        }

    }

}

class Lev3
{

    protected $higher = 'lev4';

    public function operation($lev)
    {

        if ($lev <= 3) {

            echo '市委解决';

        } else {

            $higher = new $this->higher;

            $higher->operation($lev);

        }

    }

}

class Lev4
{

    protected $higher = null; //没有比中央更大的了，所以可以不用判断直接解决

    public function operation($lev)
    {

        echo '中央解决';

    }

}

$question = new Lev1(); //从最低级的开始尝试

$question->operation(3); //事件等级为3，所以到市委才能解决

//output :"市委解决";
