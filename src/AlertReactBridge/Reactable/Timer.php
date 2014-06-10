<?php

namespace AlertReactBridge\Reactable;

use React\EventLoop\LoopInterface,
    React\EventLoop\Timer\Timer as ReactTimer;

class Timer extends ReactTimer
{
    /**
     * Alert ID for this timer
     *
     * @var int
     */
    private $id;

    /**
     * Constructor
     *
     * @param LoopInterface $loop
     * @param int|float $interval
     * @param callable $callback
     * @param int $id
     * @param bool $periodic
     */
    public function __construct(LoopInterface $loop, $interval, callable $callback, $id, $periodic = false)
    {
        parent::__construct($loop, $interval, $callback, $periodic);
        $this->id = $id;
    }

    /**
     * Get the Alert ID for this timer
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
