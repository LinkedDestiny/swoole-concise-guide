<?php
/**
 * Created by PhpStorm.
 * User: lidanyang
 * Date: 16/6/16
 * Time: 下午1:59
 */

swoole_timer_tick(2000, function(){
   $process = new swoole_process(function(swoole_process $worker){
       echo "new process start\n";
       // $worker->name("new worker");
       sleep(1);
       echo "process shtudown\n";
       $worker->exit(0);
   });

    $pid = $process->start();
    swoole_process::wait();
});