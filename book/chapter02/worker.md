# Worker进程

---

<!-- toc -->

## Swoole进程模型

首先，我们需要了解一下Swoole的进程模型。Swoole是一个多进程模式的框架（可以类比Nginx的进程模型），当启动一个Swoole应用时，一共会创建2 + n + m个进程，其中n为Worker进程数，m为TaskWorker进程数，2为一个Master进程和一个Manager进程，它们之间的关系如下图所示。

![structure.png-18kB](http://static.zybuluo.com/Lancelot2014/xpatz2wxco47xrzi5xc3keni/structure.png)

其中，Master进程为主进程，该进程会创建Manager进程、Reactor线程等工作进/线程。

* Reactor线程实际运行epoll实例，用于accept客户端连接以及接收客户端数据；
* Manager进程为管理进程，该进程的作用是创建、管理所有的Worker进程和TaskWorker进程。

## Worker进程简介
Worker进程作为Swoole的工作进程，所有的业务逻辑代码均在此进程上运行。当Reactor线程接收到来自客户端的数据后，会将数据打包通过管道发送给某个Worker进程（数据分配方法见[dispatch_mode](http://www.baidu.com)）。


## Worker进程生命周期

一个Worker进程的生命周期如图所示：

<!-- {% flowchart %}
st=>start: Create
start=>operation: onWorkerStart
recv=>operation: onReceive/onConnect/onClose
op=>operation: Receive and Handle Data
cond=>condition: Max Request or Error
stop=>operation: onWorkerStop
e=>end: Stop

st->start->recv->op->cond
cond(yes)->stop
cond(no)->recv
stop->e
{% endflowchart %}
 -->
当一个Worker进程被成功创建后，会调用`onWorkerStart`回调，随后进入事件循环等待数据。当通过回调函数接收到数据后，开始处理数据。如果处理数据过程中出现严重错误导致进程退出，或者Worker进程处理的总请求数达到指定上限，则Worker进程调用`onWorkerStop`回调并结束进程。

