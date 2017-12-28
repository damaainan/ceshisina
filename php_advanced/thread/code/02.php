<?php
class HelloWorld extends Thread {
    public function __construct($world) {
        $this->world = $world;
    }

    public function run() {
        print_r(sprintf("Hello %s\n", $this->world));
    }
}

$thread = new HelloWorld("World");

if ($thread->start()) {
    printf("Thread #%lu says: %s\n", $thread->getThreadId(), $thread->join());
}