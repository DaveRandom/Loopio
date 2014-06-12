<?php

namespace AlertReactBridge;

use AlertReactBridge\Alertable,
    AlertReactBridge\Reactable,
    Alert\Reactor,
    React\EventLoop\LoopInterface;

class LoopFactory
{
    /**
     * Create a loop which is fully compatible with React
     *
     * @param Reactor $reactor
     * @return LoopInterface
     */
    public function createReactLoop(Reactor $reactor)
    {
        return new Reactable\Loop($reactor, new Reactable\TimerFactory);
    }

    /**
     * Create a loop which is fully compatible with Alert
     *
     * @param LoopInterface $reactor
     * @return Reactor
     */
    public function createAlertLoop(LoopInterface $reactor)
    {
        return new Alertable\Loop($reactor);
    }
}
