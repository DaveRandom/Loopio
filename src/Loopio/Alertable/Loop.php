<?php

namespace Loopio\Alertable;

use Alert\Reactor,
    React\EventLoop\LoopInterface;

class Loop implements Reactor
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
    private $reactor;

    /**
     * Array of registered watchers
     *
     * @var array
     */
    private $watchers = [];

    /**
     * List of disabled watchers
     *
     * @var bool[]
     */
    private $disabledWatchers = [];

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
     * Register a new watcher and get the ID
     *
     * @param int $type
     * @param mixed $data
     * @return int
     */
    private function registerWatcher($type, $data)
    {
        do {
            $id = $this->watcherId++;

            if ($this->watcherId > PHP_INT_MAX) {
                $this->watcherId = -PHP_INT_MAX;
            }
        } while (isset($this->watchers[$id]));

        $this->watchers[$id] = [$type, $data];

        return $id;
    }

    /**
     * Deregister a watcher by ID
     *
     * @param $watcherId
     */
    private function deregisterWatcher($watcherId)
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
        if (isset($this->watchers[$watcherId])) {
            list($type, $data) = $this->watchers[$watcherId];

            switch ($type) {
                case self::WATCHER_TYPE_READ:
                    $this->reactor->removeReadStream($data);
                    break;

                case self::WATCHER_TYPE_WRITE:
                    $this->reactor->removeWriteStream($data);
                    break;

                case self::WATCHER_TYPE_TIMER:
                    $this->reactor->cancelTimer($data);
                    break;
            }
        }

        unset($this->watchers[$watcherId], $this->disabledWatchers[$watcherId]);
    }

    /**
     * Schedule a callback for immediate invocation in the next event loop iteration
     *
     * @param callable $callback Any valid PHP callable
     * @return int
     */
    public function immediately(callable $callback)
    {
        $watcherId = null;

        $timer = $this->reactor->addTimer(0, function () use (&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);

            if (isset($this->disabledWatchers[$watcherId])) {
                return null;
            }

            return call_user_func($callback, $watcherId, $this);
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_TIMER, $timer);

        return $watcherId;
    }

    /**
     * Schedule a callback to execute once
     *
     * Time intervals are measured in milliseconds.
     *
     * @param callable $callback Any valid PHP callable
     * @param float $delay The delay in seconds before the callback will be invoked (zero is allowed)
     * @return int
     */
    public function once(callable $callback, $delay)
    {
        $watcherId = null;

        $timer = $this->reactor->addTimer($delay / 1000, function () use (&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);

            if (isset($this->disabledWatchers[$watcherId])) {
                return null;
            }

            return call_user_func($callback, $watcherId, $this);
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_TIMER, $timer);

        return $watcherId;
    }

    /**
     * Schedule a recurring callback to execute every $interval seconds until cancelled
     *
     * Time intervals are measured in milliseconds.
     *
     * @param callable $callback Any valid PHP callable
     * @param float $interval The interval in seconds to observe between callback executions (zero is allowed)
     * @return int
     */
    public function repeat(callable $callback, $interval)
    {
        $watcherId = null;

        $timer = $this->reactor->addPeriodicTimer($interval / 1000, function () use (&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);

            if (isset($this->disabledWatchers[$watcherId])) {
                return null;
            }

            return call_user_func($callback, $watcherId, $this);
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_TIMER, $timer);

        return $watcherId;
    }

    /**
     * Schedule an event to trigger once at the specified time
     *
     * @param callable $callback Any valid PHP callable
     * @param string $timeString Any string that can be parsed by strtotime() and is in the future
     * @return int
     * @throws \InvalidArgumentException
     */
    public function at(callable $callback, $timeString)
    {
        $now = time();
        $executeAt = @strtotime($timeString);

        if ($executeAt === false || $executeAt <= $now) {
            throw new \InvalidArgumentException(
                'Valid future time string (parsable by strtotime()) required'
            );
        }

        return $this->once($callback, ($executeAt - $now) * 1000);
    }

    /**
     * Watch a stream resource for IO readable data and trigger the callback when actionable
     *
     * @param resource $stream A stream resource to watch for readable data
     * @param callable $callback Any valid PHP callable
     * @param bool $enableNow Should the watcher be enabled now or held for later use?
     * @return int
     */
    public function onReadable($stream, callable $callback, $enableNow = true)
    {
        $watcherId = null;

        $this->reactor->addReadStream($stream, function () use ($callback, &$watcherId, $stream) {
            if (isset($this->disabledWatchers[$watcherId])) {
                return null;
            }

            return call_user_func($callback, $watcherId, $stream, $this);
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_READ, $stream);

        if (!$enableNow) {
            $this->disable($watcherId);
        }

        return $watcherId;
    }

    /**
     * Watch a stream resource to become writable and trigger the callback when actionable
     *
     * @param resource $stream A stream resource to watch for writability
     * @param callable $callback Any valid PHP callable
     * @param bool $enableNow Should the watcher be enabled now or held for later use?
     * @return int
     */
    public function onWritable($stream, callable $callback, $enableNow = true)
    {
        $watcherId = null;

        $this->reactor->addWriteStream($stream, function () use ($callback, &$watcherId, $stream) {
            if (isset($this->disabledWatchers[$watcherId])) {
                return null;
            }

            return call_user_func($callback, $watcherId, $stream, $this);
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_WRITE, $stream);

        if (!$enableNow) {
            $this->disable($watcherId);
        }

        return $watcherId;
    }

    /**
     * Temporarily disable (but don't cancel) an existing timer/stream watcher
     *
     * @param int $watcherId
     */
    public function disable($watcherId)
    {
        $this->disabledWatchers[$watcherId] = true;
    }

    /**
     * Enable a disabled timer/stream watcher
     *
     * @param int $watcherId
     */
    public function enable($watcherId)
    {
        unset($this->disabledWatchers[$watcherId]);
    }
}
