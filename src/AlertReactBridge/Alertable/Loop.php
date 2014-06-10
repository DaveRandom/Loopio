<?php

namespace AlertReactBridge\Alertable;

use Alert\Reactor,
    React\EventLoop\LoopInterface;

abstract class Loop implements Reactor
{
    /**
     * Underlying reactor implementation
     *
     * @var LoopInterface
     */
    protected $reactor;

    /**
     * Constructor
     *
     * @param LoopInterface $reactor
     */
    public function __construct(LoopInterface $reactor)
    {
        $this->reactor = $reactor;
    }

    /**
     * Start the event reactor and assume program flow control
     *
     * @param callable $onStart Optional callback to invoke immediately upon reactor start
     */
    public function run(callable $onStart = NULL)
    {
        if ($onStart) {
            $this->reactor->nextTick($onStart);
        }

        $this->reactor->run();
    }

    /**
     * Execute a single event loop iteration
     */
    public function tick()
    {
        $this->reactor->tick();
    }

    /**
     * Stop the event reactor
     */
    public function stop()
    {
        $this->reactor->stop();
    }
}
