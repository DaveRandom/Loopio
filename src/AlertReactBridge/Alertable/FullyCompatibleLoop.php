<?php

namespace AlertReactBridge\Alertable;

class FullyCompatibleLoop extends Loop
{
    /**
     * Schedule a callback for immediate invocation in the next event loop iteration
     *
     * Though it can't be enforced at the interface level all timer/stream scheduling methods
     * should return a unique integer identifying the relevant watcher.
     *
     * @param callable $callback Any valid PHP callable
     */
    public function immediately(callable $callback)
    {
        // TODO: Implement immediately() method.
    }

    /**
     * Schedule a callback to execute once
     *
     * Time intervals are measured in seconds. Floating point values < 0 denote intervals less than
     * one second. e.g. $interval = 0.001 means a one millisecond interval.
     *
     * Though it can't be enforced at the interface level all timer/stream scheduling methods
     * should return a unique integer identifying the relevant watcher.
     *
     * @param callable $callback Any valid PHP callable
     * @param float $delay The delay in seconds before the callback will be invoked (zero is allowed)
     */
    public function once(callable $callback, $delay)
    {
        // TODO: Implement once() method.
    }

    /**
     * Schedule a recurring callback to execute every $interval seconds until cancelled
     *
     * Time intervals are measured in seconds. Floating point values < 0 denote intervals less than
     * one second. e.g. $interval = 0.001 means a one millisecond interval.
     *
     * Though it can't be enforced at the interface level all timer/stream scheduling methods
     * should return a unique integer identifying the relevant watcher.
     *
     * @param callable $callback Any valid PHP callable
     * @param float $interval The interval in seconds to observe between callback executions (zero is allowed)
     */
    public function repeat(callable $callback, $interval)
    {
        // TODO: Implement repeat() method.
    }

    /**
     * Schedule an event to trigger once at the specified time
     *
     * Though it can't be enforced at the interface level all timer/stream scheduling methods
     * should return a unique integer identifying the relevant watcher.
     *
     * @param callable $callback Any valid PHP callable
     * @param string $timeString Any string that can be parsed by strtotime() and is in the future
     */
    public function at(callable $callback, $timeString)
    {
        // TODO: Implement at() method.
    }

    /**
     * Watch a stream resource for IO readable data and trigger the callback when actionable
     *
     * Though it can't be enforced at the interface level all timer/stream scheduling methods
     * should return a unique integer identifying the relevant watcher.
     *
     * @param resource $stream A stream resource to watch for readable data
     * @param callable $callback Any valid PHP callable
     * @param bool $enableNow Should the watcher be enabled now or held for later use?
     */
    public function onReadable($stream, callable $callback, $enableNow = TRUE)
    {
        // TODO: Implement onReadable() method.
    }

    /**
     * Watch a stream resource to become writable and trigger the callback when actionable
     *
     * Though it can't be enforced at the interface level all timer/stream scheduling methods
     * should return a unique integer identifying the relevant watcher.
     *
     * @param resource $stream A stream resource to watch for writability
     * @param callable $callback Any valid PHP callable
     * @param bool $enableNow Should the watcher be enabled now or held for later use?
     */
    public function onWritable($stream, callable $callback, $enableNow = TRUE)
    {
        // TODO: Implement onWritable() method.
    }

    /**
     * Cancel an existing timer/stream watcher
     *
     * @param int $watcherId
     */
    public function cancel($watcherId)
    {
        // TODO: Implement cancel() method.
    }

    /**
     * Temporarily disable (but don't cancel) an existing timer/stream watcher
     *
     * @param int $watcherId
     */
    public function disable($watcherId)
    {
        // TODO: Implement disable() method.
    }

    /**
     * Enable a disabled timer/stream watcher
     *
     * @param int $watcherId
     */
    public function enable($watcherId)
    {
        // TODO: Implement enable() method.
    }
}
