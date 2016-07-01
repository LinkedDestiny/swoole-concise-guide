<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/24
 * Time: 下午2:20
 */
$client = new swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC); //异步非阻塞
$client->set(array(
    'open_eof_check' => true,
    'package_eof' => "\r\n",
    'open_eof_split' => true,
));

$client->on("connect", function(swoole_client $cli) {
    $data = str_repeat("1234567890" , 8);

    $cli->send($data . "\r\n");
});
$client->on("receive", function(swoole_client $cli, $data){
    echo "Receive: $data";
});

$client->on("error", function(swoole_client $cli){
    echo "error\n";
});

$client->on("close", function(swoole_client $cli){
    echo "Connection close\n";
});

$client->connect('127.0.0.1', 9502);