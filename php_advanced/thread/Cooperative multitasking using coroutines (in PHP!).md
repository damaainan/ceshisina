# Cooperative multitasking using coroutines (in PHP!)

22. December 2012 One of the large new features in PHP 5.5 will be support for generators and coroutines. Generators are already sufficiently covered by the [documentation][0] and various other blog posts (like [this one][1] or [this one][2]. Coroutines on the other hand have received relatively little attention. The reason is that coroutines are both a lot more powerful and a lot harder to understand and explain.

In this article I’d like to guide you through an implementation of a task scheduler using coroutines, so you can get a feeling for the stuff that they allow you to do. I’ll start off with a few introductory sections. If you feel like you already got a good grasp of the basics behind generators and coroutines, then you can jump straight to the “Cooperative multitasking” section.

## Generators

The basic idea behind generators is that a function doesn’t return a single value, but returns a _sequence of values_ instead, where every value is emitted one by one. Or in other words, generators allow you to implement iterators more easily. A very simple example of this concept is the xrange() function:

```php
function xrange($start, $end, $step = 1) {
    for ($i = $start; $i <= $end; $i += $step) {
        yield $i;
    }
}

foreach (xrange(1, 1000000) as $num) {
    echo $num, "\n";
}
```
The xrange() function shown above provides the same functionality as the built-in range() function. The only difference is that range() will return an array with one million numbers in the above case, whereas xrange() returns an iterator that will emit these numbers, but never actually compute an array with all of them.

The advantages of this approach should be evident. It allows you to work with large datasets without loading them into memory all at once. You can even work with _infinite_ data-streams.

All this can also be done without generators, by manually implementing the Iterator interface. Generators only make it (a lot) more convenient, because you no longer have to implement five different methods for every iterator.

## Generators as interruptible functions

To go from generators to coroutines it’s important to understand how they work internally: Generators are interruptible functions, where the yield statements constitute the interruption points.

Sticking to the above example, if you call xrange(1, 1000000) no code in the xrange() function is actually run. Instead PHP just returns an instance of the Generator class which implements the Iterator interface:

```php
$range = xrange(1, 1000000);
var_dump($range); // object(Generator)#1
var_dump($range instanceof Iterator); // bool(true)
    
```

The code is only run once you invoke one of the iterator methods on the object. E.g. if you call $range->rewind() the code in the xrange() function will be run until the first occurrence of yield in the control flow. In this case it means that $i = $start and then yield $i are run. Whatever was passed to the yield statement can then be fetched using $range->current().

To continue executing the code in the generator you need to call the $range->next() method. This will again resume the generator until a yield statement is hit. Thus, using a succession of ->next() and ->current() calls, you can get all values from the generator, until at some point no yield is hit anymore. For the xrange() this happens once $i exceeds $end. In this case control flow will reach the end of the function, thus leaving no more code to run. Once this happens the ->valid() method will return false and as such the iteration ends.

## Coroutines

The main thing that coroutines add to the above functionality is the ability to send values back to the generator. This turns the one-way communication from the generator to the caller into a two-way channel between the two.

Values are passed into the coroutine by calling its ->send() method instead of ->next(). An example of how this works is the following logger() coroutine:

```php
function logger($fileName) {
    $fileHandle = fopen($fileName, 'a');
    while (true) {
        fwrite($fileHandle, yield . "\n");
    }
}

$logger = logger(__DIR__ . '/log');
$logger->send('Foo');
$logger->send('Bar');
```

As you can see yield isn’t used as a statement here, but as an expression, i.e. it has a return value. The return value of yield is whatever was passed to ->send(). In this example yield will first return 'Foo' and then 'Bar'.

The above is an example where the yield acts as a mere receiver. It is possible to combine both usages, i.e. to both send _and_ receive. Here is an example of how this works:

```php
function gen() {
    $ret = (yield 'yield1');
    var_dump($ret);
    $ret = (yield 'yield2');
    var_dump($ret);
}

$gen = gen();
var_dump($gen->current());    // string(6) "yield1"
var_dump($gen->send('ret1')); // string(4) "ret1"   (the first var_dump in gen)
                              // string(6) "yield2" (the var_dump of the ->send() return value)
var_dump($gen->send('ret2')); // string(4) "ret2"   (again from within gen)
                                  // NULL               (the return value of ->send())
    
```

The exact order of the outputs can be a bit hard to understand at first, so make sure that you get why it comes out in exactly this way. There are two things I’d like to especially point out: First, the use of parentheses around the yield expression is no accident. These parentheses are required for technical reasons (though I have been considering adding an exception for assignments, just like it exists in Python). Secondly, you may have noticed that ->current() is used without calling ->rewind() first. If this is done then the rewind operation is performed implicitly.

## Cooperative multitasking

If reading the above logger() example you thought “Why would I use a coroutine for this? Why can’t I just use a normal class?”, then you were totally right. The example demonstrates the basic usage, but there aren’t really any advantages to using a coroutine in this context. This is the case for a lot of coroutine examples. As already mentioned in the introduction coroutines are a very powerful concept, but their applications are rare and often sufficiently complicated, making it hard to come up with simple and non-contrived examples.

What I decided to go for in this article is an implementation of cooperative multitasking using coroutines. The problem we’re trying to solve is that you want to run multiple tasks (or “programs”) concurrently. But a processor can only run one task at a time (not considering multi-core for the purposes of this post). Thus the processor needs to switch between the different tasks and always let one run “for a little while”.

The “cooperative” part of the term describes how this switching is done: It requires that the currently running task _voluntarily_ passes back control to the scheduler, so it can run another task. This is in contrast to “preemptive” multitasking where the scheduler can interrupt the task after some time whether it likes it or not. Cooperative multitasking was used in early versions of Windows (pre Win95) and Mac OS, but they later switched to using preemption. The reason should be fairly obvious: If you rely on a program to pass back control voluntarily, badly-behaving software can easily occupy the whole CPU for itself, not leaving a share for other tasks.

At this point you should see the connection between coroutines and task scheduling: The yield instruction provides a way for a task to interrupt itself and pass control back to the scheduler, so it can run some other task. Furthermore the yield can be used for communication between the task and the scheduler.

For our purposes a “task” will be a thin wrapper around the coroutine function:

```php
class Task {
    protected $taskId;
    protected $coroutine;
    protected $sendValue = ;
    protected $beforeFirstYield = true;

    public function __construct($taskId, Generator $coroutine) {
        $this->taskId = $taskId;
        $this->coroutine = $coroutine;
    }

    public function getTaskId() {
        return $this->taskId;
    }

    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = ;
            return $retval;
        }
    }

    public function isFinished() {
        return !$this->coroutine->valid();
    }
}
```

A task will be a coroutine tagged with a task ID. Using the setSendValue() method you can specify which value will be sent into it on the next resume (you’ll see what we need this for a bit later). The run() function really does nothing more than call the send() method on the coroutine. To understand why the additional beforeFirstYield flag is needed consider the following snippet:

```php
function gen() {
    yield 'foo';
    yield 'bar';
}

$gen = gen();
var_dump($gen->send('something'));

// As the send() happens before the first yield there is an implicit rewind() call,
// so what really happens is this:
$gen->rewind();
var_dump($gen->send('something'));

// The rewind() will advance to the first yield (and ignore its value), the send() will
// advance to the second yield (and dump its value). Thus we loose the first yielded value!
    
```

By adding the additional beforeFirstYield condition we can ensure that the value of the first yield is also returned.

The scheduler now has to do little more than cycle through the tasks and run them:

```php
class Scheduler {
    protected $maxTaskId = 0;
    protected $taskMap = []; // taskId => task
    protected $taskQueue;

    public function __construct() {
        $this->taskQueue = new SplQueue();
    }

    public function newTask(Generator $coroutine) {
        $tid = ++$this->maxTaskId;
        $task = new Task($tid, $coroutine);
        $this->taskMap[$tid] = $task;
        $this->schedule($task);
        return $tid;
    }

    public function schedule(Task $task) {
        $this->taskQueue->enqueue($task);
    }

    public function run() {
        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            $task->run();

            if ($task->isFinished()) {
                unset($this->taskMap[$task->getTaskId()]);
            } else {
                $this->schedule($task);
            }
        }
    }
}
```

The newTask() method creates a new task (using the next free task id) and puts it in the task map. Furthermore it schedules the task by putting it in the task queue. The run() method then walks this task queue and runs the tasks. If a task is finished it is dropped, otherwise it is rescheduled at the end of the queue.

Lets try out the scheduler with two simple (and very pointless) tasks:

```php
function task1() {
    for ($i = 1; $i <= 10; ++$i) {
        echo "This is task 1 iteration $i.\n";
        yield;
    }
}

function task2() {
    for ($i = 1; $i <= 5; ++$i) {
        echo "This is task 2 iteration $i.\n";
        yield;
    }
}

$scheduler = new Scheduler;

$scheduler->newTask(task1());
$scheduler->newTask(task2());

$scheduler->run();
```

Both tasks will just echo a message and then pass control back to the scheduler with yield. This is the resulting output:

```
    This is task 1 iteration 1.
    This is task 2 iteration 1.
    This is task 1 iteration 2.
    This is task 2 iteration 2.
    This is task 1 iteration 3.
    This is task 2 iteration 3.
    This is task 1 iteration 4.
    This is task 2 iteration 4.
    This is task 1 iteration 5.
    This is task 2 iteration 5.
    This is task 1 iteration 6.
    This is task 1 iteration 7.
    This is task 1 iteration 8.
    This is task 1 iteration 9.
    This is task 1 iteration 10.
```
    

The output is exactly as expected: For the first 5 iterations the tasks alternate, then the second task finishes and only the first task continues to run.

## Communicating with the scheduler

Now that the scheduler works we can turn to the next point on the agenda: Communication between the tasks and the scheduler. We will use the same method that processes use to talk to the operating system: Through system calls. The reason we need syscalls is that the operating system is on a different privilege level than the processes. So in order to perform privileged actions (like killing another process) there has to be some way to pass control back to the kernel, so it can perform said actions. Internally this is once again implemented using interruption instructions. Historically the generic int instruction was used, nowadays there are more specialized and faster syscall/sysenter instructions.

Our task scheduling system will reflect this design: Instead of simply passing the scheduler into the task (and thus allowing it to do whatever it wants) we will communicate via system calls passed through the yield expression. The yield here will act both as an interrupt and as a way to pass information to (and from) the scheduler.

To represent a system call I’ll use a small wrapper around a callable:

```php
class SystemCall {
    protected $callback;

    public function __construct(callable $callback) {
        $this->callback = $callback;
    }

    public function __invoke(Task $task, Scheduler $scheduler) {
        $callback = $this->callback; // Can't call it directly in PHP :/
        return $callback($task, $scheduler);
    }
}
```

It will behave just like any callable (using __invoke), but tells the scheduler to pass the calling task and itself into the function. To handle it we have to slightly modify the scheduler’s run method:

```php
public function run() {
    while (!$this->taskQueue->isEmpty()) {
        $task = $this->taskQueue->dequeue();
        $retval = $task->run();

        if ($retval instanceof SystemCall) {
            $retval($task, $this);
            continue;
        }

        if ($task->isFinished()) {
            unset($this->taskMap[$task->getTaskId()]);
        } else {
            $this->schedule($task);
        }
    }
}
```

The first system call will do nothing more than return the task ID:

```php
function getTaskId() {
    return new SystemCall(function(Task $task, Scheduler $scheduler) {
        $task->setSendValue($task->getTaskId());
        $scheduler->schedule($task);
    });
}
```

It does so by setting the tid as next send value and rescheduling the task. For system calls the scheduler does not automatically reschedule the task, we need to do it manually (you’ll see why a bit later). Using this new syscall we can rewrite the previous example:

```php
function task($max) {
    $tid = (yield getTaskId()); // <-- here's the syscall!
    for ($i = 1; $i <= $max; ++$i) {
        echo "This is task $tid iteration $i.\n";
        yield;
    }
}

$scheduler = new Scheduler;

$scheduler->newTask(task(10));
$scheduler->newTask(task(5));

$scheduler->run();
```

This will give the same output as with the previous example. Notice how the system call is basically done like any other call, but with a prepended yield. Two more syscalls for creating new tasks and killing them again:

```php
function newTask(Generator $coroutine) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($coroutine) {
            $task->setSendValue($scheduler->newTask($coroutine));
            $scheduler->schedule($task);
        }
    );
}

function killTask($tid) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) {
            $task->setSendValue($scheduler->killTask($tid));
            $scheduler->schedule($task);
        }
    );
}
```

The killTask function needs an additional method in the scheduler:

```php
public function killTask($tid) {
    if (!isset($this->taskMap[$tid])) {
        return false;
    }

    unset($this->taskMap[$tid]);

    // This is a bit ugly and could be optimized so it does not have to walk the queue,
    // but assuming that killing tasks is rather rare I won't bother with it now
    foreach ($this->taskQueue as $i => $task) {
        if ($task->getTaskId() === $tid) {
            unset($this->taskQueue[$i]);
            break;
        }
    }

    return true;
}
```

A small script to test the new functionality:

```php
function childTask() {
    $tid = (yield getTaskId());
    while (true) {
        echo "Child task $tid still alive!\n";
        yield;
    }
}

function task() {
    $tid = (yield getTaskId());
    $childTid = (yield newTask(childTask()));

    for ($i = 1; $i <= 6; ++$i) {
        echo "Parent task $tid iteration $i.\n";
        yield;

        if ($i == 3) yield killTask($childTid);
    }
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```

This will print the following:

```
    Parent task 1 iteration 1.
    Child task 2 still alive!
    Parent task 1 iteration 2.
    Child task 2 still alive!
    Parent task 1 iteration 3.
    Child task 2 still alive!
    Parent task 1 iteration 4.
    Parent task 1 iteration 5.
    Parent task 1 iteration 6.
```
    

The child is killed after three iterations, so that’s when the “Child is still alive” messages end. One should probably point about that this is not a real parent/child relationship, because the child can continue running even after the parent finished. Or the child could kill the parent. One could modify the scheduler to have a more hierarchic task structure, but I won’t implement that in this article.

There are many more process management calls one could implement, for example wait (which waits until a task has finished running), exec (which replaces the current task) and fork (which creates a clone of the current task). Forking is pretty cool and you can actually implement it with PHP’s coroutines, because they support cloning.

But I’ll leave these for the interested reader. Instead lets get to the next topic!

## Non-Blocking IO

A really cool application of our task management system obviously is … a web server. There could be one task listening a socket for new connections and whenever a new connection is made it would create a new task handling that connection.

The hard part about this is that normally socket operations like reading data are blocking, i.e. PHP will wait until the client has finished sending. For a web-server that’s obviously not good at all: It would mean that it can only handle a single connection at a time.

The solution is to make sure that the socket is “ready” before actually reading/writing to it. To find out which sockets are ready to read from or write to the [stream_select][3] function can be used.

First, lets add two new syscalls, which will cause a task to wait until a certain socket is ready:

```php
function waitForRead($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForRead($socket, $task);
        }
    );
}

function waitForWrite($socket) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($socket) {
            $scheduler->waitForWrite($socket, $task);
        }
    );
}
```

These syscalls are just proxies to the respective methods in the scheduler:

```php
// resourceID => [socket, tasks]
protected $waitingForRead = [];
protected $waitingForWrite = [];

public function waitForRead($socket, Task $task) {
    if (isset($this->waitingForRead[(int) $socket])) {
        $this->waitingForRead[(int) $socket][1][] = $task;
    } else {
        $this->waitingForRead[(int) $socket] = [$socket, [$task]];
    }
}

public function waitForWrite($socket, Task $task) {
    if (isset($this->waitingForWrite[(int) $socket])) {
        $this->waitingForWrite[(int) $socket][1][] = $task;
    } else {
        $this->waitingForWrite[(int) $socket] = [$socket, [$task]];
    }
}
```

The waitingForRead and waitingForWrite properties are just arrays containing the sockets to wait for and the tasks that are waiting for them. The interesting part is the following method, which actually checks whether the sockets are ready and reschedules the respective tasks:

```php
protected function ioPoll($timeout) {
    $rSocks = [];
    foreach ($this->waitingForRead as list($socket)) {
        $rSocks[] = $socket;
    }

    $wSocks = [];
    foreach ($this->waitingForWrite as list($socket)) {
        $wSocks[] = $socket;
    }

    $eSocks = []; // dummy

    if (!stream_select($rSocks, $wSocks, $eSocks, $timeout)) {
        return;
    }

    foreach ($rSocks as $socket) {
        list(, $tasks) = $this->waitingForRead[(int) $socket];
        unset($this->waitingForRead[(int) $socket]);

        foreach ($tasks as $task) {
            $this->schedule($task);
        }
    }

    foreach ($wSocks as $socket) {
        list(, $tasks) = $this->waitingForWrite[(int) $socket];
        unset($this->waitingForWrite[(int) $socket]);

        foreach ($tasks as $task) {
            $this->schedule($task);
        }
    }
}
```

The stream_select function takes arrays of read, write and except sockets to check (we’ll ignore that last category). The arrays are passed by reference and the function will only leave those elements in the arrays that changed state. We can then walk over those arrays and reschedule all tasks associated with them.

In order to regularly perform the above polling action we’ll add a special task in the scheduler:

```php
protected function ioPollTask() {
    while (true) {
        if ($this->taskQueue->isEmpty()) {
            $this->ioPoll();
        } else {
            $this->ioPoll(0);
        }
        yield;
    }
}
```

This task needs to be registered at some point, e.g. one could add $this->newTask($this->ioPollTask()) to the start of the run() method. Then it will work just like any other task, performing the polling operation once every full task cycle (this isn’t necessarily the best way to handle it). The ioPollTask will call ioPoll with a 0 second timeout, which means that stream_select will return right away (rather than waiting).

Only if the task queue is empty we use a timeout, which means that it will wait until some socket becomes ready. If we wouldn’t do this the polling task would just run again and again and again until a new connection is made. This would result in 100% CPU usage. It’s much more efficient to let the operating system do the waiting instead.

Writing the server is relatively easy now:

```php
function server($port) {
    echo "Starting server at port $port...\n";

    $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);

    stream_set_blocking($socket, 0);

    while (true) {
        yield waitForRead($socket);
        $clientSocket = stream_socket_accept($socket, 0);
        yield newTask(handleClient($clientSocket));
    }
}

function handleClient($socket) {
    yield waitForRead($socket);
    $data = fread($socket, 8192);

    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);

    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;

    yield waitForWrite($socket);
    fwrite($socket, $response);

    fclose($socket);
}

$scheduler = new Scheduler;
$scheduler->newTask(server(8000));
$scheduler->run();
```

This will accept connections to localhost:8000 and just send back a HTTP response with whatever it was sent. Doing anything “real” would be a lot more complicated (properly handling HTTP requests is way outside the scope of this article). The above snippet just demos the general concept.

You can try the server out using something like ab -n 10000 -c 100 localhost:8000/. This will send 10000 requests to it with 100 of them arriving concurrently. Using these numbers I get a median response time of 10ms. But there is an issue with a few requests being handled _really_ slowly (like 5 seconds), that’s why the total throughput is only 2000 reqs/s (with a 10ms response time it should be more like 10000 reqs/s). With higher concurrency count (e.g. -c 500) it mostly still works well, but some connections will throw a “Connection reset by peer” error. As I know very little about this low-level socket stuff I didn’t try to figure out what the issue is.

## Stacked coroutines

If you would try to build some larger system using our scheduling system you would soon run into a problem: We are used to breaking up code into smaller functions and calling them. But with coroutines this is no longer possible. E.g. consider the following code:

```php
function echoTimes($msg, $max) {
    for ($i = 1; $i <= $max; ++$i) {
        echo "$msg iteration $i\n";
        yield;
    }
}

function task() {
    echoTimes('foo', 10); // print foo ten times
    echo "---\n";
    echoTimes('bar', 5); // print bar five times
    yield; // force it to be a coroutine
}

$scheduler = new Scheduler;
$scheduler->newTask(task());
$scheduler->run();
```

This code tries to put the recurring “output n times” code into a separate coroutine and then invoke it from the main task. But this won’t work. As mentioned at the very beginning of this article calling a generator (or coroutine) will not actually do anything, it will only return an object. This also happens in the above case. The echoTimes calls won’t do anything than return an (unused) coroutine object.

In order to still allow this we need to write a small wrapper around the bare coroutines. I’ll call this a “stacked coroutine” because it will manage a stack of nested coroutine calls. It will be possible to call sub-coroutines by yielding them:

```php
$retval = (yield someCoroutine($foo, $bar));
    
```

The subcoroutines will also be able to return a value, again by using yield:

```php
yield retval("I'm a return value!");
    
```

The retval function does nothing more than returning a wrapper around the value which will signal that it’s a return value:

```php
class CoroutineReturnValue {
    protected $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function getValue() {
        return $this->value;
    }
}

function retval($value) {
    return new CoroutineReturnValue($value);
}
```

In order to turn a coroutine into a stacked coroutine (which supports subcalls) we’ll have to write another function (which is _obviously_ yet-another-coroutine):

```php
function stackedCoroutine(Generator $gen) {
    $stack = new SplStack;

    for (;;) {
        $value = $gen->current();

        if ($value instanceof Generator) {
            $stack->push($gen);
            $gen = $value;
            continue;
        }

        $isReturnValue = $value instanceof CoroutineReturnValue;
        if (!$gen->valid() || $isReturnValue) {
            if ($stack->isEmpty()) {
                return;
            }

            $gen = $stack->pop();
            $gen->send($isReturnValue ? $value->getValue() : NULL);
            continue;
        }

        $gen->send(yield $gen->key() => $value);
    }
}
```

This function acts as a simple proxy between the caller and the currently running subcoroutine. This is handled in the $gen->send(yield $gen->key() => $value); line. Additionally it checks whether a return value is a generator, in which case it will start running it and pushes the previous coroutine on the stack. Once it gets a CoroutineReturnValue it will pop the stack again and continue executing the previous coroutine.

In order to make the stacked coroutines usable in tasks the $this->coroutine = $coroutine; line in the Task constructor needs to be replaced with $this->coroutine = stackedCoroutine($coroutine);.

Now we can improve the webserver example from above a bit by grouping the wait+read (and wait+write and wait+accept) actions into functions. To group the related functionality I’ll use a class:

```php
class CoSocket {
    protected $socket;

    public function __construct($socket) {
        $this->socket = $socket;
    }

    public function accept() {
        yield waitForRead($this->socket);
        yield retval(new CoSocket(stream_socket_accept($this->socket, 0)));
    }

    public function read($size) {
        yield waitForRead($this->socket);
        yield retval(fread($this->socket, $size));
    }

    public function write($string) {
        yield waitForWrite($this->socket);
        fwrite($this->socket, $string);
    }

    public function close() {
        @fclose($this->socket);
    }
}
```

Now the server can be rewritten a bit cleaner:

```php
function server($port) {
    echo "Starting server at port $port...\n";

    $socket = @stream_socket_server("tcp://localhost:$port", $errNo, $errStr);
    if (!$socket) throw new Exception($errStr, $errNo);

    stream_set_blocking($socket, 0);

    $socket = new CoSocket($socket);
    while (true) {
        yield newTask(
            handleClient(yield $socket->accept())
        );
    }
}

function handleClient($socket) {
    $data = (yield $socket->read(8192));

    $msg = "Received following request:\n\n$data";
    $msgLength = strlen($msg);

    $response = <<<RES
HTTP/1.1 200 OK\r
Content-Type: text/plain\r
Content-Length: $msgLength\r
Connection: close\r
\r
$msg
RES;

    yield $socket->write($response);
    yield $socket->close();
}
```

## Error handling

As a good programmer you obviously noticed that the above examples all lack error handling. Pretty much every socket operation is fallible and can produce errors. I obviously did this because error handling is really tedious (especially for sockets!) and would easily blow up the code size by a few factors.

But still I’d like to cover how error handling for coroutines works in general: Coroutines provide the ability to throw exceptions _inside_ them using the throw() method. As of this writing this method does not yet exist in PHP’s implementation, but I will commit it later today.

The throw() method takes an exception and throws it at the current suspension point in the coroutine. Consider this code:

```php
function gen() {
    echo "Foo\n";
    try {
        yield;
    } catch (Exception $e) {
        echo "Exception: {$e->getMessage()}\n";
    }
    echo "Bar\n";
}

$gen = gen();
$gen->rewind();                     // echos "Foo"
$gen->throw(new Exception('Test')); // echos "Exception: Test"
                                        // and "Bar"
    
```

This is really awesome for our purposes, because we can make system calls and subcoroutine calls throw exceptions. For the system calls the Scheduler::run() method needs a small adjustment:

```php
if ($retval instanceof SystemCall) {
    try {
        $retval($task, $this);
    } catch (Exception $e) {
        $task->setException($e);
        $this->schedule($task);
    }
    continue;
}
```

And the Task class needs to handle throw calls too:

```php
class Task {
    // ...
    protected $exception = ;

    public function setException($exception) {
        $this->exception = $exception;
    }

    public function run() {
        if ($this->beforeFirstYield) {
            $this->beforeFirstYield = false;
            return $this->coroutine->current();
        } elseif ($this->exception) {
            $retval = $this->coroutine->throw($this->exception);
            $this->exception = ;
            return $retval;
        } else {
            $retval = $this->coroutine->send($this->sendValue);
            $this->sendValue = ;
            return $retval;
        }
    }

    // ...
}
```

Now we can start throwing exceptions from system calls! E.g. for the killTask call, lets throw an exception if the passed task ID is invalid:

```php
function killTask($tid) {
    return new SystemCall(
        function(Task $task, Scheduler $scheduler) use ($tid) {
            if ($scheduler->killTask($tid)) {
                $scheduler->schedule($task);
            } else {
                throw new InvalidArgumentException('Invalid task ID!');
            }
        }
    );
}
```

Try it out:

```php
function task() {
    try {
        yield killTask(500);
    } catch (Exception $e) {
        echo 'Tried to kill task 500 but failed: ', $e->getMessage(), "\n";
    }
}
```

Sadly this won’t work properly yet, because the stackedCoroutine function doesn’t handle the exception correctly. To fix it the function needs some modifications:

```php
function stackedCoroutine(Generator $gen) {
    $stack = new SplStack;
    $exception = ;

    for (;;) {
        try {
            if ($exception) {
                $gen->throw($exception);
                $exception = ;
                continue;
            }

            $value = $gen->current();

            if ($value instanceof Generator) {
                $stack->push($gen);
                $gen = $value;
                continue;
            }

            $isReturnValue = $value instanceof CoroutineReturnValue;
            if (!$gen->valid() || $isReturnValue) {
                if ($stack->isEmpty()) {
                    return;
                }

                $gen = $stack->pop();
                $gen->send($isReturnValue ? $value->getValue() : NULL);
                continue;
            }

            try {
                $sendValue = (yield $gen->key() => $value);
            } catch (Exception $e) {
                $gen->throw($e);
                continue;
            }

            $gen->send($sendValue);
        } catch (Exception $e) {
            if ($stack->isEmpty()) {
                throw $e;
            }

            $gen = $stack->pop();
            $exception = $e;
        }
    }
}
```

## Wrapping up

In this article we built a task scheduler using cooperative multitasking, including the ability to perform “system calls”, doing non-blocking IO operations and handling errors. The really cool thing about all this is that the resulting code for the tasks looks totally synchronous, even though it is performing a lot of asynchronous operations. If you want to read data from a socket you don’t have to pass some callback or register an event listener. Instead you write yield $socket->read(). Which is basically what you would normally do too, just with a yield in front of it.

When I first heard about all this I found this concept _totally awesome_ and that’s what motivated me to implement it in PHP. At the same time I find coroutines really scary. There is a thin line between awesome code and a total mess and I think coroutines sit exactly on that line. It’s hard for me to say whether writing async code in the way outlined above is really beneficial.

In any case, I think it’s an interesting topic and I hope you found it interesting too. Comments welcome :)

If you liked this article, you may want to [browse my other articles][4] or [follow me on Twitter][5].

[0]: http://de2.php.net/manual/en/language.generators.overview.php
[1]: http://blog.ircmaxell.com/2012/07/what-generators-can-do-for-you.html
[2]: http://sheriframadan.com/2012/10/test-drive-php-5-5-a-sneak-peek/#generators
[3]: http://de3.php.net/manual/en/function.stream-select.php
[4]: /
[5]: https://twitter.com/#!/nikita_ppv