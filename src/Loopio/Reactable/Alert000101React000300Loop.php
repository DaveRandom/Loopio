<?php

namespace Loopio\Reactable;

use Alert\Reactor;

class Alert000101React000300Loop extends Alert000101Loop
{
    /**
     * Factory which makes Timer instances
     *
     * @var TimerFactory
     */
    private $timerFactory;

    /**
     * Constructor
     *
     * @param Reactor $reactor
     * @param TimerFactory $timerFactory
     */
    public function __construct(Reactor $reactor, TimerFactory $timerFactory)
    {
        $this->timers = new \SplObjectStorage;
        $this->timerFactory = $timerFactory;
        parent::__construct($reactor);
    }

    /**
     * Create a timer entity to return to the user
     *
     * In React >=0.3 this is a Timer object, so we need to make one of those
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     * @param bool $repeating Whether the time is repeating
     * @return mixed
     */
    protected function createTimer($interval, $callback, $repeating)
    {
        return $this->timerFactory->create($this, $interval, $callback, $repeating);
    }
}
