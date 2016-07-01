# Task Worker

---

<!-- toc -->

## 简介

Task Worker是Swoole中一种特殊的工作进程，该进程的作用是处理一些耗时较长的任务，以达到释放Worker进程的目的。Worker进程可以通过`swoole_server`对象的task方法投递一个任务到Task Worker进程，其流程如下所示：

<!-- {% sequence %}

Worker->Task Worker: task()
Note right of Task Worker: onTask()
Task Worker->Worker: finish()
Note left of Worker: onFinish()

{% endsequence %} -->

Worker进程通过Unix Sock管道将数据发送给Task Worker，这样Worker进程就可以继续处理新的逻辑，无需等待耗时任务的执行。需要注意的是，由于Task Worker是独立进程，因此无法直接在两个进程之间共享全局变量，需要使用Redis、MySQL或者swoole_table来实现进程间共享数据。

## 实例

要使用Task Worker，需要进行一些必要的操作。

首先，需要设置swoole_server的配置参数：

```php
$serv->set(array(
    'task_worker_num' => 2, // 设置启动2个task进程
));
```

接着，绑定必要的回调函数：

```php
$serv->on('Task', 'onTask');
$serv->on('Finish','onFinish');
```
其中两个回调函数的原型如下所示：

```php
/**
 * @param $serv swoole_server swoole_server对象
 * @param $task_id int 任务id
 * @param $from_id int 投递任务的worker_id
 * @param $data string 投递的数据
 */
function onTask(swoole_server $serv, $task_id, $from_id, $data);

/**
 * @param $serv swoole_server swoole_server对象
 * @param $task_id int 任务id
 * @param $data string 任务返回的数据
 */
function onFinish(swoole_server $serv, $task_id, $data)；
```

在实际逻辑中，当需要发起一个任务请求时，可以使用如下方法调用：

```php
$data = "task data";
$serv->task($data , -1 ); // -1代表不指定task进程

// 在1.8.6+的版本中，可以动态指定onFinish函数
$serv->task($data, -1, function (swoole_server $serv, $task_id, $data) {
    echo "Task Finish Callback\n";
});
```