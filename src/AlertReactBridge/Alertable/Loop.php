<?php

namespace AlertReactBridge\Alertable;

use Alert\Reactor,
    React\EventLoop\LoopInterface;

abstract class Loop implements Reactor
{
    /**
     * Watcher type constants
     */
    const WATCHER_TYPE_READ  = 1;
    const WATCHER_TYPE_WRITE = 2;
    const WATCHER_TYPE_TIMER = 3;

    /**
     * Underlying reactor implementation
     *
     * @var LoopInterface
     */
    protected $reactor;

    /**
     * Array of registered watchers
     *
     * @var array
     */
    protected $watchers = [];

    /**
     * Counter for watcher IDs
     *
     * @var int
     */
    private $watcherID = 0;

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
     * Generate the next available watcher ID
     *
     * @return int
     */
    private function generateWatcherID()
    {
        do {
            $result = $this->watcherID++;

            if ($this->watcherID > PHP_INT_MAX) {
                $this->watcherID = 0;
            }
        } while (isset($this->watchers[$result]));

        return $result;
    }

    /**
     * Register a new watcher and get the ID
     *
     * @param int $type
     * @param mixed $data
     * @return int
     */
    protected function registerWatcher($type, $data = null)
    {
        $id = $this->generateWatcherID();
        $this->watchers[$id] = [$type, $data];

        return $id;
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
