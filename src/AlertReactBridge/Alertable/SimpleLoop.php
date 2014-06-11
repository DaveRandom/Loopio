<?php

namespace AlertReactBridge\Alertable;

use AlertReactBridge\UnimplementedArgumentException,
    AlertReactBridge\UnimplementedMethodException;

class SimpleLoop extends Loop
{
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
            return call_user_func($callback);
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
            return call_user_func($callback);
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
            return call_user_func($callback);
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
            return call_user_func($callback);
        });
        $watcherId = $this->registerWatcher(self::WATCHER_TYPE_TIMER, $timer);

        return $watcherId;
    }

    /**
     * Watch a stream resource for IO readable data and trigger the callback when actionable
     *
     * @param resource $stream A stream resource to watch for readable data
     * @param callable $callback Any valid PHP callable
     * @param bool $enableNow Not implemented
     * @return int
     * @throws UnimplementedArgumentException
     */
    public function onReadable($stream, callable $callback, $enableNow = true)
    {
        if (!$enableNow) {
            throw new UnimplementedArgumentException('The onReadable() method does not implement the $enableNow argument, use the fully compatible loop');
        }

        $this->reactor->addReadStream($stream, $callback);
        return $this->registerWatcher(self::WATCHER_TYPE_READ, $stream);
    }

    /**
     * Watch a stream resource to become writable and trigger the callback when actionable
     *
     * @param resource $stream A stream resource to watch for writability
     * @param callable $callback Any valid PHP callable
     * @param bool $enableNow Not implemented
     * @return int
     * @throws UnimplementedArgumentException
     */
    public function onWritable($stream, callable $callback, $enableNow = true)
    {
        if (!$enableNow) {
            throw new UnimplementedArgumentException('The onWritable() method does not implement the $enableNow argument, use the fully compatible loop');
        }

        $this->reactor->addWriteStream($stream, $callback);
        return $this->registerWatcher(self::WATCHER_TYPE_WRITE, $stream);
    }

    /**
     * Not implemented
     *
     * @param int $watcherId
     * @throws UnimplementedMethodException
     */
    public function disable($watcherId)
    {
        throw new UnimplementedMethodException('The disable() method is not implemented, use the fully compatible loop');
    }

    /**
     * Not implemented
     *
     * @param int $watcherId
     * @throws UnimplementedMethodException
     */
    public function enable($watcherId)
    {
        throw new UnimplementedMethodException('The enable() method is not implemented, use the fully compatible loop');
    }
}
