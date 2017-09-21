<?php

namespace tests;

use \PHPUnit\Framework\TestCase;
use tyam\edicue\Dispatcher;

class MyEvent {
    public $what;
    private $calledHandlers;
    public function __construct($what) {
        $this->what = $what;
        $this->calledHandlers = [];
    }
    public function markHandlerAsCalled($handler) {
        $this->calledHandlers[] = $handler;
    }
    public function getCalledHandlers() {
        return $this->calledHandlers;
    }
}

class BasicTest extends TestCase
{
    public function handleMyEvent(MyEvent $e) {
        $e->markHandlerAsCalled('instance method');
    }
    public function __invoke(MyEvent $e) {
        $e->markHandlerAsCalled('object invoke');
    }
    public function testHandlerTypes() {
        $handleMyEvent = function (MyEvent $e) {
            $e->markHandlerAsCalled('function object');
        };
        $map = ['tests\MyEvent' => [
            $handleMyEvent,  // function object
            [$this, 'handleMyEvent'],  // instance method
            $this  // invokable object
        ]];
        $dispatcher = new Dispatcher(null, null, $map);
        $e = new MyEvent('ok');
        $dispatcher($e);
        $hs = $e->getCalledHandlers();
        $this->assertTrue(array_search('function object', $hs) !== false);
        $this->assertTrue(array_search('instance method', $hs) !== false);
        $this->assertTrue(array_search('object invoke', $hs) !== false);
    }
    public function testSetters() {
        $map = ['tests\MyEvent' => [
            [self::class, 'handleMyEventStatic'], 
            $this
        ]];
        $dispatcher = new Dispatcher(null, null, $map);
        $hs0 = $dispatcher->getHandlers('tests\MyEvent');
        $this->assertEquals(count($hs0), 2);
        $this->assertContains($map['tests\MyEvent'][0], $hs0);
        $this->assertContains($this, $hs0);

        $dispatcher->addHandler('tests\MyEvent', [$this, 'handleMyEvent']);
        $hs1 = $dispatcher->getHandlers('tests\MyEvent');
        $this->assertEquals(count($hs1), 3);
        $this->assertContains([$this, 'handleMyEvent'], $hs1);

        $dispatcher->replaceHandlers('tests\MyEvent', $map['tests\MyEvent']);
        $hs2 = $dispatcher->getHandlers('tests\MyEvent');
        $this->assertEquals(count($hs2), 2);

        $map2 = $dispatcher->getHandlerMap();
        $this->assertEquals(count($map2), 1);
        $map2['foo'][] = 'unknownHandler';
        $dispatcher->setHandlerMap($map2);
        $this->assertEquals(count($dispatcher->getHandlers('foo')), 1);
    }
}