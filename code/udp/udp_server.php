<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/21
 * Time: 下午3:11
 */

$server = new swoole_server('0.0.0.0', 9501, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
$server->set(['worker_num' => 2]);
$server->on('Packet', function (swoole_server $serv, $data, $addr)
{
    // save $addr
    swoole_timer_tick(1000, function() use($serv, $addr){
        $serv->sendto($addr['address'], $addr['port'], "Swoole: Hello\n");
    });

});
//$server->on('receive', function (swoole_server $serv, $fd, $reactor_id, $data)
//{
//    var_dump($data);
//    var_dump($serv->connection_info($fd, $reactor_id));
//});
$server->start();