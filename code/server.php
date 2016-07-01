<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/24
 * Time: 下午2:07
 */

class Server
{
    private $serv;

    private $tcp_connect = [];

    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode'=> 1 ,
            'open_eof_split' => true,
            'package_max_length' => 819200,
            'open_eof_check' => true,
            'package_eof' => "\r\n",
        ));


        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        $len = strlen($data);
        echo "Get Message From Client {$fd}:{$len}\n";
        var_dump($serv->exist($fd));
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

}

new Server();