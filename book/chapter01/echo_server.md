# 搭建Echo服务器 Start With An Echo Server!

---

[TOC]

所有讲解网络通信编程的书籍都会最先讲解如何编写一个Echo服务器，本书也不例外。本章将讲解如何快速编写一个基于Swoole扩展的Echo服务器。
Almost all network programming books start with teaching how to build an Echo Server. Well, we will do the same for this guide. In this chapter, we will learn how to quickly build an Echo Server based on Swoole!

## 服务端 Server
创建一个`Server.php`文件并输入如下内容：
Create a file named 'Server.php' and type in following codes:
```php
// Server
class Server
{
    private $serv;

    public function __construct() {
        $this->serv = new swoole_server("0.0.0.0", 9501);
        $this->serv->set(array(
            'worker_num' => 8,
            'daemonize' => false,
        ));

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));

        $this->serv->start();
    }

    public function onStart( $serv ) {
        echo "Start\n";
    }

    public function onConnect( $serv, $fd, $from_id ) {
        $serv->send( $fd, "Hello {$fd}!" );
    }

    public function onReceive( swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd}:{$data}\n";
        $serv->send($fd, $data);
    }

    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} close connection\n";
    }
}
// 启动服务器 Start the server
$server = new Server();
```

## 客户端 Client

创建一个`Client.php`文件并输入如下内容：
Create a file named 'Client.php' and type in following codes:
```php
<?php
class Client
{
	private $client;

	public function __construct() {
		$this->client = new swoole_client(SWOOLE_SOCK_TCP);
	}
	
	public function connect() {
		if( !$this->client->connect("127.0.0.1", 9501 , 1) ) {
			echo "Error: {$this->client->errMsg}[{$this->client->errCode}]\n";
		}
		
		fwrite(STDOUT, "请输入消息 Please input msg：");  
		$msg = trim(fgets(STDIN));
		$this->client->send( $msg );

        $message = $this->client->recv();
        echo "Get Message From Server:{$message}\n";
	}
}

$client = new Client();
$client->connect();
```

## 运行 Run it!
在Terminal下执行命令`php Server.php`即可启动服务器，在另一个Terminal下执行`php Client.php`，输入要发送的内容，即可发送消息到服务器，并收到来自服务器的消息。
To start the Echo Server? Simply run command 'php Server.php' in Terminal. Then run command 'php Client.php' in another Terminal window and input whatever you want to send. Aha! See what's coming up in your Server's Terminal? Your messages are sent from the Client to the Server.
