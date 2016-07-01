<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/7/1
 * Time: 下午1:39
 */

class SimpleTask
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 1,

            'open_eof_split' => true,
            'package_eof' => "\r\n",

            'task_worker_num' => 2,
        ));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->on('Task', array($this, 'onTask'));
        $this->serv->on('Finish', array($this, 'onFinish'));

        $this->serv->start();
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo "Client {$fd} connect\n";
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";

        echo "Send Task\n";
        $serv->task("Hello Task");
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }

    /**
     * @param $serv swoole_server swoole_server对象
     * @param $task_id int 任务id
     * @param $from_id int 投递任务的worker_id
     * @param $data string 投递的数据
     */
    public function onTask($serv, $task_id, $from_id, $data)
    {
        echo "Handle Task {$task_id} : {$data}\n";

        //$serv->finish("Task End");
        return "Task End";
    }

    public function onFinish(swoole_server $serv, $task_id, $data)
    {
        echo "Task {$task_id} finish: {$data}\n";
    }
}

new SimpleTask();