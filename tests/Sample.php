<?php

namespace tests;

class Sample {
    private $callback;

    public function __construct($callback) {
        $this->callback = $callback;
    }

    public function handle($event) {
        call_user_func($this->callback, $event, 'Sample');
    }
}