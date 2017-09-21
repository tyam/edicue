<?php

namespace tyam\edicue;

class Dispatcher {
    /**
     *
     * resolve
     *
     */
    private $resolve;

    /**
     *
     * handler map: [eventClass => [handler, ...], ...] 
     *   handler := string  -- resolve key string, then invoked
     *            | object  -- invoked
     *            | [object, string]  -- instance-method string called
     *            | [string, string]  -- resolve key string, then instance-method string called
     *
     */
    private $map;

    /**
     *
     * handleException
     *
     */
    private $handleException;

    public function __construct(Callable $resolve = null, Callable $handleException = null, array $map = []) {
        $this->resolve = $resolve;
        $this->handleException = ($handleException) ? $handleException : [self::class, 'doNothing'];
        $this->map = $map;
    }

    public static function doNothing($exception, $event, $handler) {}

    public static function rethrow($exception, $event, $handler) {
        throw $exception;
    }

    public function getResolve() {
        return $this->resolve;
    }

    public function setResolve(Callable $resolve = null) {
        $this->resolve = $resolve;
    }

    public function getHandleException() {
        return $this->handleException;
    }

    public function setHandleException(Callable $handleException) {
        $this->handleException = $handleException;
    }

    public function addHandler($eventClass, $handler) {
        $this->map[$eventClass][] = $handler;
    }

    public function getHandlers($eventClass) {
        return $this->map[$eventClass];
    }

    public function replaceHandlers($eventClass, array $handlers) {
        $this->map[$eventClass] = $handlers;
    }

    public function getHandlerMap() {
        return $this->map;
    }

    public function setHandlerMap(array $map) {
        $this->map = $map;
    }

    public function __invoke($event) {
        $eventClass = get_class($event);
        foreach ($this->map[$eventClass] as $handler) {
            if (is_string($handler) || (is_array($handler) && is_string($handler[0]))) {
                // lazy one.
                if (! $this->resolve) {
                    throw new \LogicException("You must provide resolve.");
                }
                $handlerObj = call_user_func($this->resolve, $handler);
            } else {
                $handlerObj = $handler;
            }
            try {
                call_user_func($handlerObj, $event);
            } catch (\RuntimeException $e) {
                call_user_func($this->handleException, $e, $event, $handler);
            }
        }
    }
}