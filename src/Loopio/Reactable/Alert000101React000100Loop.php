<?php

namespace Loopio\Reactable;

use Alert\Reactor;

class Alert000101React000100Loop extends Alert000101Loop
{
    /**
     * Counter for timer IDs to return to the user
     *
     * @var int
     */
    private $timerIdCounter = 0;

    /**
     * Constructor
     *
     * @param Reactor $reactor
     */
    public function __construct(Reactor $reactor)
    {
        $this->timers = new \ArrayObject;
        parent::__construct($reactor);
    }

    /**
     * Create a timer entity to return to the user
     *
     * In React <0.3 this is just an spl_object_hash, so returning an integer ID should be safe
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     * @param bool $repeating Whether the time is repeating
     * @return mixed
     */
    protected function createTimer($interval, $callback, $repeating)
    {
        do {
            $result = $this->timerIdCounter++;

            if ($this->timerIdCounter >= PHP_INT_MAX) {
                $this->timerIdCounter = 0;
            }
        } while ($this->timers->offsetExists($result));

        return $result;
    }
}
