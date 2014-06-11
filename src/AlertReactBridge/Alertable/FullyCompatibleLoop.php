<?php

namespace AlertReactBridge\Alertable;

class FullyCompatibleLoop extends Loop
{
    /**
     * List of disabled watchers
     *
     * @var bool[]
     */
    private $disabledWatchers = [];

    /**
     * Schedule a callback for immediate invocation in the next event loop iteration
     *
     * @param callable $callback Any valid PHP callable
     * @return int
     */
    public function immediately(callable $callback)
    {
        $watcherId = null;

        $timer = $this->reactor->addTimer(0, function() use(&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);
            return !isset($this->disabledWatchers[$watcherId]) ? call_user_func($callback) : null;
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

        $timer = $this->reactor->addTimer($delay / 1000, function() use(&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);
            return !isset($this->disabledWatchers[$watcherId]) ? call_user_func($callback) : null;
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

        $timer = $this->reactor->addPeriodicTimer($interval / 1000, function() use(&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);
            return !isset($this->disabledWatchers[$watcherId]) ? call_user_func($callback) : null;
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
        $watcherId = null;

        if ($executeAt === false || $executeAt <= $now) {
            throw new \InvalidArgumentException(
                'Valid future time string (parsable by strtotime()) required'
            );
        }

        $timer = $this->reactor->addTimer($executeAt - $now, function() use(&$watcherId, $callback) {
            $this->deregisterWatcher($watcherId);
            return !isset($this->disabledWatchers[$watcherId]) ? call_user_func($callback) : null;
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_TIMER, $timer);

        return $watcherId;
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

        $this->reactor->addReadStream($stream, function() use(&$watcherId, $callback) {
            return !isset($this->disabledWatchers[$watcherId]) ? call_user_func($callback) : null;
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

        $this->reactor->addWriteStream($stream, function() use(&$watcherId, $callback) {
            return !isset($this->disabledWatchers[$watcherId]) ? call_user_func($callback) : null;
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

    /**
     * Cancel an existing timer/stream watcher
     *
     * @param int $watcherId
     */
    public function cancel($watcherId)
    {
        parent::cancel($watcherId);
        unset($this->disabledWatchers[$watcherId]);
    }
}
