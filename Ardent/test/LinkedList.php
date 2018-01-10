<?php

namespace Ardent\Collection;

require_once __DIR__ . '/../vendor/autoload.php';



        // return new LinkedList();


        $list = new LinkedList();
        var_dump($list);


        $list = new LinkedList();
        $list->push(0);
        var_dump($list);


        $list = new LinkedList();
        $list->unshift(0);
        var_dump($list);


        $list = new LinkedList();
        var_dump($list->isEmpty());


        $list = new LinkedList();
        $list->push(0);
        var_dump($list->isEmpty());


    /**
     * @dataProvider provide_rangeOneToN
     */
        $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = end($data);
        $actual = $list->pop();
        var_dump($actual);
        var_dump($expect);


    /**
     * @depends      test_pop_sizeN_returnsNValue
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        foreach ($data as $value) {
            $list[] = $value;
        }

        $expect = end($data);
        $actual = $list->pop();
        var_dump($actual);
        var_dump($expect);


    /**
     * @depends      test_pop_sizeN_returnsNValue
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        foreach ($data as $value) {
            $list[] = $value;
        }

        $expect = count($data);
        var_dump($list);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = count($data) - 1;
        $list->pop();
        var_dump($list);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = reset($data);
        $actual = $list->shift();
        var_dump($actual);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = count($data) - 1;
        $list->shift();
        var_dump($list);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = reset($data);
        $actual = $list->first();
        var_dump($actual);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = count($data);
        $list->first();
        var_dump($list);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = end($data);
        $actual = $list->last();
        var_dump($actual);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $expect = count($data);
        $list->last();
        var_dump($list);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeZeroToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        $n = count($data);
        array_walk($data, [$list, 'push']);

        $expect = $n > 0;
        for ($i = 0; $i < $n; $i++) {
            var_dump($expect);
            var_dump($list->offsetExists($i));
        }


    /**
     * @dataProvider provide_rangeZeroToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        $n = count($data);
        array_walk($data, [$list, 'push']);
        var_dump($list->offsetExists($n));


    /**
     * @dataProvider provide_rangeZeroToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        $n = count($data) * 1;
        var_dump($list->offsetExists($n));


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);
        $n = count($data);
        for ($i = 0; $i < $n; ++$i) {
            $expect = $i;
            $actual = $list->offsetGet($i);
            var_dump($actual);
            var_dump($expect);
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);
        $n = count($data);
        for ($i = 0; $i < $n; $i++) {
            $list->seek($i);
            $expect = $i;
            $actual = $list->current();
            var_dump($actual);
            var_dump($expect);
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);
        $n = count($data);
        for ($i = 0; $i < $n; $i++) {
            $list->seek($i);
            $expect = $i;
            $actual = $list->key();
            var_dump($actual);
            var_dump($expect);
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        foreach ($data as $value) {
            $list->push($value * 2);
        }
        $n = count($data);
        for ($i = 0; $i < $n; ++$i) {
            $expect = $i * 2;
            $actual = $list->seek($i);
            var_dump($actual);
            var_dump($expect);
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        foreach ($data as $value) {
            $list->push($value * 2);
        }
        $n = count($data);
        for ($i = 1; $i < $n; ++$i) {
            $expect = ($i - 1) * 2;
            $actual = $list->seek($i - 1);
            var_dump($actual);
            var_dump($expect);
        }


        $list = new LinkedList();
        $n = 6;
        for ($i = 0; $i < $n; ++$i) {
            $list->push($i * 13);
        }
        $list->seek(0);
        $expect = 3 * 13;
        $actual = $list->seek(3);
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        $n = 6;
        for ($i = 0; $i < $n; ++$i) {
            $list->push($i * 13);
        }
        $list->seek(5);
        $expect = 3 * 13;
        $actual = $list->seek(3);
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        var_dump($list->valid());


        $list = new LinkedList();
        $list->rewind();
        var_dump($list->valid());


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);
        $list->rewind();
        var_dump($list->valid());


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);
        $list->rewind();
        var_dump($list->valid());


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        $list->rewind();
        for ($i = 0; $i < count($data); $i++) {
            var_dump($list->valid());
            $list->next();
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        foreach ($data as $value) {
            $list->push($value * 2);
        }
        array_walk($data, [$list, 'push']);

        $list->rewind();
        for ($i = 0; $i < count($data); $i++) {
            $expect = $i;
            $actual = $list->key();
            var_dump($actual);
            var_dump($expect);
            $list->next();
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        foreach ($data as $value) {
            $list->push($value * 2);
        }

        $list->rewind();
        for ($i = 0; $i < count($data); $i++) {
            $expect = $i * 2;
            $actual = $list->current();
            var_dump($actual);
            var_dump($expect);
            $list->next();
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        for ($i = 0; $i < count($data); $i++) {
            $list[$i] = $i * 2;
        }
        $list->rewind();
        for ($i = 0; $i < count($data); $i++) {
            $expect = $i * 2;
            $actual = $list[$i];
            var_dump($actual);
            var_dump($expect);
        }


        $list = new LinkedList();
        $list->push(0);
        $before = count($list);
        $list->insertBefore(0, 0);
        $after = count($list);
        var_dump($after);
        var_dump($before);


        $list = new LinkedList();
        $list->push(0);
        $list->insertBefore(0, 1);
        $expect = 1;
        $actual = $list->first();
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        $list->push(0);
        $list->insertBefore(0, 1);
        $expect = 0;
        $actual = $list->last();
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        $list->push(0);
        $before = count($list);
        $list->insertAfter(0, 0);
        $after = count($list);
        var_dump($after);
        var_dump($before);


        $list = new LinkedList();
        $list->push(0);
        $list->insertAfter(0, 1);
        $expect = 0;
        $actual = $list->first();
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        $list->push(0);
        $list->insertAfter(0, 1);
        $expect = 1;
        $actual = $list->last();
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        unset($list[0]);


        $list = new LinkedList();
        $list->push(0);
        $before = count($list);
        unset($list[0]);
        $after = count($list);
        var_dump($after);
        var_dump($before);


        $list = new LinkedList();
        $list->push(0);
        $list->push(1);
        unset($list[0]);
        $expect = 1;
        $actual = $list[0];
        var_dump($actual);
        var_dump($expect);


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_shift($data);
        array_walk($data, [$list, 'push']);

        $clone = clone $list;
        foreach ($clone as $key => $value) {
            $expect = $value * 2;
            $clone[$key] = $expect;
            $actual = $list[$key];
            var_dump($actual);
            var_dump($expect);
        }


    /**
     * @dataProvider provide_rangeOneToN
     */
    $data    = [0, 2, 4, 6];
        $list = new LinkedList();
        array_walk($data, [$list, 'push']);

        array_shift($data);
        $expect = $data;
        $actual = iterator_to_array($list->tail());
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        $list->push(0);
        $list->push(1);
        $list->push(2);
        $list->prev();
        $expect = 1;
        $actual = $list->current();
        var_dump($actual);
        var_dump($expect);


        $list = new LinkedList();
        $ndx = $list->indexOf(0);
        var_dump($ndx);


        // $list = new LinkedList();
        // $list->push(1);
        // $expect = 0;
        // $actual = $list->indexOf(1);
        // var_dump($actual);
        // var_dump($expect);


        // $list = new LinkedList();
        // $list->push(1);
        // $list->push(3);
        // $expect = 1;
        // $actual = $list->indexOf(3);
        // var_dump($actual);
        // var_dump($expect);


        // $list = new LinkedList();
        // $list->push(1);
        // $list->push(3);
        // $actual = $list->indexOf(2);
        // var_dump($actual);


        $list = new LinkedList();
        var_dump($list->contains(1));


        // $list = new LinkedList();
        // $list->push(1);
        // $list->push(3);
        // var_dump($list->contains(1));


        $list = new LinkedList();
        $list->push(1);
        $list->push(3);
        var_dump($list->contains(3));


        $list = new LinkedList();
        $list->push(1);
        $list->push(2);
        $list->push(3);
        var_dump($list->contains(2));


        $list = new LinkedList();
        $list->push(1);
        $list->push(3);
        var_dump($list->contains(PHP_INT_MAX));


        $list = new LinkedList();
        $list->push($a = new \StdClass);
        $list->push(new \StdClass);
        var_dump($list->contains($a, __NAMESPACE__ . '\\same'));


        $list = new LinkedList();
        $list->push(new \StdClass);
        $list->push($a = new \StdClass);
        var_dump($list->contains($a, __NAMESPACE__ . '\\same'));


        $list = new LinkedList();
        $list->push(new \StdClass);
        $list->push($a = new \StdClass);
        $list->push(new \StdClass);
        var_dump($list->contains($a, __NAMESPACE__ . '\\same'));


        $list = new LinkedList();
        $list->push(new \StdClass);
        $list->push(new \StdClass);
        $list->push(new \StdClass);
        var_dump($list->contains(new \StdClass, __NAMESPACE__ . '\\same'));

