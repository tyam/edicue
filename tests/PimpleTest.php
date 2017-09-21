<?php

namespace tests;

use \PHPUnit\Framework\TestCase;
use tyam\edicue\Dispatcher;
use Pimple\Container;

class MyEvent2 {}

class PimpleTest extends TestCase {
    private $box;
    public function callback0($event, $caller) {
        $this->box = get_class($event)." $caller";
    }
    public function test() {
        $di = new Container();
        $di['tests\Sample'] = $di->factory(function ($di) {
            return new Sample($di['tests\Sample::callback']);
        });
        $di['tests\Sample::callback'] = [$this, 'callback0'];
        $resolve = function ($xcallable) use ($di) {
            if (is_string($xcallable)) {
                return $di[$xcallable];
            }
            $xcallable[0] = $di[$xcallable[0]];
            return $xcallable;
        };

        $map = ['tests\MyEvent2' => [
            ['tests\Sample', 'handle']
        ]];
        $dispatcher = new Dispatcher($resolve, null, $map);
        $dispatcher(new MyEvent2());
        $this->assertEquals($this->box, 'tests\MyEvent2 Sample');
    }
}
