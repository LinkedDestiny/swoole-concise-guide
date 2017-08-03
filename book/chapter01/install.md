<!-- toc -->
# 环境搭建 Environment Setup

---

[TOC]

# Linux环境下安装 Setup for Linux

Linux操作系统通常都有自己的包管理软件（Ubuntu的apt-get，CentOS的yum，Mac OSX的HomeBrew等），因此一般情况下可以通过这些包管理软件直接安装PHP。但是这样安装的PHP不太适用于运行Swoole，因此本章将介绍如何通过源码编译安装。
Linux has usually got its own package management tools (like apt-get for Ubuntu, yum for CentOS, HomeBrew for Mac OSX...). So for most of the cases, we can directly install php using those tools. But for Swoole, compiling the source codes to install is suggested.

## 编译环境 Compiling Environment
想要编译安装PHP首先需要安装对应的编译工具。
Ubuntu上使用如下命令安装编译工具和依赖包：
To successfully compile PHP, we have to get our compilers and other related tools ready.
We can install compiling tools and dependencies by using commands:

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

## PHP安装 PHP Installation

[PHP下载地址 Download](http://php.net/)
在这里挑选你想用的版本即可。下载源码包后，解压至本地任意目录（保证读写权限）。
Download PHP of the version you want. Once done with that, extract it to any R/W directory.

使用如下命令编译安装PHP：
Use following commands to compile and install PHP:

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
To be sure, any of these compiling options is adjustable according to your specific needs.

另外，还需要将PHP的可执行目录添加到环境变量中。
使用Vim/Sublime打开~/.bashrc，在末尾添加如下内容：
Also, we need to add PHP's bin directory to environment variables.
Open file '~/.bashrc' by Vim/Sublime. And add following contents to the end of the file:

```shell
export PATH=/usr/local/php/bin:$PATH
export PATH=/usr/local/php/sbin:$PATH
```
保存后，终端输入命令：
Having saved that, run following commands in Terminal:

```bash
source ~/.bashrc
```
此时即可通过`php --version`查看php版本。
Right now, you can check out the PHP version using `php --version`!

# Mac环境下安装 Setup for Mac
Mac系统自带PHP，但是Mac上对于OpenSSL的相关功能做了一些限制，使用了一个`Secure Transport`来取代OpenSSL。因此仍然建议重新编译安装PHP环境。
MacOS comes with a certain version of PHP. But OpenSSL for that version is restricted and got replaced by `Secure Transport`.  For that matter, reinstalling in a source code compiling way is still highly recommanded.

## 安装OpenSSL OpenSSL Installation
Mac原装的0.9.8版本的OpenSSL使用的时候会有些Warning，反正我看不惯……
The OpenSSL that comes with MacOS, while using, will produce some warnings. And why is that? Kill me to know...
安装命令：
Installation Commands:

```shell
brew install openssl
```
安装之后，还需要链接新的openssl到环境变量中。
After that, we will need to link the new OpenSSL into environment variables.
```shell
brew link --force openssl
```

## 安装Curl Curl Installation
Mac系统原装的Curl默认使用了Secure Transport，导致通过option函数设置的证书全部无效。果断重新安装之。
The Curl that comes with MacOS uses Secure Transport by default. That causes the invalid of all the certificates set by function 'option'. So reinstallation is a must.

```shell
brew install curl --with-openssl && brew link curl --force
```

## 安装PHP PHP Installation
PHP官网上下载某个版本的PHP（我选择的是5.6.22），使用如下命令编译安装。
Download a version of PHP from PHP.net (for my case, it's 5.6.22). Compile source codes to install by using following commands:

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
Here I filled up two specific options `with-openssl` and `with-curl`.
安装完成后，执行如下命令：
After installation, run following commands:

```shell
sudo cp /usr/local/php/bin/php /usr/bin/
sudo cp /usr/local/php/bin/phar* /usr/bin/
sudo cp /usr/local/php/bin/php-config /usr/bin/
sudo cp /usr/local/php/bin/phpize /usr/bin/
```

随后，设置php.ini
Then, we can configure php.ini:

```shell
sudo mkdir /etc/php
sudo cp php.ini.development /etc/php/php.ini
```

# Swoole扩展安装 Swoole Installation
[Swoole扩展下载地址 Download](https://github.com/swoole/swoole-src/releases)
解压源码至任意目录，执行如下命令：
Extact the codes to any directory and run following commands:

```shell
cd swoole-src-swoole-1.7.6-stable/
phpize
./configure
sudo make
sudo make install
```

> swoole的./configure有很多额外参数，可以通过./configure --help命令查看,这里均选择默认项)
> swoole's './configure' has got many optinal configurations. Check them out by using './configure --help'. 
Here we are just going to leave them default.

安装完成后，进入/etc/php目录下，打开php.ini文件，在其中加上如下一句：
Having finished all that, go to the path '/etc/php' and edit php.ini. Add the following line to the file:
```bash
extension=swoole.so
```
随后在终端中输入命令`php -m`查看扩展安装情况。如果在列出的扩展中看到了swoole，则说明安装成功。
All Done! Use `php -m` to list all the extensions and see if swoole is around.
