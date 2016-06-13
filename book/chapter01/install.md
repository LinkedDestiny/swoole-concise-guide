<!-- toc -->
# 环境搭建

---

[TOC]

# Linux环境下安装

Linux操作系统通常都有自己的包管理软件（Ubuntu的apt-get，CentOS的yum，Mac OSX的HomeBrew等），因此一般情况下可以通过这些包管理软件直接安装PHP。但是这样安装的PHP不太适用于运行Swoole，因此本章将介绍如何通过源码编译安装。

## 编译环境
想要编译安装PHP首先需要安装对应的编译工具。
Ubuntu上使用如下命令安装编译工具和依赖包：

```shell
sudo apt-get install \
build-essential \
gcc \
g++ \
autoconf \
libiconv-hook-dev \
libmcrypt-dev \
libxml2-dev \
libmysqlclient-dev \
libcurl4-openssl-dev \
libjpeg8-dev \
libpng12-dev \
libfreetype6-dev \
```

## PHP安装

[PHP下载地址](http://php.net/)
在这里挑选你想用的版本即可。下载源码包后，解压至本地任意目录（保证读写权限）。

使用如下命令编译安装PHP：

```shell
cd php-5.6.22/
./configure --prefix=/usr/local/php \
--with-config-file-path=/etc/php \
--enable-fpm \
--enable-pcntl \
--enable-mysqlnd \
--enable-opcache \
--enable-sockets \
--enable-sysvmsg \
--enable-sysvsem \
--enable-sysvshm \
--enable-shmop \
--enable-zip \
--enable-soap \
--enable-xml \
--enable-mbstring \
--disable-rpath \
--disable-debug \
--disable-fileinfo \
--with-mysql=mysqlnd \
--with-mysqli=mysqlnd \
--with-pdo-mysql=mysqlnd \
--with-pcre-regex \
--with-iconv \
--with-zlib \
--with-mcrypt \
--with-gd \
--with-openssl \
--with-mhash \
--with-xmlrpc \
--with-curl \
--with-imap-ssl

sudo make
sudo make install
sudo mkdir /etc/php
sudo cp php.ini-development /etc/php/php.ini
```
注意，以上PHP编译选项根据实际情况可调整。

另外，还需要将PHP的可执行目录添加到环境变量中。
使用Vim/Sublime打开~/.bashrc，在末尾添加如下内容：
```shell
export PATH=/usr/local/php/bin:$PATH
export PATH=/usr/local/php/sbin:$PATH
```
保存后，终端输入命令：
```bash
source ~/.bashrc
```
此时即可通过`php --version`查看php版本。

# Mac环境下安装
Mac系统自带PHP，但是Mac上对于OpenSSL的相关功能做了一些限制，使用了一个`Secure Transport`来取代OpenSSL。因此仍然建议重新编译安装PHP环境。

## 安装OpenSSL
Mac原装的0.9.8版本的OpenSSL使用的时候会有些Warning，反正我看不惯……

安装命令：

```shell
brew install openssl
```
安装之后，还需要链接新的openssl到环境变量中。
```shell
brew link --force openssl
```

## 安装Curl
Mac系统原装的Curl默认使用了Secure Transport，导致通过option函数设置的证书全部无效。果断重新安装之。

```shell
brew install curl --with-openssl && brew link curl --force
```

## 安装PHP
PHP官网上下载某个版本的PHP（我选择的是5.6.22），使用如下命令编译安装。

```shell
cd /path/to/php/
./configure 
--prefix=/usr/local/php 
--with-config-file-path=/etc/php 
--with-openssl=/usr/local/Cellar/openssl/1.0.2g/ 
--with-curl=/usr/local/Cellar/curl/7.48.0/

make && make install
```
这里我仅列出两个需要特殊设置的选项`with-openssl`和`with-curl`。
安装完成后，执行如下命令：

```shell
sudo cp /usr/local/php/bin/php /usr/bin/
sudo cp /usr/local/php/bin/phar* /usr/bin/
sudo cp /usr/local/php/bin/php-config /usr/bin/
sudo cp /usr/local/php/bin/phpize /usr/bin/
```

随后，设置php.ini
```shell
sudo mkdir /etc/php
sudo cp php.ini.development /etc/php/php.ini
```

# Swoole扩展安装
[Swoole扩展下载地址](https://github.com/swoole/swoole-src/releases)
解压源码至任意目录，执行如下命令：
```shell
cd swoole-src-swoole-1.7.6-stable/
phpize
./configure
sudo make
sudo make install
```

> swoole的./configure有很多额外参数，可以通过./configure --help命令查看,这里均选择默认项)

安装完成后，进入/etc/php目录下，打开php.ini文件，在其中加上如下一句：
```bash
extension=swoole.so
```
随后在终端中输入命令`php -m`查看扩展安装情况。如果在列出的扩展中看到了swoole，则说明安装成功。