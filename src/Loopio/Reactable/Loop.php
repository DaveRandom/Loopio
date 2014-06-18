<?php

namespace Loopio\Reactable;

use React\EventLoop\LoopInterface,
    React\EventLoop\Timer\TimerInterface,
    Alert\Reactor;

abstract class Loop implements LoopInterface
{
    /**
     * Underlying reactor implementation
     *
     * @var Reactor
     */
    private $reactor;

    /**
     * Map of stream IDs to Alert read watcher IDs
     *
     * @var int[]
     */
    private $readWatcherIDs = array();

    /**
     * Map of stream IDs to Alert write watcher IDs
     *
     * @var int[]
     */
    private $writeWatcherIDs = array();

    /**
     * Map of timers to Alert timer watcher IDs
     *
     * @var \ArrayAccess
     */
    protected $timers;

    /**
     * Scale factor by which times are adjusted
     *
     * @var int
     */
    protected $timeScaleFactor = 1;

    /**
     * Constructor
     *
     * @param Reactor $reactor
     */
    public function __construct(Reactor $reactor)
    {
        $this->reactor = $reactor;
    }

    /**
     * Create a timer entity to return to the user
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     * @param bool $repeating Whether the time is repeating
     * @return mixed
     */
    abstract protected function createTimer($interval, $callback, $repeating);

    /**
     * Register a listener to be notified when a stream is ready to read.
     *
     * @param resource $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addReadStream($stream, callable $listener)
    {
        $this->readWatcherIDs[(int) $stream] = $this->reactor->onReadable($stream, function() use($listener, $stream) {
            return call_user_func($listener, $stream, $this);
        });
    }

    /**
     * Register a listener to be notified when a stream is ready to write.
     *
     * @param resource $stream The PHP stream resource to check.
     * @param callable $listener Invoked when the stream is ready.
     */
    public function addWriteStream($stream, callable $listener)
    {
        $this->writeWatcherIDs[(int) $stream] = $this->reactor->onWritable($stream, function() use($listener, $stream) {
            return call_user_func($listener, $stream, $this);
        });
    }

    /**
     * Remove the read event listener for the given stream.
     *
     * @param resource $stream The PHP stream resource.
     */
    public function removeReadStream($stream)
    {
        $id = (int) $stream;

        if (isset($this->readWatcherIDs[$id])) {
            $this->reactor->cancel($this->readWatcherIDs[$id]);
            unset($this->readWatcherIDs[$id]);
        }
    }

    /**
     * Remove the write event listener for the given stream.
     *
     * @param resource $stream The PHP stream resource.
     */
    public function removeWriteStream($stream)
    {
        $id = (int) $stream;

        if (isset($this->writeWatcherIDs[$id])) {
            $this->reactor->cancel($this->writeWatcherIDs[$id]);
            unset($this->writeWatcherIDs[$id]);
        }
    }

    /**
     * Remove all listeners for the given stream.
     *
     * @param resource $stream The PHP stream resource.
     */
    public function removeStream($stream)
    {
        $this->removeReadStream($stream);
        $this->removeWriteStream($stream);
    }

    /**
     * Enqueue a callback to be invoked once after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addTimer($interval, callable $callback)
    {
        $timer = null;

        $wrappedCallback = function() use($callback, &$timer) {
            return call_user_func($callback, $timer);
        };

        $id = $this->reactor->once($wrappedCallback, $interval * $this->timeScaleFactor);
        $timer = $this->createTimer($interval, $wrappedCallback, false);
        $this->timers->offsetSet($timer, $id);

        return $timer;
    }

    /**
     * Enqueue a callback to be invoked repeatedly after the given interval.
     *
     * The execution order of timers scheduled to execute at the same time is
     * not guaranteed.
     *
     * @param int|float $interval The number of seconds to wait before execution.
     * @param callable $callback The callback to invoke.
     *
     * @return TimerInterface
     */
    public function addPeriodicTimer($interval, callable $callback)
    {
        $timer = null;

        $wrappedCallback = function() use($callback, &$timer) {
            return call_user_func($callback, $timer);
        };

        $id = $this->reactor->repeat($wrappedCallback, $interval * $this->timeScaleFactor);
        $timer = $this->createTimer($interval, $wrappedCallback, true);
        $this->timers->offsetSet($timer, $id);

        return $timer;
    }

    /**
     * Cancel a pending timer.
     *
     * @param TimerInterface $timer The timer to cancel.
     */
    public function cancelTimer(TimerInterface $timer)
    {
        if ($this->timers->offsetExists($timer)) {
            $id = $this->timers->offsetGet($timer);
            $this->timers->offsetUnset($timer);
            $this->reactor->cancel($id);
        }
    }

    /**
     * Check if a given timer is active.
     *
     * @param TimerInterface $timer The timer to check.
     *
     * @return boolean True if the timer is still enqueued for execution.
     */
    public function isTimerActive(TimerInterface $timer)
    {
        return $this->timers->offsetExists($timer);
    }

    /**
     * Schedule a callback to be invoked on the next tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued,
     * before any timer or stream events.
     *
     * @param callable $listener The callback to invoke.
     */
    public function nextTick(callable $listener)
    {
        $this->reactor->immediately(function() use($listener) {
            call_user_func($listener, $this);
        });
    }

    /**
     * Schedule a callback to be invoked on a future tick of the event loop.
     *
     * Callbacks are guaranteed to be executed in the order they are enqueued.
     *
     * @param callable $listener The callback to invoke.
     */
    public function futureTick(callable $listener)
    {
        $this->nextTick($listener);
    }

    /**
     * Perform a single iteration of the event loop.
     */
    public function tick()
    {
        $this->reactor->tick();
    }

    /**
     * Run the event loop until there are no more tasks to perform.
     */
    public function run()
    {
        $this->reactor->run();
    }

    /**
     * Instruct a running event loop to stop.
     */
    public function stop()
    {
        $this->reactor->stop();
    }
}
