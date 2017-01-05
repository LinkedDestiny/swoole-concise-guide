# Timer定时器

---

## 定时器原理

Timer定时器是Swoole扩展提供的一个毫秒级定时器，其作用是每隔指定的时间间隔之后执行一次指定的回调函数，以实现定时执行任务的功能。
新版本的Swoole中，定时器是基于epoll方法的timeout机制实现的，不再依赖于单独的定时线程，准确度更高。同时，Swoole扩展使用最小堆存储定时器，减少定时器的检索次数，提高了运行效率。

## 定时器使用

在Swoole中，定时器的函数原型如下：

```php

// function onTimer(int $timer_id, mixed $params = null); // 回调函数的原型
int swoole_timer_tick(int $ms, mixed $callback, mixed $param = null);
int swoole_server::tick(int $ms, mixed $callback, mixed $param = null);

// function onTimer(); // 回调函数的原型（不接受任何参数）
void swoole_timer_after(int $after_time_ms, mixed $callback_function);
void swoole_server::after(int $after_time_ms, mixed $callback_function);
```

tick定时器是一个永久定时器，使用tick方法创建的定时器会一直运行，每隔指定的毫秒数之后执行一次callback函数。在创建定时器的时候，可以通过tick函数的第三个参数，传递一些自定义参数到callback回调函数中。另外，也可以使用PHP的闭包（use关键字）实现传参。具体实例如下：

```php
$str = "Say ";
$timer_id = swoole_timer_tick( 1000 , function($timer_id , $params) use ($str) {
    echo $str . $params;  // 输出“Say Hello”
    
} , "Hello" );

```

tick函数会返回定时器的id。当我们不再需要某个定时器的时候，可以根据这个id，调用`swoole_timer_clear`函数删除定时器。需要注意的是，创建的定时器是不能跨进程的，因此，在一个Worker进程中创建的定时器，也只能在这个Worker进程中删除，这一点一定要注意（使用`$worker_id`变量来区分Worker进程）；

after定时器是一个临时定时器。使用after方法创建的定时器仅在指定毫秒数之后执行一次callback函数，执行完成后定时器就会删除。after定时器的回调函数不接受任何参数，可以通过闭包方式传递参数，也可以通过类成员变量的方式传递。具体实例如下：

```php

class Test
{
    private $str = "Say Hello";
    public function onAfter()
    {
        echo $this->str; // 输出”Say Hello“
    }
}

$test = new Test();
swoole_timer_after(1000, array($test, "onAfter"); // 成员变量

swoole_timer_after(2000, function() use($test){ // 闭包
    $test->onAfter(); // 输出”Say Hello“
});

```
