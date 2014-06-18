<?php

namespace Loopio;

use Loopio\Reactable\TimerFactory,
    Alert\Reactor,
    React\EventLoop\LoopInterface;

class LoopFactory
{
    /**
     * React version identifier string
     *
     * @var string
     */
    private $reactVersion;

    /**
     * Alert version identifier string
     *
     * @var string
     */
    private $alertVersion;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detectAlertVersion();
        $this->detectReactVersion();
    }

    /**
     * Detect the installed Alert version
     */
    private function detectAlertVersion()
    {
        try {
            $reflector = new \ReflectionClass('Alert\NativeReactor');
        } catch(\ReflectionException $e) {
            throw new \LogicException('The Alert library does not appear to be fully installed');
        }

        if ($reflector->hasProperty('microsecondResolution')) {
            $this->alertVersion = '000101';
        } else {
            $this->alertVersion = '000600';
        }
    }

    /**
     * Detect the installed React version
     */
    private function detectReactVersion()
    {
        try {
            $reflector = new \ReflectionClass('React\EventLoop\LoopInterface');
        } catch(\ReflectionException $e) {
            throw new \LogicException('The React library does not appear to be fully installed');
        }

        if (!$reflector->hasMethod('isTimerActive')) {
            $this->reactVersion = '000100';
        } else {
            $this->reactVersion = '000300';
        }
    }

    /**
     * Create a loop which is compatible with React
     *
     * @param Reactor $reactor
     * @return LoopInterface
     * @throws \LogicException
     */
    public function createReactLoop(Reactor $reactor)
    {
        $className = "Loopio\\Reactable\\Alert{$this->alertVersion}React{$this->reactVersion}Loop";
        return new $className($reactor, new TimerFactory);
    }

    /**
     * Create a loop which is compatible with Alert
     *
     * @param LoopInterface $loop
     * @return Reactor
     */
    public function createAlertLoop(LoopInterface $loop)
    {
        $className = "Loopio\\Alertable\\React{$this->reactVersion}Alert{$this->alertVersion}Loop";
        return new $className($loop);
//        return new Alertable\Loop($reactor);
    }
}
