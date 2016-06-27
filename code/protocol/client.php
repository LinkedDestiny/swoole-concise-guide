<?php
require __DIR__ . "/WebSocketClient.php";
$host = '127.0.0.1';
$prot = 9501;

$client = new WebSocketClient($host, $prot);
$data = $client->connect();
//echo $data;
$data = "data";

$client->send("hello swoole data:" . $data);
$tmp = $client->recv();
