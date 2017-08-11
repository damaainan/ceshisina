<?php

namespace Flyweight;

interface FlyweightInterface
{
    public function render(string $extrinsicState): string;
}