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
        $this->serv = new swoole_websocket_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
            'max_request' => 10000,
            'dispatch_mode' => 2,
            'debug_mode'=> 1 ,
        ));

        $this->serv->on('Message', array($this, 'onMessage'));
        $this->serv->on('Close', array($this, 'onWsClose'));

        $this->port = $this->serv->listen("0.0.0.0", 9502, SWOOLE_TCP);
        $this->port->set([
            'open_eof_split' => true,
            'package_eof' => "\r\n",
        ]);
        $this->port->on('Connect', array($this, 'onConnect'));
        $this->port->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        var_dump($serv->exist($fd));
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

    public function onMessage( swoole_server $server, swoole_websocket_frame $frame) {
        echo "Get Message From WS {$frame->fd}:{$frame->data}\n";
        foreach($server->connections as $fd)
        {
            $connection_info = $server->connection_info($fd);
            if( $connection_info['server_port'] == 9502 )
            {
                var_dump("hello");
                $server->send($fd, "hello\r\n");
            }
        }
    }

    public function onOpen(swoole_websocket_server $svr, swoole_http_request $req)
    {

    }
    public function onWsClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}

new Server();