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
    private $watchers = [];

    /**
     * Counter for watcher IDs
     *
     * @var int
     */
    private $watcherId = 0;

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
            $result = $this->watcherId++;

            if ($this->watcherId > PHP_INT_MAX) {
                $this->watcherId = 0;
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
    protected function registerWatcher($type, $data)
    {
        $id = $this->generateWatcherID();
        $this->watchers[$id] = [$type, $data];

        return $id;
    }

    /**
     * Deregister a watcher by ID
     *
     * @param $watcherId
     */
    protected function deregisterWatcher($watcherId)
    {
        unset($this->watchers[$watcherId]);
    }

    /**
     * Start the event reactor and assume program flow control
     *
     * @param callable $onStart Optional callback to invoke immediately upon reactor start
     */
    public function run(callable $onStart = null)
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

    /**
     * Cancel an existing timer/stream watcher
     *
     * @param int $watcherId
     */
    public function cancel($watcherId)
    {
        list($type, $data) = $this->watchers[$watcherId];

        switch ($type) {
            case Loop::WATCHER_TYPE_READ:
                $this->reactor->removeReadStream($data);
                break;

            case Loop::WATCHER_TYPE_WRITE:
                $this->reactor->removeWriteStream($data);
                break;

            case Loop::WATCHER_TYPE_TIMER:
                $this->reactor->cancelTimer($data);
                break;
        }

        unset($this->watchers[$watcherId]);
    }
}
