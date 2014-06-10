<?php

namespace AlertReactBridge\Reactable;

use React\EventLoop\LoopInterface,
    React\EventLoop\Timer\Timer;

class TimerFactory
{
    /**
     * Create a new Timer instance
     *
     * @param LoopInterface $loop
     * @param int|float $interval
     * @param callable $callback
     * @param bool $periodic
     * @param mixed $data
     * @return Timer
     */
    public function create(LoopInterface $loop, $interval, callable $callback, $periodic = false, $data = null)
    {
        return new Timer($loop, $interval, $callback, $periodic, $data);
    }
}
