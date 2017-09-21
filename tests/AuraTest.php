<?php

namespace tests;

use \PHPUnit\Framework\TestCase;
use tyam\edicue\Dispatcher;
use Aura\Di\ResolutionHelper;
use Aura\Di\ContainerConfig;
use Aura\Di\ContainerBuilder;
use Aura\Di\Container;

class MyEvent1 {}

class AuraTest extends TestCase {
    private $box;
    public function callback0($event, $caller) {
        $this->box = get_class($event)." $caller";
    }
    public function test() {
        $builder = new ContainerBuilder();
        $di = $builder->newInstance();
        $di->params['tests\Sample']['callback'] = [$this, 'callback0'];
        $resolve = new ResolutionHelper($di);

        $map = ['tests\MyEvent1' => [
            ['tests\Sample', 'handle']
        ]];
        $dispatcher = new Dispatcher($resolve, null, $map);
        $dispatcher(new MyEvent1());
        $this->assertEquals($this->box, 'tests\MyEvent1 Sample');
    }
}
