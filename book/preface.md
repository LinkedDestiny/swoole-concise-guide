# 写在前面的话

本书默认读者已具备如下能力：

* 熟练使用PHP语言
* 熟练使用MySQL、Redis数据库
* 熟练使用Linux操作系统
* 基本了解Unix网络编程相关知识（参阅《Unix网络编程（卷1）》）
* 基本的gdb使用

第一章将讲解如何配置PHP&Swoole的开发环境，会一步步列出安装所需的依赖和命令。

第二章将讲解Swoole的基本功能和配置选项，包括Worker进程、Task Worker进程、Timer计时器、Process进程、swoole_table内存表等，也会讲解这些功能的基本使用方法。

第三章将讲解Swoole的内置协议部分，讲解如何自定义TCP的应用层通信协议。同时也会介绍Swoole内置的多种协议解析方式，比如Http服务器、WebSocket服务器等等。

第四章将讲解Swoole的一些实战用法，比如使用Task进程进行异步任务处理、使用Process执行监控命令等

第五章将讲解Swoole的一些相关框架，比如ZPHP，Hprose，Dora-rpc等等

第六章将讲解Swoole与一些现有框架的结合，比如Swoole-Yaf，Swoole-Phalcon等

第七章开始将讲解Swoole实战，通过一些实际项目来深入了解Swoole的应用。（构思中）