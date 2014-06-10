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
    public function createReactCompatibleLoop(Reactor $reactor)
    {
        return new Reactable\Loop($reactor, new Reactable\TimerFactory);
    }

    /**
     * Create a loop which is fully compatible with Alert
     *
     * @param LoopInterface $reactor
     * @return Reactor
     */
    public function createAlertCompatibleLoop(LoopInterface $reactor)
    {
        return new Alertable\FullyCompatibleLoop($reactor);
    }

    /**
     * Create a maximum performance loop which is partially compatible with Alert
     *
     * @param LoopInterface $reactor
     * @return Reactor
     */
    public function createAlertFastLoop(LoopInterface $reactor)
    {
        return new Alertable\SimpleLoop($reactor);
    }
}
