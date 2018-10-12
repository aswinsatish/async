<?php

use LogicalSteps\Async\Async;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;

require __DIR__ . '/../vendor/autoload.php';

/** @var LoopInterface $loop */
$loop = Factory::create();

function two_seconds(callable $call_back)
{
    global $loop;
    $loop->addTimer(2, function () use ($call_back) {
        $call_back(null, true);
    });
}

class Timer
{
    static function delay(int $seconds, callable $call_back)
    {
        global $loop;
        $loop->addTimer($seconds, function () use ($call_back) {
            $call_back(null, true);
        });
    }

    function wait(int $seconds, callable $call_back)
    {
        global $loop;
        $loop->addTimer($seconds, function () use ($call_back) {
            $call_back(null, true);
        });
    }

    function hold(int $seconds)
    {
        yield [$this, 'wait', $seconds];

        return true;
    }

    function promise(int $seconds)
    {
        global $loop;
        $differed = new Deferred();
        $loop->addTimer($seconds, function () use ($differed, $seconds) {
            $differed->resolve($seconds);
        });

        return $differed->promise();
    }
}

function flow()
{
    //echo 'started' . PHP_EOL;
    yield 'two_seconds';
    //echo 'after two seconds' . PHP_EOL;
    yield ['Timer', 'delay', 8];
    //echo 'after eight seconds' . PHP_EOL;
    $timer = new Timer();
    yield [$timer, 'wait', 3];
    //echo 'after three seconds' . PHP_EOL;
    yield $timer->hold(1);
    //echo 'after one second' . PHP_EOL;
    yield $timer->promise(2);
    //echo 'after two seconds' . PHP_EOL;
    return $timer->hold(7);
}

$async = new Async();
$async->setLoop($loop);
$async->execute(flow(), 'var_dump');

$loop->run();

