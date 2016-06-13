# 多端口监听

---

## 多端口监听

在实际运用场景中，服务器可能需要监听不同host下的不同端口。比如，一个应用服务器，可能需要监听外网的服务端口，同时也需要监听内网的管理端口。在Swoole中，可以轻松的实现这样的功能。 Swoole提供了addlistener函数用于给服务器添加一个需要监听的host及port，并指定对应的Socket类型（TCP，UDP，Unix Socket以及对应的IPv4和IPv6版本）。 代码如下：

```php
$serv = new swoole_server("192.168.1.1", 9501); // 监听外网的9501端口
$serv->addlistener("127.0.0.1", 9502 , SWOOLE_TCP); // 监听本地的9502端口
$serv->start(); // addlistener必须在start前调用
```

此时，swoole_server就会同时监听两个host下的两个端口。这里要注意的是，来自两个端口的数据会在同一个`onReceive`回调函数中获取到，这时就要用到swoole的另一个成员函数connection_info，通过这个函数获取到fd的from_port，就可以判定消息的类型。

```php
$info = $serv->connection_info($fd, $from_id);
//来自9502的内网管理端口
if($info['from_port'] == 9502) {
    $serv->send($fd, "welcome admin\n");
}
//来自外网
else {
    $serv->send($fd, 'Swoole: '.$data);
}

```

## 多端口混合协议接听       

通过上面的实例可以看到，使用上面的方法进行多端口监听有诸多的局限性：协议单一，回调函数无法区分等。在实际应用中，我们往往希望服务能够同时监听两个端口，并且两个端口分别采用不同的协议，比如一个端口采用RPC协议提供服务，另一个端口提供Http协议用于Web管理页面。
因此，Swoole从1.8.0版本开始提供了一套全新的多端口监听方式。在1.8.0以后的版本，Server可以监听多个端口，每个端口都可以设置不同的协议处理方式(set)和回调函数(on)
开始监听新端口的代码如下：

```php
$port1 = $server->listen("127.0.0.1", 9501, SWOOLE_SOCK_TCP);
$port2 = $server->listen("127.0.0.1", 9502, SWOOLE_SOCK_UDP);
$port3 = $server->listen("127.0.0.1", 9503, SWOOLE_SOCK_TCP | SWOOLE_SSL);
```

可以看到，新添加的监听端口可以设置多种属性，监听的IP，端口号，TCP或者UDP，是否需要SSL加密。
除此之外，每个新建立的Port对象还可以分别设置配置选项，如下所示：

```php
$port1->set(    // 开启固定包头协议
    'open_length_check' => true,
    'package_length_type' => 'N',
    'package_length_offset' => 0,
    'package_max_length' => 800000,
);

$port3->set( // 开启EOF协议并设置SSL文件
    'open_eof_split' => true,
    'package_eof' => "\r\n",
    'ssl_cert_file' => 'ssl.cert',
    'ssl_key_file' => 'ssl.key',
);
```

除了协议不同，每个Port还可以设置自己独有的回调函数，由此避免了在同一个回调函数里针对数据来源进行判定的问题。

```php
$port1->on('receive', function ($serv, $fd, $from_id, $data) {
    $serv->send($fd, 'Swoole: '.$data);
    $serv->close($fd);
});
$port3->on('receive', function ($serv, $fd, $from_id, $data) {
    echo "Hello {$fd} : {$data}\n";
});
```

### 注意事项

* 未设置协议处理选项的监听端口，默认使用无协议模式
* 未设置回调函数的监听端口，使用$server对象的回调函数
* 监听端口返回的对象类型为swoole_server_port
* 不同监听端口的回调函数，仍然是相同的Worker进程空间内执行
* 主服务器是WebSocket或Http协议，新监听的TCP端口默认会继承主Server的协议设置。必须单独调用`set`方法设置新的协议才会启用新协议